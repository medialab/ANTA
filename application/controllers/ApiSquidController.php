<?php

/**
 * howto: // check identity in an action 
 *	           $user = $this->_authorizeUser( $this->_getUser() );
 */
class ApiSquidController extends Application_Model_Controller_Api
{

	protected function _getGraphTypes(){
		// the three types of graph
		$graphDte = $this->_request->getParam("doc-to-ent");
		$graphDtd = $this->_request->getParam("doc-to-doc");
		$graphEte = $this->_request->getParam("ent-to-ent");
		return array(
			"doc_to_ent" => empty($graphDte)? true: $graphDte==="false"? false: true,
			"doc_to_doc" => empty($graphDtd)? true: $graphDtd==="false"? false: true,
			"ent_to_ent" => empty($graphEte)? true: $graphEte==="false"? false: true
		);
	}
	
	
	
	protected function _getEntities( $prefix ){
		$selectAllEntitiesTags = $this->_request->getParam("all-entities-tags");
		// the exception
		$entitiesTags  = $this->_request->getParam("exception-entities-tags");
		
		// check if entities have valid values
		if( !empty( $entitiesTags ) ){
			if(!is_array( $entitiesTags )){
				$this->_response->throwError("no valid entities-tags found in your request...");
			}
			foreach( $entitiesTags as $tag) {
				// filter by numeric stuff
				if (!is_numeric( $tag ) ){
					$this->_response->throwError("'{$tag}' isn't a valid value");
				}
			}
		} else {
			$entitiesTags = array();
		}
		
		// setup entities info
		return (object) array(
			"tags"		=> (object) array( 
				"select_all" => empty( $selectAllEntitiesTags  )? true: $selectAllEntitiesTags  == "true"? true: false,
				"exception"  => $entitiesTags
			),
			"loaded"	=> 0,
			"unignored" => Application_Model_SubEntitiesMapper::getUnignoredNumberOfEntities( $this->_user, $prefix ),
			"total"		=> Application_Model_SubEntitiesMapper::getNumberOfEntities( $this->_user, $prefix )
		);
	}
	
	protected function _getDocuments(){
		// all tags (document tags ) selected?
		$selectAllDocumentsTags = $this->_request->getParam("all-documents-tags");
		$documentsTags = $this->_request->getParam("exception-documents-tags");
		
		// check if documentsTags have valid values
		if( !empty( $documentsTags ) ){
			if(!is_array( $documentsTags )){
				$this->_response->throwError("no valid documents-tags found in your request...");
			}
			foreach( $documentsTags as $tag) {
				// filter by numeric stuff
				if (!is_numeric( $tag ) ){
					$this->_response->throwError("'{$tag}' isn't a valid value");
				}
			}
		} else {
			$documentsTags = array();
		}
		
		return (object) array(
			"tags"		=> (object) array( 
				"select_all" => empty( $selectAllDocumentsTags  )? true: $selectAllDocumentsTags  == "true"? true: false, 
				"exception"  => $documentsTags
			),
			"loaded"	=> 0,
			"unignored"	=> Application_Model_DocumentsMapper::getUnignoredNumberOfDocuments( $this->_user ),
			"total"		=> Application_Model_DocumentsMapper::getNumberOfDocuments( $this->_user )
		);
	}
	
	protected function _getMetrics(){
	
	}
	
	/**
	 * to be used inside inteerface perpare visualization
	 */
	public function graphUpdateStatsAction(){
		$this->_response->setAction( 'get-df-itf-stats' );
		
		
		
		$graphs = $this->_getGraphTypes();
		$prefix = $this->_getPrefix();
		$entities  = $this->_getEntities( $prefix );
		$documents = $this->_getDocuments();
		
		$this->_computateTfIdfMetrics( $prefix, $entities, $documents, false );
		
		// print out information collected
		$this->_response->documents = $documents;
		$this->_response->entities = $entities;
		$this->_response->graphs = $graphs;
			
		exit( $this->_response );
	}
	
	
	
