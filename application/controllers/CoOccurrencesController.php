<?php
class CoOccurrencesController extends Zend_Controller_Action
{
	protected $_user;
	protected $_document;
	
	/**
	 * Cannonical url: edit document :id_document of user :id_user
	 * /edit/:id_document/user/:id_user
	 */ 
    public function init()
    {
		// check user param
        $idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		// validate ownerships
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		// check that user sists
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		
		// check the docu,ment into user's docs table
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'document' ) );
		$this->_document = Application_Model_DocumentsMapper::getDocument( $this->_user, $idDocument );
			
		if( $this->_document == null ){
			throw( new Zend_Exception( I18n_Json::get( 'documentNotFoundException', 'errors' ) ) );
		}
		
		$this->view->user = $this->_user;
		$this->view->document = $this->_document;
		
    }
    
	/**
	 * Avoir les mots, par nombre d'occurrences, dans les phrases proches de celles qui contiennent un mot source donné. Dans un document donné.
	 */
    public function matchAgainstAction(){
		
		
		$this->view->dock = new Ui_Dock();
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo(
			"co-occurrence-match",
			I18n_Json::get( 'match cooccurrences' )
		));
		
		// deploy the form, basically a search textfield and a button
		$this->view->dock->co_occurrence_match->setCreateForm( new Ui_Forms_MatchAgainst(
			'co-occurreces-form',
			I18n_Json::get( "CoOccurrencesForm" ),
			$_SERVER['REQUEST_URI']
		));
		
		
		
		if( $this->getRequest()->isPost() ){
			
		
			// print_r( $_POST );
			$form = $this->view->dock->co_occurrence_match->getCreateForm();
			$result = Anta_Core::validateForm( $form );	
			
			if( $result !== true ){
				Anta_Core::setError( $result );
				return $this->render( 'list' );
			}
			
			$stemming = $form->use_stemming->getValue();
			$groupByStem = $form->use_stemmed_result->getValue();
			$maxDistance = $form->max_distance->getValue();
			
			// if is true, change the header
			$csvOutput = isset( $_POST [ 'export-fields' ] );
			
			
			
			if( $stemming ){
				$word = Anta_Core::getStem(  $form->query->getValue(), $form->language->getValue()  );
				$source = "stem";
				
			} else {
				$word = $form->query->getValue();
				$source = "word";
				
			}
			
			if( $csvOutput ) {
				Anta_Core::setHttpHeaders(
					"text/plain",
					$this->_user->username."_doc_". $this->_document->cryptoId."_".( $groupByStem? "stem": "word" )."s_matching_".$source."_".$word.".csv", 
					true );
			}
			
			Dnst_Filter::start( array(
					"offset"=>0,
					"limit"=>250,
					"order"=>"term ASC"
			));
			// read, 
			// read stuff from filters
			if( Dnst_Filter::exists() ){
			
				Dnst_Filter::setRequired( array (
					"offset", "limit", "order"
				));
			
				// properties requirend and not required
				Dnst_Filter::setValidProperties( array (
					"offset", "limit", "order"
				));
				
				Dnst_Filter::setValidators( array (
					"order"  => new Dnst_Filter_Validator_Array( array("frequency DESC", "frequency ASC", "term DESC", "term ASC") ),
					"offset" => new Dnst_Filter_Validator_Range( 0, 1000000000 ),
					"limit"  => new Dnst_Filter_Validator_Range( 1, 500 ),
				));
				
			}
			
			// print_r( $_POST );
			
			 
			// exceute the word match query 
			$query = "
				SELECT
					SQL_CALC_FOUND_ROWS ".( $groupByStem? "stem": "word" ).",
					oc.id_sentence, oc.".( $groupByStem? "stem": "word" )." as term,
					count( oc.id_occurrence) as frequency,
					group_concat( distinct oc.word ORDER BY word ASC SEPARATOR ', ') as label
				FROM (
					SELECT 
						se.id_document, se.position, se.id_sentence
					FROM (
						SELECT
							id_sentence, oc1.id_document, position
						FROM
							anta_".$this->_user->username.".`occurrences` oc1 JOIN 
							anta_".$this->_user->username.".`sentences` USING ( id_sentence )
						WHERE
							oc1.id_document = ? AND
							{$source} = ? 
					) as home, anta_".$this->_user->username.".`sentences` se
					WHERE
						home.id_document = se.id_document AND
						ABS( home.position - se.position ) <= ? 
				) as neighborough, anta_".$this->_user->username.".`occurrences` oc
				WHERE
					oc.id_sentence = neighborough.id_sentence
				GROUP BY ".( $groupByStem? "stem": "word" )."
				ORDER BY ".Dnst_Filter::read()->order." ".( 
					$csvOutput?
						"":"LIMIT ".Dnst_Filter::read()->offset.", ".Dnst_Filter::read()->limit
			);
						
			$stmt = Anta_Core::mysqli()->query( $query, array( $this->_document->id, $word, $maxDistance ) );
			

			$words = array();
			
			$maxFrequency = 0;
			
			while( $row = $stmt->fetchObject() ){
				$words[] =  $row;
				$maxFrequency = max( $maxFrequency, $row->frequency );
			}
			
			// exit with csv fields
			if( $csvOutput ) {
				return $this->exportMatchAgainstResults( $words );
			}
			
			
			foreach( array_keys( $words ) as $k ){
				$this->view->dock->co_occurrence_match->addItem( new Ui_Crafts_Items_MatchAgainst( $words[ $k ], $maxFrequency ) );
			}
			
			
			$stmt = Anta_Core::mysqli()->query( "SELECT FOUND_ROWS() as totalRows;" );
			$row = $stmt->fetchObject();
			
			$this->view->dock->co_occurrence_match->setHeader(
				new Ui_Crafts_Headers_Lens( array(
					"totalItems"  => $row->totalRows,
					"loadedItems" => count( $words ),
					"label" => "co-occurrence of term <strong>".$word."</strong> in document <em>".$this->_document->title."</em>
						<br/><blockquote>use stemming: ".($stemming?'true':'false').", group result by stem: ".($groupByStem?'true':'false')."</blockquote>
					"
			)));
		}	
	}
	
	private function exportMatchAgainstResults( array $words ){
		// no render
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$header = new Anta_Csv_Header( array( 'term', 'label', 'frequency' ));
		// transform such table into csv table
		// fields: term, label, id_sentence, frequency, 
		$table = new Anta_Csv_Table(
			$header
		);
		foreach( array_keys( $words ) as $k )
			$table->addRow( new Anta_Csv_Row( $header, array(
				'frequency'	=> new Anta_Csv_Cell( $words[ $k ]->frequency ),
				'term' 		=> new Anta_Csv_Cell( $words[ $k ]->term ),
				'label'		=> new Anta_Csv_Cell( $words[ $k ]->label ),
			)));
		//	print_r( $words );
		echo $table;
	}
	
	public function listAction(){
		
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo(
			"co-occurrences",
			I18n_Json::get( 'list of cooccurrences' ).': <a href="'.anta_Core::getBase().'/edit/props/user/'.$this->_user->cryptoId.'/document/'.$this->_document->cryptoId.'">'.$this->_document->title.'</a>'
		));
		
		// deploy the form
		$this->view->dock->co_occurrences->setCreateForm( new Application_Model_Forms_CoOccurrenceForm(
			'co-occurreces-form',
			I18n_Json::get( "CoOccurrencesForm" ),
			$_SERVER['REQUEST_URI']
		));
		
		
		if( $this->getRequest()->isPost() ){
			// if is true, change the header
			$csvOutput = isset( $_POST [ 'export-fields' ] );
			$useStemming = isset( $_POST[ "use-stemming" ]);
			$termType = $useStemming? "stem": "word";
			
			if( $csvOutput ) {
				Anta_Core::setHttpHeaders(
					"text/plain",
					$this->_user->username."_doc_". $this->_document->cryptoId."_".$termType."s_cooccurrences.csv", 
					true );
			}
			
			$form = $this->view->dock->co_occurrences->getCreateForm();
			
			// use validators
			$result = Anta_Core::validateForm( $form, $this->getRequest()->getParams() );
			
			// print_r( $_POST );
			
			// wordlists loading
			$fields_a = new Anta_Utils_WordList( $form->field_a->getValue() ); 
			$fields_b = new Anta_Utils_WordList( $form->field_b->getValue() ); 
			
			if( $useStemming ){
				$fields_a -> applyStem( $this->_document->language );
				$fields_b -> applyStem( $this->_document->language );
			}
			
			// in the header, let's put some info
			$this->view->dock->co_occurrences->setHeader( json_encode( $fields_a ) . json_encode( $fields_b ) );
			
			
			// THE LOOP
			foreach( $fields_a->words as $termA ){
				
				foreach( $fields_b->words  as $termB ){
					
					
					$query = "
						SELECT 
							word_1_positions.id_sentence as id1,
							word_1_positions.content as c1,
							word_1_positions.position as p1,
							word_2_positions.id_sentence as id2,
							word_2_positions.content as c2,
							word_2_positions.position as p2,
							(word_2_positions.position - word_1_positions.position) AS diff 
							
							FROM (
								SELECT occurrences.*, sentences.position, sentences.content FROM anta_".$this->_user->username.".occurrences JOIN anta_".$this->_user->username.".sentences USING (id_sentence)
								WHERE occurrences.id_document = ? AND {$termType} = ?
							) AS word_1_positions,
							
							(
								SELECT occurrences.*, sentences.position, sentences.content FROM anta_".$this->_user->username.".occurrences JOIN anta_".$this->_user->username.".sentences USING (id_sentence)
								WHERE occurrences.id_document = ? AND {$termType} = ?
							) AS word_2_positions
							
							WHERE ABS( word_1_positions.position - word_2_positions.position ) <= ? 
							GROUP BY id1, id2
						
						";
						
					
					$stmt = Anta_Core::mysqli()->query( $query, array(
						$this->_document->id, $termA, $this->_document->id, $termB, $form->max_distance->getValue()
					));
					
					// if is true, change the header
					if( $csvOutput ){
						// create table
						// no render
						$this->_helper->layout->disableLayout();
						$this->_helper->viewRenderer->setNoRender(true);
						
						$header = new Anta_Csv_Header( array( 'distance', $termType.'_A', $termType.'_B', "context" ));
						// transform such table into csv table
						// fields: term, label, id_sentence, frequency, 
						$table = new Anta_Csv_Table(
							$header
						);
						
						while( $row = $stmt->fetchObject() ){
							if( $row->diff == 0 ){
								$context = $row->c1;
							} else if( $row->diff < 0 ){
								$context = $row->c2 . ". ".$row->c1;
							} else {
								$context = $row->c1 . ". ".$row->c2;
							}
							$table->addRow( new Anta_Csv_Row( $header, array(
								'distance'		=> new Anta_Csv_Cell( $row->diff ),
								$termType.'_A'	=> new Anta_Csv_Cell( $termA ),
								$termType.'_B'	=> new Anta_Csv_Cell( $termB ),
								"context"		=> new Anta_Csv_Cell( $context )
							)));
						}
						
						
						
						/**
						foreach( array_keys( $words ) as $k )
							$table->addRow( new Anta_Csv_Row( $header, array(
								'frequency'	=> new Anta_Csv_Cell( $words[ $k ]->frequency ),
								'term' 		=> new Anta_Csv_Cell( $words[ $k ]->term ),
								'label'		=> new Anta_Csv_Cell( $words[ $k ]->label ),
							)));
						//	print_r( $words );
						
						*/
						echo $table;
						return;
					}
			
					
					
					while( $row = $stmt->fetchObject() ){
					
						$this->view->dock->co_occurrences->addItem( new Ui_Crafts_Items_CoOccurrence( $row, $termA, $termB ) );
					}
				}
			
			}
			
			// print_r( $fields_a );
			
			
			

			
			echo $result;
		}
		
	}
	
	
	
}
?>
