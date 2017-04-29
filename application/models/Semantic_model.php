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
 * WorkflowHunt Semantic Annotation Model
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */

class Semantic_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();

        $this->load->helper('semantic');
    }

    // --------------------------------------------------------------------

    /**
	 * Extract semantic annotations from workflow metadata
	 *
	 * It collects the ontology terms in a dictionary and use them to annotes
	 * the workflow metadata. Workflow metadata fields can have strings or arrays
	 *
	 * @return	array
	 */
    public function annotate()
    {
    	// Getting the dictionary of ontology terms by their length in 
    	// descending order
    	$dictionary = array();
    	$this->db->select('id_ontology_concept, string');
    	$this->db->where('string !=', "");
    	$this->db->order_by('LENGTH(string)', 'desc');
    	$dictionary_query = $this->db->get('ontology_term');

    	foreach ($dictionary_query->result() as $term) {
    		$dictionary[$term->string] = $term->id_ontology_concept;
    	}


    	// Handling the workflow metadata
    	$this->db->select('id, title, description');
    	$query_wf = $this->db->get('workflow');

    	foreach ($query_wf->result() as $workflow) {

    		$tags = array();
    		$title = $workflow->title;
    		$description = $workflow->description;
    		
    		// Looking for tag associated to the workflow
    		$this->db->select('tag.id AS id, tag.name AS name');
    		$this->db->from('tag_wf');
    		$this->db->where('tag_wf.id_workflow', $workflow->id);
    		$this->db->join('tag', 'tag_wf.id_tag = tag.id');
    		$this->db->join('workflow', 'workflow.id = tag_wf.id_workflow');
    		$query_tags = $this->db->get();

    		foreach ($query_tags->result() as $tag) {
    			$tags[$tag->id] = $tag->name;
    		}

    		$metadata = array(
    			'title' => $title,
    			'description' => $description,
    			'tags' => $tags
    		);

    		// Iterate over the workflow metadata to collect the semantic annotations
    		foreach ($metadata as $key => $value) {
    			if(is_string($value)) {
    				$semantic_annotations = get_semantic_annotations_from_text($dictionary, $value);

    				// Arrange the semantic annotations in a tidy format to store in SQL
		    		$tidy_sem_annotations = array();

		    		foreach ($semantic_annotations as $concept) {
		    			$tidy_sem_annotations[] = array(
		    				'id_workflow' => $workflow->id,
		    				'id_ontology_concept' => $concept,
		    				'annotation_type' => 'Direct',
		    				'distance' => 0,
		    				'metadata_type' => $key,
		    				'created_at' => date("Y-m-d H:i:s")
		    			);
		    		}

		    		// Save the semantic annotations in the database
		    		$sem_annotations_length = count($tidy_sem_annotations);
		    		if($sem_annotations_length > 1) {
		    			$this->db->insert_batch('semantic_annotation', $tidy_sem_annotations);
		    		} elseif ($sem_annotations_length == 1) {
		    			$this->db->insert('semantic_annotation', $tidy_sem_annotations[0]);
		    		}

    			} elseif (is_array($value)) {
    				foreach ($value as $id => $tag) {
    					$semantic_annotations = get_semantic_annotations_from_text($dictionary, $tag);

    					// TODO: This is inefficient for tags because tags have
    					// short strings
    					// Arrange the semantic annotations in a tidy format to store in SQL
			    		$tidy_sem_annotations = array();

			    		foreach ($semantic_annotations as $concept) {
			    			$tidy_sem_annotations[] = array(
			    				'id_workflow' => $workflow->id,
			    				'id_ontology_concept' => $concept,
			    				'annotation_type' => 'Direct',
		    					'distance' => 0,
			    				'id_metadata' => $id,
			    				'metadata_type' => $key,
			    				'created_at' => date("Y-m-d H:i:s")
			    			);
			    		}

						// Save the semantic annotations in the database
			    		$sem_annotations_length = count($tidy_sem_annotations);
			    		if($sem_annotations_length > 1) {
			    			$this->db->insert_batch('semantic_annotation', $tidy_sem_annotations);
			    		} elseif ($sem_annotations_length == 1) {
			    			$this->db->insert('semantic_annotation', $tidy_sem_annotations[0]);
			    		}
       				}
    			}
    		}
    	}
    	
    	return array('status' => 'OK');
    }


    // --------------------------------------------------------------------

    /**
	 * Expand the initial set of semantic annotations
	 *
	 * It uses generalization to get expanded semantic annotations from the 
	 + ontology concept parents.
	 *
	 * @return	array
	 */
    public function expand()
    {
    	$this->db->select('iri_parent, id_workflow, id_metadata, metadata_type');
    	$this->db->from('semantic_annotation');
    	$this->db->join('ontology_concept', 'ontology_concept.id = semantic_annotation.id_ontology_concept');
    	$query_annotations = $this->db->get();

    	foreach ($query_annotations->result() as $sem_annotation) {
    		if($sem_annotation->iri_parent != null) {
    			$this->db->select('id');
	    		$this->db->where('iri', $sem_annotation->iri_parent);
	    		$parent_query = $this->db->get('ontology_concept', 1, 0);

	    		$id_parent = null;
	    		if($parent_query->num_rows() > 0) {
	    			$id_parent = $parent_query->row()->id;
	    		} else {
	    			continue;
	    		}

	    		$data = array(
		    				'id_workflow' => $sem_annotation->id_workflow,
		    				'id_ontology_concept' => $id_parent,
		    				'annotation_type' => 'Generalization',
	    					'distance' => 1,
		    				'id_metadata' => $sem_annotation->id_metadata,
		    				'metadata_type' => $sem_annotation->metadata_type,
		    				'created_at' => date("Y-m-d H:i:s")
		    			);

	    		$this->db->insert('semantic_annotation', $data);
    		}
    	}

    	return array('status' => 'OK');
    }

}

/* End of file Semantic_model.php */
/* Location: ./application/models/Semantic_model.php */