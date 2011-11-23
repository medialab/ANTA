<?php
/**
 * howto: // check identity in an action 
 *	           $user = $this->_authorizeUser( $this->_getUser() );
 */
class ApiFrogController extends Application_Model_Controller_Api
{
	/** a Dnst_Json_Response instance */
	protected $_response;
	protected $_user;
	
	
	public function indexAction(){
		// try some speciality
		echo $this->_response;
	}
	
	
	
	/**
	 * group entities by:
	 *   date-format:Ymd
	 */
	public function getMostCommonEntitiesAction(){
		
		$this->_response->setAction( 'get-most-common-entities');
		
		// default available date formats
		$dateFormats = array( "Ymdh"=>"%Y%.m.%d.%H", "Ymd" => "%Y.%m.%d", "Ym" =>"%Y.%m", "Y" => "%Y" );	
		
		$dateFormat = $this->_request->getParam( "date-group" );
		
		// get left / right date limit
		
		// offset, limit
		$limit  = $this->_request->getParam( "limit" );
		$offset = $this->_request->getParam( "offset" );
		
		// left-date
		$leftDate =  $this->_request->getParam( "left-date" );
		$rightDate =  $this->_request->getParam( "right-date" );
		$dateLimits = "";
		
		$binds = array();
		
		if( $leftDate != null && $rightDate != null ){
			// check date format
			$dateValidator = new Ui_Forms_Validators_Date( array(
				'minLenght' => 10,
				'maxLength' => 10
			));
			
			if( !$dateValidator->isValid( $leftDate ) ){
				$this->_response->throwError( "left-date param" .' '.implode( array_keys( $dateValidator->getMessages() ) ) );
			}
			
			if( !$dateValidator->isValid( $rightDate ) ){
				$this->_response->throwError( "right-date param" .' '.implode( array_keys( $dateValidator->getMessages() ) ) );
			}
			
			$dateBounds = (object) array( 'left-date' =>  $leftDate, 'right-date' => $rightDate );
			$dateLimits = "WHERE do.date BETWEEN STR_TO_DATE( ?, '%d/%m/%Y' ) AND STR_TO_DATE( ?, '%d/%m/%Y' ) ";
			$binds[] = $leftDate;
			$binds[] = $rightDate;
			$this->_response->dateBounds =  $dateBounds;
		}
		
		
		// right-date
		
		if( !isset( $dateFormats[ $dateFormat ] ) ){
			$this->_response->throwError("date-group param '{$dateFormat}' value is not valid or is null");
		}
		
		$this->_response->date_format = $dateFormats[ $dateFormat ];
		
		$query = "
			SELECT content, id_rws_entity, distro, date_group  FROM anta_{$this->_user->username}.rws_entities JOIN 
			(
				SELECT id_rws_entity, COUNT( DISTINCT do.id_document ) as distro,
				  DATE_FORMAT(do.date, '".$dateFormats[$dateFormat]."') as date_group, do.date
				  
				FROM anta_{$this->_user->username}.rws_entities_documents JOIN anta_{$this->_user->username}.documents do 
				  USING (id_document)
				{$dateLimits}
				GROUP BY date_group, id_rws_entity order BY distro DESC
			) distros
			USING ( id_rws_entity ) ORDER BY date_group ASC, distro DESC";
		// echo $query;
		// $this->_response->query =$query;
		
		$stmt = Anta_Core::mysqli()->query( $query, $binds );
		
		$entities = array();
		$num_of_entities = 0;
		
		
		// extablish connection
		$_mysqli = Anta_Core::getMysqliConnection();
		
		while( $row = $stmt->fetchObject() ){
			
			// create subsection
			if( !isset( $entites[ $row->date_group ] ) ){
				$entites[ $row->date_group ] = array();
			}
			$entity = (object) array("id"=>$row->id_rws_entity, "content" => $row->content, "d" => $row->distro, "tags"=>array() );
			
			$_stmt = $_mysqli->query( "
				SELECT ta.id_tag, ta.content, 
				ta.parent_id_tag FROM anta_".$this->_user->username.".`rws_entities_tags` dt NATURAL JOIN anta_".$this->_user->username.".`tags` ta
				WHERE dt.id_rws_entity = ? ", array(
					$row->id_rws_entity
			));
			
			// attach tags
			while( $_row = $_stmt->fetchObject() ){
				$entity->tags[] = $_row->content;
			}
			
			// carica tags
			$entities[ $row->date_group ][] = $entity;
			
			$num_of_entities++;
			
			
		}
		
