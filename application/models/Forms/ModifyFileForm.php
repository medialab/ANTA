<?php
class Application_Model_Forms_ModifyFileForm extends Application_Model_Forms_SimpleForm{
	
	protected function _init(){
		
		$title = new Application_Model_Forms_Elements_Input( "text", I18n_Json::get( 'createFileTitle'), array(
			"name"  => "file-title",
			"id"    => "file-title",
			"maxlength"=>200,
			"class" => "width_4 height_1"
		));
		
		$title->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>3,
			"maxLength"=>200
		)));
		
		/**
		$description = new Application_Model_Forms_Elements_TextArea( I18n_Json::get( 'createFileDescription'), array(
			"name"  => "file-description",
			"id"    => "file-description",
			"class" => "width_4 height_4"
		));
	    $description->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>0,
			"maxLength"=>1000
		)));
		*/
		$date = new Application_Model_Forms_Elements_Input( "text", "date (dd/mm/yyyy)", array(
			"name"      => "file-date",
			"id"        => "file-date",
			"minLength" => 0,
			"maxLength" => 10,
			"class"     => "datepicker width_4 height_1"
		));
		
		$date->setValidator( new Application_Model_Forms_Validators_DateValidator( array( 
			"minLength"=>0,
			"maxLength"=>10
		)));
		
		/**
		$author = new Application_Model_Forms_Elements_TagsArea( I18n_Json::get( 'createFileAuthor'), array(
			"name"=>"file-author",
			"id"=>"file-author",
			"class" => "width_4 height_4"
		));
		$author->setValidator( new Application_Model_Forms_Validators_TextValidator( array(
			"minLength"=>0,
			"maxLength"=>50
		)));
		*/
		// creating available language select
		$languages = new Application_Model_Forms_Elements_Select( I18n_Json::get( 'createFileLanguage' ), array(
			"name"  => "file-lang",
			"id"    => "file-lang",
			"class" => "width_2 height_1"
		));
		$languages->addOptions( array(
			new Application_Model_Forms_Elements_Option( "english" , 'en' ), 
			new Application_Model_Forms_Elements_Option( "français", 'fr' ),
			new Application_Model_Forms_Elements_Option( "italian" , 'it' ),
			new Application_Model_Forms_Elements_Option( "español" , 'es' )
		));
		
		$languages->setValidator( new Ui_Forms_Validators_Match( array(
			"minLength"  => 2,
			"maxLength"  => 2,
			"availables" => array(
				"en", "fr","it", "es"
			)
		)));
		
		
		
		$submit = new Application_Model_Forms_Elements_Input( "submit", $this->title, array(
				"name"  => "add-file",
				"id"    => "add-file",
				"value" => $this->title));
		
		
		/** add created element */
		$this->addElement( $title );
		//$this->addElement( $description );
		//$this->addElement( $author );
		$this->addElement( $date );
		$this->addElement( $languages );
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
		<div class="grid_24 alpha omega">
			<div class="grid_19 alpha">
			
				<div class="grid_6 prefix_1 alpha margin_1">
					<input type="hidden" name="form-action" value="'.$this->id.'"/>'.$this->file_title->label.'</p>'.$this->file_title.'
					
				</div>
				<div class="grid_6 prefix_1 margin_1">
					<p>'.$this->file_date->label.'</p>'.$this->file_date.'
					
				</div>
				<div class="grid_4 prefix_1 omega margin_1">
					<p>'.$this->file_lang->label.'</p>'.$this->file_lang.'
				</div>
			</div>
			<div class="grid_5 omega align-right margin_1" style="text-align:right">
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