	/**
	 * Using tf measure,
	 */
	public function ignoreEntitiesByTermFrequencyAction(){
		$this->_response->setAction( 'ignore-entities-by-term-frequency' );
		$time = microtime(true);
		
		$prefix = "rws";
		
		$query = "
			SELECT 
			`red`.id_document, `red`.id_{$prefix}_entity as id_entity, `red`.frequency, epd.entities_per_document
			FROM
			(
				select `red`.`id_document` AS `id_document`,
				sum(`red`.`frequency`) AS `entities_per_document` 
				from 
				`anta_{$this->_user->username}`.`{$prefix}_entities_documents` `red`,
				`anta_{$this->_user->username}`.`documents` `doc`
				
				WHERE 
					`doc`.`ignore` = 0 and `red`.`id_document` = `doc`.`id_document`
				GROUP BY `red`.`id_document`
			)as `epd`
			INNER JOIN `anta_{$this->_user->username}`.`{$prefix}_entities_documents_unignored` `red` 
				USING(`id_document`) 
			 
		";
		# execute query for computation of tf for each entity (not ignored)
		$stmt = Anta_Core::mysqli()->query(
			$query
		);
		
		# instantiate the object which computate the metrics
		$metrics = (object) array( 
			'documents' => array(), 
			'entities'  => array()
		);
		
		#looping
		while( $row = $stmt->fetchObject() ){
			
			# increment the number of loaded documents
			if( !isset( $metrics->documents[ $row->id_document ] ) ){
				$metrics->documents[ $row->id_document ] = 0;
				$documents->loaded++;
			}
			
			# increment the number of loaded entities
			if( !isset( $metrics->entities[ $row->id_entity ] ) ){
				$metrics->entities[ $row->id_entity ] = new Application_Model_EntityMetrics( $row->id_entity );
				$entities->loaded++;
			}
			
			# computate tf-idf only if it is required
			$metrics->entities[ $row->id_entity ]->appendTermFrequency( 
				$row->id_document, 
				$row->frequency / $row->entities_per_document
			);
			
			
		}
		
		$this->_response->documents = $documents->loaded;
		$this->_response->entities  = $entities->loaded; 
		
		# some limits
		$bounds = array();
		
		// only entities with distro = $forx
		$forx = $this->_request->getParam( "distro" );
		$forx = $bounds["distro"] = empty( $forx )? null: is_numeric( $forx )? $forx: null;
		
		$minx = $this->_request->getParam( "min-distro" );
		$minx = $bounds["min-distro"] = empty( $minx )? null: is_numeric( $minx )? $minx: null;
		
		$maxx = $this->_request->getParam( "max-distro" );
		$maxx = $bounds["max-distro"] = empty( $maxx )? null: is_numeric( $maxx )? 
			$maxx < 0 ? $documents->loaded + $maxx :$maxx: null;
		
		$miny = $this->_request->getParam( "min-tf" );
		$miny = $bounds["min-tf"] = empty( $miny )? null: is_numeric( $miny )? $miny: null;
		
		$maxy = $this->_request->getParam( "max-tf" );
		$maxy = $bounds["max-tf"] = empty( $maxy )? null: is_numeric( $maxy )? $maxy: null;
		$this->_response->bounds = $bounds;
		
		# array of ids to be ignored
		$excluded = array();
		
		#check stuff
		foreach( array_keys( $metrics->entities ) as $k ){
			
			$entity = $metrics->entities [$k ];
			
			$point = (object) array(
				'x' => $entity->getNumberOfDocuments(),
				'y' => $entity->getMaxUniqueness( $documents->loaded ),
				't' => $k
			);
			# min-distribution and max-distribution filter
			if( $forx == null ){
				if( $minx != null && $point->x < $minx ){
					$excluded[] = $k;
				} else if ( $maxx != null &&  $point->x > $maxx ){
					$excluded[] = $k;
				}
			}
			
			# min-tf and max-tf filter
			if( $miny != null && $point->y < $miny ){
				if( $forx == null ){
					$excluded[] = $k;
				} else if( $forx == $point->x ){
					$excluded[] = $k;
				}
			} else if( $maxy != null && $point->y > $maxy ){
				if( $forx == null || $forx == $point->x ){
					$excluded[] = $k;
				}
			};
			
			//echo $k."\t".$point->x.",".$point->y." $miny\n";
			
		};
		
		if( count($excluded ) == 0 ){
			$this->_response->entitiesToIgnore = 0;
			echo $this->_response;
			exit;
		}
		$this->_response->entitiesToIgnore = count( $excluded);
		
		$this->_response->cycles = $cycles = ceil( count( $excluded ) / 1000 );
		
		$preview = $this->_request->getParam("preview")!=null;
		$this->_response->preview = $preview;
		// clean metrics
		// unset($metrics);
		for( $i = 0; $i < $cycles; $i++ ){
			$offset = max( 0, count( $excluded ) - 1000 );
			$partial = array_splice( $excluded, $offset  ) ;
			$query = "
				UPDATE `anta_{$this->_user->username}`.`{$prefix}_entities` SET
					`ignore` = 1 
				WHERE `id_{$prefix}_entity` IN (". implode(",", array_fill( 0, count( $partial ), "?" ) ).");
				
			";
			if(!$preview){
				$stmt = Anta_Core::mysqli()->query(
				$query, $partial
			);
			}
			/*
			# execute query for computation of tf for each group of entities ( not ignored )
			
			*/
		}
		
		
		
		if( $this->verbose != null ){
			$this->_response->query = $query;
		}
		
		
		$this->_response->elapsed = microtime(true) - $time;
		
		echo $this->_response;
	}
	
