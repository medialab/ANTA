<?php
class Application_Model_Forms_ModifyUserForm extends Application_Model_Forms_SimpleForm{
	
	protected function _init(){
		
		$fullname = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'modifyUserFullName'), array(
			"name"  => "user-fullname",
			"id"    => "user-fullname",
			"class" => "width_4"
		));
		$fullname->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>3,
			"maxLength"=>200
		)));
		
		
		
		$email = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createUserEmail'), array(
			"name"  => "user-email",
			"id"    => "user-email",
			"class" => "width_4"
		));
	    $email->setValidator( new Application_Model_Forms_Validators_EmailValidator( ));
				
		
		
		
		$password = new Application_Model_Forms_Elements_Input( "password", I18n_Json::get( 'modifyUserPassword'), array(
			"name"  => "password",
			"id"    => "password",
			"class" => "width_4"
		));
		$password->setValidator( new Application_Model_Forms_Validators_PasswordValidator() );
		
		// validate before sending...
		$submit = new Application_Model_Forms_Elements_Input( "submit", $this->title, array(
			"name"  => "add-user",
			"id"    => "add-user",
			"value" => $this->title
		));
		
		
		/** add created element */
		$this->addElement( $fullname );
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
		<div class="grid_22 prefix_1 suffix_1 alpha omega">
			<div class="grid_8 alpha">
				<p class="margin_1">'.$this->user_email->label.'</p>'.$this->user_email.'
				<p class="margin_1">'.$this->user_fullname->label.'</p>'.$this->user_fullname.'
				
			</div>
			<div class="grid_8 prefix_1">
				<p class="margin_1">'.$this->password->label.'</p>'.$this->password.'
				
			</div>
			<div class="grid_3 omega align-right">
				<p class="margin_1">&nbsp;</p>'.$this->add_user.'
			</div>
		</div>
		</form>
		';
		

	}
	
	protected function _loadScript(){
		return;
	
	}
	
}