		$this->_response->num_of_groups = count( $entities );
		$this->_response->num_of_entities = $num_of_entities;
		$this->_response->entities = $entities;
		
		if( $this->_request->getParam( 'debug' ) !== null ){
			print_r( json_decode( $this->_response ) );
			return;
		}
		
		echo( $this->_response );
	}
	
	public function getDocumentsInRangeAction(){
		$this->_response->setAction( 'get-documents-in-range');
		
		// default available date formats
		$dateFormats = array( "Ymdh"=>"%Y%.m.%d.%H", "Ymd" => "%Y.%m.%d", "Ym" =>"%Y.%m", "Y" => "%Y" );	
		
		$dateFormat = $this->_request->getParam( "date-group" );
		
		// available orders
		$availableOrders = array(
			"unix ASC", "unix DESC", "title ASC", "title DESC",
			"id ASC", "id DESC", "language ASC", "language DESC"
		);
		
		// offset, limit, orders
		$limit  = $this->_request->getParam( "limit" );
		$limit  = is_numeric( $limit ) && $limit > 0 && $limit < 5000? $limit: 100;
		
		$offset = $this->_request->getParam( "offset" );
		$offset = is_numeric( $offset ) && $offset > 0? $offset: 0;
		
		$order  = $this->_request->getParam( "order" );
		$order  = in_array( $order, $availableOrders )? $order: "unix ASC";           
		
		// query title content, jsonified
		$search = $this->_request->getParam( "search" );
		if( !empty( $search ) ){ // add title as filter
			
			$search = $this->_response->search = json_decode( str_replace('\\"','"', $search ));
			if( $search == null ){
				$this->_response->throwError( 'title not found or is not json readable ' );
			}
		}
		$titleClause = "";
		
		// left-date
		$leftDate  =  $this->_request->getParam( "left-date" );
		$rightDate =  $this->_request->getParam( "right-date" );
		$atDate    =  $this->_request->getParam( "at-date" );
		$dateClause = "";
		
		// check date format
		$dateValidator = new Ui_Forms_Validators_Date( array(
			'minLenght' => 10,
			'maxLength' => 10
		));
		
		/** binds of stmt */
		$binds = array();
		
		if( !empty( $search->title ) ){
			$titleClause = "AND title LIKE ?";
			$binds[] =  "%".$search->title."%";
		}
		
		// understanding bounds
		if( $leftDate != null && $rightDate != null ){
			
			
			if( !$dateValidator->isValid( $leftDate ) ){
				$this->_response->throwError( "left-date param" .' '.implode( array_keys( $dateValidator->getMessages() ) ) );
			}
			
			if( !$dateValidator->isValid( $rightDate ) ){
				$this->_response->throwError( "right-date param" .' '.implode( array_keys( $dateValidator->getMessages() ) ) );
			}
			
			$dateBounds = (object) array( 'left-date' =>  $leftDate, 'right-date' => $rightDate );
			$dateClause = "AND do.date BETWEEN STR_TO_DATE( ?, '%d/%m/%Y' ) AND STR_TO_DATE( ?, '%d/%m/%Y' ) ";
			$binds[] = $leftDate;
			$binds[] = $rightDate;
			$this->_response->dateBounds =  $dateBounds;
		} else if( $atDate != null ){
			// override leftdate and right date
			
			if( !$dateValidator->isValid( $atDate ) ){
				$this->_response->throwError( "at-date param" .' '.implode( array_keys( $dateValidator->getMessages() ) ) );
			}
			$leftDate  = $atDate.' 00:00:00';//$atDate; // STR_TO_DATE('2003-15-10 00:00:00', '%Y-%m-%d %H:%i:%s')
			$rightDate = $atDate.' 23:59:59';//$atDate; 
			
			$dateClause = "AND do.date >= STR_TO_DATE( ?, '%d/%m/%Y %H:%i:%s' ) AND do.date <= STR_TO_DATE( ?, '%d/%m/%Y %H:%i:%s' )";
			$binds[] = $leftDate;
			$binds[] = $rightDate;
			
			$this->_response->date =  $atDate;
		} 
		
		
		
		// others resumee
		$this->_response->limit  = $limit;
		$this->_response->offset = $offset;
		$this->_response->order  = $order;
		$this->_response->availableOrders = $availableOrders;
		// query of docs
		$query = "
			SELECT SQL_CALC_FOUND_ROWS *, id_document as id, title, `ignore`, language,
				DATE_FORMAT( do.date, '%d/%m/%Y') as tdate,
				UNIX_TIMESTAMP( do.date ) as unix
			FROM anta_{$this->_user->username}.`documents` do 
			  WHERE `ignore` = 0 {$titleClause} {$dateClause}
			order by {$order}
			limit {$offset},{$limit}";
		
		$mysqli = Anta_Core::getMysqliConnection();
		
		$stmt = $mysqli->query( $query, $binds );
		
		$documents = array();
		
		while ( $row = $stmt->fetchObject() ){
			
			$tags = Application_Model_DocumentsMapper::getTags( $this->_user, $row->id );
			$document =  array( "id"=>$row->id, "title"=>$row->title, "date" => $row->date );
			
			foreach( $tags as $tag ){
				if( !isset( $document[ $tag->category ] ) ){
					$document[ $tag->category ] = $tag->content;
					continue;
				}
				$document[ $tag->category ].=", ". $tag->content;
			}
			
			
			
			// load main entities
			$entities = Application_Model_SubEntitiesDocumentsMapper::getEntities( 
				$this->_user,
				$document[ 'id' ],
				array("frequency DESC"),  0, 10  
			);
			
			$document[ 'entities' ] = array();
			
			// cycle through document entities (rws, super and ngr)
			foreach( array_keys( $entities->results ) as $k ){
				$_entity =& $entities->results[ $k ];
				$entity = array(
					"id" => $_entity->id,
					"f"  => $_entity->frequency,
					"r"  => $_entity->relevance,
					"t"  => $_entity->content
				);
				// attach tags to entities
				foreach( $_entity->tags as $tag ){
					if( !isset( $entity[ $tag->category ] ) ){
						$entity[ $tag->category ] = $tag->content;
						continue;
					}
					$entity[ $tag->category ].=", ". $tag->content;
				}
				$document[ 'entities' ][] = $entity;
			}
			
			
			
			$documents[] = $document;
		}
		
		$stmt = $mysqli->query("SELECT FOUND_ROWS() as totalItems" );
		$totalItems = $stmt->fetchObject()->totalItems;
		
		$this->_response->totalDocuments = $totalItems;
		$this->_response->loadedDocuments = count( $documents );
		$this->_response->documents = $documents;
		
		if( $this->_request->getParam( 'debug' ) !== null ){
			print_r( json_decode( $this->_response ) );
			return;
		}
		
		echo $this->_response;
	}
	
	
	public function getDocumentsAction(){
		$this->_response->setAction( 'get-documents');
		
		$stmt = Anta_Core::mysqli()->query("
			SELECT ca.content as category, ta.content as tag, docs.* FROM 
			(
			SELECT id_document, title, `ignore`, count(id_sentence) as sentences, language,
			DATE_FORMAT( d.date, '%Y-%m-%d') date,
			UNIX_TIMESTAMP( d.date ) as unix
			FROM anta_{$this->_user->username}.`documents` d JOIN anta_{$this->_user->username}.sentences s 
			USING (`id_document`)
			GROUP BY `id_document`
			  HAVING `ignore` = 0
			) docs LEFT OUTER JOIN anta_{$this->_user->username}.documents_tags dt USING( id_document ) LEFT JOIN anta_{$this->_user->username}.tags ta USING(id_tag) LEFT JOIN
			anta_{$this->_user->username}.categories ca USING (id_category)");
		
		$documents = array();
		
		$languages = array();
		
		while( $row = $stmt->fetchObject() ){
			if ( !isset( $documents[ $row->id_document ] ) ) {
				$documents[ $row->id_document ] = $row;
				$documents[ $row->id_document ]->cryptoId = Dnst_Crypto_SillyCipher::crypt( $row->id_document );
				$documents[ $row->id_document ]->tags = array();
			}
			
			if( !isset( $languages[ $row->language ] ) ){
				$languages[ $row->language ] = 1;
			} else{
				$languages[ $row->language ]++;
			}
	
			
			$documents[ $row->id_document ]->tags[] = (object) array( "t" => $row->tag, "c"=>$row->category );
			
		}
		$this->_response->languages = $languages;
		$this->_response->documents = $documents;
		
		if( $this->_request->getParam( 'debug' ) !== null ){
			print_r( json_decode( $this->_response ) );
			return;
		}
		echo $this->_response;
	}
	
	public function regexpAction(){
		
		$this->_response->setAction( 'regexp');
		
		// limit result to first 1000
		
		
	}
	
	public function fullTextAction(){
		$this->_response->setAction( 'full-text' );
		
		# has document
		if( $this->_request->getParam( 'document' ) == null )
			$this->_response->throwError( 'document= param value not found in your request' );
		
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'document' ) );
			
		if( !is_numeric( $idDocument ) )			
			$this->_response->throwError( 'document provided is not a valid identifier' );
		
		# get document, along with tags if requested
		$document = $this->_response->document = Application_Model_DocumentsMapper::getDocument( $this->_user,  $idDocument );
		$document->tags = Application_Model_DocumentsMapper::getTags($this->_user,  $idDocument );
		
		# get local url for the given document
		$url = Anta_Core::getDocumentUrl( $this->_user,  $document );
		
		if( !file_exists( $url ) )
			$this->_response->throwError( 'document exists, but the related text file was not found where it is supposed to be. Contact the system administrator' );
		
		# get full text approach
		$document->fullText = @file_get_contents( $url );
		
		echo $this->_response;
	}
	
	public function queryAction(){
		$this->_response->setAction( 'query');
		
		$terms  = json_decode( stripslashes( $this->_request->getParam( "terms" ) ) );
		$bounds = json_decode( stripslashes( $this->_request->getParam( "bounds" ) ) );
		
		if( $terms == null ){ $this->_response->throwError( "'terms' param is not valid or is null" );}
		
		
		
		// stem and other stuff
		$wl = new Anta_Utils_WordList( $terms );
		
		
		// gest some request specs optional
		$strictMode = $this->_request->getParam("strict-mode") != null?true:false;
		$usePattern = $this->_request->getParam("use-pattern") != null?true:false;
		$language   = $this->_request->getParam( "lang" )!= null? $this->_request->getParam( "lang" ):"none";
		
		// apply stemmatisation
		$wl->applyStem( $language );
		
		
		// store info
		$this->_response->strictMode   =  $strictMode;
		$this->_response->usePattern   =  $usePattern;
		$this->_response->stemLanguage =  $language;
		
		// initialize and list dictionaries
		$sentences = array(); 
		$documents = array();
		$neighborhood = array(); 
		
		// clean terms
	    // print_r( $wl );
		
		// get words related to term:
		$sentencesInfo = array(
			"f"  => "stem frequency",
			"d"  => "distinct stem frequency",
			"a"  => "word aliases",
			"id" => "sentence id",
			"x"  => "document id",
			"t"  => "content"
		);
		
		$binds = array();
		// delimits the documents by tags...
		$boundsClause = "";
		if( $bounds != null ){
			$boundClause = "
			AND id_document NOT IN (
				SELECT DISTINCT id_document FROM anta_{$this->_user->username}.documents_tags NATURAL JOIN anta_{$this->_user->username}.tags ta
				WHERE ta.content LIKE ?
			)"; 
			
		}
		
		// if usePattern is requred, we apply a regexp to the mysql 
		
		
		
		// main query 
		
		// f for stem frequency;
		// d for distinc stem frequency
		// a for commaseparated aliases
		// x for id_document
		// t for content
		$query = "
			SELECT SQL_CALC_FOUND_ROWS *, id_sentence as id, count( stem ) as f, count( distinct stem ) as d, group_concat( distinct word ) as a, id_document as x
			FROM 
			(
				SELECT id_sentence, id_occurrence, id_document, stem, word FROM anta_{$this->_user->username}.`occurrences`
				WHERE 
				stem IN ( '".implode("','",$wl->words )."') AND
				id_document NOT IN 
				( 
					SELECT id_document FROM anta_{$this->_user->username}.documents WHERE `ignore` = 1
				)
				
			) as your_sentences
			GROUP BY (id_sentence ) ".( $strictMode? 'HAVING d = '.count( $wl->words ) :'' )."
			ORDER by d DESC, f DESC LIMIT 1000";
		
		$stmt = Anta_Core::mysqli()->query( $query  );
		
		
		while( $row = $stmt->fetchObject() ){
			$sentences[ $row->id ] = $row;
			$sentences[ $row->id ]->r = $row->f * $row->d; // pseudo relevance, quantity-based
		}
		
		// get the sql
		$stmt = Anta_Core::mysqli()->query("SELECT  FOUND_ROWS() as counted");
		$numOfSentences = $stmt->fetchObject()->counted;
		$this->_response->numOfSentences = $numOfSentences;
		
		// divide by ids
		$idSentences = array_keys( $sentences );
		
		// cycle through sentences. "n" is the number of sentences per document
		foreach( $idSentences as $idSentence ){
			$sentence =& $sentences[ $idSentence ];
			if( !isset( $documents[ $sentence->x ] ) ){
				$documents[ $sentence->x ] = array( "id"=>$sentence->x, "n" => 0 );
			}
			$documents[ $sentence->x ][ "n" ] ++;
			
		}
		
		// sort documents by
		uasort( $documents, array( $this, "_sortQueryDocuments" ) );
		
		// divide the ids sentences into less big packages
		$idSentencesChunks = array_chunk ( $idSentences , 100 );
		
		
		// for each sentences group, flush context (group of 100 sentences)
		// main cycle
		foreach( array_keys( $idSentencesChunks ) as $k ){
			$stmt = Anta_Core::mysqli()->query("
				SELECT oc.id_occurrence, stem, LOWER( word ) as word, se.id_sentence, se.content, se.id_document FROM  anta_{$this->_user->username}.`occurrences` oc JOIN anta_{$this->_user->username}.sentences se USING (id_sentence)
				WHERE id_sentence IN (  '".implode("','",$idSentencesChunks[$k] )."' )
				ORDER BY se.id_document ASC, se.id_sentence ASC, oc.id_occurrence ASC "
			);
			
			// esceute query
			while( $row = $stmt->fetchObject() ){
				if( $sentences[ $row->id_sentence ]->t == null ){
					$sentences[ $row->id_sentence ]->t = $row->content;
				}
				if( ! isset( $neighborhood[ $row->stem ] ) ) {
					$neighborhood[ $row->stem ] = array();
				}
				
				$neighborhood[ $row->stem ][] = $row->word;
			}
			
		
		}
		
		
		
		$query = "
			
			
			SELECT * FROM  anta_{$this->_user->username}.`occurrences`
			WHERE id_sentence IN ( '".implode("','",$wl->words )."' )
		";
		
		// filter and computate relevance
		if( $usePattern ){
		
		}
		
		
		
		$this->_response->terms = $terms;
		$this->_response->sentences = $sentences;
		$this->_response->documents = $documents;
		
		
		// print_r( $neighborhood );
		
		// print_r( $documents );
		
		// get sentences key
		
		
		// filter sentences...
		
		
		// load content, if you like
		
		
		// $stmt = Anta_Core::mysqli()->query("
		
		
		/*
		// case study A: no order for the stems; easy
		
		// select the right documents to visualize
		(
		SELECT id_document FROM documents WHERE ignore != 0
		)
		
		// select sentences where given stems were found, OR clause
		(
		SELECT id_sentence, id_occurrence, stem FROM `occurrences`
		  WHERE 
		    stem IN ( "council", "commiss" ) AND
			id_document NOT IN 
			( 
			SELECT id_document FROM documents WHERE `ignore` = 1
			)
		) 
		as your_sentences
		
		// note thqt we do not qpply stopwordlist
		// computate relevance for the given sentence, groupbing by id_sentence		
		// distro: distinct stem into sequence
		// frequency: stem found
		
		SELECT id_sentence, count( stem ) as frequency, count( distinct stem ) as distro
		FROM your_sentences
		GROUP BY (id_sentence )
		ORDER by distro DESC, frequency ASC
		
		SELECT id_sentence, count( stem ) as frequency, count( distinct stem ) as distro
FROM (
 SELECT id_sentence, id_occurrence, stem FROM `occurrences`
 WHERE 
 stem IN ( "council", "commiss" ) AND
 id_document NOT IN 
 ( 
 SELECT id_document FROM documents WHERE `ignore` = 1
 )
) as your_sentences
		GROUP BY (id_sentence )
		ORDER by distro DESC, frequency ASC
		
		
		// END
		// case B: difficile, teniamo conto della posizione, dobbiamo tirarle fuori tutte
		
		
		
		
		$stmt = Anta_Core::mysqli()->query("
			SELECT id_document, title, `ignore`, count(id_sentence) as sentences,
			DATE_FORMAT( d.date, '%Y-%m-%d') date,
			UNIX_TIMESTAMP( d.date ) as unix
			FROM anta_{$this->_user->username}.`documents` d JOIN anta_{$this->_user->username}.sentences s 
			USING (`id_document`)
			GROUP BY `id_document`
			  HAVING `ignore` = 0");
		
		$documents = array();
		
		while( $row = $stmt->fetchObject() ){
			$documents[] = $row;
		}
		
		$this->_response->documents = $documents;
		*/
		$this->_response->translit();
		
		if( $this->_request->getParam( 'debug' ) !== null ){
			print_r( json_decode( $this->_response ) );
			return;
		}
		echo $this->_response;
	}
	
	private function _sortQueryDocuments( $a, $b ){
		return $a[ 'n' ] < $b[ 'n' ] ? 1 : ( $a[ 'n' ] > $b[ 'n' ] ? -1 : 0);
	}
	
	
	
	
	
}

