<?php
/**
 * @package
 */

/**
 * Describe an Document_Entity relationship
 */
class Application_Model_DocumentEntity{
	public $idEntity;
	public $idDocument;
	public $relevance;
	
	public function __construct( $idDocument, $idEntity, $relevance ){
		$this->idDocument = $idDocument;
		$this->idEntity = $idEntity;
		$this->relevance = $relevance;
	}
}