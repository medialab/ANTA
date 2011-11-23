<?php
/**
 * @package Ui_Items
 */

/**
 * A thing is a group of entities. Use a list of entities
 * to build this thing.
 */
class Application_Model_Ui_Items_AvailableThread {
 
	public function __construct( Application_Model_User $user ){
		$this->user = $user;
	}
	
	public function __toString(){
		return '
		<div class="grid_24 alpha omega ">
		 
		 <div class="grid_7 prefix_1 alpha" style="text-align:center"><a href="'.Anta_Core::getBase().'/thread/add/type/alchemy/user/'.$this->user->cryptoId.'" class="tip-helper" title="'.I18n_Json::get('threadsAddAlchemy').'"><img src="'.Anta_Core::getBase().'/images/alchemy.png" alt="add alchemy analysis to the queue"></a></div>
		 <!-- <div class="grid_7 prefix_1" style="text-align:center"><a href="'.Anta_Core::getBase().'/thread/add/type/opencalais/user/'.$this->user->cryptoId.'" class="tip-helper" title="'.I18n_Json::get('threadsAddOpenCalais').'"><img src="'.Anta_Core::getBase().'/images/opencalais.png" alt="add opencalais web-service analysis to the queue"></a></div>
		 -->
		 <div class="grid_7 prefix_1" style="text-align:center"><a href="'.Anta_Core::getBase().'/thread/add/type/ngram/user/'.$this->user->cryptoId.'" class="tip-helper" title="'.I18n_Json::get('ngram standard analysis').'"><img src="'.Anta_Core::getBase().'/images/opencalais.png" alt="add ngram experimental service analysis to the queue"></a></div>
		 
		 <div class="grid_7 prefix_1 omega" style="text-align:center"><a href="'.Anta_Core::getBase().'/thread/add/type/stemming/user/'.$this->user->cryptoId.'" class="tip-helper" title="'.I18n_Json::get('threadsAddStemming').'"><img src="'.Anta_Core::getBase().'/images/stemmings.png" alt="add alchemy analysis to the queue"></a></div>
		 
		 
		</div>
		<div class="grid_24 alpha omega ">
		 
		 <div class="grid_7 prefix_1 alpha" style="text-align:center">'.I18n_Json::get('alchemiAPI').'</div>
		 <!-- <div class="grid_7 prefix_1" style="text-align:center">'.I18n_Json::get('OpenCalais').'</div>
		 -->
		 <div class="grid_7 prefix_1 omega" style="text-align:center">'.I18n_Json::get('Indexing').'</div>
		 
		 
		</div>';
	}
	

}

?>
