<?php
/**
 * @package Anta_Utils
 */

/**
 * provide a way to compress various javascript files
 */ 
class Anta_Utils_Scriptify{
	
	private static $__zendCache;

	public static function getChache() {
		
		if( self::$__zendCache == null ){
			// créer un objet Zend_Cache_Core
			self::$__zendCache = Zend_Cache::factory( 'Core', 'File', 
				array(
					'lifetime' => 7200, // temps de vie du cache de 2 heures
					'automatic_serialization' => true
				), array( 
					'cache_dir' => APPLICATION_PATH. '/tmp/'
				)
			);
		}
		
		return self::$__zendCache;
		
	}

	/**
	 * return cache available static script javascript.
	 * A filename like "243903509509059094095.js" inside js/static
	 * use load function to generate the file if it is not available.
	 */
	public static function getStaticScript( $filename ){
		
		$filenames = func_get_args();
		
		$hash = md5( implode( $filenames ) );
		$script = APPLICATION_PATH. '/../public/js/static/'.$hash.'.js';
		
		//if( ! file_exists( $script ) ){
			file_put_contents( $script, self::load( $filenames  ) );
		//}
		
		return Anta_Core::getBase().'/js/static/'.$hash.'.js';
	}
	
	
	/** 
	 * read files from server, compress it and send to
	 * the client; use it directly into html, inside <script></script> tags.
	 * No cache support yet.
	 */
	public static function load( array $filenames ){
		
		$z = "";
		foreach( $filenames as $file ){
			//$myPacker = new Ui_JavaScriptPacker( @file_get_contents( APPLICATION_PATH."/../public/js/".$file ) );
			//$z .= $myPacker->pack();
			$z .= @file_get_contents( APPLICATION_PATH."/../public/js/".$file ).";";
		}
		return $z;
	}

}

?>