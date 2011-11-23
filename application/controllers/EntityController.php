<?php

class EntityController extends Zend_Controller_Action
{
	protected $_user;
	protected $_entity;
	protected $_prefix;

    public function init()
    {
        /* Initialize action controller here */
		$this->_user = $this->view->user = Anta_Core::getAuthorizedUser(  Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) ) );
		
		$id = preg_replace('/[^\d]/', '', $this->_request->getParam( 'id' ));
		
		if ( $this->_request->getParam( 'prefix' ) == "super" ){
			
			/* get super entity id */
			$this->_entity = Application_Model_SuperEntitiesMapper::getEntity(  $this->_user, $id );
		
		} else {
			/* get entity id */
			$this->_entity = Application_Model_SubEntitiesMapper::getEntity(  $this->_user, $this->_request->getParam( 'prefix' ), $id );
		
		}
		
		 
		if( $this->_entity == null ){
			throw( new Zend_Exception( I18n_Json::get( 'entiy not found', 'errors') ) );
		}
		
		/* get entity tag */
		$this->view->dock = new Ui_Dock();
		
		
		
    }

	public function superViewAction(){
		
		$this->view->user = $this->_user;
		$this->view->dock->addCraft( new Ui_Crafts_Cargos_Entities( 'entities', $this->_entity->content ) );
		
		
		foreach( array_keys( $this->_entity->children ) as $k ){ 
			$this->view->dock->entities->addItem( new Ui_Crafts_Items_Entity( $this->_entity->children[ $k ] ) );
		}
		
	}

    public function viewAction()
    {
		if ($this->entity->prefix== 'super'){
			throw( new Zend_Exception( I18n_Json::get( 'super entiy not found', 'errors') ) );
		}
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'entities', $this->_entity->content ) );
		
		
		// get world list
		
		$wl = new Anta_Utils_WordList( $this->_entity->content );
		$wl->applyStem( "en" );
		
		$this->view->dock->entities->addItem(
			new Ui_Crafts_Items_Phylogeny( $this->_entity, $this->_user )
		);
		
		flush();
		ob_flush();
		
		/* attach tags
		*/
		
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'occurrences', 'statistics' ) );
        
		// read filters, limit and offset only
		Dnst_Filter::start( array(
				"offset" => 0,
				"limit"  => 30,
				"order"  => array( "date ASC", "id_document DESC", "position ASC" ),
				"query"  => $this->_entity->content, // the exact matches
			), array (
				"order"  => new Dnst_Filter_Validator_Array( array(
					"id_document DESC", "id_document ASC", "position ASC", "position DESC",
					"title ASC", "title DESC", "date ASC", "date DESC"
				)),
				"offset" => new Dnst_Filter_Validator_Range( 0, 10000000 ),
				"limit"  => new Dnst_Filter_Validator_Range( 1, 500 )
			)
		);
		
		
		
		$results = Application_Model_SubEntitiesDocumentsMapper::getSentences( $this->_user, Dnst_Filter::read() );
		
		$filters = new Anta_Ui_Header_Entity( array(
			"loadedItems" => count( $results ),
			"totalItems" => $results->totalItems
		));
		
		// read filters
		$this->view->dock->occurrences->setHeader( $filters );
		$this->view->dock->occurrences->setFooter( $filters );
		
		foreach( array_keys( $results->results ) as $k ){
			$sentence =   $results->results[ $k ];
			// get document and tags as well
			/*$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Document( 
				 Application_Model_DocumentsMapper::getDocument( $this->_user, $k ),
				 Application_Model_DocumentsMapper::getTags( $this->_user, $sentence->documentId ),
				 array(
					"amountOfSentences" => count( $results[ "docs" ][ $k ] )
				 )
			));
			
			// print phrases
			foreach( array_keys( $results[ "docs" ][ $k ] ) as $s ){
				$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Sentence( $results[ "docs" ][ $k ][$s], $wl->words ) );
			}
			*/
			
			$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Sentence( $sentence, $wl->words, $this->_user ) );
			
		}
		
		// action body
		$this->render( 'index' );
		
		
    }


}

