<?php
/**
 * howto: // check identity in an action 
 *	           $user = $this->_authorizeUser( $this->_getUser() );
 */
class ApiSheepController extends Zend_Controller_Action{
	/** a Dnst_Json_Response instance */
	protected $_response;
	protected $_user;
	
    public function init()
    {
		/* Initialize action controller here */
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		/** reinitialize headers */
		Anta_Core::setHttpHeaders("text/plain");
		
		// initialize json response
		$this->_response = new Dnst_Json_Response();
		$this->_response->setStatus( 'ok' );
		
		// add verbose information only if it is specified in htttp params
		if( isset( $_REQUEST[ 'verbose' ] ) ){
			$this->_response->params = $this->_request->getParams(); 
		}
		
		$this->_user = $this->_getUser( false );
		
	}
	
	
	/**
	 * after doing some "readsomeline", user can sync all the documents in the google doc with the 
	 * element provided
	 */
	public function syncDocumentsAction(){
		$this->_response->setAction( 'sync-documents');
		
		// see below, readSomelinesAction function
		$sheepToken = $this->_sheepToken();
		$googleKey = $this->_getGoogleKey();
		
		
		// get crypted username and password
		$googleUser = $this->_request->getParam( "google-user" );
		$googlePasswd = $this->_request->getParam( "google-passwd" ); 
		
		// decrypt using session token
		if( empty( $googleUser ) || empty( $googlePasswd ) ){
			$this->_response->throwError( $this->_response->throwError( "unauthenticated" ) );
		}
		
		// decrypt using session token
		$googleUser = Dnst_Crypto_Des::des ( $sheepToken, Dnst_Crypto_Des::hexToString( $googleUser ), 0, 0, 2 );
		$googlePasswd = Dnst_Crypto_Des::des ( $sheepToken, Dnst_Crypto_Des::hexToString( $googlePasswd ), 0, 0, 2 );
		
		// open the zend connection with google service
		$sheep = new Dnst_SpreadSheep( $googleUser, $googlePasswd );
		
		// test the connectovity
		if( !$sheep->isValid() ){
			$this->_response->messages = $sheep->getMessages();
			$this->_response->throwError( $this->_response->throwError( "wrong-credentials" ) );
		}
		// set google working key
		$sheep->googleKey = $googleKey;
		
		$this->_response->sheep = $sheep; 
		
		// get column headers
		$googleDoc = (object) array();
		$googleDoc->title = (string) $sheep->getTitle();
		$googleDoc->status = "ok";
		$googleDoc->headers = $sheep->getHeaders();
		$rows = $sheep->getRow( 0 );
		
		// 
		
		// default headers
		$requiredHeaders = array( 'id doc',	'id hash', 'title', 'ignore', 'date','language','description' );
		
		if( count( array_intersect( $requiredHeaders, $googleDoc->headers ) ) != count( $requiredHeaders ) ){
			$googleDoc->error = "some required column name hasn't been found...";
			$googleDoc->status = "ko";
		};
		
		// save customheaders as ncategories
		$customHeaders = array_diff( $googleDoc->headers, $requiredHeaders );
		$googleDoc->customHeaders = $customHeaders;
		
		foreach( $customHeaders as $header ){
			Application_Model_CategoriesMapper::add( $this->_user, $header );
		}
		$affectedDocuments = 0;
		// cycle through rows
		// get document id
		for( $i = 2; $i < count( $rows ) ; $i++){
			$row = $rows[$i];
			$idDocument = $row[ 'id doc' ];
			
			if( !is_numeric( $idDocument ) ) continue;
			
			$affected = Application_Model_DocumentsMapper::editDocument(
				$this->_user, $idDocument,
				$row[ "title" ],
				$row[  "description" ], $row[  "date" ],
				$row[  "language" ]
			);
			
			$affectedDocuments++;
			
			// custom fields
			foreach( $customHeaders as $header ){
				
				$values = explode( ",", $row[ $header ] );
				
				// cycle through value
				foreach( $values as $value ){
					$value = trim( $value );
					// ignore null
					if( strlen( $value ) == 0 ) continue;
					// create / get id tag
					Application_Model_DocumentsTagsMapper::add(  $this->_user, $idDocument, Application_Model_TagsMapper::add( $this->_user, $value,  $header ) );
						
				}
			}
		} 
		
		$googleDoc->affectedDocuments = $affectedDocuments;
		
		$this->_response->google = $googleDoc;
		
		if( isset( $_GET['debug'] ) ){
			print_r( json_decode( $this->_response ) );
		}
		echo $this->_response;
	}
	
