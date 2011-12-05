<?php

class AddController extends Zend_Controller_Action
{
	/** the Application_MOdel_User instance. Files will be added to his folder */
	protected $_user;

	
    public function init()
    {
		$this->_user = Anta_Core::authorizeOwner();	
		
    }

	public function webCrawlAction(){
		# view output: the title of the page. Cfr the related view to get all the script
		$this->view->dock = new Ui_Dock();
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 
			'google', I18n_Json::get( 'google search crawl' ).": ".$this->_user->username 
		));
		$form = $this->view->dock->google->setCreateForm( new Ui_Forms_AddGoogle('add-google', I18n_Json::get( 'start crawl' ), Anta_Core::getBase()."/add/web-crawl" ) );
		
		
		if( !$this->_request->isPost() ) return;
		
		# validate form
		$messages = Anta_Core::validateForm( $form );
		if( $messages !== true ){
			Anta_Core::setError( $messages );
			return;
		};
		
		# split by dummy char sequence ------n_-_-_-
		$words = explode( "------n_-_-_-",  $form->google_query->getValue()  );
		
		# curl our secrete service
		$ch = curl_init();
		
		# post the data
		$params = array( 
			'project'	=> "googlescrap",
			'spider'	=> "google",
			'words'		=> $words,
			'crawl_table'		=> "crawls",
			'crawl_storage'		=> "documents",
			'relation_table'	=> "documents_crawls",
			'crawl_database'	=> "anta_".$this->_user->username
		);
		print_r( $params );
		curl_setopt($ch, CURLOPT_URL, "http://lrrr.medialab.sciences-po.fr:6800/schedule.json");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		
		// exécution de la session
		curl_exec($ch);
		curl_close($ch);
		echo "done";
		/**
		crawl_database = 
		crawl_table = 
		crawl_storage =
		relation_table =
		*/
	}
	
	public function userAction(){
		Anta_Core::authorizedOnly( 'admin' );
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		$this->view->dock = new Application_Model_Ui_Docks_Dock();

		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'users', I18n_Json::get( 'userList' ) ) );
		
		$this->view->dock->users->setCreateForm( new Application_Model_Forms_CreateUserForm('create-user', I18n_Json::get( 'addUser' ), Anta_Core::getBase()."/add/user" ) );
		
		// validate file form NOW
		if( $this->getRequest()->isPost() ){
			$form =  $this->view->dock->users->getCreateForm();
			
			$result = Anta_Core::validateForm( $form, $this->getRequest()->getParams() );
			
			if( $result !== true ){
				Anta_Core::setError( $result );
				return $this->render( 'index' );
			}
			
			Application_Model_UsersMapper::addUser(
				$form->user_firstname->getValue() .' '. $form->user_lastname->getValue(),
				$form->username->getValue(), $form->user_email->getValue(),
				$form->password->getValue()
			);
						
			Anta_Core::setMessage( I18n_Json::get( 'userAdded' ) );
				
		}
		
		$this->render( 'index' );
	}

	public function filesAction(){
		# copy user in view		
		$this->view->user = $this->_user;

		# view output: the title of the page. Cfr the related view to get all the script
		$this->view->dock = new Ui_Dock();
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 
			'documents', I18n_Json::get( 'upload documents' ).": ".$this->_user->username 
		));
	}
	public function documentsAction(){
		# copy user in view		
		$this->view->user = $this->_user;
		
		# get total amount of document ( it uses username value of user instance )
		$totalDocuments =  Application_Model_DocumentsMapper::getNumberOfDocuments( $this->_user ); 
		
		# no documents? remove unavaialbe links from left menu
		if( $totalDocuments == 0 ){
			
			# remove menu items. note: we need to specify the init properties ("reset" the user )
			Ui_Board::getInstance( "Documents",array( 'user' => $this->_user ) )->removeItem(
				"documents.import-tags", 
				"documents.export-tags",
				"api.reset"
			);
			
		}
		
		# view output: the title of the page. Cfr the related view to get all the script
		$this->view->dock = new Ui_Dock();
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 
			'documents', I18n_Json::get( 'documentsList' ).": ".$this->_user->username 
		));
		
		
	}
	
	
	

}

