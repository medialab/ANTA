<?php
/**
 * @package
 */
 
/**
 * manage quotas of various web services used
 */

class Application_Model_QuotasMapper{

	public static function addQuota( $service, $requestLength, $responseLength ){
		Anta_Core::mysqli()->query( "
			INSERT into anta.quotas ( 
				service, request_length, response_length
			) VALUES (
				?, ?, ?
			)", array(  $service, $requestLength, $responseLength	));
		
	}
	
	/**
	 * return the number of request in the last 24 hours
	 */
	public static function getDailyRequest(){
		$stmt = Anta_Core::mysqli()->query("SELECT COUNT(*) as daily_use FROM `quotas` WHERE date > SUBDATE( NOW(), INTERVAL 24 hour)");
		return $stmt->fetchObject()->daily_use;
	}
}
