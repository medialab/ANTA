<?php
/**
 * @package Ui_Headers
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Headers_ListHeader {
	
	protected $_properties;
	protected $_pageInfo;
	
	/* 
		array(
			"order"  => "id_document",
			"dir"    => "ASC",
			"offset" => 0,
			"limit"  => 2,
			"search" => "searchquery"
		);
	 */
	public function __construct( array $properties=array(), array $pageInfo=array() ){
		
		$this->_pageInfo = array(
			"amount" => 2		
		);
		
		$this->_properties = $properties; 
	}
	
	public function getEntry( $orderBy, $orderDir ){
		return '<a href="?'.$this->getQueryString( $orderBy, $orderDir ).'">'.I18n_Json::get( $orderBy.$orderDir ).'</a>';
	}
	
	public function getQueryString( $orderBy, $orderDir ){
		$q = '';
		$this->_properties[ 'order' ] = $orderBy;
		$this->_properties[ 'dir' ]   = $orderDir;
		foreach( $this->_properties as $k => $v ){
			$q .= $k."=".$v."&";
		}
		return $q ;
	}
	
 }