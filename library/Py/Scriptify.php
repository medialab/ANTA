<?php
/**
 * @package Py
 */

/**
 * execute a script python following applications/pys file structure, using a linux/unix python command. 
 * The script should output a text, and you can access it using methof getResult(). If the result is a json string, 
 * use the class method getJson().
 * Basic usage
 * <code>
 *   $py = new Py_Scriptify( "dummy.py" );
 *   echo $py->getResult();
 * </code>
 */ 
class Py_Scriptify{
	
	protected $_result;
	
	protected $_pyScript;
	
	protected $_pyPath;
	
	public $flushResult;
	
	/**
	 * the command line as given to python interpreter
	 * @var string
	 */
	public $command = "";
	
	/**
	 * class constructor
	 * @param pythonScript	- string location python script inside application/py hierarchy
	 * @param autoExecute	- boolean if false, you should call execute method manually
	 * @param flushResult	- boolean if true does not store the result in a variable, and echoes directly the result udsing passthru
	 */
	public function __construct( $pyScript, $autoExecute = true, $flushResult = false ){
	
		$this->_pyScript = $pyScript;
		$this->flushResult = $flushResult;
		
		// load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );
		
		// store py path locally
		$this->_pyPath = $config->pys->path;
		
		// execute the python script and store the result, locally
		if( $autoExecute ) $this->execute();
		
		
	}
	
	public function silently(){
		$this->command = 'python '.$this->_pyPath.'/'.$this->_pyScript;
		
		proc_close( proc_open (
			$this->command." &" ,
			array(),
			$foo 
		));
	}
	
	public function execute(){
		
		$this->command = 'python '.$this->_pyPath.'/'.$this->_pyScript;
		
		if( $this->flushResult ){
			echo passthru( $this->command );
		} else {
			$response = array();
			exec( $this->command, $response );
			$this->_result = implode("",$response);
		}
	}
	
	
	
	public function getResult(){
		return $this->_result;
	}
	
	public function getJson(){
		return json_encode( $this->_result );
	}
	
	protected function _error( $message ){
		
	}
	
	/**
	 * interpret and return teh output
	 */
	public function getJsonObject(){
		$decoded = json_decode( $this->_result );
		if( $decoded == null ){
			$this->_error( "unable to decode json output");
			return null;
		}
		return $decoded;
	}
}

?>