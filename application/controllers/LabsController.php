<?php
/**
 * this controller it's a dummy controller.
 * It will contain some test feature and useful examples as well
 *
 */
class LabsController extends Zend_Controller_Action
{
	/** a Dnst_Json_Response instance */
	protected $_response;
	protected $_user;
	
    public function init()
    {
        /* Initialize action controller here */
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		/** reinitialize headers */
		Anta_Core::setHttpHeaders("text/plain");
		
		// initialize json response
		$this->_response = new Dnst_Json_Response();
		$this->_response->setStatus( 'ok' );
		
		// add verbose information only if it is specified in htttp params
		if( isset( $_REQUEST[ 'verbose' ] ) ){
			$this->_response->params = $this->_request->getParams(); 
		}
		
		$this->_user = $this->_getUser( false );
		 
	}
	
	public function searchCooccurrencesAction(){
		
		$t = $this->_request->getParam("tag");
		$a = trim( $this->_request->getParam("a") );
		$b = trim( $this->_request->getParam("b") );
		
		$binds = array();
		
		if( !empty( $t ) ){
			$binds[]=$t;
		}
		
		if( empty( $a) || empty( $b ) ){
			exit;
		} else {
			$binds[]= "[\t \n\!\?\.;\(\)\d\r]{$a}";
			$binds[]= "[\t \n\!\?\.;\(\)\d\r]{$b}";
		}
		
		# 1. table of involved document
		$query = "
			SELECT 
				d.title,
				dt.id_document,
				t.content as tag,
				s_a.id_sentence as contain_a, 
				s_b.id_sentence as contain_b, 
				s_a.content as sample_a, 
				s_b.content as sample_b,
				epd.* 
			FROM anta_{$this->_user->username}.`documents_tags` dt
			JOIN anta_{$this->_user->username}.`documents` d USING( id_document )
			JOIN anta_{$this->_user->username}.`tags` t USING( id_tag )
			JOIN anta_{$this->_user->username}.`rws_entities_per_documents` epd USING( id_document),
				 anta_{$this->_user->username}.`sentences` s_a, 
				 anta_{$this->_user->username}.`sentences` s_b
			
				WHERE ".( empty($t)?'':'t.content=? AND')." s_a.id_document = s_b.id_document 
					AND s_a.id_document = dt.id_document 
					AND s_a.content REGEXP ? 
					AND s_b.content REGEXP ?
			";
		
		# esxecute query with bind	
		$stmt = Anta_Core::mysqli()->query( $query, $binds );
		
		# dictionary of documents
		$documents = array();
		$accepted_list = array(); // list of valid document id
		
		while( $row = $stmt->fetchObject() ){
			if( !isset( $documents[ $row->id_document ]	) ){
				 $documents[ $row->id_document ] = array( "title"=>$row->title, "sample_a"=>$row->sample_a, "couples"=>0, "sample_b"=>$row->sample_b, "tags"=>array());
			}
			
			$documents[ $row->id_document ]['tags'][] = $row->tag;
			$documents[ $row->id_document ]['couples']++;
			
			if( $row->contain_a ==  $row->contain_b ){
				$documents[ $row->id_document ]['same-sentence'] = true;
				$documents[ $row->id_document ]['sample_a']=$row->sample_a;
				$documents[ $row->id_document ]['sample_b']=$row->sample_b;
				$accepted_list[ $row->id_document ] = $row->id_document;
			}
			
		}
		
		
		# echo "\n","number documents where the co-occurrence appears: ", count( $documents ),"\n\n";
		
		# clean from duplicates
		foreach( array_keys( $documents ) as $k ){
			$documents[ $k ]["tags"] = array_unique( $documents[ $k ]["tags"]  );	
		}
		uasort($documents, function($a,$b){
			if( $a['couples'] == $b['couples'])
				return 0;
			return $a['couples'] > $b['couples']?-1:1;	
		});
		
		
		if( empty($accepted_list) ){
			$accepted_list = array_keys( $documents );	
		}
			;
		
		#print_r ($documents );
		$h = @fopen("php://output", 'w');
		# csv sample
		
		# echo "\n","csv of co-occurrences";
		# echo "\n","only relevant expressions from documents where the two terms occurr in the same sentences","\n\n";
		// print_r( $accepted_list );
		# query
		$query = "
			SELECT 
				e.content, 
				COUNT(DISTINCT id_document) as distribution, 
				SUM(frequency) as sum_frequency 
			FROM anta_{$this->_user->username}.rws_entities_documents ed 
			JOIN anta_{$this->_user->username}.rws_entities e USING ( id_rws_entity ) 
				WHERE ed.id_document IN (".implode(',', array_fill( 0, count( $accepted_list ), '?') ).") 
				AND e.ignore = 0 
			GROUP BY id_rws_entity 
			ORDER BY sum_frequency DESC, distribution DESC";
		// echo $query;
		$stmt = Anta_Core::mysqli()->query( $query, $accepted_list );
		echo '"entity";"distribution";"sum_frequency"', "\n";	
			
		while( $row = $stmt->fetchObject() ){
			
			echo '"', str_replace( array('"',";"),array('\"','\;'), $row->content ),'";', $row->distribution,';', $row->sum_frequency, "\n";	
			
		}
		fclose( $h );
	}
	
