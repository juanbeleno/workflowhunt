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

	public function index_semantics()
	{
		$response = $this->elasticsearch_model->index_semantics();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function restart_metadata_index()
	{
		$response = $this->elasticsearch_model->restart_metadata_index();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function restart_semantic_index()
	{
		$response = $this->elasticsearch_model->restart_semantic_index();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function keyword_search()
	{
		$query = @$this->input->get('query', TRUE);
		$offset = $this->input->get('offset', TRUE);

		$response = $this->elasticsearch_model->keyword_search($query, $offset);

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function semantic_search()
	{
		$query = @$this->input->get('query', TRUE);
		$offset = $this->input->get('offset', TRUE);

		$response = $this->elasticsearch_model->semantic_search($query, $offset);

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Elasticsearch.php */
/* Location: ./application/controllers/Elasticsearch.php */