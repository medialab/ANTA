<?php
class dictionary{
	function __construct($dictionary_file){
        /* read file line by line */
		$handle = fopen($dictionary_file, "r");
 
		if ($handle) {
			while (!feof($handle)){
				$buffer = fgets($handle, 4096);
				$vars = explode(':',$buffer);
				$varName = trim( $vars[0] );
				if( count( $vars )!=2 ){
					if( strlen( $varName ) == 0 ) continue; 
					$this->$varName = "-";
					continue;
				}
				
				$this->$varName =  trim($vars[1]);
				$stemName = trim($vars[1]);
				
				if( !isset( $this->$stemName ) ){
				
					$this->$stemName = trim($vars[1]);
				}
			}
			fclose($handle);
		}
    }
}

class Stem extends dictionary{
    
	// var $STEM____not_yet_stemmed = array();
	var $____stopwords;
	// var $STEM____links = array();
	
	/** all the stemmings found */
	var $____stemmings = array();
	
	function __construct($dictionary_url){
        parent::__construct($dictionary_url);
		
    }
	
	function set_stopwords( $dictionary_url ){
		$this->____stopwords = new dictionary( $dictionary_url );
	}
	
	function get_stopwords(){
		return $this->____stopwords;
	}
	
	public function get_stem( $term ){
		$term_index = trim( strtolower( $term  ) );
		if(isset($this->$term_index)){
			return $this->$term_index;
		}
		return $term_index;
	}
	
	public static $min_strlen = 3;
	
	/**
	 * save the position in the sentence
	 */
	function get_stemmed_words( $words ){
		$words_stems = array();
		
		foreach( array_keys( $words ) as $k ){
		
			$word =& $words[ $k ];
			
			// limit word length
			if( strlen( $word ) < self::$min_strlen ) continue;
			
			// use word inde for comparison and variable names
			$word_index = trim( strtolower( $word  ) );
			
			// stopwords, if exists
			if( $this->____stopwords != null && $this->____stopwords->$word_index != null )	continue;
			
			// cerca se lo stemming esiste
			if( isset( $this->$word_index ) ) $words_stems[ $word_index ] = $this->$word_index;
			else $words_stems[ $word_index ] = $word;
		}
		
		return $words_stems;
	}
	
	/**
	* 
	* @param array of word, like the one returned by str_word_count($string, 1) function
	* @return an array where keys are stem of index.
	*
	*/
	function get_stemmed_dictionary( $words, $min_strlen=3 ){
		$stemmed = array();
		foreach( array_keys( $words ) as $k ){
			// extract single words stemmed
			$word =& $words[ $k ];
			
			// nidificate (es, sentences are array of words )
			if( is_array( $word ) ){
				$stemmed[] = $this->get_stemmed_dictionary( $word, $min_strlen );
				continue;
			}
			
			// limit word length
			if( strlen( $word ) < $min_strlen ) continue;
			
			// use word inde for comparison and variable names
			$word_index = strtolower( $word  );
			
			// stopwords, if exists
			if( $this->____stopwords != null && $this->____stopwords->$word_index != null )	continue;
			
			// cerca se lo stemming esiste
			if( !isset( $this->$word_index ) ) $this->$word_index = $word;
			
			// cerca se lo stemming é incluso nella tabella totale
			if( !isset( $this->____stemmings[ $this->$word_index ] ) ) {
				// echo $word.":".$word_index.":".$this->$word_index."\r\n";
				// store word into LOCAL result set
				$stemmed[ $this->$word_index ] = array( $k=>$word );
				
				// store word into the Stem object
				$this->____stemmings[ $this->$word_index ] = array( new word( $word, $k ) );
				
				// uncomment to trace back all the words NOT having stem
				// $this->STEM____not_yet_stemmed[] = $word;
				continue;
			}
			
			
			$stemmed[ $this->$word_index ][ $k ] = $word;
			array_push( $this->____stemmings[ $this->$word_index ], new word( $word, $k ) );
		}	
		
		return $stemmed;
	}
	
	/**
	 * return the number of word found for each stem.
	 */
	function get_stemmed_dictionary_stats( $limit = -1){
		// return $this->STEM____links;
		
		$a = array();
		foreach( array_keys( $this->____stemmings ) as $k ){
			// echo "[".$k."]".strlen($k)."\n";
			if(strlen($k)==0)continue;
			$stem =& $this->____stemmings[ $k ];
			$a[ $k ] = array( "n"=>count($stem), "words"=>$this->merge_words( $stem ) );//$this->multi_implode(",",$stem);
		}
		uasort( $a, array( "Stem", "sort_words" ) );
		
		return $a;
	}
	
	static function sort_words($a, $b){
        $al = $a[ 'n' ];
        $bl = $b[ 'n' ];
        if ($al == $bl) {
            return 0;
        }
        return ($al < $bl) ? +1 : -1;
    }
	
	private function merge_words( $words ){
		$ws = array();
		foreach( $words as $w ){
			$ws[] = utf8_encode($w->v);
		}
		
		return $this->array_iunique($ws);
		
	}
	function in_iarray($str, $a){
		foreach( array_keys($a) as $k){
			
		if(strcasecmp($str, $a[ $k ])==0){return true;}
		}
		return false;
	}

	function array_iunique($a){
	$n = array();
	foreach( array_keys($a) as $k){
		// $v =& ;
		if(!$this->in_iarray( $a[ $k ] , $n)){$n[$k]=$a[ $k ];}
	}
	return $n;
	}
	
	
	private function multi_implode( $glue, $pieces ){
		$string='';
		
		if(is_array($pieces)){
			
			reset($pieces);
			
			while(list($key,$value)=each($pieces)){
				$string.=$glue.$this->multi_implode($glue, $value);
			}
		} else {
			return $pieces;
		}
		
		return trim($string, $glue);
	}
}

class word{
	function __construct($w, $l){
		$this->v = $w;//$w = $l;
	}
	function __toString(){
		return $this->v;
	}
	
}

?>