	public function searchCooccurrencesTestAction(){
		
		echo "Search co-occurrences","\n\n";
		
		$t = $this->_request->getParam("tag");
		$a = trim( $this->_request->getParam("a") );
		$b = trim( $this->_request->getParam("b") );
		
		$binds = array();
		
		if( !empty( $t ) ){
			echo "\t", "having tag: [$t]", "\n";	
			$binds[]=$t;
		}
		
		if( empty( $a) || empty( $b ) ){
			echo "\t","warning, a or b is an empty string", "\n\n";
			exit;
		} else {
			$binds[]= "[\t \n\!\?\.;\(\)\d\r]{$a}";
			$binds[]= "[\t \n\!\?\.;\(\)\d\r]{$b}";
			echo "\t", "containing content: [$a] and [$b]", "\n";	
		}
		
		# 1. table of involved document
		$query = "
			SELECT 
				d.title,
				dt.id_document,
				t.content as tag,
				s_a.id_sentence as contain_a, 
				s_b.id_sentence as contain_b, 
				s_a.content as sample_a, 
				s_b.content as sample_b,
				epd.* 
			FROM anta_{$this->_user->username}.`documents_tags` dt
			JOIN anta_{$this->_user->username}.`documents` d USING( id_document )
			JOIN anta_{$this->_user->username}.`tags` t USING( id_tag )
			JOIN anta_{$this->_user->username}.`rws_entities_per_documents` epd USING( id_document),
				 anta_{$this->_user->username}.`sentences` s_a, 
				 anta_{$this->_user->username}.`sentences` s_b
			
				WHERE ".( empty($t)?'':'t.content=? AND')." s_a.id_document = s_b.id_document 
					AND s_a.id_document = dt.id_document 
					AND s_a.content REGEXP ? 
					AND s_b.content REGEXP ?
			";
		
		# esxecute query with bind	
		$stmt = Anta_Core::mysqli()->query( $query, $binds );
		
		# dictionary of documents
		$documents = array();
		$accepted_list = array(); // list of valid document id
		
		while( $row = $stmt->fetchObject() ){
			if( !isset( $documents[ $row->id_document ]	) ){
				 $documents[ $row->id_document ] = array( "title"=>$row->title, "sample_a"=>$row->sample_a, "couples"=>0, "sample_b"=>$row->sample_b, "tags"=>array() );
			}
			
			$documents[ $row->id_document ]['tags'][] = $row->tag;
			$documents[ $row->id_document ]['couples']++;
			
			if( $row->contain_a ==  $row->contain_b ){
				$documents[ $row->id_document ]['same-sentence'] = true;
				$documents[ $row->id_document ]['sample_a']=$row->sample_a;
				$documents[ $row->id_document ]['sample_b']=$row->sample_b;
				$accepted_list[ $row->id_document ] = $row->id_document;
			}
			
		}
		
		echo "\n","number documents where the co-occurrence appears: ", count( $documents ),"\n\n";
		
		# clean from duplicates
		foreach( array_keys( $documents ) as $k ){
			$documents[ $k ]["tags"] = array_unique( $documents[ $k ]["tags"]  );	
		}
		uasort($documents, function($a,$b){
			if( $a['couples'] == $b['couples'])
				return 0;
			return $a['couples'] > $b['couples']?-1:1;	
		});
		
		
		print_r ($documents );
		
		if( empty($accepted_list) ){
			$accepted_list = array_keys( $documents );	
		}
			;
		
		$h = @fopen("php://output", 'w');
		# csv sample
		
		echo "\n","csv of co-occurrences";
		echo "\n","only relevant expressions from documents where the two terms occurr in the same sentences","\n\n";
		// print_r( $accepted_list );
		# query
		$query = "
			SELECT 
				e.content, 
				COUNT(DISTINCT id_document) as distribution, 
				SUM(frequency) as sum_frequency 
			FROM anta_{$this->_user->username}.rws_entities_documents ed 
			JOIN anta_{$this->_user->username}.rws_entities e USING ( id_rws_entity ) 
				WHERE ed.id_document IN (".implode(',', array_fill( 0, count( $accepted_list ), '?') ).") 
				AND e.ignore = 0 
			GROUP BY id_rws_entity 
			ORDER BY sum_frequency DESC, distribution DESC";
		// echo $query;
		$stmt = Anta_Core::mysqli()->query( $query, $accepted_list );
		
		while( $row = $stmt->fetchObject() ){
			
			echo '"', str_replace( array('"',";"),array('\"','\;'), $row->content ),'";', $row->distribution,';', $row->sum_frequency, "\n";	
			
		}
		fclose( $h );
	}
	
