<?php

class GexfController extends Zend_Controller_Action
{

    public function init()
    {
		if ( $this->_request->getParam( 'user' ) == null ){
			throw ( new Zend_Exception("check your request: no 'user' param was found") );
		}
        /* Initialize action controller here */
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		$this->user = Application_Model_UsersMapper::getUser( $idUser );
		
		if( $this->user == null ){
			throw ( new Zend_Exception( "'".$this->_request->getParam( 'user' )."' user not found" ) );
		}
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if( isset($_GET['debug'])){
			Anta_Core::setHttpHeaders( "text/plain");
		} else {
			Anta_Core::setHttpHeaders( "text/xml", $this->user->username.".gexf", true);
		}
		
    }

    public function documentsAction(){
		$this->_gexfHeaders();
		
		echo '<nodes>';
		// get documents nodes...
		$documents = Application_Model_DocumentsMapper::getDocuments(
			$this->user, "all", 0, 0 );
		
		foreach( array_keys( $documents ) as $k ){
			echo new Anta_Gexf_Node(
				"nd".$documents[ $k ]->id,
				$documents[ $k ]->title, 
				array(
					"type"=>"document"
				),
				array(
					"color" => 'r="242" g="125" b="187"'
				)
				
			);
			
		}
		echo '</nodes>';
		
		unset( $documents );
		
		$links = array();
		
		// computate link betweend documents
		$query = "
			SELECT
				e2.id_entity as e, e2.`relevance`, e1.`id_document` as d1,
				e2.`id_document` as d2
			FROM anta_".$this->user->username.".`entities_occurrences` e1, anta_".$this->user->username.".`entities_occurrences` e2
			WHERE
				e1.`id_entity` = e2.`id_entity` AND
				e1.relevance > .7 AND
				e1.`id_document` != e2.`id_document`
			ORDER BY d1
		";
		
		$stmt = Anta_Core::mysqli()->query( $query );
		
		while( $row = $stmt->fetchObject() ){
			
			if ( ! isset( $links[ $row->d1 ] ) ){
				$links[ $row->d1 ] = array();
			}
			
			if ( ! isset( $links[ $row->d1 ][ $row->d2 ] ) ){
				$links[ $row->d1 ][ $row->d2 ] = array();
			}
			
			if ( ! isset( $links[ $row->d1 ][ $row->d2 ][ $row->e ] ) ){
				$links[ $row->d1 ][ $row->d2 ][ $row->e ] = 0;
			}
			
			$links[ $row->d1 ][ $row->d2 ][ $row->e ] ++;
		}
		// print_r( $links );
		
		foreach( array_keys( $links ) as $d1 ){
			foreach( array_keys( $links[ $d1 ] ) as $d2 ){
				echo new Anta_Gexf_Edge(
					"nd".$d1,
					"nd".$d2,
					array_sum( $links[ $d1 ][ $d2 ] ) / count( $links[ $d1 ][ $d2 ] )
				);
			}
		}
		
		$this->_gexfFooters();
	}
	
	public function documentEntitiesAction(){
		$this->_gexfHeaders();
		echo '<nodes>';
		
		$query = " SELECT id_entity as id, type, content	FROM  anta_".$this->user->username.".`entities` ";
		
		
		$stmt = Anta_Core::mysqli()->query( $query );
		
		while( $row = $stmt->fetchObject() ){
			echo new Anta_Gexf_Node(
				"ne".$row->id,
				$row->content, 
				array(
					"type"=>$row->type
				),
				array(
					"color" => 'r="200" g="0" b="187"'
				)
				
			);
		}
		echo '</nodes>';
		
		// computate relationships
		$links = array();
		
		// computate link betweend documents
		$query = "
		    SELECT 
			  eo1.id_entity as e1,
			  eo2.id_entity as e2,
			  eo2.id_document,
			  count( eo2.id_document )as gipsy
			FROM
			  `entities_occurrences` eo1,
			  `entities_occurrences` eo2
			WHERE 
			  eo1.id_entity != eo2.id_entity AND
			  eo1.id_document = eo2.id_document
			GROUP BY 
			  eo2.id_entity, eo2.id_document
			LIMIT 1000
			
		    SELECT 
			  eo1.id_entity as e1, eo2.id_entity as e2,
			  eo1.id_document, eo2.id_document
			FROM anta_mathieu.`entities_occurrences` eo1, anta_mathieu.`entities_occurrences` eo2
			WHERE 
			  eo1.id_entity != eo2.id_entity AND
			  eo1.id_document = eo2.id_document
			LIMIT 1000
			
		";
		
		$stmt = Anta_Core::mysqli()->query( $query );
		
		while( $row = $stmt->fetchObject() ){
			
			if ( ! isset( $links[ $row->e1 ] ) ){
				$links[ $row->e1 ] = array();
			}
			
			if ( ! isset( $links[ $row->e1 ][ $row->e2 ] ) ){
				$links[ $row->e1 ][ $row->e2 ] = array();
			}
			
			if ( ! isset( $links[ $row->e1 ][ $row->e2 ][ $row->id_document ] ) ){
				$links[ $row->e1 ][ $row->e2 ][ $row->id_document ] = 0;
			}
			$links[ $row->e1 ][ $row->e2 ][ $row->id_document ]++;
		}
		
		foreach( array_keys( $links ) as $e1 ){
			foreach( array_keys( $links[ $e1 ] ) as $e2 ){
				echo new Anta_Gexf_Edge(
					"ne".$e1,
					"ne".$e2,
					array_sum( $links[ $e1 ][ $e2 ] ) / count( $links[ $e1 ][ $e2 ] )
				);
			}
		}
		
		$this->_gexfFooters();
	}
	
