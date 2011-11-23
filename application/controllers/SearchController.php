<?php

class SearchController extends Zend_Controller_Action
{
	protected $_user;
	
    public function init()
    {
        /* Initialize action controller here */
		// check user param
        $idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		// validate ownerships
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		// check that user sists
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		
		$this->view->dock = new Ui_Dock();
		
		
    }

	private function disabledAction(){
		// so sorry...
		$this->view->dock->search->setHeader('
			<div class="grid_22 alpha omega prefix_1">crasp.. maybe you\'re interested in doing some index jamming session?</div>
		');
		
	}
	
	/**
	 * use regexp on content...
	 */
	public function regexpAction(){
		
		$query = stripslashes( $this->_request->getParam( "query" ) );
		$pattern = $query == null? "":$query;
		
		
		
		
		
		Dnst_Filter::start( array(
			"limit"  => 10,
			"offset" => 0,
			"prefix" => "rws",
			"query"  => $pattern,
			"tags" => array()
		));
		
		$pattern = Dnst_Filter::getProperty("query");
		
		# block here if there is nothing to search
		if( empty( $pattern ) ){
			Anta_Core::setError( I18n_Json::get("enter something to search",'errors'));
			return $this->render( "something" );
		}
		
		
		// get documlents
		$documentFilters = (object) array(
			"limit"  => 10,
			"offset" => 0,
			"query" => $pattern,
			"order" => array("title ASC"),
			"tags" => array()
		);
		$documents = Application_Model_DocumentsMapper::select( $this->_user, $documentFilters );
		
		
		
		
		 
		// search into sentences
		$results = Anta_Frog::regexp( $this->_user, Dnst_Filter::read() );
		
		
		$matches = $this->view->dock->addCraft( new Ui_Crafts_Matches ( 'matches', 'statistics' ) );
		$matches->frogMatches = $results;
		$matches->docsMatches = $documents;
		$matches->user = $this->_user;
		
		#echo "<pre>";print_r( $results->documents);echo "</pre>";
		return;
		// get regexp
		// SELECT * FROM `rws_entities_documents` JOIN rws_entities USING (id_rws_entity) WHERE id_document = 126 AND content REGEXP '^argentina'
		
		//FROM `sentences`
		//WHERE content
		//REGEXP '(appropriate|action on mitigation)'
		
		// start reading from DnstFilters
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'occurrences', 'statistics' ) );
		
		$wl = new Anta_Utils_WordList( $pattern );
		$wl->applyStem( "en" );
		
		$results = Application_Model_SentencesMapper::match( $this->_user, $pattern );
		
		foreach( array_keys( $results[ "docs" ] ) as $k ){
			
			// get document and tags as well
			$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Document( 
				 Application_Model_DocumentsMapper::getDocument( $this->_user, $k ),
				 Application_Model_DocumentsMapper::getTags( $this->_user, $k ),
				 array(
					"amountOfSentences" => count( $results[ "docs" ][ $k ] )
				 )
			));
			
			// print phrases
			foreach( array_keys( $results[ "docs" ][ $k ] ) as $s ){
				$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Sentence( $results[ "docs" ][ $k ][$s], $wl->words ) );
			}
		
			
		}
		$this->render( "something" );
	}
	
	public function somethingAction(){
		$query = stripslashes( $this->_request->getParam( "query" ) ) ;
		$wl = new Anta_Utils_WordList( $query );
		$wl->applyStem( "en" );
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'occurrences', 'statistics' ) );
		
		$results = Application_Model_SubEntitiesDocumentsMapper::getSentences( $this->_user, $wl->words );
		
		foreach( array_keys( $results[ "docs" ] ) as $k ){
			
			// get document and tags as well
			$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Document( 
				 Application_Model_DocumentsMapper::getDocument( $this->_user, $k ),
				 Application_Model_DocumentsMapper::getTags( $this->_user, $k ),
				 array(
					"amountOfSentences" => count( $results[ "docs" ][ $k ] )
				 )
			));
			
			// print phrases
			foreach( array_keys( $results[ "docs" ][ $k ] ) as $s ){
				$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Sentence( $results[ "docs" ][ $k ][$s], $wl->words ) );
			}
		
			
		}
		
	}
	
    public function withLuceneAction()
    {
        // action body
		// check number of document indexed
		if( Anta_Lucene::hasZendLuceneIndex( $this->_user ) === false ){
			return $this->disabledAction();
		}
		
		$form = $this->view->dock->search->setCreateForm( new Ui_Forms_Search(
			'search-stuff',
			I18n_Json::get( 'find' ),
			''
		));
		echo $this->_getQueryResults( $form );
		$this->render( "something" );
    }
	
	protected function _getQueryResults( $form ){
		print_r( $_POST );
		if( $this->_request->isPost() ){
			
			$result = Anta_Core::validateForm( $form );
			
			if( $result !== true ){
				Anta_Core::setError( I18n_Json::get( 'not a valid search' ), 'errors' );
				return;
			}
			
			$results = Anta_Lucene::searchLucene( stripslashes($form->query->getValue()) , $this->_user );
			
			
			$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'occurrences', 'statistics' ) );
			foreach( array_keys( $results[ "docs" ] ) as $k ){
				$doc = Application_Model_DocumentsMapper::getDocument( $this->_user, $k );
				// get document and tags as well
				if( $doc == null ) {
					echo "$k";
					continue;
				}
				
				$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Texts_Document( 
					 Application_Model_DocumentsMapper::getDocument( $this->_user, $k ),
					 Application_Model_DocumentsMapper::getTags( $this->_user, $k ),
					 array(
						"amountOfSentences" => count( $results[ "docs" ][ $k ]->hits ),
						"score" => $results[ "docs" ][ $k ]->score
					 )
				));
				
				/* print phrases */
				foreach( array_keys( $results[ "docs" ][ $k ]->hits ) as $s ){
					$this->view->dock->occurrences->addItem( new Ui_Crafts_Items_Text( $results[ "docs" ][ $k ]->hits[$s] ) );
				}
				
			}
			
		}
		
		$query = stripslashes( $this->_request->getParam( "query" ) ) ;
		
		if( $query == null ){
			return ;
		}
		// analyse query
	}

	

}

