<?php
/**
 * @package Anta_Distiller
 */
/**
 * index stemming
 */
class Anta_Distiller_Indexer extends Anta_Distiller_ThreadHandler{
	
	public function init(){
		$document =& $this->_target;
		$user =& $this->_distiller->user;
		
		$language = Anta_Core::getLanguage( $document->language );
		
		$this->_log ("document language: ".$language);
		
		$stopwords = new Textopoly_Dictionary( "stopwords-{$language}.txt" );
		
		$this->_log ("stopword dictionary: ".$stopwords);
		
		$sentences = Application_Model_SentencesMapper::getSentences( $user, $document->id );
		
		$this->_log( "sentences found: ".count( $sentences ) );
		
		if( count( $sentences ) == 0 ){
			$this->_chunkIntoSentences();
			$sentences = Application_Model_SentencesMapper::getSentences( $user, $document->id );
			$this->_log( "sentences found, stored into database: ".count( $sentences ) );
		}
		
		// clear co occurrences...
		$removed = Application_Model_TermsMapper::removeCoOccurrences( $user, $document->id );
		
		$this->_log( "existing cooccurrences removed: ".$removed );
		$this->_log( "indexing sentences: " );
		
		$countCoOccurrences = 0;
		for( $i = 0; $i < count( $sentences ); $i++ ){
			$row = $sentences[ $i ];
			if( isset( $_GET['debug'] ) ) echo $row->content;
			// clear occurrences...
			echo Application_Model_TermsMapper::removeOccurrences( $user, $row->id_sentence );
			
			
			// words in each sentence
			$words = Anta_Utils_WordList::str_word_count_utf8( $row->content,1);
			
			if( isset( $_GET['debug'] ) ) print_r( $words);
			$realWords = array();
			
			foreach( array_keys( $words ) as $k ){
				$term =& $words[ $k ]; 
				
				// at least 3 letters per words?
				if( strlen( $term ) < 3 ) continue;
				
				// stopping by stem language
				if( $stopwords->exists( $term ) ) continue;
				
				// PECL stem function according to language used
				$stem = call_user_func( 'stem_'.$language, $term );
				// $stem = stem_english( $term );
				
				// add term of the stored word... in the occurrences table, not term...
				// Application_Model_TermsMapper::addTerm( $user, $term, $stem, $document->language );
				Application_Model_TermsMapper::addOccurrence(  $user,  $document->id, $row->id_sentence, $term, $stem );
	
				// save the couple term stem in a table
				$realWords[] = array( 't'=>$term, 's'=>$stem );
			}
			if( isset( $_GET['debug'] ) ) print_r( $realWords);
			// cycle through the array of words
			for( $j = 0; $j < count( $realWords ); $j++ ){
				
				// get matrix right half of cooccurrences
				for( $k = $j+1; $k < count(  $realWords ); $k++ ) {
					
					// save cooccurrence and distance.
					// @todo extablish a distance when they're separated by a comma?					
					$countCoOccurrences  += Application_Model_TermsMapper::addCoOccurrence(
						$user,
						$document->id,
						$row->id_sentence,
						$realWords[ $j ][ 't' ],
						$realWords[ $j ][ 's' ],
						$realWords[ $k ][ 't' ],
						$realWords[ $k ][ 's' ],
						$k - $j
					);
				}
			}
			
			
			if( isset( $_GET[ 'debug' ] ) ) break;
		}
		
		$this->_log("sentences: ".count( $sentences ) );
		$this->_log("cooccurrences saved: ".$countCoOccurrences );
			
	}
	
	
	
}
?>
