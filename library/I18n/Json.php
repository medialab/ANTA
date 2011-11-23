<?php
/**
 * @package i18n
 */
 
 /**
  * Load a i18n file in json format
  */
 class I18n_Json extends I18n_Locale {
	 
	protected function _translate( $identifier, $namespace){
		
		if( !isset( $this->$namespace ) ){
			
			$localeFile = $this->languagePath . "/" . $namespace . ".json";
			if ( ! file_exists( $localeFile ) ) {
				$this->addError( "'". $localeFile ."' does not exist...");
				
				return;
			}
			
			$this->$namespace = json_decode(  file_get_contents( $localeFile )  );
			
			if ( $this->$namespace == null ){
				switch(json_last_error())
					{
						case JSON_ERROR_DEPTH:
							$lastError = ' - Maximum stack depth exceeded';
						break;
						case JSON_ERROR_CTRL_CHAR:
							$lastError = ' - Unexpected control character found';
						break;
						case JSON_ERROR_SYNTAX:
							$lastError = ' - Syntax error, malformed JSON';
						break;
						case JSON_ERROR_NONE:
							$lastError = ' - No errors';
						break;
					}
				$this->addError( "'". $localeFile ."' does not seems to be a valid json file". $lastError );
				return;
			}
			
		}
		
		return isset( $this->$namespace->$identifier ) ? $this->$namespace->$identifier: $identifier;
		
	}
	 
 }
 
?>
