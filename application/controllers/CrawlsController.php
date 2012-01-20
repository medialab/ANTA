<?php
/**
 * This controller show user folder content. Can be accessed by owner only.
 * 
 */
class CrawlsController extends Zend_Controller_Action
{
    /** the Application_MOdel_User instance. Files will be added to his folder */
	protected $_user;

	
    public function init()
    {
		$this->_user = Anta_Core::authorizeOwner();	
		$this->view->dock = new Ui_Dock();
    }
	public function indexAction(){
		$this->_forward( 'list' );
		
	}

	public function installAction(){
		Application_Model_CrawlsMapper::install( $this->_user->username ) ;
		Application_Model_DocumentsCrawlsMapper::install( $this->_user->username );
		$this->_forward( 'list' );
		
	}

	public function listAction(){
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 
			'google', I18n_Json::get( 'your google crawls' ).": ".$this->_user->username 
		));
		
		
		$this->_googleQueryHandler();
		
		
		
		$results = Application_Model_CrawlsMapper::select( $this->_user );
		// print_r($results);
		foreach( array_keys( $results ) as $k ){
			$crawl =& $results[ $k ];
			$this->view->dock->google->addItem( 
				new Ui_Crafts_Items_Crawl( $crawl )
			);
		}
	}
	
	/**
	 * attach the form "add crawl" to the current crawls list
	 */
	private function _googleQueryHandler(){
		
		$form = $this->view->dock->google->setCreateForm( new Ui_Forms_AddGoogle('add-google', I18n_Json::get( 'start crawl' ), Anta_Core::getBase()."/crawls" ) );
		
		if( !$this->_request->isPost() ) return;
		# post the data
			$params = array( 
				'project'	=> "googlescrap",
				'spider'	=> "google",
				'words'		=> $word,
				'crawl_table'		=> "crawls",
				'crawl_storage'		=> "documents",
				'relation_table'	=> "documents_crawls",
				'crawl_database'	=> "anta_".$this->_user->username,
				"language"			=> $form->google_language->getValue()
			);
			print_r( $params );  
		# validate form
		$messages = Anta_Core::validateForm( $form );
		if( $messages !== true ){
			Anta_Core::setError( $messages );
			return;
		};
		
		# split by dummy char sequence ------n_-_-_-
		$words = explode( "------n_-_-_-",  $form->google_query->getValue()  );
		
		foreach( $words as $word ){
			if( strlen( trim( $word ) ) == 0 ) 
				continue;	
			
			# curl our secrete service
			$ch = curl_init();
			
			
			# post the data
			$params = array( 
				'project'	=> "googlescrap",
				'spider'	=> "google",
				'words'		=> $word,
				'crawl_table'		=> "crawls",
				'crawl_storage'		=> "documents",
				'relation_table'	=> "documents_crawls",
				'crawl_database'	=> "anta_".$this->_user->username,
				"lang"			=> $form->google_language->getValue(),
				"num"			=> $form->google_n_results->getValue()
			);
			
			print_r( $params );
			curl_setopt($ch, CURLOPT_URL, "http://lrrr.medialab.sciences-po.fr:6800/schedule.json");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			
			// exécution de la session
			$response = curl_exec($ch);
			curl_close($ch);
			
			echo ( "done." );
			
			// 1.5 sec pause between curl calls
			usleep( 1500000 );
		}	
	}
	
	
}
