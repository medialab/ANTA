<?php
/**
 * This controller show user folder content. Can be accessed by owner only.
 * 
 */
class DocumentsController extends Zend_Controller_Action
{
	protected $_user;

    public function init()
    {
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		$this->_user = Zend_Auth::getInstance()->getIdentity();//Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		
		$this->view->user = $this->_user;
		
		/**
		if ( $this->_request->getParam( 'remove' ) != null ){
				// verify document to delete
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'remove' ) );
			
			$document = Application_Model_DocumentsMapper::getDocument( $this->_user, $idDocument );
			
			if( $document != null ){
				$this->_remove( $document );
			} else{
				Anta_Core::setMessage( I18n_Json::get( 'documentRemoved' ) );
			}
			
			
		} else
		*/
		if ( $this->_request->getParam( 'download' ) != null ){
				// verify document to delete
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'download' ) );
			
			$document = Application_Model_DocumentsMapper::getDocument( $this->_user, $idDocument );
			
			if( $document == null ){
					exit( "document not found ");
			}
			$this->_download( $document );
			
		}
    }

	/**
	 * allow user to qualify document list before submitting data into the database
	 */
	public function exportTagsAction(){
	
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		Anta_Core::setHttpHeaders( "text/tsv", $this->_user->username."_documents.csv", true );
		
		// extablish default headers
		$header = new Anta_Csv_Header( array( 'id doc', 'id hash', 'title', 'ignore', 'date', 'language', 'description' ));
			
		// get categories for current stuff
		$categories = Application_Model_CategoriesMapper::getAll( $this->_user );

		// get categories for current stuff
		$categories = Application_Model_CategoriesMapper::getAll( $this->_user );
		
		// fill table header
		foreach( $categories as $category ){
			$header->addField( $category->content );
		}
		
		// flush headers
		echo $header."\n"; 
	
		// get documents
		$stmt = Application_Model_DocumentsMapper::get( $this->_user, array( "title ASC" ), $offset = 0, $limit = -1, $searchQuery = null, $loadTags = false, $flush = true );
		
		// free connection
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "database" );
		// extablish connection
		$_mysqli = new Zend_Db_Adapter_Mysqli( array(
			'dbname'   => $config->mysql->dbnm,
			'username' => $config->mysql->user,
			'password' => $config->mysql->pass,
			'host'     => $config->mysql->host
		));
		$_mysqli->getConnection()->set_charset('utf8');
		
		
		
		while( $row = $stmt->fetchObject() ){
			
			$documentRow =  new Anta_Csv_Row( $header, array(
				'id doc'	=> new Anta_Csv_Cell( $row->id ),
				'id hash'	=> new Anta_Csv_Cell( Dnst_Crypto_SillyCipher::crypt( $row ->id ) ),
				'title'		=> new Anta_Csv_Cell( $row->title ),
				'ignore'	=> new Anta_Csv_Cell( $row->ignore == 0? "": 1 ),
				'date'		=> new Anta_Csv_Cell( $row->formatted_date ),
				'language'	=> new Anta_Csv_Cell( $row->language ),
				'description'	=> new Anta_Csv_Cell( $row->description )
			));
			
			// add custom field for each document, empty cells
			foreach( $categories as $category ){
				$documentRow->addCell( $category->content, new Anta_Csv_Cell( "" ) );
			}
			
			$_stmt = $_mysqli->query( "
				SELECT ta.id_tag, ta.content, ( SELECT cat.content FROM anta_".$this->_user->username.".categories cat WHERE cat.id_category = ta.id_category ) as category, 
				ta.parent_id_tag FROM anta_".$this->_user->username.".`documents_tags` dt NATURAL JOIN anta_".$this->_user->username.".`tags` ta
				WHERE dt.id_document = ? ", array(
					$row->id
			));
			
			// attach tags
			while( $_row = $_stmt->fetchObject() ){
				$documentRow->append( $_row->category, $_row->content );
			}
			
			
			echo $documentRow."\n";
		}
		
		exit;
		
		$documents = Application_Model_DocumentsMapper::dumpDocuments( $this->_user );
		
		// fill table
		$table = new Anta_Csv_Table( $header );
		
		
		
		foreach( array_keys( $documents ) as $k ){
			$document =& $documents[ $k ];
			$documentRow =  new Anta_Csv_Row( $header, array(
				'id doc'	=> new Anta_Csv_Cell( $document->id ),
				'id hash'	=> new Anta_Csv_Cell( $document ->cryptoId ),
				'title'		=> new Anta_Csv_Cell( $document->title ),
				'date'		=> new Anta_Csv_Cell( $document->date ),
				'language'	=> new Anta_Csv_Cell( $document->language ),
				'description'	=> new Anta_Csv_Cell( $document->description )
			));
			
			// add custom field for each document, empty cells
			foreach( $categories as $category ){
				$documentRow->addCell( $category->content, new Anta_Csv_Cell( "" ) );
			}
			
			// get all documents tags and dispatch them to various categories
			$tags = Application_Model_DocumentsMapper::getTags( 
					$this->_user, $document->id
			);
			
			foreach( $tags as $tag ){
				$documentRow->append( $tag->category, $tag->content );
			}
			
			// fill the row with bogus values
			$table->addRow( $documentRow );
		
		
		}
		
		
		echo $table ;
		
		
		
	}
	
	public function importFromGoogleSpreadsheetAction(){
		$this->view->dock = new Ui_Dock();
		
		// add module "edit property of"
		$this->view->dock->addCraft( new Ui_Crafts_Cargo(
			'tags',
			I18n_Json::get( 'import tags from google docs' )
		));
		
		// brief explaination
		$this->view->dock->tags->setCreateForm( 
			new Ui_Forms_ImportGoogleDocs( 'import-google-docs', I18n_Json::get( "load google document" ) ) 
		);
		
		$this->view->dock->tags->addItem(
			new Ui_Crafts_Items_Void( 'gdoc-some-lines' )
		);
		
		// add previous uploaded graph (auto save google docs in table)
		
		
	}
	
	
	public function importTagsAction(){
		
		// accept a csv file! just like edit.importtags...
		$this->view->dock = new Ui_Dock();
		
		// add module "edit property of"
		$this->view->dock->addCraft( new Ui_Crafts_Cargo(
			'tags',
			I18n_Json::get( 'import tags for all documents' )
		));
		
		// upload file form
		$this->view->dock->tags->setCreateForm(
			new Ui_Forms_ImportTags(
				'modify-files',
				I18n_Json::get( 'editDocuments' ),
				$_SERVER[ 'REDIRECT_URL' ]
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
			$minimumHeaders = array( 'id doc',	'id hash', 'title', 'ignore', 'date',	'language', 'description' );
			
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
			
			// foreach row, validate...
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
				
				// save default changements, by using minimumHeader
				Application_Model_DocumentsMapper::editDocument(
					$this->_user, $idDocument,
					$row->getCell( "title" )->getValue(),
					$row->getCell( "description" )->getValue(), $row->getCell( "date" )->getValue(),
					$row->getCell( "language" )->getValue()
				);
				
				// echo  $idDocument. " ".$row->getCell( "title" )->getValue(). " ". $row->getCell( "date" )->getValue(). " " . $row->getCell( "language" )->getValue() . "<br />";
				
				// custom fields
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
			
			// a. save modification to documents table
			
			// b. save document tags
			
			
			
		}
		
		$this->render( 'index' );
	}
	
	/**
	 * who enters here, is someone who's ...
	 */
/*
 * 
 * name: listAction
 * @param
 * @return
 */
    public function listAction()
    {
		$identity = Zend_Auth::getInstance()->getIdentity();
	
		// if identity and user aren't the same identity, add an entry to the identityBoard (menu )
		if( $identity->id != $this->_user->id ){
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->addEntry(
				Anta_Core::getBase()."/documents/".$this->_user->cryptoId,
				I18n_Json::get( 'userDocumentList' ).' @ '.$this->_user->username , array( 
					'class' => 'admin entry-selected'
			));
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->addEntry(
				Anta_Core::getBase()."/gexf/entities/user/".$this->_user->cryptoId,
				I18n_Json::get( 'gexf' ).' @ '.$this->_user->username , array( 
					'class' => 'admin'
			));

		}
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'documents', I18n_Json::get( 'documentsList' ).": ".$this->_user->username ) );
		
		$totalDocuments =  Application_Model_DocumentsMapper::getNumberOfDocuments( $this->_user ); 
		
		// no documents? send directly to upload, with a link, and stop this script
		if( $totalDocuments == 0 ){
			
			// draw nice upload link "start uploading file!" and welcome message as well
			$this->view->dock->documents->setHeader( new Ui_Crafts_Headers_Welcome( $this->_user ) );
			
			
			
			// remove menu items
			Ui_Board::getInstance( "Documents", array( 'user' => $this->_user ) )->removeItem(
				"documents.import-tags", 
				"documents.export-tags",
				"api.reset"
			);
			
			return $this->render( 'index');
		}
		
		// start listening to request filter, or default vars here
		// values and their validators
		Dnst_Filter::start( array(
				"offset" => 0,
				"limit"  => 100,
				"order"  => array( "mimetype DESC" ),
				"tags"	 => array(),
				"query"  => "",
				"date_start"=>"",
				"date_end"  =>""
			), array (
				"order"  => new Dnst_Filter_Validator_Array( array(
					"id_document DESC", "id_document ASC",
					"title ASC", "title DESC", "date ASC", "date DESC","`ignore` DESC", "`ignore` ASC",
					"status ASC", "status DESC",
					"language ASC", "language DESC", "mimetype ASC", "mimetype DESC",
				)),
				"offset" => new Dnst_Filter_Validator_Range( 0, 10000000 ),
				"limit"  => new Dnst_Filter_Validator_Range( 1, 500 ),
				"query"  => new Dnst_Filter_Validator_Pattern( 0, 100 )
			)
		);
		
		if( !Dnst_Filter::isValid() ){
			// if you set the filters properly, then these variables MUST be in place
			Anta_Core::setError("uhm..not valid string..".Dnst_Filter::getErrors() );
			return $this->render( 'index' );
		}
		// print_r( Dnst_Filter::read() );
		// get all the documents
		$documents = Application_Model_DocumentsMapper::select(
			$this->_user,
			Dnst_Filter::read()
		);
		// send some variables to the view
		$this->view->totalItems = $documents->totalItems;
		$this->view->loadedItems = count( $documents->results );
		
		// query to group documents by month according to number of groups
		$stmt = Anta_Core::mysqli()->query("
			SELECT COUNT(*) as countable, DATE_FORMAT(date,'%d.%m.%Y') as simple_date FROM anta_{$this->_user->username}.`documents` GROUP BY (`simple_date`)"
		);
		$timestamps = array();
		while ( $row = $stmt->fetchObject() ){
			$timestamps[] =	new Ui_D3_Timeline_Point( 
				$row->simple_date, 
				array( 
					"y" => $row->countable,
					"title" => $row->simple_date ." (".$row->countable.")",
					"href"  => $_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'date_start', $row->simple_date )
				) 
			);	
		}
		
		// prepare headers
		$header = new Anta_Ui_Header_Documents();
		$header->user = $this->_user;
		$header->loadedItems =  count( $documents->results );
		$header->totalItems  =  $documents->totalItems;
		$header->offset         =  Dnst_Filter::getProperty( "offset" );
		$header->limit          =  Dnst_Filter::getProperty( "limit" );
		$header->searchQuery    =  Dnst_Filter::getProperty( "query" );
		$header->setTimeline( new Ui_D3_Timeline( 
			$timestamps, "dd.mm.yy", 
			array( "width"=>832, "height"=>40)
		));
		
		$this->view->dock->documents->setHeader( 
			$header
		);
		
		
		
		foreach (array_keys( $documents->results ) as $k ){
			$this->view->dock->documents->addItem( new Anta_Ui_Item_Document( $documents->results[ $k ] ) );
		}
		
		$this->render( 'index' );
		
    }
    
	public function removeAction(){
		if ( $this->_request->getParam( 'doc' ) == null ){
			throw( new Zend_Exception( I18n_Json::get( 'document not found','errors') ) );
		}
		
		// verify document to delete
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'doc' ) );
			
		$document = Application_Model_DocumentsMapper::getDocument( $this->_user, $idDocument );
			
		if( $document != null ){
			Application_Model_DocumentsMapper::removeDocument( $this->_user, $document->id );
			Anta_Core::setMessage( I18n_Json::get( 'documentRemoved' ) );
		}
		
		return $this->listAction();
	}
	
    protected function _remove( $document ){
		Application_Model_DocumentsMapper::removeDocument( $this->_user, $document->id );
		// physically remove document
		
		$this->_forward( $this->_user->cryptoId );
	}
	
	protected function _download( $document ){
		
		$this->_helper->layout()->disableLayout();
		
	}

	
	public function __call( $a, $b){
		//$this->_forward( 'index' );
	}
}

