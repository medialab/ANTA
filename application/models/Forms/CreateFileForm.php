<?php
class Application_Model_Forms_CreateFileForm extends Application_Model_Forms_SimpleForm{
	
	protected function _init(){
		
		$title = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createFileTitle'), array(
			"name"  => "file-title",
			"id"    => "file-title",
			"class" => "width_4 height_1"
		));
		$title->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>3,
			"maxLength"=>200
		)));
		
		$description = new Application_Model_Forms_Elements_TextArea( I18n_Json::get( 'createFileDescription'), array(
			"name"  => "file-description",
			"id"    => "file-description",
			"class" => "width_4 height_4"
		));
	    $description->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>0,
			"maxLength"=>1000
		)));
		
		
		
		$author = new Application_Model_Forms_Elements_TagsArea( I18n_Json::get( 'createFileAuthor'), array(
			"name"=>"file-author",
			"id"=>"file-author",
			"class" => "width_4 height_4"
		));
		$author->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>0,
			"maxLength"=>50
		)));
		
		$content = new Application_Model_Forms_Elements_Input( "file", I18n_Json::get( 'createFileUpload'), array(
			"name"  => "file-content",
			"id"    => "file-content",
			"class" => "ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
		));
		
		$title->setValidator( new Zend_Validate_StringLength(array('min' => 3, 'max' => 256 ) ) );
		
		$content->setValidator( new Application_Model_Forms_Validators_FileUploadValidator() );
		
		// validate before sending...
		$submit = new Application_Model_Forms_Elements_Input( "submit", $this->title, array(
				"name"  => "add-file",
				"id"    => "add-file",
				"value" => $this->title));
		
		
		/** add created element */
		$this->addElement( $title );
		$this->addElement( $description );
		$this->addElement( $author );
		$this->addElement( $content );
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
		<div class="grid_19 alpha omega">
			<div class="grid_16 alpha">
			
				<div class="grid_6 alpha">
					<p><input type="hidden" name="form-action" value="'.$this->id.'"/>'.$this->file_title->label.'</p>'.$this->file_title.'
					<p class="margin_1">'.$this->file_description->label.'</p>'.$this->file_description.'
				</div>
				<div class="grid_6 prefix_1 suffix_3 omega">
					<p>'.$this->file_content->label.'</p>'.$this->file_content.'
					<p class="margin_1">'.$this->file_author->label.'</p>'.$this->file_author.'
				</div>
				
			</div>
			<div class="grid_3 omega align-right">
				'.$this->add_file.'
			</div>
		</div>
		</form>
		';
		

	}
	
	protected function _loadScript(){
		return;
	
	}
	
}
