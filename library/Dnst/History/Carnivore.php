<?php
/**
 * @package Dnst_History
 */

/**
 * Handle session history varialbes. Use sniff() static function to store 
 * current REQUEST_URI into session variables. use the backBefore static function
 * to retrieve the first past entry before a certain request uri.
 * Note: use sniff() function at the very beginning ( the bootstrap.php file is a nice place to do so.
 * 
 * usage: 
 *   // to sniff REQUEST_URI
 *   History_Carnivore::sniff();
 * 
 *   // to retrieve the last request uri before a certain address
 *   History_Carnivore::backBefore( '/index/login
 * 
 * @author Daniele Guido
 */
class Dnst_History_Carnivore{
	
	public static function toString(){
		print_r( $_SESSION[ 'history' ] );
	}
	
	public static function sniff(){
		
		if(! isset( $_SESSION[ 'history' ] ) ){
			$_SESSION[ 'history' ] = array();
		}
		
		// limit history flow
		$limit = 3;
		
		// filter images and other link
		if( strrpos( $_SERVER[ 'REQUEST_URI' ], "." ) ) return;
		
		// filter by stuff
		if ( stripos(  $_SERVER[ 'REQUEST_URI' ], ANTA_URL.'/api'  ) !== false ) return;
		
		
		$desiredUri =& $_SERVER[ 'REQUEST_URI' ];
		
		
		// ignore and exit if the last element is the same
		if( $desiredUri == end( $_SESSION[ 'history' ])) {
			return;
		}
		
		
		// add actual page
		$_SESSION[ 'history' ][] = $desiredUri;
		
		// delete oldest page
		if ( count( $_SESSION[ 'history' ] ) > $limit ){
			// copy history
			$history = $_SESSION[ 'history' ];
			//apply splice
			$_SESSION[ 'history' ] = array_splice( $history, count( $history ) - $limit );
		}
		
	}
	
	/**
	 * get a look into the history flow in session. 
	 * Clean the session history
	 */
	public static function backBefore( $beginning ){
		
		
		$matching = false;
		
		// back in time
		for( $i = count( $_SESSION[ 'history' ] ) - 1; $i >=0; $i-- ){
			// the first not matching beginning
			if( $matching ) return $_SESSION[ 'history' ][ $i ];
			
			if( stripos( $_SESSION[ 'history' ][ $i ], $beginning  ) !== false ) {
				$matching = true;
			}
		}
		
		return $matching? $_SESSION[ 'history' ][0]: '/';
	}
	
	
	/**
	 * store a session variable, given an identifier. If a session variables exists
	 * it will be replaced. Added "carn-store-" prefix to the identifier to avoid misunderstanding in reading
	 * session global vars.
	 */
	public static function store( $identifier, $var ){
		$_SESSION[ 'carn-store-'.$identifier ] = $var;
	}
	
	/**
	 * return the session stored var and unset it.
	 * Do not use the prefix carn-store- in the identifier string.
	 */
	public static function consume( $identifier ){
		if( isset ( $_SESSION[ 'carn-'.$identifier ] ) ){
			$temporary = $_SESSION[ 'carn-'.$identifier ];
			unset( $_SESSION[ 'carn-'.$identifier ] );
			return $temporary;
		}
		return null;
	}
}
