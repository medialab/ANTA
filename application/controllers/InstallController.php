<?php
class InstallController extends Zend_Controller_Action
{
	/** 
	 * the database and anta user
	 * @var Application_Model_User
	 */
	protected $_user;
	
    public function init()
    {
       
		
	}
	
	public function instructionAction(){
		
		$this->view->dock = new Ui_Dock();
		
		$this->view->dock->addCraft( new Ui_Craft( 'install-instruction-a', I18n_Json::get( "prerequisites" ) ) );
		
		
		
		$this->view->dock->addCraft( new Ui_Craft( 'install-instruction-c', I18n_Json::get( "mysql database configuration" ) ) );
		
		
		
		$this->view->dock->addCraft( new Ui_Craft( 'install-instruction-d', I18n_Json::get( "uploads folder configuration" ) ) );
		
		
		$this->view->dock->addCraft( new Ui_Craft( 'check-analysis', I18n_Json::get( "test analysis script" ) ) );
		
		$this->view->dock->check_analysis->setContent('
			<div class="grid_22 prefix_2 alpha omega text-preview">
				<h3>Verify global behaviour</h3>
				check thtat <a href="/anta_application/type-distiller.php?debug=true">this links</a> point toward the correct location,
			i.e 
			<pre class="margin_1">/anta_application/type-distiller.php?debug=true</pre>
			You should get a text/plain document, like this one
			<pre>
--
2011-04-10 19:00:24
param \'?user=\'\' was not found, or is not a valid user
received from cmd: ehm, cmd line is not in use...
memory peak: 7602176
errors: []
elapsed: 0.052946805953979
--
</pre>
			<h3 class="margin_1">start analysis, without documetn. Check working process</h3>
			<pre>sudo ps aux | grep php</pre>
			</div>
		');
		
		

		
		$this->render( 'index' );
	}
	
	/**
	 * installation queue
	 * create virtualhost or simply aliases:
	 * - http://domain/anta_application/
	 * - http://domain/anta_dev/
	 * create user "anta" and attibutes all privileges ( anta user will create other db users and other database, like a virus )
	 * create anta database
	 * setup application.ini file with your mysql server access info
	 * create a dummy admin with username 'dummy' (db user : anta_dummy) and password 'admini'
	 * modify the 
	 */
	public function indexAction(){
		// read mysql stuff
		$query = "
			CREATE USER 'anta'@'localhost' IDENTIFIED BY '***';
			GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP,	FILE, INDEX, ALTER,	SHOW DATABASES,	SUPER,
			CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE,
			CREATE USER, EXECUTE ON * . * TO 'anta'@'localhost IDENTIFIED BY '***' WITH GRANT OPTION
			MAX_QUERIES_PER_HOUR 0
			MAX_CONNECTIONS_PER_HOUR 0
			MAX_UPDATES_PER_HOUR 0
			MAX_USER_CONNECTIONS 0 ;

			CREATE DATABASE IF NOT EXISTS `anta` ;
			GRANT ALL PRIVILEGES ON `anta` . * TO 'anta'@'localhost'";
		
		// load ini connect file
		// load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "database" );
		
		// print_r( $config );
		
		// try to add admin user
		Application_Model_UsersMapper::addUser(
				"Dummy Admin",
				"dummy","",
				"admini",
				"admin"
		);
		
		// try to connect
		
		// create "admin user"
	}
	
	public function mysqlAction(){
		$this->_response->setAction( 'install-mysql' );
		
		
		echo $this->_response;
	}
	
	private function exceptionHandler( Exception $e ){
		$this->_response->errorCode = $e->getCode();
		$this->_response->exception = get_class($e);
		$this->_response->throwError( $e->getMessage() );
	}
	
}
