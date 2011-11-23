<?php
/**
 * @package
 */

/**
 * anta.terms table api. This table contains all terms found, so it's a huge multilanguage dictionary.
 * It's better that having one table per corpus.
 * With installer, static method install().
 */
class Application_Model_TermsMapper{

	/**
	 * Add an entry into the users's terms table: a word, its stem and the language used for stemming.
	 * Note: the PECL "stem" extension has been used to extract stems.
	 * The function returns the id_term inserted id ( an integer )
	 *
	 * @param antaUser	- the Application_Model_User instance of an user (db user)
	 * @param term		- the word
	 * @param stem		- the stem of the word
	 * @param language	- the language used to stem the word ( document language )
	 * @return the id_term inserted into user's terms
	 */
	public static function addTerm( Application_Model_User $antaUser, $term, $stem, $language ){
		
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO  anta_".$antaUser->username.".`terms` (
				`term`, `stem`, `language`
			) VALUES (
				LOWER( ? ), ?, ?
			)",array( $term, $stem, $language )
		);
		
		$idTerm = Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".`terms`" );
		
		if ( $idTerm != 0 ) return $idTerm;
		
		return self::getIdTerm( $antaUser, $term, $language );
		
	}

	/**
	 * 
	 */
	public static function getIdTerm(  Application_Model_User $antaUser, $term, $language ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT term
				FROM anta_".$antaUser->username.".`terms`
			WHERE term = LOWER( ? ) and language = ?", array(
				$term, $language
		));
		
		$row = $stmt->fetchObject();
		
		if( $row == null ) return 0;
		
		return $row->term;
		
	}
	
	public static function addCoOccurrence( Application_Model_User $antaUser, $idDocument, $idSentence, $termA, $stemA, $termB, $stemB, $distance ){
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO   anta_".$antaUser->username.".`co_occurrences` (
				`id_document`, `id_sentence`,
				`stem_A`, `stem_B`, `word_A`, `word_B`, `distance`
			) VALUES (
				?, ?,
				?, ?,
				LOWER( ? ), 
				LOWER( ? ),
				?
			)", array( $idDocument, $idSentence, $stemA, $stemB, $termA, $termB, $distance )
		);
		return $stmt->rowCount();
	}
	
	public static function removeCoOccurrences( Application_Model_User $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query( "
			DELETE FROM anta_".$antaUser->username.".`co_occurrences` WHERE id_document = ?
		", array( $idDocument ) );
		
		return $stmt->rowCount();
	}
	
	public static function addOccurrence( Application_Model_User $antaUser, $idDocument, $idSentence, $term, $stem ){
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO   anta_".$antaUser->username.".`occurrences` (
				`id_document`,
				`id_sentence`,
				`word`, `stem`
			) VALUES (
				?, ?, LOWER( ? ), LOWER( ? )
			)", array( $idDocument, $idSentence, $term, $stem )
		);
		return $stmt->rowCount();
	}
	
	public static function removeOccurrences( Application_Model_User $antaUser, $idSentence ){
		$stmt = Anta_Core::mysqli()->query( "
			DELETE FROM anta_".$antaUser->username.".`occurrences` WHERE id_sentence = ?
		", array( $idSentence ) );
		
		return $stmt->rowCount();
	}
		
	
}
