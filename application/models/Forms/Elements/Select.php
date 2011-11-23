<?php
/**
 * Select (jquery) input field
 */
class Application_Model_Forms_Elements_Select extends Application_Model_Forms_Elements_Input{
	
	public $options;
	
	
	/**
	 * Class constructor
	 * @param label - title for the select area
	 * @param type	- either 'jquery' or 'plain-option' REMOVED
	 * @param atts	- atts name=>value array. atts should have at least two params, named 'name' and 'id'
	 */
	public function __construct( $label, $atts ){
		// base class
		$this->name = $atts['name'];
		$this->id   = $atts['id'];
		
		// this class
		$this->type = 'plain-option';
		$this->atts = $atts;
		$this->label = $label;
		
		$this->options = array();
	}
	
	/**
	 * add option to select menu and force Application_Model_Forms_Elements_Option type properties
	 * to fit select type property, jquery or plain-option
	 * @param option	- a new Application_Model_Forms_Elements_Option instance to add
	 */
	public function addOption( Application_Model_Forms_Elements_Option $option ){
		$this->options[] = $option;
		$option->type = $this->type;
		
	}
	
	
	/**
	 * Repeat addOption function for each options in the given array
	 * @param options	- the array of Application_Model_Forms_Elements_Option instances
	 */
	public function addOptions( array $options ){
		foreach( $options as $option )
			$this->addOption( $option );
		
	}
	
	public function __toString(){
		if( $this->type != "plain-option" ){ $this->_loadScript(); }
		
		$optionDefaultSelected = null;
		
		$options =& $this->options;
		
		if( isset($_REQUEST[ $this->id ]) ){
			foreach( $options as $option ){
				if( $option->value == $_REQUEST[ $this->id ] ){
					$option->setSelected( true );
					$optionDefaultSelected =& $option;
					break;
				}
			}
		}
		
		$this->_value = @$_REQUEST[ $this->id ];
		
		if( $this->type == "plain-option" ){
			$html = '<select ';
		
			foreach( $this->atts as $att=>$value ){
				$html .= $att.'="'.$value.'" ';
			}
			
			$html .= '>';
			
			foreach( $options as $option ){
				$html .= $option;
			}
			
			$html .= '</select>';
			return  $html;
		}
		// jquery style
		return '
		<div id="#select-'.$this->id.'">
			<button>'. ( $optionDefaultSelected != null? $optionDefaultSelected->label:$options[0]->label ).'</button>
				<ul>'.implode(' ',$options).'</ul>
		</div>';
		
		
	}
	
	/**
	 * Inject selectable menu script
	 */
	protected function _loadScript(){
	?>
	
	<script type="text/javascript"> 
	$(function() {
		$("#<?php echo 'select-'.$this->id?>").button().each(function() {
			$(this).next().menu({
				select: function(event, ui) {
					$(this).hide();
					//$(location).attr('href', $("#ui-active-menuitem").attr("href") );
					return false;
				},
				input: $(this)
			}).hide();
		}).click(function(event) {
			var menu = $(this).next();
			if (menu.is(":visible")) {
				menu.hide();
				return false;
			}
			menu.menu("deactivate").show().css({top:0, left:0}).position({
				my: "left top",
				at: "left bottom",
				of: this
			});
			$(document).one("click", function() {
				menu.hide();
			});
			return false;
		})
	});
	</script> 
	<?php
	
	
	}
}