	protected function _validateHeaders( $headers ){
		
	}
	
	public function syncEntitiesAction(){
		$this->_response->setAction( 'sync-entities');
		echo $this->_response;
	}

	
	/**
	 * store an unique key in local session variables.
	 * It generates a one time tooken into the $this->_response variable.
	 */
	protected function _sheepToken(){
		// create a new unique session key, immediately
		$token = "DdayEkJc4lHuPAmK8v9tIrFe3z0wBgpj567qLGhNofsb1MOCxn2";//str_shuffle("0123456789abcdefghjklmnopqrstuvwxyzABCDEFGHIJKLMNOP");
		$this->_response->token = $token;
		
		if( !isset( $_SESSION['sheep-ottoken'] ) ){
			$_SESSION['sheep-ottoken'] = $token;
		}
		
		// get old token value from session
		$sheepToken = @$_SESSION['sheep-ottoken'];
		$this->_response->token = $sheepToken;
		// override/create ottoken
		// $_SESSION['sheep-ottoken'] = $token;
		
		return $sheepToken;
	}
	
	protected function _getGoogleKey(){
		// get key from url
		$googleKey = $this->_request->getParam( "google-key" );
		$googleRealKey = $this->_request->getParam( "google-real-key" );
		// if it's an url, get the key
		if( $googleKey == null){
			if( $googleRealKey != null ){
				return $googleRealKey;
			}
			$this->_response->throwError( $this->_response->throwError( "'google-key' param shouldn't be null" ) );
		}
		
		
		
		$googleKey = Dnst_SpreadSheep::getGoogleKeyFromUrl(  $googleKey  );
		
		if( $googleKey === false ){
			$this->_response->throwError( $this->_response->throwError( "the google document url provided via 'google-key' param is not valid" ) );
		}
		
		$this->_response->googleKey = $googleKey;
		return $googleKey;
	}
	
	
	/**
	 * with one time token
	 * Use Triple Des crypto function with one time password.
	 * A one time token - the sheepToken - is created at every request to this function.
	 * The sheepToken is used to decrypt some text received from javascript, crypted with the 3Des
	 * via javascript using the sheeptoken sent during the precedent request. From cliant side,
	 * the sheepToken must be updated with the token param received in every ajax response.
	 * 
	 * 
	 */
	public function readSomeLinesAction(){
		
		$this->_response->setAction( 'query');
		
		$sheepToken = $this->_sheepToken();
		
		
		$this->_response->matchToken = $sheepToken;
		
		$googleKey = $this->_getGoogleKey();
		
		// get crypted username and password
		$googleUser = $this->_request->getParam( "google-user" );
		$googlePasswd = $this->_request->getParam( "google-passwd" ); 
		
		// decrypt using session token
		if( empty( $googleUser ) || empty( $googlePasswd ) ){
			$this->_response->throwError( $this->_response->throwError( "unauthenticated" ) );
		}
		
		// decrypt using session token
		$googleUser = Dnst_Crypto_Des::des ( $sheepToken, Dnst_Crypto_Des::hexToString( $googleUser ), 0, 0, 2 );
		$googlePasswd = Dnst_Crypto_Des::des ( $sheepToken, Dnst_Crypto_Des::hexToString( $googlePasswd ), 0, 0, 2 );
		
		$this->_response->googleUser = $googleUser;
		
		// open the zend connection with google service
		$sheep = new Dnst_SpreadSheep( $googleUser, $googlePasswd );
		
		// test the connectovity
		if( !$sheep->isValid() ){
			$this->_response->messages = $sheep->getMessages();
			$this->_response->throwError( $this->_response->throwError( "wrong-credentials" ) );
		}
				
		// set google working key
		$sheep->googleKey = $googleKey;
		
		$this->_response->sheep = $sheep; 
		
		// get column headers
		$headers = $sheep->getHeaders();
		
		// get google object
		$googleDoc = (object) array();
		$googleDoc->title = (string) $sheep->getTitle();
		$googleDoc->status = "ok";
		$googleDoc->headers = $sheep->getHeaders();
		
		
		
		$googleDoc->samples = $sheep->getRow(2);
		
		$googleDoc->evaluated = array();
		// default headers
		
		$customHeaders = $this->_evaluateRequiredHeaders( $googleDoc );
		$googleDoc->customHeaders = $customHeaders;
		
		foreach( $googleDoc->headers as $header ){
			if( ! isset( $googleDoc->samples[ $header ] ) ){
				$googleDoc->evaluated[] = "ok";
				continue;
			}
			$result = $this->_evaluateCell( $header, $googleDoc->samples[ $header ] ) ;
			if( $result === true ){
				$googleDoc->evaluated[] = "ok";
			} else {
				$googleDoc->status = "ko";
				$googleDoc->error = $result; 
				$googleDoc->evaluated[] = "ko";
			}
		}
		
		// output google document
		$this->_response->googleDoc = $googleDoc;
		
		// get info
		echo $this->_response;
	}
	
