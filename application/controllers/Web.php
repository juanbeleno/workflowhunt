<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Web extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('elasticsearch_model');
        $this->load->model('semantic_model');
        $this->load->model('workflow_model');
    }

	public function index()
	{
		$this->load->view('web/home');
	}

	public function results(){
		$query = @$this->input->get('query', TRUE);
		$method = @$this->input->get('method', TRUE);
		$offset = @$this->uri->segment(3);
		$offset = ($offset == '')? 0 : $offset;

		$response = array();
		
		if($method == "semantics")
		{
			$response = $this->elasticsearch_model->semantic_search($query, $offset);
		}
		else
		{
			$response = $this->elasticsearch_model->keyword_search($query, $offset);
		}

		$data['query'] = @$query;
		$data['method'] = @$method;
		$data['status'] = @$response['status'];
		$data['results'] = @$response['results'];
		$data['total'] = @$response['total'];
		$data['msg'] = @$response['msg'];

		$this->load->library('pagination');
		$this->load->helper('text');

		$config['base_url'] = base_url().'index.php/web/results/';
		$config['total_rows'] = ($data['total'] == '')? 0 : $data['total'];
		$config['per_page'] = 10;
		$config['reuse_query_string'] = true;
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item">';
		$config['cur_tag_close'] = '</li>';

		$this->pagination->initialize($config);

		$data['pagination'] = $this->pagination;

		$this->load->view('web/results', $data);
	}

	public function create_comparison()
	{
		$query = @$this->input->get('query', TRUE);
		$size = 5000;
		$offset = 0;
		
		$myexp_results = $this->workflow_model->myexp_keyword_search($query, $offset, $size);
		$keyword_search_results = $this->elasticsearch_model->keyword_search($query, $offset, $size);
		$semantic_search_results = $this->elasticsearch_model->semantic_search($query, $offset, $size);

		$this->load->helper('test');

		$response = get_search_comparison(	$myexp_results['results'], 
    										$keyword_search_results['results'], 
    										$semantic_search_results['results']);

		$response['query'] = $query;
		
		$this->load->view('web/test', $response);
	}

	public function workflow()
	{
		$id_workflow = @$this->input->get('id', TRUE);
		$response = $this->semantic_model->show($id_workflow);
		$data['title'] = @$response['workflow']['title'];
		$data['description'] = @$response['workflow']['description'];
		$data['tags'] = @$response['workflow']['tags'];
		$data['id'] = @$response['workflow']['id'];
		$data['ontologies'] = @$response['ontologies'];
		$data['annotations'] = @$response['annotations'];
		$this->load->view('web/workflow', $data);
	}

}