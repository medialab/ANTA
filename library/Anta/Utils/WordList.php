<?php
/**
 * @package Anta_Utils
 */
 
/**
 * usage
 *  usage
 * <code>
 *   // simply an array uniwue of a str word count    
 *   $wl = new Anta_Utils_WordList( "profile of something" )
 *   print_r( $wl );
 *
 *   // use stemming capabilities, english dictionary 
 *   $wl->applyStem( "en" );
 *   print( $wl );
 *
 * </code>
 */
class Anta_Utils_WordList{
	
	public static $pattern = "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u";
	
	public $words;
	
	/**
	 * @param string list	- text of words
	 */
	public function __construct( $list ){
		if( !is_array( $list ) ){
			$this->words = array_unique( self::str_word_count_utf8( $list, 1 ) );
		} else{
			$this->words = array_unique($list );
		}
		
	}

	public static function str_word_count_utf8($string, $format = 0)
    {
        switch ($format) {
        case 1:
            preg_match_all(self::$pattern, $string, $matches);
            return $matches[0];
        case 2:
            preg_match_all(self::$pattern, $string, $matches, PREG_OFFSET_CAPTURE);
            $result = array();
            foreach ($matches[0] as $match) {
                $result[$match[1]] = $match[0];
            }
            return $result;
        }
        return preg_match_all(self::$pattern, $string, $matches);
    }
	

	public function applyStem( $language ){
		$this->raw = $this->words;
		$stems  = array();
		
		$stemFunction = "stem_" . Anta_Core::getLanguage( $language ); 
	
		foreach( $this->words as $word ){
			$stems[] = call_user_func( $stemFunction, $word );
			
		}
		$this->words =$stems;
	}
	
	
}
?>