	/**
	 * exit with error; return the custom header if everything is ok
	 */
	protected function _evaluateRequiredHeaders( $googleDoc ){
		$requiredHeaders = array( 'id doc',	'id hash', 'title', 'ignore', 'date','language','description' );
		
		$foundHeaders = array_intersect( $requiredHeaders, $googleDoc->headers );
		
		if( count( $foundHeaders ) != count( $requiredHeaders ) ){
			$googleDoc->error = "some required column name hasn't been found: '".implode("', '",array_diff( $requiredHeaders, $foundHeaders ))."'";
			$googleDoc->status = "ko";
			$this->_response->googleDoc = $googleDoc;
			exit( $this->_response );
		};
		
		return array_diff( $googleDoc->headers, $requiredHeaders );
	}
	
	protected function _evaluateCell( $header, $value ){
		// evaluate column headers
		switch( $header ){
			case "id doc":
				if( is_numeric( $value ) ) return true;
				else return "'id doc' value $value should be an integer representing a document";
			
			case "date":
				// evaluate date
				$validator = new Ui_Forms_Validators_Date ( array( "minLength" => 10, "maxLength" => 10 ) );
				if( $validator->isValid( $value  ) ){
					return true;
				}
				return $validator->getPlainMessages() ;
		}
		
		return true;
	}
	
	/**
	 * Handle user param error. Return the user if is valid.
	 * If the user is not provided or is not valid, exit with json error
	 */
	protected function _getUser( $forceAuth = true ){
		if ( $this->_request->getParam( 'user' ) == null ){
			$this->_response->throwError( "user not found" );
		}
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		if(  $forceAuth ){
			// the current identity
			$identity = Zend_Auth::getInstance()->getIdentity();
			
			if( $identity == null ){
				$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not authenticated, maybe your session has expired" );
			}
			
			// the user has not the right to handle other users docs
			if( $identity->id != $idUser && !$identity->is( 'admin' ) ){
				$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not authorized" );
			} 
		}
		// load the user
		$user = Application_Model_UsersMapper::getUser( $idUser );
		
		if( $user == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not found!" );
		}
		
		return $user;
	}
	
	/**
	 * action not found handler
	 */
	public function __call( $a, $b ){
		$action = str_replace( "Action", "", $a );
		
		// method available?
		
		$this->_response->setAction( $action );
		$this->_response->throwError( "action '$action' not found" );
	}
	
}
?>