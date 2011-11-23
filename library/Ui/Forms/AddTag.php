<?php
/**
 * @package Ui_Forms
 */
/**
 * Allow to upload a single csv file that will be interpreted.
 * The csv parsing is done by Anta_Csv::parse( $filepath )
 */ 
class Ui_Forms_AddTag extends Ui_Form{

	protected function _init(){
	
        $content = new Ui_Forms_Elements_Input( 'text', I18n_Json::get( "tag content" ), array(
			"name"  => "tag-content",
			"id"    => "tag-content",
			"class" => "width_7 margin_1"
		));
		
		$category = new Ui_Forms_Elements_Select( I18n_Json::get( "category" ), array(
			"name"  => "tag-category",
			"id"    => "tag-category",
			"class" => "width_2 height_1 margin_1",
			"value" => "keyword"
		));
		
		$customCategory = new Ui_Forms_Elements_Input( 'text', I18n_Json::get( "tag content" ), array(
			"name"  => "tag-custom-category",
			"id"    => "tag-custom-category",
			"class" => "width_2 margin_1"
		));
		// $content->setValidator( new Ui_Forms_Validators_FileUpload() );
		
		$submit = new Ui_Forms_Elements_Input( "button", $this->title, array(
			"name"  => "save-tag",
			"id"    => "save-tag",
			"value" => I18n_Json::get( "attach tag" )
		));
		
		
		
		$this->addElement( $content );
		$this->addElement( $category );
		$this->addElement( $customCategory );
		$this->addElement( $submit );
	}

	public function __toString(){
		$this->__loadScript();
		return '
			<div class="grid_23 prefix_1 alpha omega margin_1" style="display:none;" id="sliding-add-tag-form">
				<form name="form" id="new-entity-form" method="post" action="'.$this->action.'" method="'.$this->method.'">
				<div class="grid_23 alpha omega">
					<div class="grid_23 alpha omega" id="new-tag-log"></div>
					<div class="grid_12 alpha">
						<p class="margin_1">'.$this->tag_content->label.'</p>
						'.$this->tag_content.'
					</div>
					<div class="grid_10 omega">
						
						<div class="grid_6 alpha">
							<p class="margin_1">'.$this->tag_category->label.'</p>
							<div class="grid_4 alpha omega">
								'.$this->tag_category.'
							</div>
							<div class="grid_6 alpha omega" style="display:none" id="box-'.$this->tag_custom_category->id.'">
								'.$this->tag_custom_category.'
								<a href="#" id="box-'.$this->tag_custom_category->id.'-save"><img src="'.Anta_Core::getBase().'/images/plus.png"/></a>
								<a href="#" id="box-'.$this->tag_custom_category->id.'-close" >
									<img src="'.Anta_Core::getBase().'/images/cross-small.png"/>
								</a>
							</div>
						</div>
						
						<div class="grid_4 omega">
							'.$this->save_tag.'
						</div>
					</div>
					
				</div>
				</form>
			</div>
			
		 ';
		
	}	
	
	private function __loadScript(){
		?>
		<!-- script into <?php echo get_class( $this ) ?> -->
		<script type="text/javascript">
			$(document).ready( function(){
				// attach behaviour to select items
				$('#<?php echo $this->tag_category->id ?>').change( function(){
					var $this = $(this);
					if( $this.val() == 0 ){
						// alert("create new, finally...");
						// switch between category select and category custom
						$this.slideToggle( 50 );
						$('#box-<?php echo $this->tag_custom_category->id ?>').slideToggle( 50 );
						$("#<?php echo $this->tag_custom_category->id ?>").val( "" );
						$("#<?php echo $this->tag_custom_category->id ?>").focus();
						// bind ok value
					};
				});
				$('#box-<?php echo $this->tag_custom_category->id ?>-close').click( function( event ){
					event.preventDefault();
					$('#<?php echo $this->tag_category->id ?>').val( "-1" );
					$('#box-<?php echo $this->tag_custom_category->id ?>').slideToggle( 50 );
					$('#<?php echo $this->tag_category->id ?>').slideToggle( 50 );
				});
				
				// save click, requirees addCategoryUrl var
				$('#box-<?php echo $this->tag_custom_category->id ?>-save').click( function( event ){
					event.preventDefault();
					var $this = $(this);
					
					
					var category = $("#<?php echo $this->tag_custom_category->id ?>");
					
					console.log( addCategoryUrl + " : " + category.val() );
					
					$.ajax({
						url: addCategoryUrl,
						context: document.body,
						dataType: 'json',
						data: { name:category.val() },
						success: function( result ){
							console.log( result );
							if( result.status == 'ok'){
								// add as an option
								console.log( result.category + " added" );
								$('#<?php echo $this->tag_category->id ?>').append('<option value="'+result.category+'" selected="selected">'+result.category+'</option>');
								$('#box-<?php echo $this->tag_custom_category->id ?>').slideToggle( 50 );
								$('#<?php echo $this->tag_category->id ?>').slideToggle( 50 );
							}
						},
						error:function( fault ){
						console.log( fault );
						}
					});
				});
				
			});
		</script>
		<?php
	}
}