	/**
	 * Computate tfidf
	 * This weight is a statistical measure used to evaluate how important a word is to a document in a collection or corpus
	 * cfr. http://en.wikipedia.org/wiki/Tf%E2%80%93idf
	 */
	protected function _fastComputateTfIdfMetrics( $prefix, &$entities, &$documents ){
		$time = microtime(true);
		# build a real table with stuff...
		$query = "
			
			SELECT 
			`red`.id_document, `red`.id_{$prefix}_entity as id_entity, `red`.frequency, epd.entities_per_document
			FROM
			(
				select `red`.`id_document` AS `id_document`,
				sum(`red`.`frequency`) AS `entities_per_document` 
				from 
				`anta_{$this->_user->username}`.`{$prefix}_entities_documents` `red`,
				`anta_{$this->_user->username}`.`documents` `doc`
				
				WHERE 
					`doc`.`ignore` = 0 and `red`.`id_document` = `doc`.`id_document`
				 group by `red`.`id_document`
			) as `epd`
			JOIN `anta_{$this->_user->username}`.`{$prefix}_entities_documents_unignored` `red` 
				USING(`id_document`) 
			
		";
		
		//if( $this->verbose != null ){
			$this->_response->query = $query;
		//}
		
		# execute query for computation of tf for each entity (not ignored)
		$stmt = Anta_Core::mysqli()->query(
			$query
		);
		
		# instantiate the object which computate the metrics
		$metrics = (object) array( 
			'documents' => array(), 
			'entities'  => array()
		);
		
		#looping
		while( $row = $stmt->fetchObject() ){
			
			# increment the number of loaded documents
			if( !isset( $metrics->documents[ $row->id_document ] ) ){
				$metrics->documents[ $row->id_document ] = 0;
				$documents->loaded++;
			}
			
			# increment the number of loaded entities
			if( !isset( $metrics->entities[ $row->id_entity ] ) ){
				$metrics->entities[ $row->id_entity ] = new Application_Model_EntityMetrics( $row->id_entity );
				$entities->loaded++;
			}
			
			# computate tf-idf only if it is required
			$metrics->entities[ $row->id_entity ]->appendTermFrequency( 
				$row->id_document, 
				$row->frequency / $row->entities_per_document
			);
			
			
		}
		
		# the "easy" grdi way. cmax is the number of point for each cell in the grid( see below)
		$xmax = $ymax = $cmax = 0;
		$xmin = $ymin = $cmin = PHP_INT_MAX;
		
		$yres = 100;	// max y distinct values
		$yval = 0;	// max distinct values of y
		$xres = 200;	// max x distinct values
		$xval = 0;	// max distinct values of x
		
		
		
		# set of points (all, without grouping them)
		$points = array();
		$c = 0;
		# computation of maxs and mins
		foreach( array_keys( $metrics->entities ) as $k ){
			$entity = $metrics->entities [$k ];
			$point = (object) array(
				'x' => $entity->getNumberOfDocuments(),
				'y' => $entity->getMaxUniqueness( $documents->loaded ),
				't' => $k
			);
			
			$xmin = min($xmin, $point->x);
			$ymin = min($ymin, $point->y);
			$xmax = max($xmax, $point->x);
			$ymax = max($ymax, $point->y);
			
			$xkey = "".$point->x;
			$ykey = "".$point->y;
			
			if( !isset( $points[ $xkey ] ) ){
				$points[ $xkey ] = array();
				$xval++;
			}
			if( !isset( $points[ $xkey ][ $ykey ] ) ){
				$points[ $xkey ][ $ykey ] = array();
			}
			$points[ $xkey ][ $ykey ][] = $point;
			$yval = max( $yval, count( $points[ $xkey ][ $ykey ] ) );
			
			$c++;
		}
		
		#regrouping values
		$grid = array();
		
			// delta values 
			$c = 0;
			$dy = $ymax - $ymin;
			foreach( array_keys( $points ) as $xkey ){
				$grid[ $xkey ] = array();
				
				// echo $xkey. " ". count(  $points[ $xkey ] )."\n";
				foreach( array_keys( $points[  $xkey ] ) as $ykey ){
					
					// floor: mediane of tf
					$ry = floor( $yres * ( $ykey - $ymin ) / $dy );
					
					if( !isset( $grid[ $xkey ][ $ry ] ) ){
						$grid[  $xkey ][ $ry ] = (object) array( 'c'=>0, 'v'=>0,'t'=> array() );
					}
					
					$grid[ $xkey ][ $ry ]->c += count( $points[ $xkey ][ $ykey ] ) ;
					// foreach point in points[x][y]
					foreach( array_keys( $points[ $xkey ][ $ykey ] ) as $pkey ){
						$grid[ $xkey ][ $ry ]->v +=  $points[  $xkey ][ $ykey ][ $pkey ]->y;
						$grid[ $xkey ][ $ry ]->t[] = $points[  $xkey ][ $ykey ][ $pkey ]->t;
						$c++;
					}
					
				}
			}
		
		
		# substitute points with grid simplified values
		if( !empty( $grid ) ){
			
			$points = array();
			foreach( array_keys( $grid ) as $x ){
				foreach( array_keys( $grid[$x] ) as $y){
					$points	[] = (object) array(
						'x' => $x,
						'y' => $grid[$x][$y]->v / $grid[$x][$y]->c, // average of tf-idf for each grid cell
						't' => $grid[$x][$y]->t,
						'c' => $grid[$x][$y]->c
					);
					$cmin = min( $cmin, $grid[$x][$y]->c );
					$cmax = max( $cmax, $grid[$x][$y]->c );
				}
			};
		}
		
		
		
		$this->_response->zoom = 1;
		$this->_response->bounds = array( 
			'xmin' => $xmin,
			'ymin' => $ymin,
			'xmax' => $xmax,
			'ymax' => $ymax,
			'cmin' => $cmin,
			'cmax' => $cmax
		);
		
		
		$time4 = microtime(true);
		$this->_response->points = $points;
		$this->_response->elapsed = ($time4 - $time);
		$this->_response->number_of_documents = $documents->loaded;
	}
	
