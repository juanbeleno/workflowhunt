<?php
/**
 * WorkflowHunt
 *
 * A semantic search engine for scientific workflow repositories
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2016 - 2017, Juan Sebastián Beleño Díaz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	WorkflowHunt
 * @author	Juan Sebastián Beleño Díaz
 * @copyright	Copyright (c) 2016 - 2017, Juan Sebastián Beleño Díaz
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://github.com/jbeleno
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WorkflowHunt Ontology Model
 *
 * GAMBIARRA ALERT: Temporarily, I'm using ontology names manually, so
 * I have a table called 'ontology' with the following fields: id, name, 
 * prefix, and iri. Currently, I'm working with EDAM and CHEMINF.
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Ontology_model extends CI_Model {

	/**
	 * Ontologies URL from OLS (Ontology Lookup Service) API
	 *
	 * @var	string
	 */
	private $ONTOLOGIES_URL = "http://www.ebi.ac.uk/ols/api/ontologies/";

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
    }

    // --------------------------------------------------------------------

    /**
	 * Download and Save Ontology Terms in the Database
	 *
	 * Each page that contains ontology terms is scrapped in JSON format and
	 * the ontology terms with synonyms are stored in a relational database
	 * NOTE: Maybe is better to store this data in a JSON database like MongoDB.
	 *		 Nevertheless, this will be done later (probably).
	 *
	 * @return	array
	 */
    public function download_terms()
    {
    	$this->db->select('id, name, prefix');
    	$query_ont = $this->db->get('ontology');

    	foreach ($query_ont->result() as $ontology) 
    	{
    		$page = 0;
    		$size = 20;
    		$total_pages = 1;

    		// Iterate over all the pages that have ontology concept
    		do
    		{
	    		$ONTOLOGY_URL = $this->ONTOLOGIES_URL.$ontology->name.'/terms?page='.$page.'&size='.$size;

	    		// Request the content in JSON format
	    		$context  = stream_context_create(
	    						array(
	    							'http' => array(
	    										'header' => 'Accept: application/json'
	    										)
	    							)
	    						);

				$raw_content = file_get_contents($ONTOLOGY_URL, false, $context);
				$json_content = json_decode($raw_content);

				$total_pages = $json_content->page->totalPages;
				$concepts = $json_content->_embedded->terms;

				// Iterate over each ontology concept
				for ($i=0; $i < count($concepts); $i++) 
				{ 
					$concept = $concepts[$i];

					if(!is_null($concept) && !$concept->{'is_obsolete'})
					{
						// Getting information from ontology concept parents
						$links = $concept->{'_links'};
						$iri_parent = null;
						if(isset($links->parents)) {
							$raw_parent_content = file_get_contents($links->parents->href, false, $context);
							$json_parent_content = json_decode($raw_parent_content);

							$iri_parent = $json_parent_content->_embedded->terms[0]->iri;
						}

						$data_concept = array(
							'id_ontology' => $ontology->id,
							'iri_parent' => $iri_parent,
							'label' => $concept->label,
							'description' => $concept->description[0],
							'iri' => $concept->iri,
							'short_form' => $concept->short_form,
							'complete_short_form' => $ontology->prefix.':'.$concept->short_form,
							'obo_id' => $concept->obo_id,
							'created_at' => date("Y-m-d H:i:s")
						);

						// Storing the ontology concept in the database
						$this->db->insert('ontology_concept', $data_concept);

						// Getting the ontology concept id in the database to save ontology terms
						$id_ontology_concept = $this->db->insert_id();

						// Save label as ontology term
						$data_label = array(
							'id_ontology_concept' => $id_ontology_concept,
							'string' => $concept->label,
							'type' => 'Label',
							'source' => 'Ontology Lookup Service',
							'created_at' => date("Y-m-d H:i:s")
						);

						$this->db->insert('ontology_term', $data_label);


						if(!is_null($concept->synonyms))
						{
							foreach ($concept->synonyms as $synonym) {
								$data_synonym = array(
									'id_ontology_concept' => $id_ontology_concept,
									'string' => $synonym,
									'type' => 'Synonym',
									'source' => 'Ontology Lookup Service',
									'created_at' => date("Y-m-d H:i:s")
								);

								// Save synonyms as ontology term
								$this->db->insert('ontology_term', $data_synonym);
							}
						}
					}	
				}

				$page++;
			} while( $page < $total_pages );

    	}

    	return array( 'status' => 'OK' );
    }

}

/* End of file Ontology_model.php */
/* Location: ./application/models/Ontology_model.php */