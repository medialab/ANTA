<?php
/**
 * @package Dnst
 */
 
/**
 * It's a Paginator. It's a FilterHandler.
 * sample usage
 *	
 * 	if( Dnst_Filter::exists() ){
 *
 *		Dnst_Filter::setValidProperties( array (
 *			"offset", "limit", "query",	"order"
 *   	));
 * 
 *		Dnst_Filter::setValidators( array (
 *			"order" => new Dnst_Filter_Validator_Array( Application_Model_EntitiesMapper::validOrderBy )
 *		));
 * 		
 *		echo Dnst_Filter::read();
 *			
 *		echo Dnst_Filter::create("offset"=>100);
 *	}
 */
class Dnst_Filter{

	public static $namespace = 'filters';

	protected static $_filter;

	/**
	 * array of required properties. If the filter dopes not contain one of these properties,
	 * throw an exception.
	 * e.g array("offset", "order")
	 */
	protected static $_required;
	
	/**
	 * array of errors names
	 */
	protected static $_errors;

	/** 
	 * array of properties and related validators for properties values
	 * @var array
	 */
	protected static $_validators;
	
	/**
	 * change the http get param var to look for
	 */
	public static function setNamespace( $namespace ){
		self::$namespace = $namespace;
	}
	
	protected static $_validationResult;
	
	/**
	 * @param array intitalProperties	- the default object, with required properties
	 */
	public static function start( array $intitalProperties, array $validators = array() ){
		# initialize
		self::$_filter = (object) $intitalProperties;
		
		
		# required properties
		self::$_validators = $validators;
		
		// check again for the validity...and for default REQUEST filter!
		self::$_validationResult = self::isValid();
		return self::$_validationResult;
	}
	
	/**
	 * jquery style extend, merges arrays (without errors if the passed values are not arrays)
	 *
	 * @return array $extended
	 **/
	public static function extend( $settings, $options ) {
		// differences
		return array_merge(
			get_object_vars($options),
			get_object_vars($settings) 
		);
	}
	
	/**
	 * check the filter var existance
	 */
	public static function exists(){
		return isset( $_GET[ self::$namespace ] );
	}
	
	public static function getErrors(){
		return empty(self::$_errors)? "": implode( self::$_errors );
	}
	
	
	/**
	 * Check that the get param filter contains valid properties. Note that it's a PRIVATE method
	 * because you must use read static method to access properties. This method will do the dirty job for you.
	 * Otherwise, output a nice error ( Zend_exception )
	 *
	 * @return boolean true or false
	 */
	public static function isValid(){
		
		if( self::$_validationResult != null ){
			return self::$_validationResult;
		}
		
		# check the request query string
		if( ! self::exists() ){
			# keep the default params
			return true;
		}
		
		# read the the request query string
		$filter = json_decode( stripslashes( $_GET[ self::$namespace ] ) ); 
		
		# check its "json" validity
		if( $filter == null ){
			throw( new Zend_Exception( I18n_Json::get('filter is not a valid json object' ,'errors') ) );
			return false;
		}
		
		# extends default properties
		if( !empty( self::$_filter ) ) {
			$filter = (object) self::extend( $filter, self::$_filter );
		}
		
		# cycle through filter properties name to search for similar url params (override)
		foreach( $filter  as $property=>$value ){
			if( !isset( $_REQUEST[ $property ] ) ) continue;
			$filter ->$property = $_REQUEST[ $property ];
		}
		
		# validate entries with given validator
		if( self::$_validators != null ){
			
			foreach( array_keys( self::$_validators ) as $field ){
				
				if ( ! isset( $filter ->$field ) ) {
					continue;
				}
				
				if(! self::$_validators[ $field ]->isValid( $filter->$field ) ){
					self::$_errors = self::$_validators[ $field ]->getMessages();
					// throw( new Zend_Exception( end( self::$_validators[ $field ]->getMessages() ) ) );
					return false;
				}
				
			}
			
		}
		
		self::$_filter = $filter;
		return true;
		
	}
	
	/**
	 * set or replace the actual property with the given value
	 */
	public static function replaceProperty( $property, $value ){
		self::$_filter->$property = $value;
	}
	
	public static function addProperty( $property, $value ){
		// let's make a copy
		$filter = clone self::$_filter;
		//if the property is not set, set directly
		if( $filter->$property == null || !is_array( $filter->$property ) ){
			$filter->$property = $value;
			return self::$namespace.'='.self::toString(  $filter );
		}
		array_push( $filter->$property, $value);
		
		return self::$namespace.'='.self::toString(  $filter );
	}
	
	public static function prependProperties( array $properties ){
		return self::appendProperties( $properties, true );
	}
	
