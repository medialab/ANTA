<?php
/**
 * @package Anta
 */

/**
 * logging class, do logging
 */
class Anta_Logging{
	
	protected static $_config;
	
	public static function unixTail( $lines, $file ){
		$tempfile = "/tmp/phptail_".basename( $file );
		shell_exec("tail -n $lines $file > {$tempfile}");
		$output = file_get_contents( $tempfile );
		unlink( $tempfile );
		return $output;
	}
	
	private static $logsPath;

	public static function read( $log ){
		return file_get_contents( self::getLogsPath()."/".$log );
	}
	
	public static function getLogsPath(){
		if( self::$logsPath == null ){
			$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );
			self::$logsPath = $config->logs->path;
		}
		return self::$logsPath;
	}
	
	public static function createPidFile( $pidFile ){
		
		$filename = self::getLogsPath()."/".$pidFile.".pid";
		
		if( ! file_exists( $filename ) ){
			if(! file_put_contents( $filename, getmypid() ) ){
				echo "Cannot open file ($filename)";
				exit;
			} 
			return true;
		}
		return false;
	}
	
	/**
	 * write a log entry into a log file.
	 */
	public static function append( $log, $content, $newEntry = true, $silently = false ){
		
		if( $newEntry ){
			$content = "\n--\n".Anta_Core::getCurrentTimestamp() ."\n". $content;
		} else {
			
			$content = "\n".Anta_Core::getCurrentTimestamp()."\t". $content;
			
		}
		if( !$silently ) echo $content;
		// load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );
		
		$filename = $config->logs->path."/".$log;
		
		if( ! file_exists( $filename ) ){
			// create the file
			if(! file_put_contents( $filename, $content ) ){
				echo "Cannot open file ($filename)";
				exit;
			} 
			return;
		}
		
		
		if ( is_writable( $filename ) ) {
			// In our example we're opening $filename in append mode.
			// The file pointer is at the bottom of the file hence
			// that's where $somecontent will go when we fwrite() it.
			if ( ! $handle = fopen( $filename, 'a' ) ) {
				echo "Cannot open file ($filename)";
				exit;
			}
		
		// Write $somecontent to our opened file.
		if ( fwrite( $handle, $content ) === FALSE ) {
			echo " Cannot write to file ( $filename ) ";
			exit;
		}

		
		fclose( $handle );

		} else {
			echo "The file $filename is not writable";
		}
		
	}
	
} 