	protected function _computateTfIdfMetrics( $prefix, &$entities, &$documents, $getPoints = true ){
		# load all entities tags in a special table
		
		$stmt = Anta_Core::mysqli()->query("SELECT DISTINCT id_tag as id_tag FROM anta_{$this->_user->username}.`{$prefix}_entities_tags`");
		$entitiesTags = array();	
		while( $row = $stmt->fetchObject() ){
			$entitiesTags[] = $row->id_tag;
		}
		if( empty($entitiesTags)){
			// $this->_response->throwError("no tags was found into your entities table");
		}
		
		$includeUntaggedEntities = true;
		
		$magicEntities = "( SELECT";
			
		foreach( $entitiesTags as $tag ){
			$magicEntities.= " IF( id_tag = $tag,1,0) AS  `$tag`,";
			break;
		}
		$magicEntities.= " 
			id_{$prefix}_entity FROM anta_{$this->_user->username}.{$prefix}_entities_tags 
			
			".( !empty( $entities->tags->exception )? "WHERE `".implode( "` + `", $entities->tags->exception )."`=".
			$entities->tags->select_all?0:( count( $entities->tags->exception ) ):"").") as tag_filtered_entities";
		
		$time = microtime(true);
		
		#main query, out term_frequency value for each entity
		$query = $this->_response->query = "
			
			SELECT 
			`red`.id_document, `red`.id_rws_entity as id_entity, `red`.frequency, epd.entities_per_document
			FROM
			(
				select `red`.`id_document` AS `id_document`,
				count(`red`.`id_rws_entity`) AS `entities_per_document` 
				from 
				`anta_{$this->_user->username}`.`rws_entities_documents` `red`,
				`anta_{$this->_user->username}`.`documents` `doc`,
				`anta_{$this->_user->username}`.`rws_entities` `ent`
				WHERE 
					`doc`.`ignore` = 0 and `ent`.`ignore` = 0 and `red`.`id_document` = `doc`.`id_document` and `red`.`id_rws_entity` = `ent`.`id_rws_entity`
				 group by `red`.`id_document`
			) as `epd`
			JOIN `anta_{$this->_user->username}`.`rws_entities_documents` `red` 
				USING(`id_document`) 
			
