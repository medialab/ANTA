<?php

class EditController extends Zend_Controller_Action
{
	/**
	 * Cannonical url: edit document :id_document of user :id_user
	 * /edit/:id_document/user/:id_user
	 */ 
    public function init()
    {
		// check user param
        # $idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		// validate ownerships
		# Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		// check that user sists
		$this->_user = Zend_Auth::getInstance()->getIdentity(); // Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		
		// check the docu,ment into user's docs table
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'document' ) );
		$this->_document = Application_Model_DocumentsMapper::getDocument( $this->_user, $idDocument );
			
		if( $this->_document == null ){
			throw( new Zend_Exception( I18n_Json::get( 'documentNotFoundException', 'errors' ) ) );
		}
		
		// pass value to the view
		$this->view->user = $this->_user;
		$this->view->document = $this->_document;
    }
	
	/**
	 * test for Zend_Pdf
	 */
	public function zendPdfTestAction(){
		$fileName = Anta_Core::getUploadPath()."/".$this->_user->username."/".$this->_document->localUrl;
		// read pdf
		$pdf1 = Zend_Pdf::load($fileName, 1);
		
		echo $pdf1->render();
		
		$this->render( "index" );
	}
	
	public function downloadAction(){
		
		$file = Anta_Core::getUploadPath()."/".$this->_user->username."/".$this->_document->localUrl;
		
		if( !file_exists( $file ) ){
			throw( new Zend_Exception( "'".basename( $file )."'".I18n_Json::get( 'fileNotFound', 'errors' ) ) );
			return;
		}
		
		/* Initialize action controller here */
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		/** reinitialize headers */
		header('Content-Description: File Transfer');
		header('Content-type: '.$this->_document->mimeType );
		header('Content-Transfer-Encoding: binary');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: public");
		header('Content-Length: ' . filesize($file));
		
		ob_clean();
		flush();
		readfile($file);
		exit;
		
	}
	
	/**
	 * show the import interface. if a files isset, then try to extract csv terms
	 */
	public function importTagsAction(){
		
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		
		// add module "edit property of"
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo(
			'tags',
			I18n_Json::get( 'import tags into' ).' <span class="'.( $identity->id != $this->_user->id? 'admin': '').'">'.$this->_document->title. "</span>"
		));
		
		// upload file form
		$this->view->dock->tags->setCreateForm(
			new Ui_Forms_ImportTags(
				'modify-file',
				I18n_Json::get( 'editDocument' ),
				$_SERVER[ 'REDIRECT_URI' ]
		));
		
		if( $this->_request->isPost() ){
			
			// check file and import
			$form = $this->view->dock->tags->getCreateForm();
			
			$result = Anta_Core::validateForm( $form );
			
			if( $result !== true ){
				Anta_Core::setError( $result );
				return $this->render( 'index' );
			}
			
			// the temp uploaded file
			$filepath = $form->import_file->getValidator()->getValue();
			
			$table = Anta_Csv::parse( $filepath, "\t", '"' );
			
			if( $table == null ){
				Anta_Core::setError( I18n_Json::get( 'unable to load csv data', 'errors' ) );
				return $this->render( 'index' );
			}
			
			// validate headers
			$minimumHeaders = array( 'id doc',	'id hash', 'title',	'date',	'language' );
			
			$result = $table->getHeader()->isValid( $minimumHeaders );
			
			if(  $result !== true ){
				Anta_Core::setError( I18n_Json::get( 'csv header not found', 'errors' ). ": <strong>".$result."</strong>" );
				return $this->render( 'index' );
			};
			
			// set new categories, using ignore !
			$customFields = $table->getHeader()->getCustomFields( $minimumHeaders );
			
			// set table validator
			$table->setValidators( array(
				// 'date' => new Ui_Forms_Validators_Date ( array( "minLength" => 10, "maxLength" => 10 ) )
			));
			
			
			
			// try create all categories !
			foreach( $customFields as $newField ){
				Application_Model_CategoriesMapper::add( $this->_user, $newField );
			}
			
			// foreach row, validate
			if( $table->isValid( $validators ) === false ){
				Anta_Core::setError( implode( $table->getMessages() ) );
				// draw table
				$this->view->dock->tags->setHeader( $table->toHtmlTable() );
				return $this->render( 'index' );
			}
			
			
			// draw table, it's ok!
			$this->view->dock->tags->setHeader( $table->toHtmlTable() );
			
			// now let's save according to couple id doc id hash for each documents
			foreach( array_keys( $table->rows ) as $k ){
				
				$row =& $table->rows[ $k ];
				
				// get document id
				$idDocument = $row->getCell( 'id doc' )->getValue();
				
				// try to put document existance
				
				
				foreach( $customFields as $newField ){
					// echo $newField.;
					// explode values
					$values = explode( ",", $row->getCell( $newField )->getValue() );
					
					// cycle through value
					foreach( $values as $value ){
						$value = trim( $value );
						
						// ignore null
						if( strlen( $value ) == 0 ) continue;
						
						// create / get id tag
						Application_Model_DocumentsTagsMapper::add(  $this->_user, $idDocument, Application_Model_TagsMapper::add( $this->_user, $value,  $newField ) );
						
					}
					
				}
				
			}
			// Anta_Core::setMessage( "file $filepath is valid. Redirect to document page...");
			$this->_forward( 'props' );
		}
		$this->render( 'index' );
	}
	
	public function exportTagsAction(){
		
		/* Initialize action controller here */
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		anta_Core::setHttpHeaders( "text/plain", $this->_user->username."_doc_".$this->_document->cryptoId."_tags.csv", true );
	
		// get categories for current stuff
		$categories = Application_Model_DocumentsMapper::getCategories( $this->_user, $this->_document->id );
		
		// get all documents tags and dispatch them to various categories
		$tags = Application_Model_DocumentsMapper::getTags( 
				$this->_user, $this->_document->id 
		);
		
		// set default values
		$header = new Anta_Csv_Header( array( 'id doc', 'id hash', 'title', 'date', 'language' ));
		
		// initialize table
		$table = new Anta_Csv_Table(
			$header
		);
		
		// fill the first row for the table
		$documentRow = new Anta_Csv_Row( $header, array(
				'id doc'	=> new Anta_Csv_Cell( $this->_document->id ),
				'id hash' 	=> new Anta_Csv_Cell( $this->_document->cryptoId ),
				'title'		=> new Anta_Csv_Cell( $this->_document->title ),
				'date'		=> new Anta_Csv_Cell( $this->_document->date ),
				'language'	=> new Anta_Csv_Cell( $this->_document->language ),
		));
		
		// add custom field as table header
		foreach( $categories as $category ){
			$header->addField( $category->content );
			$documentRow->addCell( $category->content, new Anta_Csv_Cell( "" ) );
		}
		
		foreach( $tags as $tag ){
			$documentRow->append( $tag->category, $tag->content );
		}
		// fill the row with bogus values
		$table->addRow( $documentRow );
		
		echo $table;
		
	}
	
    public function propsAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
	
		if( $identity->id != $this->_user->id ){
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->addEntry(
				"/documents/".$this->_user->cryptoId,
				I18n_Json::get( 'userDocumentList' ).' user:'.$this->_user->username , array( 
					'class' => 'admin entry-selected'
			));
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->addEntry(
				"/gexf/entities/user/".$this->_user->cryptoId,
				I18n_Json::get( 'gexf' ).' user:'.$this->_user->username , array( 
					'class' => 'admin'
			));

		}
	
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		
		// add module "edit property of"
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo(
			'documents',
			I18n_Json::get( 'modifyDocument' ).' <span class="'.( $identity->id != $this->_user->id? 'admin': '').'">'.$this->_document->title. "</span>"));
		
			
		// enable download button
		$nextDocument = Application_Model_DocumentsMapper::getNextDocument( $this->_user, true, $this->_document->id );
		// print_r( $nextDocument );
		if( $nextDocument  != null ){
		
			$this->view->dock->documents->setCreationLink(
				Anta_Core::getBase()."/edit/props/document/".$nextDocument->cryptoId."/user/".$this->_user->cryptoId,
				I18n_Json::get("next document")
			);
		}
		// get next
		
		// modify properties form
		$this->view->dock->documents->setCreateForm(
			new Application_Model_Forms_ModifyFileForm(
				'modify-file',
				I18n_Json::get( 'editDocument' ),
				Anta_Core::getBase()."/edit/props/document/".$this->_document->cryptoId."/user/".$this->_user->cryptoId ) );
		
		// the form
		$form = $this->view->dock->documents->getCreateForm();
		
		// get a look at the form
		// validate file form NOW
		if( $this->getRequest()->isPost() ){
			
			$result = Anta_Core::validateForm( $form, $this->getRequest()->getParams() );
		
			if( $result !== true ){
				Anta_Core::setError( $result );
				
			} else {
				// save modification into the file
				Anta_Core::setMessage( 'documentModified' );
				
				Application_Model_DocumentsMapper::editDocument(
					$this->_user, $this->_document->id,
					$form->file_title->getValue(),
					"", 
					$form->file_date->getValue(),
					$form->file_lang->getValue()
				);
				
				/**
				Application_Model_AuthorsMapper::addAuthors(
					$this->_user,
					$this->_document->id,
					$form->file_author->getValue() 
				);
				*/
				$this->view->dock->documents->title = I18n_Json::get( 'modifyDocument' ).
					' <span class="'.( $identity->id != $this->user->id? 'admin': '').'">'.stripslashes($form->file_title->getValue()). "</span>";
			}
		}
		// default value
		
		$form->file_date->setDefaultValue( $this->_document->date );
		$form->file_title->setDefaultValue( $this->_document->title );
		// $form->file_description->setDefaultValue( $this->_document->description );
		
		// load authros
		//$authorsValue = array();
		//$authors = Application_Model_AuthorsMapper::getAuthors( $this->_user, $this->_document->id );
		// print_r($authors);
		//foreach( $authors as $author ){
		//	$authorsValue[] = $author->name;
		//}
		//$form->file_author->setDefaultValue( $authorsValue );
		
		/**
		 * 
		 * MODULE "TAGS"
		 *
		 */
		
		// add module "tags explorer";
		$this->view->dock->addCraft( new Ui_Crafts_Cargos_Categories(
			'custom-tags',
			I18n_Json::get( 'document custom categorisation' )
		));
		
		// enable add entitty button
		$addTagForm = $this->view->dock->custom_tags->setCreateForm( new Ui_Forms_AddTag( 
			"add-new-tag", I18n_Json::get('add-new-tag'), ''
		));
		
		$select = $addTagForm->tag_category->addOptions(
			new Ui_Forms_Elements_Option(  I18n_Json::get( '--add new--' ), '0' ),
			new Ui_Forms_Elements_Option( I18n_Json::get( "choose one" ), -1, true )
			
		);
		
		// fill with actual categories
		$categories = Application_Model_CategoriesMapper::getAll( $this->_user );
		
		foreach( $categories as $category ){
			$addTagForm->tag_category->addOptions(
				new Ui_Forms_Elements_Option(  I18n_Json::get( $category->content ), $category->content )
			);
		}
		
		
		// enable add entitty button
		$this->view->dock->custom_tags->setCreationLink(
			"#",
			I18n_Json::get("add tag"),
			array( "id" => "add-tag" )
		);
		
		// get categories for current stuff
		$categories = Application_Model_DocumentsMapper::getCategories( $this->_user, $this->_document->id );
		
		foreach( $categories as $category ){
			$this->view->dock->custom_tags->addItem(
				new Ui_Crafts_Items_Category( $category, $this->_user )
			);
		}
		
		// get all documents tags and dispatch them to various categories
		$this->view->dock->custom_tags->dispatch( 
			Application_Model_DocumentsMapper::getTags( 
				$this->_user, $this->_document->id 
		));
		
		/**
		$this->view->dock->custom_tags->init( $this->_user, $this->_document );
		
		// enable download button
		$this->view->dock->custom_tags->setCreationLink(
			Anta_Core::getBase()."/edit/download/document/".$this->_document->cryptoId."/user/".$this->_user->cryptoId,
			I18n_Json::get("createCustomField")
		);
		*/
		
		
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Texto(
			'texto',
			I18n_Json::get( 'documentTextPreviewer' )
		));
		
		// enable add entitty button
		
		
		$this->view->dock->texto->read( $this->_user, $this->_document );
		
		// little filter
		Dnst_Filter::start( array(
			"offset" => 0,
			"limit"  => 30
		));
		
		// print_r(Dnst_Filter::getProperty("limit") );
		$entities = Application_Model_SubEntitiesDocumentsMapper::getEntities( $this->_user, $this->_document->id, array(),  Dnst_Filter::getProperty("offset"), Dnst_Filter::getProperty("limit")  );
		
		$this->view->dock->texto->setEntities( $entities->results, $entities->totalItems );
		
		$this->render( 'index' );
    }

	
}

