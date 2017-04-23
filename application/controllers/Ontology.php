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

}

/* End of file Ontology.php */
/* Location: ./application/controllers/Ontology.php */