		";
		
		# execute query for computation of tf for each entity (not ignored)
		$stmt = Anta_Core::mysqli()->query(
			$query
		);
		
		
		$time2 = microtime(true);
		// echo "\n".($time2-$time);
		
		$metrics = (object) array( 
			'documents' => array(), 
			'entities'  => array()
		);
		
		
		
		// my temporary table
		while( $row = $stmt->fetchObject() ){
			
			# increment the number of loaded documents
			if( !isset( $metrics->documents[ $row->id_document ] ) ){
				$metrics->documents[ $row->id_document ] = 0;
				$documents->loaded++;
			}
			
			# increment the number of loaded entities and computate idf-tf
			if( !isset( $metrics->entities[ $row->id_entity ] ) ){
				$metrics->entities[ $row->id_entity ] = new Application_Model_EntityMetrics( $row->id_entity );
				$entities->loaded++;
			}
			// computate tf-idf only if it is required
			if( $getPoints ){
				$metrics->entities[ $row->id_entity ]->appendTermFrequency( 
					$row->id_document, 
					$row->frequency / $row->entities_per_document
				);
			}
			
		}
		$time3 = microtime(true);
		
		// echo "\nin ".($time3 - $time);
		// echo "\n".$entities->loaded;
		// exit;
		
		
		if( !$getPoints ) return;
		
		//print_r( $metrics);
		
		# computate df-idft
		
		
		$xmax = $ymax = 0;
		$xmin = $ymin = PHP_INT_MAX;
		
		// max y distinct values
		$yres = 100;
		// max distinct values of y
		$yval = 0;
		// max x distinct values
		$xres = 200;
		// max distinct values of x
		$xval = 0;
		
		$points = array();
		$c = 0;
		# computation of maxs and mins
		foreach( array_keys( $metrics->entities ) as $k ){
			$entity = $metrics->entities [$k ];
			$point = (object) array(
				'x' => $entity->getNumberOfDocuments(),
				'y' => $entity->getMaxUniqueness( $documents->loaded ),
				't' => $k
			);
			
			$xmin = min($xmin, $point->x);
			$ymin = min($ymin, $point->y);
			$xmax = max($xmax, $point->x);
			$ymax = max($ymax, $point->y);
			
			$xkey = "".$point->x ;
			$ykey = "".$point->y ;
			
			if( !isset( $points[ $xkey ] ) ){
				$points[ $xkey ] = array();
				$xval++;
			}
			if( !isset( $points[ $xkey ][ $ykey ] ) ){
				$points[ $xkey ][ $ykey ] = array();
			}
			$points[ $xkey ][ $ykey ][] = $point;
			$yval = max( $yval, count( $points[ $xkey ][ $ykey ] ) );
			
			$c++;
		}
		// print_r( $points );
		#regrouping values
		$grid = array();
		// echo "points: $c \n";
		if( $yval > $yres ){
		
			// delta values 
			$c = 0;
			$dy = $ymax - $ymin;
			foreach( array_keys( $points ) as $xkey ){
				$grid[ $xkey ] = array();
				
				// echo $xkey. " ". count(  $points[ $xkey ] )."\n";
				foreach( array_keys( $points[  $xkey ] ) as $ykey ){
					
					//Unsure if this is the right solution, but without the system crashes when $ymax = $ymin and we end out deviding by 0.
					if($dy != 0){ 	
						// floor something
						$ry = floor( $yres * ( $ykey - $ymin ) / $dy );
					}
					
					if( !isset( $grid[ $xkey ][ $ry ] ) ){
						$grid[  $xkey ][ $ry ] = (object) array( 'c'=>0, 'v'=>0,'t'=> array() );
					}
					
					$grid[ $xkey ][ $ry ]->c += count( $points[ $xkey ][ $ykey ] ) ;
					// foreach point in points[x][y]
					foreach( array_keys( $points[ $xkey ][ $ykey ] ) as $pkey ){
						$grid[ $xkey ][ $ry ]->v +=  $points[  $xkey ][ $ykey ][ $pkey ]->y;
						$grid[ $xkey ][ $ry ]->t[] = $points[  $xkey ][ $ykey ][ $pkey ]->t;
						$c++;
					}
					
				}
			}
			// echo "\n totals:".$c."\n";
		}
		
