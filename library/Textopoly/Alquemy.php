<?php
/**
 * @package Textopoly
 */
 
/**
 * Handle alchemy api and provide some useful methods
 */
 class Textopoly_Alquemy{
	
	public static function howDay(){
		return "eeeee";
	}
 
	public static $lastLength = 0;
 
	/**
	 * Split the text given into sentences, then
	 * split each sentence into words if stem is found into the sentence.
	 * usage:
	 *
	 * $stem = "secur";
	 * $text = "No one. The European initiative for improving the security of maritime borders."
	 * $sentences_containing_stems = alquemy::get_sentences( $text, $stem ); 
	 *
	 * // sentences should be s1="No one" and s2="The European initiative [...]",
	 * // but stem chain of character was found only into s2.
	 * 
	 * Array
	 * (
	 * [0] => Array
     *   (
     *       [0] => The
     *       [1] => European
     *       [2] => initiative
     *       [3] => for
     *       [4] => improving
     *       [5] => the
     *       [6] => security
     *       [7] => of
     *       [8] => maritime
     *       [9] => borders
     *   )
	 * )
	 * @param text - the text to be chunked
	 * @return array of selected sentences
	 */	
	public static function chunkSentences( $text, $str_word_count = false ){
		
		$length = 0;
	
		$text = preg_replace("/[\t]+/"," ",$text);            
		$text = preg_replace("/\n\s+\n/", "\n\n", $text);
		$text = preg_replace("/[\n]{3,}/", "\n\n", $text);

		$sentences=array();
		$a = preg_split( "/\n\n/", $text );
		
		foreach ( array_keys($a) as $k ) {
			$b =& $a[$k];
			
			$b = preg_replace("/http:\/\/(.*?)[\s\)]/", "", $b);
			$b = preg_replace("/http:\/\/([^\s]*?)$/", "", $b);
			$b = preg_replace("/\[\s*[0-9]*\s*\]/", "", $b);		
			
			foreach ( preg_split('/\./', $b) as $sent){
				
				$onlyLetters = preg_replace('/[\s\t]+/', '', $sent );
				
				if( is_numeric( $onlyLetters ) ) continue;
				
				if( strlen( $onlyLetters ) < 9 ) continue;
				
				$length += strlen( $sent );
				
				array_push( $sentences, $sent );
					
				
				if( $str_word_count === false ) continue;
				
				
				/** strip sentence into words */
				array_push($sentences, str_word_count($sent, 1));
			}
		}
		
		self::$lastLength = $length;
		return $sentences;
	}
 }

?>