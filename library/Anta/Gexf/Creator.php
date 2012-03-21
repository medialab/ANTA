<?php
/**
 * @package Anta_Gexf
 */
 
/**
 * Background script to build a gexf graph using a single table.
 *
 * Dependences:
 * Anta_Distiller
 * Application_Model_RoutinesMapper
 * Application_Model_User
 * Anta_Logging
 */
class Anta_Gexf_Creator extends Anta_Distiller {
	
	public $graph;
	
	public $prefix;
	
	public function __construct( ){
		$this->namespace = "gexf_default";
		parent::__construct();
	}
	
	protected function _init(){
		
		// default, empty
		$options = array('g'=>"");
	
		// get option alias graphs id
		if( $this->debug === false ){
			$options = getopt("u:g:");
			// print_r( $options );
		}
		
		// load user from request, if any,or via arg if command line php
		$idGraph = isset( $_REQUEST[ 'graph' ] )? $_REQUEST[ 'graph' ]: $options[ 'g' ];
		
		// load table prefix from request, if any has been provided, or via arg if command line php
		$this->prefix = isset( $_REQUEST[ 'prefix' ] )? $_REQUEST[ 'prefix' ]: $options[ 'p' ];
		
		// check prefix
		if( $this->prefix == null ){
			$this->prefix = "rws";
		}
		
		// load graph
		$this->graph = Application_Model_GraphsMapper::getGraph( $this->user, $idGraph );

		
		if( $this->graph == null ){
			$this->addRoutineError( "param '?graph='$idGraph' was not found, or is not a valid graph id\nreceived from cmd: ".implode( ",",$options ) );
			// force exit;
			exit;
		}
		
		
		$this->_setDescription( "initializing script..." );
	}
	
	public function exceptionHandler( $exception ){
		if( $this->graph != null && $this->user != null ){
			$this->_setError( $exception );
		}
		Anta_Logging::append(  $this->log, "! exception:".$exception->getMessage(), false );
	}
	
	protected function _setDescription( $description ){
		// update description
		Anta_Logging::append( $this->log, "updating graph entry 'description': $description".Application_Model_GraphsMapper::setDescription( $this->user, $this->graph->id, $description ), false );
		
	}
	
	/**
	 * usage: $this->_setError( "failed: unable to write into path" );
	 */
	protected function _setError( $error ){
		// update description
		Anta_Logging::append( $this->log, "updating graph entry 'error': $error".Application_Model_GraphsMapper::setError( $this->user, $this->graph->id, $error ), false );
		
	}
	
	/**
	 * usage: $this->_setError( "failed: unable to write into path" );
	 */
	protected function _setUrl( $url ){
		// update description
		Anta_Logging::append( $this->log, "updating graph entry 'localUrl': $url".Application_Model_GraphsMapper::setLocalUrl( $this->user, $this->graph->id, $url ), false );
		
	}
	
	/**
	 * replace quotes and other dummy character from the given string 
	 */
	public static function clean( $s ) {
		return str_replace( 
			array( "\\\"",	"\""),
			"",
			$s
		);
	}
	