	public $colors = array(
		"deep-red" => 'r="241" g="45" b="21"',
		"navy-blue" => 'r="59" g="68" b="77"'
	);
	
	public function entitiesAction(){

		$graph_type = isset( $_GET['static'] )? 'static': 'dynamic';

		// print headers
		$this->_gexfHeaders( $graph_type );
		
		echo '<nodes>';
		
		// get documents nodes...
		$documents = Application_Model_DocumentsMapper::getDocuments(
			$this->user, "all", 0, 0 );
		
		
		
		foreach( array_keys( $documents ) as $k ){
			// load tags
			$tags = Application_Model_DocumentsMapper::getTags( 
					$this->user, $documents[ $k ]->id
			);
			
			$atts = array( "type"=>"document","date"=>$documents[ $k ]->date );
		
			foreach( $tags as $tag ){
				if( isset( $atts[ $tag->category ] ) ){
					$atts[ $tag->category ] .= ", ".$tag->content;
					continue;
				}
				$atts[ $tag->category ] = $tag->content;
			}
			
			echo new Anta_Gexf_Node(
				"d".$documents[ $k ]->id,
				$documents[ $k ]->title, 
				$atts,
				array(
					"color" => $this->colors[ "deep-red" ]
				)
				
			);
			
		}
		
		// clean and flush
		unset( $documents );
		flush();
		ob_flush();
		
		
		
		
		
		// params
		$minRelevance = $this->_request->getParam( "min-relevance" );
		$maxRelevance = $this->_request->getParam( "max-relevance" );
		$minFrequency = $this->_request->getParam( "min-frequency" );
		$maxFrequency = $this->_request->getParam( "max-frequency" );
		
		$minRelevance = is_numeric( $minRelevance )? $minRelevance/100: 0;
		$maxRelevance = is_numeric( $maxRelevance )? $maxRelevance/100: 0;
		$minFrequency = is_numeric( $minFrequency )? $minFrequency: 0;
		$maxFrequency = is_numeric( $maxFrequency )? $maxFrequency: 0;
		
		$linksStmt = Application_Model_SubEntitiesDocumentsMapper::getLinks( $this->user, array(), $minRelevance, $maxRelevance, $minFrequency, $maxFrequency, true );
		
		$edges = array();
		
		$uniqueEntities = array();
		
		while( $row = $linksStmt->fetchObject() ){
			
			// save the edge
			$edges[] = new Anta_Gexf_Edge(
				"d".$row->id_document,
				"e".$row->prefix. "_". $row->id,
				$row->relevance
			);
			
			// unique entities only please
			if( isset( $uniqueEntities[ $row->prefix. "_". $row->id ] ) ) continue; 
			$uniqueEntities[ $row->prefix. "_". $row->id ] = true;
			
			$atts = array( "type" => "entity" );
			
			
			/* load tags...
			$tagsStmt = Anta_Core::mysqli2()->query( "
			SELECT
			  t.id_tag, t.content as content, c.content as category, t.parent_id_tag as pid
			FROM anta_{$this->user->username}.`tags` t INNER JOIN anta_{$this->user->username}.{$row->prefix}_entities_tags USING (id_tag),
			anta_{$this->user->username}.categories c  WHERE id_{$row->prefix}_entity = ? AND c.id_category = t.id_category", array( $row->id )
			);
			
			
			$atts = array( "type" => "entity" );
			
			
			while(	$trow = $tagsStmt->fetchObject() ){
				if( isset( $atts[ $trow->category ] ) ){
					$atts[ $trow->category ] .= ", ".$trow->content;
					continue;
				}
				$atts[ $trow->category ] = $trow->content;
			}
			
			
			*/
			
			// echo entities
			echo new Anta_Gexf_Node(
				"e".$row->prefix. "_". $row->id,
				$row->content, 
				$atts,
				array(
					"color" => $this->colors[ "navy-blue" ]
				)
				
			);
			
			
			
		}
		
		echo '</nodes>';
		// load edges
		echo '<edges>';
		
		// implode edges
		foreach( array_keys( $edges ) as $k ){
			echo $edges[ $k ];
		}
		
		echo '</edges>';
		
		$this->_gexfFooters();
		
	}
	
	protected function _gexfHeaders( $mode='static' ){
		echo 
		'<?xml version="1.0" encoding="UTF-8"?>
			<gexf xmlns="http://www.gexf.net/1.1draft" version="1.1" xmlns:viz="http://www.gexf.net/1.1draft/viz" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.gexf.net/1.1draft http://www.gexf.net/1.1draft/gexf.xsd">
			<meta lastmodifieddate="2011-01-27">
				<creator>Anta 0.7</creator>
				<description>Graph Document - Entities </description>
			</meta>
			<graph defaultedgetype="directed" mode="'.$mode.'">
			<attributes class="node" mode="static">';
		$categories = Application_Model_CategoriesMapper::getAll( $this->user );
		// load possible attributes
		foreach ( $categories as $category ){
			echo '<attribute id="attr_'. $category->id. '" title="'. $category->content. '" type="string"/>';
		}
		
		unset( $categories );
		
		echo
			'</attributes>
			<attributes class="edge" mode="static">
				<attribute id="width" title="width" type="double"/>
			</attributes>';
		
	}
	
	
	
	protected function _gexfFooters(){
		echo '</graph></gexf>';
	}
}

