<?php
/**
 * @package Ui
 */
 
/**
 * Shows the identity, otherwise a login form. It Contains a logout button.
 * THe action controllers are /index/login (that is tha action for the form post as well)
 * and /index/logout to destroy session and identity.
 */
class Application_Model_Ui_Boards_IdentityBoard extends Ui_Boards_TodaySpecial {
	public function getLoginForm(){
		return $this->_loginForm;
	}
	protected static $_instance;
	public static function getInstance( array $properties=array() ){
		if( self::$_instance == null ){
			self::$_instance = new Application_Model_Ui_Boards_IdentityBoard();
			self::$_instance->init( $properties );
		}

		return self::$_instance;
	}
	protected function _autoSelectItem(){
		// get controller and action
		$front = Zend_Controller_Front::getInstance();
		$selected = $front->getRequest()->getParam( 'controller' ) . "." . $front->getRequest()->getParam( 'action' );
		
		if( isset( $this->items[ $selected ] ) ){
			
			$this->items[ $selected ] -> select();
		} else if( isset( $this->items[ $this->aliases[ $selected ] ]) ){
			$this->items[ $this->aliases[ $selected ] ] -> select();
		}
		
	}
	public $aliases =array(
		"documents.import-from-google-spreadsheet"=>"documents.list",
		"documents.import-tags" => "documents.list",
		"logs.read" => "thread.index",
		"entities.included" => "entities.list",
		"entities.excluded" => "entities.list",
		"projects.create" => "projects.list"
	);
	public function init( $properties ){
		
		if( ! Zend_Auth::getInstance()->hasIdentity() ){
			$this->_loginForm = new Application_Model_Forms_LoginForm( 'login', I18n_Json::get( 'request-login' ), Anta_Core::getBase().'/index/login' );
			return;
		}
		
		$identity = $this->identity = Zend_Auth::getInstance()->getIdentity();
		
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		
		// docs
		$this->items[ "add.documents" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/add/documents/user/'.  $identity->cryptoId,
			I18n_Json::get( 'include docs' ),
			array( 'class' =>"pointing-item")

		);
		
		$this->items[ "documents.list" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/documents/list/user/'.  $identity->cryptoId,
			I18n_Json::get( 'tag docs' ),
			array( 'class' =>"pointing-item")

		);
		
		
		$this->items[ "prepare.visualization" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase().'/prepare/visualization/user/'.$identity->cryptoId,
			I18n_Json::get( 'include entities' ),
			array( 'class' =>"pointing-item")
		);
		
		$this->items[ "entities.list" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase().'/entities/list/user/'.$identity->cryptoId,
			I18n_Json::get( 'tag entities' ),
			array( 'class' =>"pointing-item")
		);
		
		
		
		
		$this->items[ "visualize.gexf" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase().'/visualize/gexf/user/'.$identity->cryptoId,
			I18n_Json::get( 'export' )
		);
		
		
		
		 $this->_autoSelectItem();
	}
	
	/**
	 * Same as its parent, but inject some javascript code into the page to handle ajax-href
	 * behaviour( not ereal link, but api request)
	 */
	public function __toString(){
	
		if( ! Zend_Auth::getInstance()->hasIdentity() ){
			return (string) $this->_loginForm;
		
		}
		
		$defaultitems = implode( "", $this->items );
		
		$this->items[ "users.whois" ] = new Ui_Boards_TodaySpecials_Item( 
			Anta_Core::getBase().'/users/whois/user/'.$this->identity->cryptoId, 
			I18n_Json::get( 'your account' ), array( 'class' => "admin" ) 
		);
		
		
		
		$this->items[ "projects.list" ] = new Ui_Boards_TodaySpecials_Item( 
			Anta_Core::getBase().'/projects/list',
			I18n_Json::get( 'PROJECTS' ),
			array( 'class' =>"admin")
		);		

		$this->items[ "index.logout" ] = new Ui_Boards_TodaySpecials_Item( 
			Anta_Core::getBase().'/index/logout',
			I18n_Json::get( 'logout' ),
			array( 'class' =>"logout")
		);

		if( $this->identity->is( 'admin' ) ){
			$this->items[ "admin.index" ] = new Ui_Boards_TodaySpecials_Item( Anta_Core::getBase()."/admin", I18n_Json::get( 'adminPage' ), array( 'class' => "admin" ) );
		}
		
		$this->_autoSelectItem();
		
		return '
			<div class="grid_24 alpha omega identity-board">
				<div class="grid_17 alpha">
				<ul>'.$defaultitems.'</ul>
				</div>
			<div class="grid_7 omega">
				<ul class="centered">
				'.$this->items[ "admin.index" ].'
				'.$this->items[ "projects.list" ].'				
				'.$this->items[ "users.whois" ].'
				'.$this->items[ "index.logout" ].'
				</ul>
			</div>
			</div>
			
		';
		
	}
	
	
	
	/**
	 * auto add "entry-selected" to class if the given url match the URL_REQUEST url
	 */
	public function addEntry( $url, $title, $atts=array() ){
		return;
		if( $_SERVER[ 'REQUEST_URI' ] ==  $url){
			$atts[ 'class' ] = isset( $atts[ 'class' ] )? $atts[ 'class' ].' entry-selected':'entry-selected';
		}
		
		$attributes = "";
		
		foreach( $atts as $k=>$v )
			$attributes .= $k.'="'.$v.'" ';
			
		$this->items[] =  ' '.$this->_s.'<a href="'.$url.'" '.$attributes.'>'.$title.'</a>';
	}
	
	
	public function addTextEntry( $content ){
		
		$this->items["free"] =  $content;
	}
	
	
	
}
