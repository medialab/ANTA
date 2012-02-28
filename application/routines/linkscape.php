<?php
/**
 * import a json file into anta, and fwrite logging
 */
# 0. setup

 set_time_limit( 0 );
 error_reporting(E_ALL);
 set_exception_handler('exception_handler');
 set_error_handler("error_handler");



# 1. load command line args
 $args = getopt("u:d:f:l:");

 $user		= $args['u'];
 $database	= $args['d'];
 $source	= $args['f'];
 $lang		= $args['l'];

 
# 2. start zend app

 # Define APPLICATION_PATH to application directory
 $scriptPath = dirname(__FILE__);
 define( 'APPLICATION_PATH', substr( $scriptPath, 0, strrpos( $scriptPath, "/", -1 ) ) );
 
 
 # Ensure library/ is on include_path
 set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
	APPLICATION_PATH . '/models',
    get_include_path(),
 )));
 
 # Zend_Application
 require_once 'Zend/Application.php';

 # Create application, bootstrap, and run
 $application = new Zend_Application(
    'development',
    APPLICATION_PATH . '/configs/application.ini'
 );
 

 
# 3. load user / verify permissions
$_user = Application_Model_UsersMapper::getUser( $user );
print_r( $_user );
if( $_user == null ){
	throw( new Zend_Exception( "User doesnot exists?" ) );
}

# 4. check database @todo
$source = json_decode( file_get_contents( $source ) ) ;
if( empty( $source ) ) throw( new Zend_Exception( "json misunderstood!" ) );

alog( "linkscape_import", "starting linkscape import for user: ".$database.", language: ". $lang , $_user );
alog( "linkscape_import", "Parsing json: ".$source ->query.", start at ".$source->start ." of ".$source->totalcount, $_user );

foreach( array_keys( $source->posts ) as $k ){
	//reparse date
	$entry =& $source->posts[ $k ];
	
	alog( "linkscape_import", "[ ".$k. "/".$source->count." ] saving: ".$entry->title.", site: ".$entry->site . ", date ({$entry->date}): ".Anta_Core::getDate( $entry->date, "Ymd" ), $_user );
	
	# has content???
	if( empty(  $entry->plain_content  ) ){
		alog( "linkscape_import", "[ ".$k. "/".$source->count." ] seems to have a void content!", $_user );
		continue;
	}
	
	# has content???
	if( empty(  $entry->title  ) ){
		$entry->title = "untitled";
		alog( "linkscape_import", "[ ".$k. "/".$source->count." ] seems to have a void title. renamed!", $_user );
		continue;
	}
	
	$idDocument = Application_Model_DocumentsMapper::addDocument(
				$_user,
				$entry->title,
				$entry->plain_content,
				strlen( $entry->plain_content ), 
				"text/plain", 
				'',
				Anta_Core::getDate( $entry->date, "Ymd" ),
				$lang,
				$entry->permalink
			);
	if( $idDocument == 0 ){
		alog( "linkscape_import", "[ ".$k. "/".$source->count." ]  failed for document : ".$entry->title."!", $_user );
		continue;
	}
	# linkscape categories
	foreach( $entry->catpath as $l_cat){
		store( $l_cat, "linkscape category", $idDocument, $_user );
	}
	
	store( $entry->site, "site", $idDocument, $_user );
	
	
}

alog( "linkscape_import", "Parsing finished for: ".$source ->query.", start at ".$source->start ." of ".$source->totalcount, $_user );



function store( $tag, $category, $idDocument, $user ){
	# store categories
	$idCategory = Application_Model_CategoriesMapper::add( $user, $category );// store categories
	if( $idCategory == 0 ){
		return false;
	}
	
	# store tags
	$idTag = Application_Model_TagsMapper::add( $user, $tag, $category );
	if( $idTag == 0 ){
		return false;
	}
	
	# link tags and document
	Application_Model_DocumentsTagsMapper::add( $user, $idDocument, $idTag );
}

function error_handler( $errno, $errstr, $errfile, $errline ) {
	global $_user;
	if( !empty( $_user ) ){
		alog( "linkscape_import", "[{$errno}] {$errstr},  Fatal error on line $errline in file $errfile", $_user );
	}
}

function exception_handler($exception) {
	global $_user;
	if( !empty( $_user ) ){
		alog( "linkscape_import", $exception->getMessage(), $_user );
	}
}

// print_r( $json );

/* post sample
[date_to] => 
    [maxcount] => 1000
    [count] => 1000
    [query] => 'changement climatique' AND adapt*
    [totalcount] => 2348
    [posts] => Array
        (
            [0] => stdClass Object
                (
                    [scores] => stdClass Object
                        (
                            [editorial_lf] => 96
                            [structural_lf] => 74
                            [lf_old] => 17
                        )

                    [catpath] => Array
                        (
                            [0] => societe
                            [1] => agora
                            [2] => environnement
                        )

                    [date] => 20100731
                    [author] => David Naulin
                    [catid] => 197640
                    [permalink] => http://www.cdurable.info/Global-Conference-2010-Ateliers-de-la-Terre-Innovation-Developpement-Durable,2750.html
                    [postid] => b15baa972f73a662af4a073674a993a50eb2856713f716ff1a665d3151cbdd54
                    [site] => http://www.cdurable.info/
                    [title] => Global Conference 2010 : l'innovation suffit-elle pour s'adapter au défi du développement durable ?
                    [id_website] => 743
                    [plain_content] =>
*/
?>