<?php
/**
 * @package Anta
 */
 
/**
 * Csv translator - csv to array
 * Allow the use of validator (fields header, content )
 */
class Anta_Csv {
	
	public function __construct(){
	
	}
	
	/**
	 * trim and remove bogus chars
	 */
	public static function clean( array $rawData ){
		// print_r ($rawData );
		$cleaned = array();
		foreach( $rawData as $k=>$entry ){
			$trimmed = trim( str_replace( '"', '', $entry ) );
			if( strlen( $trimmed ) > 0 ){
				$cleaned[ $k ] = $entry; 
			}
		}
		return $cleaned;
		
	}
	
	/**
	 * Use the csv built-in function to parse the given value.
	 */
	public static function parse( $csvFile, $separator="\t", $delimiter = '' ){
		setlocale(LC_ALL, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8');
		
		$header = null;
		$table  = null;
		
		if ( ($handle = fopen( $csvFile, "r" ) ) !== FALSE) {
			while (($data = fgetcsv( $handle, 0, $separator, $delimiter)) !== FALSE) { 
				$data = self::clean( $data );
				// echo "delimiter".$delimiter;
				// print_r( $handle );

				// first line: headers
				if( $header == null ){
					$header = new Anta_Csv_Header( $data );
					$table  = new Anta_Csv_Table( $header );
					continue;
				}

				$table->addRow(
					Anta_Csv_Row::create( $header, $data )
				);
				
			}
			fclose($handle);
		}
		
		return $table;
	}
	
}
