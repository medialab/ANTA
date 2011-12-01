<?php
/**
 * this file is a collection of useful function for an easy-to-use purpose
 */

function levenshtein_metaphone_ratio( $v1, $v2 ){
	$v1 = metaphone( $v1);
	$v2 = metaphone( $v2);
	return levenshtein_ratio( $v1, $v2 );
}

function levenshtein_ratio( $v1, $v2 ){
	$max = max( strlen( $v1 ), strlen( $v2 ) );
	return levenshtein( $v1, $v2 ) / $max;	
}

/**
 * implode attributes 
 * @param array attributes	- the html tag duples, like array("id"=>"identifier")
 * @return a string with property=values pairs, opportunately escaped
 */
function iatts( array $attributes ){
	$atts = "";
	foreach( $attributes as $name=>$value ){
		$atts .= $name.'="'.str_replace('"',"'",$value ).'" ';
	}
	return $atts;
}

function jsone(){
	switch(json_last_error())
        {
            case JSON_ERROR_DEPTH:
                $error =  ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_NONE:
            default:
                $error = '';                    
        }
	if (!empty($error))
            return 'JSON Error: '.$error;
	return true;
}

/**
 * @return a string of sign usable in binding query
 */
function sbind( array $bindable, $separator = "?" ){
	return implode(",", array_fill( 0, count( $bindable ), $separator ) );
}
/**
 * clean a string from unreadable utf-8 chars or html tag delimiters 
 */
function stripUnreadableChars( $text ){
	$string = preg_replace('/[^A-Za-z0-9 _\-\+\&àéè%çù<>]/','',$text);
	return $string;
}

/**
 * reduce - cut - a string to given chars num. Utf8strings
 * @param text - string text to cut
 * @param chars - the number of maximum chars
 * @return the reduced string
 */
function ucut( $text, $chars='25') {

    $length = mb_strlen( $text, 'UTF-8' );
	if( $length <= $chars ) return $text;
    
	// cut...
	$text = mb_substr( $text, 0, $chars );
	
	// last space
	$lastSpace = mb_strrpos ( $text, ' ', 0, 'UTF-8' );
	if( $lastSpace === FALSE ) return $text."&hellip;";
	
	// trim
    return mb_substr( $text, 0, $lastSpace, 'UTF-8' )."&hellip;";
} 
?>