	public function start(){
		
		$outputFile = APPLICATION_PATH ."/../gexf/".$this->namespace."_".$this->user->username."_".$this->prefix."_graph_".$this->graph->id.".gexf";
		
		$this->_setDescription( "opening gexf file for writing: ".basename( $outputFile ) );
		
		if( !is_writable( APPLICATION_PATH ."/../gexf" ) ){
			$this->_setError( "unable to write file gexf/".basename( $outputFile ) );
			return;
		}
		
		$fp = fopen( $outputFile, 'w');
		
		$this->_setDescription( "1/7. writing headers..." );
		
		fwrite( $fp, '<?xml version="1.0" encoding="UTF-8"?>
			<gexf xmlns="http://www.gexf.net/1.1draft" version="1.1" xmlns:viz="http://www.gexf.net/1.1draft/viz" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.gexf.net/1.1draft http://www.gexf.net/1.1draft/gexf.xsd">
			<meta lastmodifieddate="2011-01-27">
				<creator>Anta 0.7</creator>
				<description>Graph Document - '.$this->prefix.' Entities </description>
			</meta>
			<graph defaultedgetype="directed" mode="static">
			<attributes class="node" mode="static">
				<attribute id="type" title="type" type="string"/>' 
		);
		
		// load possible attributes
		$categories = Application_Model_CategoriesMapper::getAll( $this->user );
		// void categorization
		fwrite( $fp, '<attribute id="attr_" title="without categorization" type="string"/>' );		
		foreach ( $categories as $category ){
			fwrite( $fp, '<attribute id="attr_'. $category->id. '" title="'. str_replace( array( "\\", "\"" ), "", $category->content ). '" type="string"/>' );
		}
		
		// load possible tags categories
		// $entitiesTags = Application_Model_SubEntitiesTagsMapper::getTableTags( $this->_user, $prefix , $ignore);
		
		// edge attributes
		fwrite( $fp, '</attributes>
			<attributes class="edge" mode="static">
				<attribute id="width" title="width" type="double"/>
			</attributes>'
		);
		
		fwrite( $fp, '<nodes>' );
		
		$this->_setDescription( "2/7. loading documents..." );
		
		// dump all documents and their attributes
		$stmt = Anta_Core::mysqli()->query( "
			SELECT content, title, id_document, id_category FROM anta_{$this->user->username}.documents
			LEFT OUTER JOIN  (
			  SELECT dt.id_tag, dt.id_document, content, id_category
			    FROM anta_{$this->user->username}.tags t, anta_{$this->user->username}.`documents_tags` dt 
				WHERE t.id_tag = dt.id_tag
			) as ctags USING( id_document ) WHERE `ignore` = 0
			ORDER BY id_document ASC" 
		);
		$id_document = 0;
		$currentNode  = null;
		
		$colors = array(
			"deep-red" => 'r="241" g="45" b="21"',
			"navy-blue" => 'r="59" g="68" b="77"'
		);
		
		while( $row = $stmt->fetchObject() ){
			if( $row->id_document != $id_document ){
				
				// print previous node
				if( $currentNode != null ) fwrite( $fp, $currentNode );
			
				// create a new node
				$currentNode = new Anta_Gexf_Node(
					"d".$row->id_document, 
					$row->title, // Anta_Core::translit($row->title, -1, false, true),
					array( "type"=>"document" ),
					array(
						"color" => $colors[ "deep-red" ]
					)
				);
				
				$id_document = $row->id_document;
			}
			
			// fill attribute
			
			$index = "attr_".$row->id_category;
			// echo $index;
			if( empty( $index ) ) continue; // the node hasn't tags
			if( !isset( $currentNode->atts[ $index ] ) ){
				$currentNode->atts[  $index  ] = self::clean( $row->content );
			} else {
				$currentNode->atts[ $index ] .= ", ". $row->content;
			}
			
			
		}
		if( $currentNode != null ) fwrite( $fp, $currentNode );
		
		$this->_setDescription( "3/7. loading entities..." );
		
		$stmt = Anta_Core::mysqli()->query( "
			SELECT
				en.content, id_{$this->prefix}_entity as id_entity, id_category, tag
			FROM anta_{$this->user->username}.{$this->prefix}_entities en
				LEFT OUTER JOIN
			(
			 SELECT et.id_tag, et.id_{$this->prefix}_entity, content as tag, id_category
			 FROM anta_{$this->user->username}.tags t, anta_{$this->user->username}.`{$this->prefix}_entities_tags` et 
			 WHERE t.id_tag = et.id_tag
			) as ctags USING( id_{$this->prefix}_entity) WHERE en.`ignore` = 0 ORDER BY id_entity
		");
		
		// echo file_get_contents( $outputFile );
		$currentId = 0;
		$currentNode = null;
		
		while( $row = $stmt->fetchObject() ){
			if( $row->id_entity != $currentId ){
				
				// print previous node
				if( $currentNode != null ) fwrite( $fp, $currentNode );
			
				// create a new node
				$currentNode = new Anta_Gexf_Node(
					"e".$row->id_entity, 
					$row->content,//Anta_Core::translit( $row->content, -1, false, true ),
					array(),
					array( "color" => $colors[ "navy-blue" ])
				);
				
				$currentId = $row->id_entity;
			}
			
			// fill attribute
			$index = $categories[ $row->id_category ]->content;
			if( empty( $index ) ) continue; // the node hasn't tags
			if( !isset( $currentNode->atts[ $index ] ) ){
				$currentNode->atts[  $index  ] = $row->tag;
			} else {
				$currentNode->atts[ $index ] .= ", ". $row->tag;
			}
			
			
		}
		if( $currentNode != null ) fwrite( $fp, $currentNode );
		
		fwrite( $fp, '</nodes><edges>' );
		
		$this->_setDescription( "4/7. computating link documents-entities..." );
		
		// stampiamo tuttti i link
		$stmt = Anta_Core::mysqli()->query( "
			SELECT id_document, `id_{$this->prefix}_entity` as id_sub_entity, frequency FROM anta_{$this->user->username}.`{$this->prefix}_entities_documents_unignored`"
		);
		
		// output from table
		while( $row = $stmt->fetchObject() ){
			fwrite( $fp, new Anta_Gexf_Edge ( "d".$row->id_document, "e".$row->id_sub_entity, $row->frequency ) );
		}
		
		$this->_setDescription( "5/7. saving file..." );
		
		fwrite( $fp, "</edges></graph></gexf>" );
		fclose( $fp );
		
		$this->_setDescription( "6/7. saving $outputFile..." );
		 
		$this->_setUrl( basename( $outputFile ) );
		
		$this->_setDescription( "done, file saved." );
		// $this->_splitGexf( $outputFile );
		
		$this->_setError( "" );
	}
	
	protected function _splitGexf( $filepath ){
		$zip = new Ui_Zip( $filepath.".zip" );
		$this->_setDescription( "7/7. splitting bipartite gexf into two separated gexf..." );
		$py = new Py_Scriptify( "splitGexf.py ".$filepath, true, false );
		echo $py->command;
		
		$response = str_replace( "'","\"",$py->getResult() );
		echo "\n\n°".$response."°\n\n";
		$response = json_decode( $response );
		
		if( empty( $response ) ){
			$this->_setDescription( "bipartite gexf only " );
			$this->_setError( "cannot split bipartited gexf. You can download the bipartite gexf only" );
			return $filepath;
		} else if( $response->status != "ok" ){
			$this->_setDescription( "bipartite gexf only " );
			$this->_setError( $response->error );
			return $filepath;
		}
		
		$zip->add( $filepath );
		
		foreach ($response->output_gexf as $bipartite ){
			$zip->add( $bipartite ); // add bipartite graph to zip files	
		}
		
		$zip->close();
		
		if( !$zip->isValid() ){
			$this->_setDescription( "bipartite gexf only " );
			$this->_setError( "cannot split bipartited gexf, file zip is not valid." );
			return $filepath;
		}
		
		$this->_setDescription( "done, zip contains 3 files." );
		$this->_setUrl( basename( $zip ) );
		$this->_setError( "" );
		return $zip;
	} 
	
	/**
	 * 
	 */
	

}
