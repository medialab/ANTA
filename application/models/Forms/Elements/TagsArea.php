<?php
/**
 * TagsArea, cfr. http://levycarneiro.com/projects/tag-it/example.html.
 * Check its values using the array $_POST[ 'item' ][ 'tags ] 
 * 
 */
class Application_Model_Forms_Elements_TagsArea extends Application_Model_Forms_Elements_FormElement{
	
	public $atts;
	public $type;
	public $validator;
	protected $_value;
	/**
	 * 
	 * @param atts	- atts name=>value array. atts should have at least two params, named 'name' and 'id'
	 */
	public function __construct( $label, $atts ){
		// base class
		$this->name = $atts['name'];
		$this->id   = $atts['id'];
		
		// this class
		$this->type = "tagArea";
		$this->atts = $atts;
		$this->label = $label;
	}
	
	public function setAttribute( $att, $value ){
		$this->atts[ $att ] = $value;
	}
	
	/**
	 * TODO filter request
	 */
	public function __toString(){
		?>
		<!-- some inline style -->
		<style type="text/css">
		ul.tagit {
	padding:1px 5px;
	border-style:solid;
	border-width:1px;
	border-color:#C6C6C6;
	overflow:auto;
	border: 1px solid #D1D1D1;
	background: #fafafa;
	margin-top:3px;
}
ul.tagit li {
	-moz-border-radius:5px 5px 5px 5px;
	display: block;
	float: left;
	margin:2px 5px 2px 0;
}
ul.tagit li.tagit-choice {
	background-color:#DEE7F8;
	border:1px solid #CAD8F3;
	padding:2px 4px 3px;
}
ul.tagit li.tagit-choice:hover {
	background-color:#bbcef1;
	border-color:#6d95e0;
}
ul.tagit li.tagit-new {
	padding:2px 4px 3px;
	padding:2px 4px 1px;
	padding:2px 4px 1px 0;
}

ul.tagit li.tagit-choice input {
	display:block;
	float:left;
	margin:2px 5px 2px 0;
}
ul.tagit li.tagit-choice a.close {
	color:#777777;
	cursor:pointer;
	font-size:12px;
	font-weight:bold;
	outline:medium none;
	padding:2px 0 2px 3px;
	text-decoration:none;
}
ul.tagit input[type="text"] {
	-moz-box-sizing:border-box;
	border:none;
	margin:0;
	padding:0;
	width:inherit;
}
	</style>
		<!-- endof some inline style -->
		
		<!-- TagsArea scripts -->
		<script src="<?php echo Anta_Core::getBase() ?>/js/tag-it.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript">
		
			$(document).ready(function(){

				$("#<?php echo $this->id ?>").tagit({
					availableTags: ["c++", "groovy", "haskell", "perl"]
					, defaultTags: [<?php echo $this->getDefaultValue() ?>]
				});
				// load default values
				
			});
			
		</script>
		<!-- endof TagsArea scripts -->
		<?php
		if( $this->type != "submit" )
		
		$html = '<ul ';
		
		foreach( $this->atts as $att=>$value ){
			$html .= $att.'="'.$value.'" ';
		}
		
		$html .= '></ul>';
		return  $html;
	}
	
	protected $_defaultValue = "";
	
	/**
	 * To add a list of tags, use an array as args
	 * @value	- either a string or an array
	 */
	public function setDefaultValue( $value ){
		$this->_defaultValue = $value;
	}
	
	public function getDefaultValue(){
		if( isset( $_POST[ $this->id  ] ) && is_array( $_POST[ $this->id  ] ) ){
			$items =& $_POST[ $this->id  ];
			if( isset( $_POST['temporaryTag'] ) && strlen ($_POST['temporaryTag'])> 0 ){
				$items[] = $_POST['temporaryTag'];
			}
		}
		
		if( empty( $items ) ) $items = array();
		
		if( is_array( $this->_defaultValue ) ){
			$items = array_merge( $items, $this->_defaultValue );
		} else {
			$items[] =  $this->_defaultValue;
		}
		
		return '"'.implode( '","', $items ).'"';
	}
	
	/**
	 * listen to a css id selector
	 */
	public function listen( Application_Model_Forms_Elements_Input $target ){
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#<?php echo $target->id ?>-extract-tags").click( function(){
					
					var text =  $("#<?php echo $target->id?>").val() ;
					text = text.replace(/^\s+|\s+$/g,"");
					
					if( text.length < 50 ){
						alert("to use autotagging, the field should contain at least some words...")
						return;
					}
					
					// change icons
					$(this).html( "calling service..." );
					
					var el = $(this);
					
					// ajax call
					$.ajax({
					   type: "POST",
					   url: "<?php echo Anta_Core::getBase() ?>/api/extract",
					   data: "text="+text,
					   async: false,
					   success: function(msg){
							var json = eval('(' + msg + ')');
							
							if( json.status != "ok" ){
								// throw error
								el.html( "Aje! could not fill tag fields... " + json.error );
								return;
							}
							
							el.html( "done. Retrieved "+json.terms.results.Result.length + " tags.");
							
							$("#<?php echo $this->id ?>").trigger( "addTags", json.terms.results.Result );
							
					   }
					 });
					
					// trigger event
					
					
					
				});
			});
		</script>
		<?php
	}
	
	public function getValue(){
		return $this->_value;
	}
	
	/**
	 * The tag area should validate a list of tag.
	 * The _value stored will be an array of tags, numeric indexed.
	 */
	public function isValid(){
		
		$this->_value = @$_REQUEST[ $this->id ];
		
		
		
		if( !is_array( $this->_value ) ){
			$this->_value = array();
			return true;
		}
		
		if( isset( $_POST['temporaryTag'] ) && strlen ($_POST['temporaryTag'])> 0 ){
			$this->_value[] = $_POST['temporaryTag'];
		}
		
		if( $this->validator == null ) return true;
		
		if( $this->_evaluated ) return $this->_result;
		
		$this->_evaluated = true;
		
		// check validator overe every item[ tags ]
		foreach( $this->_value as $tag ){
			
			if(! $this->validator->isValid( $tag )){
				$this->messages = $this->validator->getMessages();
				$this->_result = false;
				return false;
			}
		}
		
		$this->_result = true;
		return true;
	}
}

?>