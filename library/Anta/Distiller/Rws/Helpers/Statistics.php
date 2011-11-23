<?php
class Anta_Distiller_Rws_Helpers_Statistics{
	
	public $aliases;
	
	protected $_frequency = 0;
	
	public function __construct(){
		$this->aliases = array();
	}
	
	public function addValue( $value, $weight ){
		$this->aliases[] = $value;
		
		$this->_frequency += $weight;
	}
	
	public function getFrequency(){
		return $this->_frequency;
	}
	
	public function getMedian(){
		sort( $this->aliases );
		$count = count( $this->aliases ); //total numbers in array
		$middleValue = floor( ( $count-1 ) / 2 ); // find the middle value, or the lowest middle value
		
		if( $count % 2 ) { // odd number, middle is the median
			$median = $this->aliases[ $middleValue ];
		} else { // even number, calculate avg of 2 medians
			$low = $this->aliases[ $middleValue ];
			$high = $this->aliases[ $middleValue + 1 ];
			$median = ( ( $low + $high ) / 2 );
		}
		
		return $median;
	}
	
	public function getAverage(){
		$count = count( $this->aliases ); //total numbers in array
		foreach ( $this->aliases as $value ) {
			$total = $total + $value; // total value of array numbers
		}
		$average = ( $total/$count ); // get average value
		return $average;
	}
}