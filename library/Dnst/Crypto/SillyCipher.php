<?php
/**
 *@package Dnst_Crypto
 */

/**
 * Symmetric simple cipher
 */
class Dnst_Crypto_SillyCipher{
	
	static $_alphabet    = "0123456789bcdfgjklmnpqrstvxywzaeiou";
	static $_permutation = "lo84s0c5pzxun1r3wqet9fykd67ijm2agbv";
	
	public static function crypt( $string ){
		
		$result = "";
		
		// force string conversion
		$string = ''.$string;
		
		// the first letter provide the starting point
		$shift = mt_rand( 0, strlen( self::$_permutation ) - 1 );
		
		// use the shift to reorganize the permutation
		$secret = substr( self::$_permutation, $shift ).substr( self::$_permutation, 0, $shift );
		
		// generate indexed alphabet		
		$alphabet = array_flip( str_split( $secret ) );
		
		for( $i = 0 ; $i < strlen( $string ); $i++ ){
			$result.= self::$_permutation{ $alphabet[ $string{$i} ] };
		}
		
		return self::$_permutation{$shift}.$result;
	}
	
	
	public static function decrypt( $string ){
		if( strlen( $string ) < 2 ) return '';
		$result = "";
		
		// force string conversion
		$string = ''.$string;
		
		$alphabet = array_flip( str_split( self::$_permutation ) );
		$shift = $alphabet[ $string{0} ];
		
		$secret = substr( self::$_permutation, $shift ).substr( self::$_permutation, 0, $shift );
		
		// decypher
		for( $i = 1 ; $i < strlen( $string ); $i++ ){
			
			$result.= $secret{ $alphabet[ $string{$i} ] };
		}
		
		
		return $result;
		
		// shift according to the first letter
		
	}
	
	/**
	 * Utils to hide email address
	 */
	public static function hide( $email ){
		return "*****".substr( $email, strrpos( $email, '@' ));
	}
}
 
 

?>
