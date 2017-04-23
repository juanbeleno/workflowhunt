<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wordnet extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('wordnet_model');
    }

	public function store_synonyms()
	{
		$response = $this->wordnet_model->store_synonyms();
		
		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Wordnet.php */
/* Location: ./application/controllers/Wordnet.php */