	/**
	 * the same as addProperty for multiple values.
	 *
	 * @param array properties	- couples of name-value e.g. array("limit"=>100)
	 */
	public static function appendProperties( array $properties, $prepend= false ){
		// let's make a copy
		$filter = clone self::$_filter;
		foreach( $properties as $property=>$value ){
			//if the property is not set, set directly
			if( $filter->$property == null ){
				
				$filter->$property = $value;
				continue;
			}
			// substitute with the given value
			//if( !is_array( $filter->$property ) ){
				$filter->$property = $value;
				continue;
			//}
			
			if( !is_array( $value ) ){
				//if( $prepend ) array_unshift( $filter->$property, $value );
				//else 
				array_push( $filter->$property, $value );
				$filter->$property = array_values(  $filter->$property );
			} else {
				// if value is an array, merge the values
				$filter->$property = $prepend? array_merge ( $value, $filter->$property ): array_merge ( $filter->$property,  $value );
				$filter->$property = $filter->$property;
			}
			
			$filter->$property = array_unique( $filter->$property );
			
		}
		return self::$namespace.'='.self::toString(  $filter );
	}
	
	/**
	 * add a value to a given property, to a COPY of the actual $filter. !
	 * Use *after* calling the isValid method...
	 * If the value is an array, the function will merge the new array with the previous one: if arrays
	 * have the same string keys, then the later value for that key will overwrite the previous one.
	 * 
	 */
	public static function setProperty( $property, $value, $opposite = null){
		// let's make a copy
		$filter = clone self::$_filter;
		
		
		//f the property is not set, set directly
		if( $filter->$property == null ){
			$filter->$property = $value;
			return self::$namespace.'='.self::toString(  $filter );
		}
		
		// if property does not exist, create it and return...
		//if (! is_array( $value) ){
			$filter->$property = $filter->$property  == $value && $opposite != null? $opposite: $value;
			return self::$namespace.'='.self::toString(  $filter );
		//} else {
			// merge with previous one 
			$filter->$property = array_unique( array_merge( $filter->$property, $value ) );
			return self::$namespace.'='.self::toString(  $filter );
		// }
		
		// property is an array (the value is an array, so it must be treated as an array
		// opposite is not set
		if( empty( $opposite ) ){
			// @todo...
			return self::$namespace.'='.self::toString(  $filter );
		}
		
		
		// value is into the array
		if( in_array( end( $value ), $filter->$property ) ){
			
			$filter->$property = array_merge( array_diff( $filter->$property, $value ), $opposite );
		
		} else if ( in_array ( end( $opposite ), $filter->$property ) ){
			// click to set value; reclick to toggle to the opposite; click again to delete
			$filter->$property = array_values( array_diff( $filter->$property, $opposite ) );	
		
		} else {
			// neither value or opposite are into the property array
			$filter->$property = array_merge( $filter->$property, $value );
		}
		
		return self::$namespace.'='.self::toString(  $filter );
		
		
	}
	
	public static function getProperty( $property ){
		return self::$_filter->$property;
	}
	
	public static function hasProperty( $property, $value ){
		if( !isset( self::$_filter->$property ) ) return false;
		if( empty( $value ) ) return true;
		// handle multiple values (OR)
		if( is_array( $value ) ){
			if( is_array(  self::$_filter->$property ) ){
				return count( array_intersect( $value, self::$_filter->$property ) ) > 0;
			}
			return array_search(  self::$_filter->$property, $value ) !== false;
		}
		if( is_array(  self::$_filter->$property ) ) return in_array( $value, self::$_filter->$property );
		return  self::$_filter->$property == $value;
		
	}
	
	/**
	 * If a $default value exists into $property, then return the $alternate value and viceversa.
	 * If no property has been found, return the first value $default
	 */
	public static function toggleProperty( $property, $default, $alternate ){
		if( !self::hasProperty( $property, $default ) ){
			return $default;
		}
		if( self::hasProperty( $property, $default ) ) return $alternate;
		return $default;
	}
	
	
	public static function remove( $property, $value =array() ){
		// let's make a copy
		$filter = clone self::$_filter;
		
		// if property does not exist, create it and return; or, if the value provided is not an array, create/ override
		if ( $filter->$property == null  ){
			return self::$namespace.'='.self::toString(  $filter );
		
		}
		if( empty( $value ) ){
			unset ($filter->$property);
		}
		
		if( is_array( $filter->$property ) ){
		
			$filter->$property = array_values( array_diff( $filter->$property, array( $value ) ));
		}
		
		return self::$namespace.'='.self::toString(  $filter );
		
		
	}
	
	
	/**
	 * This function call isValid static method
	 * 
	 */
	public static function read(){
		return self::$_filter;
	}
	
	/**
	 * This function accepts an array of properties => validator.
	 * Note that the validators may be instances of Zend_Validate_Abstract.
	 * 
	 * @param validators	- array of couples paroperties=>validator
	 */
	public static function setValidators( array $validators ){
		self::$_validators = $validators;
	}
	
	/**
	 * create a query param filter=[offset:10,limit:'...']
	 * by using the desired values
	 */
	public static function create( array $properties ){
		// return json_encode( $properties );
		
		
		
	}
	
	public static function toString( $properties ){
		return urlencode( json_encode ( $properties ) );
	}
	
	// add a filter chain (abstract validator!) to validate the filter
	public static function hook( $filter ){
		
	}
}
