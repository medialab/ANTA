<?php
/**
 * @package Anta_Utils_Href
 */

/**
 * prodide an unique mechanism to link a document
 */
class Anta_Utils_Href_Document extends Anta_Utils_Href{
	
	public static function create( $userId, $cryptoId, $title ){
		return (string) new Anta_Utils_Href( ANTA_URL."/edit/props/document/{$cryptoId}/user/{$userId}", $title );
	}
}
?>