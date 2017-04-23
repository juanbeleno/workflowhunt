<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Elasticsearch extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('elasticsearch_model');
    }

	public function index_metadata()
	{
		$response = $this->elasticsearch_model->index_metadata();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function restart_index_metadata()
	{
		$response = $this->elasticsearch_model->restart_index_metadata();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function search_in_metadata()
	{
		$query = @$this->input->get('query', TRUE);
		$offset = $this->input->get('offset', TRUE);

		$response = $this->elasticsearch_model->search_in_metadata($query, $offset);

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Elasticsearch.php */
/* Location: ./application/controllers/Elasticsearch.php */