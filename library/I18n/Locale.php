<?php
/**
 * @package i18n
 */
 
 /**
  * Handle language identification. use a subclass to handle various i18n types. To internationalize your zend application in a custom way, add a messages file under a directory
  * named your-zend-project/public/locale/localeCode, where localeCode is a code such as en for English.
  * To change language, simply use the 'lang' param via http get...
  * 
  * It uses the Zend_config; to set the path correctly, in your application.ini file,
  * add locale section:
  * 
  *   [locale : production]
  *   i18n.i18nPath = APPLICATION_PATH "/../public/locale"
  * 
  * sample usage,  when  your-zend-project/public/locale/localeCode/en/messages.json
  * contains a 'madeByDD':'made by densitydesign' vars couple.: 
  * 
  *   $string = I18n_Json::get( 'madeByDD', 'messages' );
  *   
  *   echo $string;
  *   // 'made by densitydesign'
  * 
  *
  * @author Daniele Guido - densitydesign.org
  */
 class I18n_Locale{
	 
	 static $locale;
	 
	 
	 /** the language to search for, accepted values are folder name under /public/locale */
	 public $language = 'en';
	 
	 /** to use this path, read constructor method */
	 public $languagePath = '';
	 
	 /** errors found */
	 protected $_errors = array();
	 
	 /**
	  * Singleton static function 
	  * @return I18n_Locale instance
	  */
	 public static function getInstance(){
		if( self::$locale == null ){
			if( !function_exists('get_called_class') ){
				$subClass = "I18n_Json";
			
			} else {
				$subClass = get_called_class();
			
			}
			self::$locale = new $subClass();
		}
		return self::$locale;
		
	 }
	
	
	/**
	 * auto check language changes: e.g. lang=en
	 */
	public function __construct(){
		 
		 // load ini config file
		 $config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "locale" );
		 
		 // check if locale dir exists
		 if ( ! file_exists( $config->i18n->i18nPath ) ){
			$this->addError( "'". $config->i18n->i18nPath ."' folder was not found. Check this before using this class" );
		 }
		 
		 // check if a *valid* get request exists.
		 if( isset( $_GET[ 'lang' ] ) && strlen( $_GET[ 'lang' ] ) == 2 && strspn( $_GET[ 'lang' ], 'abcdefghijklmnopqrstuvwxyz' ) == 2 ){
				
				// try to change locale folder and save session as well
			    if( file_exists( $config->i18n->i18nPath . "/" . $_GET[ 'lang' ] ) ) {
					$this->language = $_GET[ 'lang' ];
					$_SESSION[ 'lang' ] = $_GET[ 'lang' ];
				} else {
					$this->addError( "'" . $_GET[ 'lang' ] . "' language folder was not found" ); 
				}
		} else if ( isset( $_SESSION[ 'lang' ] ) && strlen(  $_SESSION[ 'lang' ] ) == 2 && strspn(  $_SESSION[ 'lang' ], 'abcdefghijklmnopqrstuvwxyz' ) == 2 ){
			// check session
			// try to load locale folder
			if( file_exists( $config->i18n->i18nPath . "/" . $_SESSION[ 'lang' ] ) ) {
				$this->language = $_SESSION[ 'lang' ];
			} else {
				$this->addError( "'" . $_SESSION[ 'lang' ] . "' language folder was not found" ); 
			}
		}
		 
		 $this->languagePath = $config->i18n->i18nPath . "/" .$this->language;
		 
	 }
	 
	 public function addError( $error ){
		 if( $this->_errors == null ) $this->_errors = array();
		 $this->_errors[] = $error;
	 }
	 
	 protected function _translation(  $identifier, $namespace ){
		 return "You need to extends i18n_Locale";
	 }
	 
	 public static function get( $identifier, $namespace='messages' ){
	
		$instance = self::getInstance();
		
		$translation = $instance->_translate( $identifier, $namespace );
		
		if ( count( $instance->_errors ) > 0 ){
			
			return end($instance->_errors);
		}
		
		return $translation;
	 }
	 
 }

?>