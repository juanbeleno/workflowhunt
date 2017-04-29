<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Semantic extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('semantic_model');
    }

	public function annotate()
	{
		$response = $this->semantic_model->annotate();
		
		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function expand()
	{
		$response = $this->semantic_model->expand();
		
		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Semantic.php */
/* Location: ./application/controllers/Semantic.php */