<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ontology extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('ontology_model');
    }

	public function download_terms()
	{
		$response = $this->ontology_model->download_terms();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function update_complete_short_form(){
		// This is hacky. Don't do this kids. Everytime you do
		// something like this, you kill a baby panda
		$this->db->select('ontology_concept.id AS id, ontology_concept.short_form AS short_form, ontology.prefix AS prefix');
		$this->db->from('ontology_concept');
		$this->db->join('ontology', 'ontology_concept.id_ontology = ontology.id');
		$query_concepts = $this->db->get();

		foreach ($query_concepts->result() as $concept) {
			$concept_identifier =  $concept->prefix.':'.$concept->short_form;
			$this->db->set('complete_short_form', $concept_identifier);
			$this->db->where('id', $concept->id);
			$this->db->update('ontology_concept');
		}
	}

}

/* End of file Ontology.php */
/* Location: ./application/controllers/Ontology.php */