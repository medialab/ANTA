<?php
/**
 * @package Ui_Forms_Upload
 */
/**
 * Allow to upload a single file that will be interpreted later.
 */ 
class Ui_Forms_Upload_Simple extends Ui_Form{

	protected function _init(){
	
        $file = new Application_Model_Forms_Elements_Input( 'file', I18n_Json::get( "chose file" ), array(
			"name"  => "import_file",
			"id"    => "import_file",
			"class" => "width_6 margin_1"
		));
		
		$file->setValidator( new Ui_Forms_Validators_FileUpload() );
		
		
		$submit = new Application_Model_Forms_Elements_Input( "submit", $this->title, array(
			"name"  => "do_import",
			"id"    => "do_import",
			"value" => I18n_Json::get( "import file" )
		));
		
		
		
		$this->addElement( $file );
		$this->addElement( $submit );
	}

	public function __toString(){
		
		return '
		<form action="'.$this->action.'" method="'.$this->method.'" enctype="multipart/form-data">
		<div class="grid_22 prefix_1 suffix_1 alpha omega">
			<div class="grid_20 alpha margin_1">
					<p>'.$this->import_file->label.'</p>
					
					'.$this->import_file.' 
	
					
					'.$this->do_import.'
			</div>
		</div>
		</form>
		';
		
	}	
}