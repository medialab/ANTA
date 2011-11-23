<?php
class Application_Model_Forms_CreateUserForm extends Application_Model_Forms_SimpleForm{
	
	protected function _init(){
		
		$firstname = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createUserFirstName'), array(
			"name"  => "user-firstname",
			"id"    => "user-firstname",
			"class" => "width_4 height_1"
		));
		$firstname->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>3,
			"maxLength"=>100
		)));
		
		$lastname = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createUserLastName'), array(
			"name"  => "user-lastname",
			"id"    => "user-lastname",
			"class" => "width_4 height_1"
		));
	    $lastname->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>3,
			"maxLength"=>100
		)));
		
		$email = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createUserEmail'), array(
			"name"  => "user-email",
			"id"    => "user-email",
			"class" => "width_4 height_1"
		));
	    $email->setValidator( new Application_Model_Forms_Validators_EmailValidator( ));
				
		$username = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createUserName'), array(
			"name"  => "username",
			"id"    => "username",
			"class" => "width_4 height_1"
		));
		$username->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>3,
			"maxLength"=>10
		)));
		
		
		$password = new Application_Model_Forms_Elements_Input( "password", I18n_Json::get( 'createUserPassword'), array(
			"name"  => "password",
			"id"    => "password",
			"class" => "width_4 height_1"
		));
		$password->setValidator( new Application_Model_Forms_Validators_PasswordValidator() );
		
		// validate before sending...
		$submit = new Application_Model_Forms_Elements_Input( "submit", $this->title, array(
			"name"  => "add-user",
			"id"    => "add-user",
			"value" => $this->title
		));
		
		
		/** add created element */
		$this->addElement( $firstname );
		$this->addElement( $lastname );
		$this->addElement( $username );
		$this->addElement( $email );
		$this->addElement( $password );
		$this->addElement( $submit );
	}
	
	
	/**
	 * You always need to extend the toString method to 
	 * render your jquery dialog form
	 * calling __String will construct the DIALOG BOX
	 */
	public function __toString(){
		return '
		<form action="'.$this->action.'" method="'.$this->method.'" enctype="multipart/form-data">
		<div class="grid_22 alpha omega">
			<div class="grid_8 alpha">
				<div class="omega">
				<p class="margin_1">'.$this->user_email->label.'</p>'.$this->user_email.'
				</div>
				<div class="omega">
				<p class="margin_1">'.$this->user_firstname->label.'</p>'.$this->user_firstname.'
				</div>
				<div class="omega">
				<p class="margin_1">'.$this->user_lastname->label.'</p>'.$this->user_lastname.'
				</div>
			</div>
			<div class="grid_8 prefix_1">
				<div class="omega">
				<p class="margin_1">'.$this->username->label.'</p>'.$this->username.'
				</div>
				<div class="omega">
				<p class="margin_1">'.$this->password->label.'</p>'.$this->password.'
				</div>
			</div>
			<div class="grid_3 omega align-right">
				'.$this->add_user.'
			</div>
		</div>
		</form>
		';
		

	}
	
	protected function _loadScript(){
		return;
	
	}
	
}