	public function zipMeAction(){
		echo "Testing Zip functions";
		
		$zip = new Ui_Zip( APPLICATION_PATH ."/../gexf/test.zip" );
		
		
		
		echo "\n creating file <$zip>";
		$zip->z->addFromString("testfilephp.txt" . time(), "#1 Ceci est une chaîne texte, ajoutée comme testfilephp.txt.\n");
		$zip->z->addFromString("testfilephp2.txt" . time(), "#2 Ceci est une chaîne texte, ajoutée comme testfilephp2.txt.\n");
		echo "\n containing ".$zip->z->numFiles." files";
		$zip->z->close();	
		
		
	}
	
	public function stemAction(){
		echo "Testing stem"."\n\n";
		echo stem_english("arps");
	}
	
	
	public function tfidfAction(){
		echo "Tfidf routine sample (time limit here)"."\n\n";
		$db = "anta_" . $this->_user->username;
		$stmt = Anta_Core::mysqli()->query( "SELECT count(*) as m FROM {$db}.`documents` WHERE `ignore`=0");
		$number_of_documents = $stmt->fetchObject()->m;
		echo "documents: ",$number_of_documents," where `ignore` !=0 in ",$db, "\n";
		# echo "df	number of documents containing a word / number of documents","\n\n";
			
		
		if( $number_of_documents < 1 ){
			echo "Not enough documents";
			exit;
		}
		
		// query for each entity
		# dfnumber of documents containing a word / number of documents
		
		
		$stmt = Anta_Core::mysqli()->query( "
			SELECT *, 
				LOG10(1/df) as idf
			FROM {$db}.rws_entities_documents ed JOIN (
				SELECT id_rws_entity, count( id_document) as distribution, count( id_document)/{$number_of_documents} as df
				FROM {$db}.rws_entities_documents GROUP BY id_rws_entity
			) as df_table
			ON df_table.id_rws_entity = ed.id_rws_entity");
		
		echo "id_rws_entity","\t","id_document","\t","updated","\t", "tfidf","\n";
		
		$tfidfs = array();
		while( $row = $stmt->fetchObject() ){
			//print_r($row);	
			$tfidf = $row->tf * $row->idf;
			$tfidfs[ $row->id_rws_entity."-".$row->id_document ] = $tfidf;
			echo $row->id_rws_entity, "\t\t", $row->id_document,"\t\t",$row->distribution, $updated, "\t\t", $tfidf, "\n";	
		}
		
		foreach( array_keys( $tfidfs ) as $k ){
			$ids = explode("-", $k );
			
			$updated = Application_Model_Rws_EntitiesDocumentsMapper::update( $this->_user, $ids[0], $ids[1], 'tfidf', $tfidfs[$k] );
			
		}
		
		
	}
	
	public function similarEntitiesAction(){
		echo "Testing similar entities engine"."\n\n";
		echo "sample query: "."http://jiminy.medialab.sciences-po.fr/anta_dev/labs/similar-entities/user/d7w?v1=borders%20crime%20system&v2=border%20-%20crime%20systems\n\n";
		# echo "crime { jaccard } crimes = ".jaccard(  );
		# echo "crime { levenshtein } crimes = ".levenshten(  );
		$v1 = $this->_request->getParam("v1");
		$v2 = $this->_request->getParam("v2");
		
		if( strlen( $v1 ) + strlen( $v2 ) > 500 ){
			exit( "too big for comparison");	
		}
		
		
		if( empty( $v1 ) || empty( $v2 ) ){
			exit( "v1 and v2 params?");	
		}
		
		echo "$v1 { soundex } $v2 = ".soundex( $v1  ). " - ".soundex(  $v2 ) ."\n";
		echo "$v1 { levenshtein } $v2 = ".levenshtein($v1, $v2) ."\n";
		echo "$v1 { levenshtein_ratio } $v2 = ".levenshtein_ratio($v1, $v2) ."\n";
		echo "$v1 { levenshtein_metaphone_ratio } $v2 = ".levenshtein_metaphone_ratio($v1, $v2) ."\n";
		
		echo "\n\n"."sample entities duplicate extraction!"."\n\n";
		
		# compare the first entity with all other entities. auto install table
		Anta_Core::mysqli()->getConnection()->query( "
			CREATE TABLE IF NOT EXISTS anta_{$this->_user->username}.`rws_entities_duplicates` (
			  `id_rws_entity_candidate` int(11) NOT NULL,
			  `id_rws_entity_clone` int(11) NOT NULL,
			  `ratio` float NOT NULL,
			  FOREIGN KEY ( `id_rws_entity_candidate` )
                              REFERENCES `id_rws_entities`( id_rws_entity )
                              ON DELETE CASCADE,
              FOREIGN KEY ( `id_rws_entity_clone` )
                              REFERENCES `id_rws_entities`( id_rws_entity )
                              ON DELETE CASCADE,
			  UNIQUE KEY `id_rws_entity_candidate` (`id_rws_entity_candidate`,`id_rws_entity_clone`)
			) ENGINE=InnoDB"
		);
		
		# get max candidate id
		$stmt = Anta_Core::mysqli()->query( "SELECT MAX( id_rws_entity_candidate ) as m FROM rws_entities_duplicates" );
		$mx = $stmt->fetchObject()->m; $mx = empty($mx)?2:$mx;
		
		# get next candidate and compare
		$stmt = Anta_Core::mysqli()->query( "
			SELECT e2.id_rws_entity as id2, e2.content as c2, e1.content as c1, e1.id_rws_entity as id1
			FROM ( 
				SELECT * FROM anta_{$this->_user->username}.rws_entities WHERE id_rws_entity > $mx LIMIT 1 
			) as e1,
			anta_{$this->_user->username}.rws_entities as e2 WHERE e2.id_rws_entity != e1.id_rws_entity
		");
		
		while( $row = $stmt->fetchObject() ){
			$lr = levenshtein_ratio( $row->c1, $row->c2 );
			if ( $lr < 0.2)	echo "\n".$row->c1." ° ".$row->c2." = ".$lr;
			
			// look for candidates
			
		}
		
		
		
		
	}
	
		
	/**
	 * Tokenize the document content and save the sentences into the database
	 *
	 * @http-param user - cryptoid of the user
	 * @http-param document - real numeric document identifier for table documents
	 */
	public function tokenizerAction(){
			
		echo "Testing tokenizer via python tokenizer script (nltk)"."\n\n";
		
		$startTime = microtime( true );
		
		# the desired document
		$document = $this->_getDocument();
		
		# the unique url
		$localUrl = Anta_Core::getDocumentUrl( $this->_user, $document );
		print_r( $document);
		$language = Anta_Core::getLanguage( $document->language );
		echo "langage: {$language}\n";
		
		# te text content
		$content = file_get_contents( $localUrl );
		
		# call the script
		$py = new Py_Scriptify( "sentencesTokenizer.py $localUrl $language", true, false );
		
		# read the result, json
		echo "executing: ".$py->command." \nresult: ".$py->getResult();
		
		# read the sentences tokenized
		$sentences = $py->getJsonObject();
		if( $sentences == null ){
			echo "error, out";
			return;
		};
		print_r( $sentences);
		# get the errors...
		if( $sentences->status != "ok" ){
			echo $sentences->status. " ".$sentences->error  ;
			return;
		}
		
		#clean the sentences
		Application_Model_SentencesMapper::cleanSentences( $this->_user, $document->id );
		
		echo "\nelapsed:".( microtime( true ) - $startTime ); 
		$startTime = microtime( true );
		
		#put the sentences into the database
		foreach( array_keys( $sentences->sentences ) as $i ){
			$sentence =& $sentences->sentences[ $i ];
			$affected = Application_Model_SentencesMapper::addSentence( $this->_user, $document->id, $i, $sentence );
			if ($affected == 0 ){
				echo "error on sentence $i";
			}
		}
		echo "\nelapsed:".( microtime( true ) - $startTime ); 
	}
	
	public function rebuildDestroyedDocumentsAction(){
		echo "Rebuild txt from saved sentences"."\n\n";
		# the desired document
		$document = $this->_getDocument();
		
		# the unique url
		$localUrl = Anta_Core::getDocumentUrl( $this->_user, $document );
		
		# exists?
		echo "\n  looking for txt file: '".basename( $localUrl )."...";
		if( file_exists ( $localUrl ) ){
			echo "\n  file: '".basename( $localUrl ) ."' EXISTS in user folder";
		} else {
			echo "\n  file: '".basename( $localUrl ) ."' DOES NOT EXIST in user folder. Rebuilding...";
			# get number of saved sentences
			$numOfSentences = Application_Model_SentencesMapper::getNumberOfSentences( $this->_user, $document->id );
			echo "\n  file [{$document->id}] contains [{$numOfSentences}] sentences ";
			
			$fh = fopen( $localUrl, "w");
			if( $fh == false ) die( "unable to create file" );
			echo "\n  file '".basename( $localUrl ) ."' opened for writing";
			
			echo "\n  merge sentences results: \n\n[\n";
			
			
			$sentences = Application_Model_SentencesMapper::getSentences( $this->_user, $document->id );
			foreach( array_keys( $sentences ) as $k ){
				$sentence = $sentences[ $k ];
				fputs ( $fh, $sentence->content.".\n" );
				echo $sentence->content.".\n";
			}
			echo "\n] end of sentences stored";
			fclose ( $fh );
			echo "\n  file closed";
			# update document, set to ready
			// save file using location
		}
	}
	
	protected function _getDocument(){
		if ( $this->_request->getParam( 'document' ) == null ){
			$this->_response->throwError( "document param was not found" );
		}
		// get text
		$doc = Application_Model_DocumentsMapper::getDocument( $this->_user, $this->_request->getParam( 'document' ) );
		
		if( $doc == null ){
			$this->_response->throwError( "document id not found" );
		}
		
		return $doc;
		
	}
	
	public function installViewsAction(){
		echo "Testing Views installation"."\n\n";
		Application_Model_ViewsMapper::install($this->_user->username );
	}
	public function cryptoDesAction(){
		echo "Testing Crypto Des Action"."\n\n";
		
		$key = "this is a 24 byte key !!"; // dummy temporary key
		$message = "This is a test message";
		$ciphertext = Dnst_Crypto_Des::des ($key, $message, 1, 0, null);
		echo "DES Test Encrypted: " . Dnst_Crypto_Des::stringToHex ($ciphertext);
		$recovered_message = Dnst_Crypto_Des::des ($key, $ciphertext, 0, 0, null);
		echo "\n";
		echo "DES Test Decrypted: " . $recovered_message;
		
	}


	public function importCsvAction(){
		echo 'csv';
		$filepath = "anta_import_test.csv";
		$table = Anta_Csv::parse( $filepath, ";", '"' );
		print_r($table);

		$minimumHeaders = array( 'id doc',	'id hash', 'title', 'ignore', 'date',	'language', 'description' );
			
		$result = $table->getHeader()->isValid( $minimumHeaders );

	}
	
	/**
	 * tests the Dnst_Spreadsheep api
	 */
	public function googleSpreadsheepAction(){
		echo "Testing SpreadSheep API for Zend Google API"."\n\n";
	}
	
	public function googleSpreadsheetAction(){
		echo "Testing Google APi spreadsheeP capabilities"."\n\n";
		
		// authenticate
		$email = 'email';
		$passwd = 'password';
		$documentKey = "documentkey";
		// set the service 
		$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
		
		
		try {
			$client = Zend_Gdata_ClientLogin::getHttpClient($email, $passwd, $service);
		} catch (Zend_Gdata_App_CaptchaRequiredException $cre) {
			echo 'URL of CAPTCHA image: ' . $cre->getCaptchaUrl() . "\n";
			echo 'Token ID: ' . $cre->getCaptchaToken() . "\n";
			return;
		} catch (Exception $ae) {
			echo 'Problem authenticating: ' . $ae->exception() . "\n";
			return;
		}
		
		// the SERVICE
		$spreadsheetService = new Zend_Gdata_Spreadsheets( $client );
		
		// all worksheet
		$feed = $spreadsheetService->getSpreadsheetFeed();
		echo "all worksheets\n";
		foreach( $feed as $value){
			echo "  key: " .basename(  $value->id )."\n  feed: ". $value->id."\n";
			
			//print_r($value->link);
			foreach( $value->link as $link ){
				echo "    ".$link->href."\n";
			};
			break;
		}
		
		// get document info
		$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
		$query->setSpreadsheetKey( "t_A7yS2M9rQG2CfH8H84g-Q" );
		$feed = $spreadsheetService->getWorksheetFeed($query);
		
		
		echo "\ndocument: {$documentKey} \n\n";
		// get document info
		$query = new Zend_Gdata_Spreadsheets_CellQuery();
		$query->setSpreadsheetKey( "t_A7yS2M9rQG2CfH8H84g-Q" );
		$cellFeed = $spreadsheetService->getCellFeed($query);
		
		$cols = $cellFeed->getColumnCount()->getText();
		$rows = $cellFeed->getRowCount()->getText();
		
		echo "  colums found: " .$cols."\n";
		echo "  rows found: " .$rows."\n"; // a Zend_Gdata_Spreadsheets_Extension_RowCount object
		
		foreach($cellFeed as $cellEntry) {
		  $row = $cellEntry->cell->getRow();
		  $col = $cellEntry->cell->getColumn();
		  $val = $cellEntry->cell->getText();
		  echo "    $row, $col = $val\n";
		}
		// print_r( $columns);
		
	}
	
	public function zendLuceneAction(){
		
		echo "Testing Zend-Lucene capabilities"."\n\n";
		
		echo "indexed documents: ".Anta_Lucene::buildLuceneIndex( $this->_user );
		
		Anta_Lucene::searchLucene( 'cancun', $this->_user );
		
		
	}

	

	public function indexAction(){
		// try some speciality
		// echo $this->_response;
	}
	
	/**
	 * Handle user param error. Return the user if is valid.
	 * If the user is not provided or is not valid, exit with json error
	 */
	protected function _getUser( $forceAuth = true ){
		
		$identity = Zend_Auth::getInstance()->getIdentity();
			
		if( $identity == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not authenticated, maybe your session has expired" );
		}
			
		return $identity;
	}
	/**
	 * action not found handler
	 */
	public function __call( $a, $b ){
		$action = str_replace( "Action", "", $a );
		$this->_response->setAction( $action );
		$this->_response->throwError( "action '$action' not found" );
	}
}
?>
