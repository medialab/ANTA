<?php

class Application_Model_Project extends Application_Model_Configurable
{
	public $id;
	public $title;
	
	public $description;
	/** 
	 * anta_ suffix, the database hosting the project
	 */
	public $database;
	
	public $creation_date;

	public static function load( $row ){
		return new Application_Model_Project( array(
				"title"			=> $row->title,
				"description"	=> $row->descriotion,
				"creation_date"	=> $row->creation_date,
				"id"			=> $row->id_project,
				"database"		=> $row->database
		));
	}
}

