<?php
/**
 * @package Ui_Forms
 */
/**
 * Allow to upload a single csv file that will be interpreted.
 * The csv parsing is done by Anta_Csv::parse( $filepath )
 */ 
class Ui_Forms_AddGoogle extends Ui_Form{

	protected function _init(){
	
        $title = new Ui_Forms_Elements_Input( 'text', I18n_Json::get( "search google" ), array(
			"name"  => "google-query",
			"id"    => "google-query",
			"class" => "width_7 margin_1"
		));
		
		$addQuery = new Ui_Forms_Elements_Input( 'button', I18n_Json::get( "add query to queue" ), array(
			"name" => "add-query-to-queue",
			"id" => "add-query-to-queue",
			"value" => I18n_Json::get( "add query to queue" )
		));
		
		
		

		$title->setValidator( new Ui_Forms_Validator( array(
			"minLength"=>2
		)));
		
		$submit = new Ui_Forms_Elements_Input( "submit", $this->title, array(
			"name"  => "save-project",
			"id"    => "save-project",
			"value" => I18n_Json::get( "start crawl" )
		));
		
		
		$this->addElement( $title );
		$this->addElement( $addQuery );
		
		$this->addElement( $submit );
	}

	public function __toString(){
		
		return '
		<form action="'.$this->action.'" method="'.$this->method.'" enctype="multipart/form-data">
		<div class="grid_22 prefix_1 alpha omega">
			<div class="grid_16 alpha margin_1">
					<p>'.$this->google_query->label.'</p>
					'.$this->google_query.' 
					
					
					
			</div>
			<div class="grid_6 omega">
				Once done, the request will be send to your favourite crawler
				'.$this->save_project.'
			</div>
		</div>
		</form>
		';
		
	}	
}
