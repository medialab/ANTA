<?php
/**
 * @package Anta_Distiller
 */

/**
 * Base class for thread handler. provides method _log() protected to shorten log entry action.
 * Override execute() method.
 */
class Anta_Distiller_ThreadHandler{

	protected $_target;
	
	protected $_distiller;

	public function __construct( $target, Anta_Distiller $distiller ){
		$this->_target = $target;
		$this->_distiller = $distiller;
		// do the job
		$this->init();
	}
	
	public function getTarget(){
		return $this->_target;
	}
	
	public function init(){
		/** your function here */
	}
	
	protected function _log( $message, $breakLine=false ){
		Anta_Logging::append( $this->_distiller->log, $message, $breakLine );
	}
	
	public $error = null;
	
	protected function _error( $error ){
		$this->error = $error;
	}
	
	public function isValid(){
		return empty($this->error);
	}
	
	/**
	 *
	 * @return false or the url string pointing to an existent file
	 */
	protected function _verifyFileIntegrity( $user, $document ){
		$localUrl = Anta_Core::getLocalUrl( $user, $document->localUrl, true );
	
		// the file does not seem to be here
		if( ! file_exists( $localUrl ) ){
			$this->_log(  "file not found: ".$localUrl, false );
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $document->id, 'notfound' );
			$this->_log(  "status: notfound, "."changed: ".$affected, false );
			return false;
		}
		
		// try to create the .txt file if it does not exist
		if( $document->mimeType != "text/plain" ){
			if( $document->mimeType == "application/pdf" ){
				if( ! file_exists( $localUrl.".txt" ) ){
					exec( "pdftotext -q -nopgbrk -eol unix ".$localUrl." ".$localUrl.".txt ");
				}
				if( ! file_exists( $localUrl.".txt" ) ){
					$this->_log(  "unable to pdftotext:".$localUrl, false );
					$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $document->id, 'nottxt' );
					
					return false;
				}
				
				// return the txt version of the localUrl
				$localUrl = $localUrl.".txt";
			} else{
				$this->_log(  "warning: ".$document->mimeType." mimetype format not supported...".$localUrl, false );
				$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $document->id, 'notsup' );
				return false;
			}
		}
		return $localUrl;
	}
	
	/**
	 * It uses Nltk PunktTokenize via python script to tokenize the given textfile into sentences.
	 * save the result of the py script ( a json ) into the database.
	 * You can use this function inside the init() method.
	 *
	 */
	protected function _sentencesPunktTokenize(){
	
		$doc  =& $this->_target;
		$user =& $this->_distiller->user;
		
		# the unique url
		$localUrl = Anta_Core::getDocumentUrl( $user, $doc );
		
		
		# verify file existance
		if( ! file_exists( $localUrl ) ) {
			$this->_log(  "file not found: ".$localUrl, false );
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'notfound' );
			$this->_log(  "status: notfound, "."changed: ".$affected, false );
			return;
		}
		
		
		$language = Anta_Core::getlanguage( $doc->language );
		
		
		
		# call the script
		$py = new Py_Scriptify( "sentencesTokenizer.py $localUrl $language", true, false );
		
		# read the result, json
		$this->_log(  "executing punkt tokenizer: sentencesTokenizer.py ".basename($localUrl)." $language", false );
		
		# read the sentences tokenized
		$sentences = $py->getJsonObject();
		if( $sentences == null ){
			$this->_log( "...unable to understand python result ( invalid json input )!", false );
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'error' );
			$this->_log(  "status: error, "."changed: ".$affected, false );
			return;
		};
		
		# get the errors...
		if( $sentences->status != "ok" ){
			echo $sentences->status. " ".$sentences->error  ;
			$this->_log( "...unable to find sentences!", false );
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'error' );
			$this->_log(  "status: error, "."changed: ".$affected, false );
			return;
		}
		
		$this->_log( count($sentences->sentences) . " sentences found, updating database...", false );
		
		#clean the sentences
		Application_Model_SentencesMapper::cleanSentences( $user, $doc->id );
		
		#put the sentences into the database
		foreach( array_keys( $sentences->sentences ) as $i ){
			$sentence =& $sentences->sentences[ $i ];
			$affected = Application_Model_SentencesMapper::addSentence( $user, $doc->id, $i, $sentence );
			if ($affected == 0 ){
				$this->_log( "error at sentence $i, content: {$content}, skipping...", false );
			}
			
		}
		$this->_log( "sentences saved", false );
		
	}
	
	/**
	 * helper function to chunk and save sentences into the database
	 */
	protected function _chunkIntoSentences( ){
	
		$doc  =& $this->_target;
		$user =& $this->_distiller->user;
		
		//todo: sobstitute with $this->_verifyFileIntegrity();
		// check txt file existance
		$localUrl = Anta_Core::getLocalUrl( $user, $doc->localUrl, true );
	
		// local start time
		$startTime = microtime( true );
		
		// the file does not seem to be here
		if( ! file_exists( $localUrl ) ){
		
			$this->_log(  "file not found: ".$localUrl, false );
			
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'notfound' );
			
			$this->_log(  "status: notfound, "."changed: ".$affected, false );
			return;
		}
		
		// try to create the .txt file if it does not exist
		if( $doc->mimeType == "application/pdf" ){
			if( ! file_exists( $localUrl.".txt" ) ){
				exec( "pdftotext -q -nopgbrk -eol unix ".$localUrl." ".$localUrl.".txt ");
			}
			if( ! file_exists( $localUrl.".txt" ) ){
				$this->_log(  "unable to pdftotext:".$localUrl, false );
				
				$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->_user, $doc->id, 'nottxt' );
				
				return;
			}
			
			// return the txt version of the localUrl
			$localUrl = $localUrl.".txt";
		}
	
		/**
		 *
		 * Sentences and text compression
		 *
		 */
	 
		// compress text
		$text = file_get_contents( $localUrl );
		$textSize = strlen( $text );
	 
		// split into sentences
		$sentences = Textopoly_Alquemy::chunkSentences( $text );

		$this->_log(  "content strlen: ". $textSize.", after chunking: ".Textopoly_Alquemy::$lastLength, false );	
	 
		// text chunking time 
		$this->_log(  "after chunks, elapsed: ". ( microtime( true ) - $startTime ). ', sentences: '.count( $sentences ), false );	
		
		
	
		// save sentences for the given document ( clean before all
		$previousSentences = Application_Model_SentencesMapper::cleanSentences( $user, $doc->id );
		
		foreach( array_keys( $sentences ) as $k ){
			$idSentence = Application_Model_SentencesMapper::addSentence( $user, $doc->id, $k, $sentences[ $k ] );
		}
		
	
	 
		// saving sentences time 
		$this->_log(  "after saving chunks, elapsed: ". ( microtime( true ) - $startTime ) , false );	
	 
	}
}
?>
