<?php

class StructuredController extends Zend_Controller_Action
{
	/** the Application_MOdel_User instance. Files will be added to his folder */
	protected $_user;

	
    public function init()
    {
		$this->_user = Anta_Core::authorizeOwner();	
		
    }
	
	public function linkscapeAction(){
		# view output. Cfr the related view to get all the script
		$this->view->dock = new Ui_Dock();
		
		# dummy csv importer
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 
			'documents', I18n_Json::get( 'structured csv upload' ).": ".$this->_user->username 
		));
		
		# upload form
		$form = $this->view->dock->documents->setCreateForm( new Ui_Forms_Upload_Simple(
			'upload', I18n_Json::get( 'upload linkscape json' ), ANTA_URL."/structured/linkscape"
		));
		
		# do not validate?
		if( !$this->_request->isPost() ) return;
		# validate form
		$messages = Anta_Core::validateForm( $form );
		if( $messages !== true ){
			Anta_Core::setError( $messages );
			return;
		};
		
		# clean stuff and send data back
		plog( "linkscape_import", $this->_user );
		
		$tmp_filename = tmp( $_FILES[ 'import_file' ][ 'tmp_name' ] );
		
		# read and parse file
		$command = "php ".APPLICATION_PATH."/routines/linkscape.php -u".$this->_user->id." -d".$this->_user->username." -f".$tmp_filename." -lfr";
		
		alog( "linkscape_import", "loading import script to parse file: '". $_FILES[ 'import_file' ]['name']."'" ,  $this->_user );

		
		proc_close( proc_open (
			$command." &" ,
			array(),
			$foo 
		));
		# view output. Cfr the related view to get all the script
		$this->view->dock = new Ui_Dock();
		
		# dummy csv importer
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 
			'documents', I18n_Json::get( 'structured csv upload' ).": ".$this->_user->username 
		));
		# forward into another view!!
		$this->view->dock->documents->addItem( new Ui_Crafts_Items_Void( 'log-console' ),array('content'=>'<pre></pre>') );
		
	}
	
	
	
	/**
	 * import a lot of documents from a csv file.
	 */
	public function simpleCsvAction(){
		# view output: the title of the page. Cfr the related view to get all the script
		$this->view->dock = new Ui_Dock();
		
		# dummy csv importer
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 
			'documents', I18n_Json::get( 'structured csv upload' ).": ".$this->_user->username 
		));
		
		# has post file data??? send it directly to the python script

	
		#
		
		// exit;
		
		# upload form
		$form = $this->view->dock->documents->setCreateForm( new Ui_Forms_Upload(
			'upload', I18n_Json::get( 'upload csv' ), ANTA_URL."/structured/simple-csv"
		));
		
		# do not validate?
		if( !$this->_request->isPost() ) return;
		
		# validate form
		$messages = Anta_Core::validateForm( $form );
		if( $messages !== true ){
			Anta_Core::setError( $messages );
			return;
		};
		
		# clean stuff and send data back
		plog( "csv_import", $this->_user );
		
		$tmp_filename = tmp( $_FILES[ 'import_file' ][ 'tmp_name' ] );
		
		# move file 
		
		
		# send temporary link to th py script
		$py = new Py_Scriptify( "csv_post.py ".$tmp_filename." ". $this->_user->username." ".$form->user_pass->getValue()." >> ".glog( "csv_import", $this->_user ), false );
		$py->silently();
		
		
		# view: listen to the log file....
		$this->view->dock = new Ui_Dock();
		
		# dummy csv importer
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 
			'documents', I18n_Json::get( 'structured csv upload' ).": ".$this->_user->username 
		));
		
		$this->view->dock->documents->setContent( '<div class="grid_20 prefix_2">importing documents. check the log file '.glog( "csv_import", $this->_user ).' '.$tmp_filename.'
			<pre>'.$py->command.'</pre>
		
		</div>' );
	}
}
?>