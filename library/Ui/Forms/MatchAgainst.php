<?php
/**
 * @package Ui_Forms
 */
/**
 * 
 */ 
class Ui_Forms_MatchAgainst extends Ui_Form{

	protected function _init(){
	
        $query = new Application_Model_Forms_Elements_Input( 'text', I18n_Json::get( "search neigborough words" ), array(
			"name"  => "query",
			"id"    => "query",
			"class" => "width_7 margin_1 "
		));
		
		$query->setValidator( new Ui_Forms_Validators_Pattern( array(
			"minLength"  => 3,
			"maxLength"  => 50,
			"pattern"    => '/^([\da-zçàéèùâêîôû\-]+)$/i',
			"patternDescription" => "Only a single word, a-z 0-9 chars and some special character allowed: ç à é è ù â ê î ô û"
		)));
		
		$maxDistance = new Application_Model_Forms_Elements_Select( I18n_Json::get( "use sentences nearer than" ), array(
			"name"  => "max-distance",
			"id"    => "max-distance",
			"class" => "margin_1",
		));
		
		$maxDistance->addOptions( array(
			new Application_Model_Forms_Elements_Option( "0", 0 ), 
			new Application_Model_Forms_Elements_Option( "1", 1 ),
			new Application_Model_Forms_Elements_Option( "2", 2 ), 
			new Application_Model_Forms_Elements_Option( "3", 3 ),
			new Application_Model_Forms_Elements_Option( "4", 4 ),
		));
		
		$maxDistance->setValidator( new Ui_Forms_Validators_NumericRange( array(
			"min" => 0,
			"max" => 4
		)));
		
		$language = new Application_Model_Forms_Elements_Select( I18n_Json::get( "word language" ), array(
			"name"  => "language",
			"id"    => "language",
			"class" => "margin_1",
		));
		
		$language->addOptions( array(
			new Application_Model_Forms_Elements_Option( "--", "--" ), 
			new Application_Model_Forms_Elements_Option( I18n_Json::get("français"), "fr" ),
			new Application_Model_Forms_Elements_Option( I18n_Json::get("english"), "en" ,  true ), 
			new Application_Model_Forms_Elements_Option( I18n_Json::get("italiano"), "it" ),
		));
		
		$language->setValidator( new Ui_Forms_Validators_Match( array(
			"minLength"  => 2,
			"maxLength"  => 2,
			"availables" => array(
				"en", "fr","it"
			)
		)));
		
		$useStemming = new Application_Model_Forms_Elements_Input( "checkbox", I18n_Json::get( "use stemmed version of words" ), array(
			"name"  => "use-stemming",
			"id"    => "use-stemming",
			"class" => "margin_1",
		));
		
		$useStemmedResult = new Application_Model_Forms_Elements_Input( "checkbox", I18n_Json::get( "group result by stemmed version of words" ), array(
			"name"  => "use-stemmed-result",
			"id"    => "use-stemmed-result",
			"class" => "margin_1",
		));
		
		$submit = new Application_Model_Forms_Elements_Input( "submit", $this->title, array(
			"name"  => "send-fields",
			"id"    => "send-fields",
			
			"value" => I18n_Json::get( "find matches" )
		));
		
		$export = new Application_Model_Forms_Elements_Input( "image", $this->title, array(
			"name"  => "export-fields[image]",
			"id"    => "export-fields",
			"src"	=> Anta_Core::getbase()."/images/download.csv.png",
			"title" => "save search results as csv file",
			"value" => I18n_Json::get( "export matches" )
		));

		$this->content = "";
		
		/** add created element */
		$this->addElement( $query );
		$this->addElement( $maxDistance );
		$this->addElement( $useStemming );
		$this->addElement( $useStemmedResult );
		$this->addElement( $language );
		$this->addElement( $submit );
		$this->addElement( $export );
    }
	
	public function __toString(){
		$this->content = '
		<div class="grid_24 alpha omega">
			<div class="grid_24 alpha omega margin_1">
				<div class="grid_20 alpha">
					<p>'.$this->query->label.'</p>
					'.$this->query.' '.$this->send_fields.'
					<span class="margin_1">'.$this->export_fields.'</span>
				</div>
				
			</div>
			<div class="grid_24 alpha omega">
					<div class="grid_4 alpha margin_1">
						<p class="margin_1">'.$this->language->label.'</p>
						'.$this->language.'
					</div>
					<div class="grid_4 margin_1">
						<p class="margin_1">'.$this->max_distance->label.'</p>
						'.$this->max_distance.'
					</div>
					<div class="grid_4 margin_1">
						<p class="margin_1">'.$this->use_stemming->label.'</p>
						'.$this->use_stemming.'
					</div>
					<div class="grid_6 omega margin_1">
						<p class="margin_1">'.$this->use_stemmed_result->label.'</p>
						'.$this->use_stemmed_result.'
					</div>
				</div>
		</div>
		
		';
		return parent::__toString();
	}
}
?>