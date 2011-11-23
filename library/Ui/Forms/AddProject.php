<?php
/**
 * @package Ui_Forms
 */
/**
 * Allow to upload a single csv file that will be interpreted.
 * The csv parsing is done by Anta_Csv::parse( $filepath )
 */ 
class Ui_Forms_AddProject extends Ui_Form{

	protected function _init(){
	
        $title = new Ui_Forms_Elements_Input( 'text', I18n_Json::get( "project title" ), array(
			"name"  => "project-title",
			"id"    => "project-title",
			"class" => "width_7 margin_1"
		));
		
		$description = new Ui_Forms_Elements_TextArea(  I18n_Json::get( "project description" ), array(
			"name"  => "project-description",
			"id"    => "project-description",
			"class" => "width_7 margin_1 height_4"
		));

		$title->setValidator( new Ui_Forms_Validators_Pattern( array(
			"minLength"=>2,
			"maxLength"=>10,
			"pattern" =>Ui_Forms_Validators_Pattern::$LABEL
		)));
		
		$submit = new Ui_Forms_Elements_Input( "submit", $this->title, array(
			"name"  => "save-project",
			"id"    => "save-project",
			"value" => I18n_Json::get( "create project" )
		));
		
		
		$this->addElement( $title );
		$this->addElement( $description );
		$this->addElement( $submit );
	}

	public function __toString(){
		
		return '
		<form action="'.$this->action.'" method="'.$this->method.'" enctype="multipart/form-data">
		<div class="grid_22 prefix_1 alpha omega">
			<div class="grid_14 alpha margin_1">
					<p>'.$this->project_title->label.'</p>
					'.$this->project_title.' 

					<p class="margin_1">'.$this->project_description->label.'</p>
					'.$this->project_description.'
					'.$this->save_project.'
			</div>
			<div class="grid_8 omega">
				Along with your master project, you can add a maximum of 10 projects.
			</div>
		</div>
		</form>
		';
		
	}	
}
