<?php
 /**
  * @package Textopoly
  * external library
  */
/**
 * $texto = new Textopoly_OpenCalais( $url, array(
 *		"outputMode"   => "json",
 * 		"text"         => 'text',
 * 		"content-type" => "text/txt",
 * 		"apikey"       => "your api key"
 * ));
 *       
 *  echo $texto->get();
 */
class Textopoly_OpenCalais extends Textopoly_Plugin {
	
	/**
	 * Class constructor
	 */
	public function __construct( $url, array $params ){
		$this->_url = $url;
		parent::__construct( $params );
	}
	
	protected function _call(){
		$paramsXML = '<c:params xmlns:c="http://s.opencalais.com/1/pred/" '.
				'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'.
			 '<c:processingDirectives c:contentType="'.$this->getParam( 'content-type' ).'" '.
				'c:enableMetadataType="GenericRelations,SocialTags,Entities" '.
				'c:outputFormat="application/json" c:docRDFaccesible="false" c:calculateRelevanceScore="true"></c:processingDirectives> '.
			 '<c:userDirectives c:allowDistribution="false" c:allowSearch="false" '.
				'c:externalID=" " c:submitter="frontex"></c:userDirectives> '.
			 '<c:externalMetadata><c:Caller>Calais REST Sample</c:Caller></c:externalMetadata></c:params>';
		
		// Construct the POST data string
		$data = "licenseID=".$this->getParam( 'api-key');
		$data .= "&paramsXML=".urlencode($paramsXML);
		$data .= "&content=".urlencode( self::compress( $this->getParam( 'text' ) ) ); 
		
		//echo $data;
		
		try{
			curl_setopt( $this->_ch, CURLOPT_POST, true);
			curl_setopt( $this->_ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt( $this->_ch, CURLOPT_URL, $this->_url);
			curl_setopt( $this->_ch, CURLOPT_RETURNTRANSFER, 1);
		} catch( Exception $e ){
			echo "urco";
		}
	}
	
	/**
	 * Error handling
	 */
	protected function _evaluate(){
	
		if( $this->_result ==null ){
			$this->setError( "response null" );
			return; 
		}
		
		// json error
		$result = json_decode( $this->_result );
		
		if( $result == null ){
			$this->setError( "error".$this->_result );
			return; 
		}
		$this->_result = $result;
		
		$result = (object) array( 'entities'=>array() );
		
		// clean result
		foreach ( $this->_result as $k => $v ){
			// print_r($v);
			if( $k == 'doc' ){
				$result->meta = $v->meta;
				$result->info = $v->info;
				continue;
			}
			
			$entityExtracted = new Textopoly_OpenCalais_Entity( $v );
			
			if( $entityExtracted->isValid() ) $result->entities[] = $entityExtracted;
			
		}
		
		$this->_result = $result;
		
	}
}
