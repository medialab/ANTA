<?php
/**
 * @package Textopoly_Dictionary
 */


/**
 * Create a dictionary.
 * 
 * usage:
 * 
 * header( "Content-Type: text/plain; charset=utf8" );
 * $dic = new Textopoly_Dictionary( "dictionaries/entities.txt" );
 * print_r($dic);
 */
class Textopoly_Dictionary{

	/**
	 * Class constructor.
	 * Accept a text file of dictionary type, require an application.ini (Zend Env )
	 * In your application.ini, configure correctly the section to use the path /public/dictionaries
	 * to store / load dictionaries:
	 * 
	 *   [contents : production]
     *   dictionaries.path = APPLICATION_PATH "/../public/dictionaries"
	 * 
	 * @param file	- txt dictionary file, basename
	 */
	public function __construct( $file ){
		// load application.ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );
		$this->_entries = array();
		$this->load_dictionary( $config->dictionaries->path . "/" . $file ) or die("bad dictionary at $file");
		
	}	

	private $_dictionaryDescription = "dictionary not loaded";
	/**
	 * describe the dictionary
	 */
	public function __toString(){
		return $this->_dictionaryDescription. "; entries: ".count( $this->_entries) ;
	}
	
	protected $__entries;
	
	public function get( $entry ){
		return $this->_entries[ mb_strtolower( $entry, 'UTF-8' ) ];
	}
	
	public function exists( $entry ){
		return isset( $this->_entries[ mb_strtolower( $entry, 'UTF-8' ) ] );
	}
	
	/**
	 * This function is public to add dictionary entries from other file
	 */
	public function load_dictionary( $file ){
		
		$handle = @fopen($file, "r");
		
		if (! $handle) return false;
		$this->_dictionaryDescription = "loaded ".basename( $file );
		// read file line by line
		while ( ( $buffer = fgets( $handle, 4096 ) ) !== false) {
			
			// ignore after the | sign, tarball comments
			$comment = strpos( $buffer, "|" );
			
			
			if( $comment !== false ){ // reduce buffer
				$buffer = substr( $buffer, 0, $comment );
			}
			
			// divide each line by the character ':'
			$couple = explode(":",$buffer );
			
			// take the left part as the variable
			$varname = trim($couple[0]);
			
			// if there are any error, like a ':' at the very beginning, it will stop execution
			if( strlen( $varname ) == 0 ) continue;			

			// if the sign ':' does not exist, the entry is not valid. useful for comments
			if( count( $couple ) == 2 ){
				$this->_entries[ $varname ] = trim( $couple[1] );
			} else {
				$this->_entries[ $varname ] = 1;
			}

		}
		
		if ( ! feof( $handle ) ) {
			echo "Error: unexpected fgets() fail\n";
			return false;
		}
		
		fclose($handle);
		return true;
		
	}

	
	/**
	 * PRint out a dictionary from a snowball file
	 */
	public static function get_dictionary_from_snowball( $snowball_file ){
		$handle = @fopen($snowball_file, "r");
		if ($handle) {
		    while (($buffer = fgets($handle, 4096)) !== false) {
			$parsed = explode(" ",$buffer );
			$couple = array();
			foreach( $parsed as $candidates ){
				if( strlen( trim( $candidates ) ) > 2 ){
					$couple[] = $candidates;
				}
			}
			echo implode(":",$couple);
		    }
		    if (!feof($handle)) {
			echo "Error: unexpected fgets() fail\n";
		    }
		    fclose($handle);
		    return true;
		}
		return false;
	
	}
}
?>
