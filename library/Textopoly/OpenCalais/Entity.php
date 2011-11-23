<?php
/**
 * @package Textopoly_OpenCalais
 * @author Daniele Guido
 */
 
/**
 * Tranform and Describe the content extracted by OpenCalais.
 * It should return an entity-compatible object, where the "text" refers to
 * entities->name or topics->categoryName. So we can have a flat array of entities where
 * the types and relevances are comparable.
 * 
 * e.g the entity found by O.C:
 * [http://d.opencalais.com/dochash-1/d71fb3f2-0765-3227-9472-007bd7aa7a1d/cat/1] => stdClass Object
 *      (
 *          [_typeGroup] => topics
 *          [category] => http://d.opencalais.com/cat/Calais/Education
 *          [classifierName] => Calais
 *          [categoryName] => Education
 *          [score] => 1
 *      )
 * is now comparable at this one:
 * 
 *  [http://d.opencalais.com/genericHasher-1/3a0f3359-b89a-3959-a958-a9141e8c1f9d] => stdClass Object
 *      (
 *          [_typeGroup] => entities
 *          [_type] => Product
 *          [name] => IPhone
 *          [producttype] => Electronics
 *          [_typeReference] => http://s.opencalais.com/1/type/em/e/Product
 *          [instances] => Array
 *              (
 *                  [0] => stdClass Object
 *                      (
 *                          [detection] => [in Italy more efficiently with Gizmo and ]IPhone[: The map will enable urban education]
 *                          [prefix] => in Italy more efficiently with Gizmo and 
 *                          [exact] => IPhone
 *                          [suffix] => : The map will enable urban education
 *                          [offset] => 95
 *                          [length] => 6
 *                      )
 *
 *              )
 *
 *          [relevance] => 0.714
 *      )
 */
class Textopoly_OpenCalais_Entity{
	
	public $type;
	
	public $text;
	
	public $relevance;
	
	/**
	 * Class Constructor
	 * todo	- the exatct entity extraction for each opencalais entity type.
	 * @param entityObject - the object returned by json openalchemy for each entity
	 */
	public function __construct( $entityObject ){
		// $this->raw = json_encode( $entityObject );
		
		$this->type = $entityObject->_type != null? $entityObject->_type: $entityObject->_typeGroup;
		$this->text = $entityObject->name != null? $entityObject->name: $entityObject->categoryName;
		$this->relevance = $entityObject->score != null? $entityObject->score: ( $entityObject->relevance != null? $entityObject->relevance: null );
		if( $entityObject->importance != null ) $this->relevance = $entityObject->importance / 2;
		
		switch( $this->type ){
			case "DiplomaticRelations":
				$this->text = $entityObject->diplomaticaction.( 
					Textopoly_Plugin::isUrl( $entityObject->diplomaticentity1 )?'': ', '.$entityObject->diplomaticentity1
				).(
					Textopoly_Plugin::isUrl( $entityObject->diplomaticentity2 )?'': ', '.$entityObject->diplomaticentity2
				);
				
				$this->relevance = 0.1; // default relevance
			break;
				
		}
		
		
	}
	
	/**
	 * Some entities extrqcted, like DiplomaticRelationship *may* not have any relevance or text.
	 */ 
	public function isValid(){
		
			return $this->text != null && $this->type != null && $this->relevance != null;
	}
	
	
}
?>