		# substitute points with grid simplified values
		if( !empty( $grid ) ){
			
			$points = array();
			foreach( array_keys( $grid ) as $x ){
				foreach( array_keys( $grid[$x] ) as $y){
					$points	[] = (object) array(
						'x' => $x,
						'y' => $grid[$x][$y]->v / $grid[$x][$y]->c, // average of tf-idf for each grid cell
						't' => $grid[$x][$y]->t,
						'c' => $grid[$x][$y]->c
					);
				}
			};
		}
		
		
		
		$this->_response->zoom = 1;
		$this->_response->bounds = array( 
			'xmin' => $xmin,
			'ymin' => $ymin,
			'xmax' => $xmax,
			'ymax' => $ymax
		);
		
		
		$time4 = microtime(true);
		$this->_response->points = $points;
		$this->_response->elapsed = ($time4 - $time);
		$this->_response->number_of_documents = $documents->loaded;
	}
	
	/**
	 * alias for entitiesGetContentsAction() of ApiController class
	 */
	public function getEntitiesContentsAction(){
		$this->_response->setAction( 'get-entities-contents"' );
		$prefix = $this->_response->prefix = $this->_getPrefix();
		$entities = $this->_request->getParam("entities");
		$this->_response->caller = $this->_request->getParam("caller");
		$this->_response->sample = http_build_query(array("entities"=>array(123,145,165) ));
		if( empty( $entities ) || !is_array( $entities ) ){
			$this->_response->throwError( "'entities' param was not found or is not valid" );
		}
		
		$tags = $this->_response->entities = Application_Model_SubEntitiesMapper::getEntitiesByIds( $this->_user, $prefix, $entities );
		echo $this->_response;
	}
	
	public function getDfItfDistributionAction(){
		$this->_response->setAction( 'get-df-itf-distribution');
		
		// request logic of prefixes here
		$prefix = $this->_getPrefix();
		
		
		$entities  = $this->_response->entities  = $this->_getEntities( $prefix );
		$documents = $this->_response->documents = $this->_getDocuments();
		
		$this->_fastComputateTfIdfMetrics( $prefix, $entities, $documents );
		
		echo $this->_response;
		
	}
	/**
	 * Return a list of point which describes the linear distribution
	 * of number of entities(x) and number of entities per number of documents
	 * f(x). Sql Queries are explicitated for debug purpose (instead of being written inside specific models).
	 */
	public function getLinearDistributionAction(){
		$this->_response->setAction( 'get-linear-distribution');
		
		
		# understanded and clean params
		/** horizontal axis resolution */
		$resolution = $this->_request->getParam('resolution');
		$resolution = $this->_response->resolution = empty($resolution) || !is_numeric( $resolution )? 1000: max(1, min( $resolution, 1000 ) );
		
		
		
		
		# get number of valid (not ignored) documents
		$query = "SELECT number_of_documents FROM anta_{$this->_user->username}.documents_metrics";
		$stmt = Anta_Core::mysqli()->query( $query );
		$number_of_documents = $this->_response->number_of_documents = $stmt->fetchObject()->number_of_documents;
		
		# prepare resopnes
		
		if( $number_of_documents < $resolution ){
			$this->_response->zoom = 1;
			# how many entities are present in the same amout of documents?
			# computate distribution of entities, then 
			$stmt = Anta_Core::mysqli()->query("
				SELECT COUNT(id_rws_entity) as y, distribution as x 
				FROM (
					SELECT 
						`red`.`id_rws_entity` AS `id_rws_entity`,
						count(`red`.`id_document`) AS `distribution`
					FROM anta_{$this->_user->username}.`rws_entities_documents_unignored` `red`
					GROUP by `red`.`id_rws_entity`
				) AS entd GROUP BY distribution ORDER BY distribution ASC"
			);
			
			
			
			$points = array();
			$xmax = $ymax = 0;
			$xmin = $ymin = PHP_INT_MAX;
			while( $row = $stmt->fetchObject() ){
				
				$y = $row->y;// > 1? log( $row->y );
				$points[] = (object)array('x'=>$row->x, 'y'=>$y);
				$xmin = min($xmin, $row->x);
				$ymin = min($ymin, $y);
				$xmax = max($xmax, $row->x);
				$ymax = max($ymax, $y);
			}
			$this->_response->bounds = array( 
				'xmin' => $xmin,
				'ymin' => $ymin,
				'xmax' => $xmax,
				'ymax' => $ymax
			);
			$this->_response->points = $points;
			
		} else {
			$this->_response->throwError("not yet available");
		}
		
		
		
		
		// check rws number
		
		echo $this->_response;
	}
	
}
?>
