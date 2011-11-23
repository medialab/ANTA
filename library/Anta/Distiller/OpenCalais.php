<?php
/**
 * @package Anta_Distiller
 */
/**
 * index stemming
 */
class Anta_Distiller_OpenCalais extends Anta_Distiller_ThreadHandler{
	
	public function init(){
		
		$document =& $this->_target;
		$user =& $this->_distiller->user;
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "services" );
		
		// 1. load alchemy configuration
		$this->_log( "curl OpenCalais: ".$config->opencalais->api->rest, false );
		
		// 2. load sentences
		$sentences = Application_Model_SentencesMapper::getSentences( $user, $document->id );
		$amountOfSentences = count( $sentences );
		
		$this->_log( "sentences stored into database: ". $amountOfSentences, false );
		
		if( $amountOfSentences == 0 ){
			// break chain, there isn't any sentence
			$this->_log( "no sentences saved into database, then chunk...", false );
			
			$this->_chunkIntoSentences();
			$sentences = Application_Model_SentencesMapper::getSentences( $user, $document->id );
			$amountOfSentences = count( $sentences );
			$this->_log( "extracted $amountOfSentences sentences", false );
		}
		
		// reset chunk
		$chunk = "";
	 
		// reset the value
		$chunksLength = 0;
		
		$startTime =  microtime( true );
		
		for( $i = 0; $i < count( $sentences ); $i++ ){
			
			if( strlen( $chunk ) + strlen( $sentences[ $i ]->content )  > 50000 ){
		
				$this->_log ( "chunk: ". strlen( $chunk ), false );	
				
				// increment chunks length
				$chunksLength += strlen( $chunk );
				
				// call please
				$result = $this->openCalaisRoutine( $chunk, $config );
				
				if( $result === false ){
					Application_Model_DocumentsMapper::changeDocumentStatus( $user, $document->id, 'incomple' );
					$this->_log (  "openCalais failed", false );
					return;
				};
				
				// call please
				$this->_log (  "after openCalais, elapsed:".( microtime( true ) - $startTime ), false );
				
				
				// pause
				usleep( mt_rand ( 4000000 , 6000000 )  );
				
				// unset chunk & json
				$chunk = "";
				$jsonResponse = null;
			}
		
			// queue sentences
			$chunk .= $sentences[ $i ]->content.". " ;
		}
		// the last round
		$chunksLength += strlen( $chunk );
		
		$result = $this->openCalaisRoutine( $chunk, $config );
				
		if( $result === false ){
			Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'incomple' );
			return;
		};
	}
	
	
	protected function openCalaisRoutine( $chunk, $config ){
	
		$doc  =& $this->_target;
		$user =& $this->_distiller->user;
		
		$openCalais = new Textopoly_OpenCalais( $config->opencalais->api->rest, array(
			"outputMode"   => "json",
			"text"         => $chunk,
			"content-type" => "text/txt",
			"api-key"      => $config->opencalais->api->key
		));

		Application_Model_QuotasMapper::addQuota( 'OC', strlen($chunk), $openCalais->getResponseLength() );
		
		// if has error, the doument is set to incomplete
		if( $openCalais->hasError() ){
			$this->_log(  "opencalais api error, error string:".$openCalais->getError(), false );
			return false;
		}
		
		// the get method returns a json
		$jsonResponse = $openCalais->get();	

		// read OpenCalais meta messagesz
		if( $jsonResponse->meta->messages != null ){
			foreach( $jsonResponse->meta->messages as $message ){
				$this->_log(  "opencalais message received ".json_encode( $message ), false );
				return false; // set document to error
			}
		}
	
	
		// log number of OPENCALAIS entities found
		$this->_log(  "opencalais found ". count( $jsonResponse->entities ). " entities", false );
		
		foreach( $jsonResponse->entities as $entity ){
			$idEntity = Application_Model_EntitiesMapper::addEntity(
				$user,  $entity->text, $doc->language
			);
			
			if( $idEntity == 0 ){
				$this->_log( $entity->text . ": ". $idEntity );
				continue;
			}
			
			Application_Model_EntitiesOccurrencesMapper::add( $user, $doc->id, $idEntity, $entity->type, $entity->relevance, 'OC');
		}
		
	
	}
		
}
?>
