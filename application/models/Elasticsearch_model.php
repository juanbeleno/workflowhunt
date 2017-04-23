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
 * WorkflowHunt ElasticSearch Model
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Elasticsearch_model extends CI_Model {

	/**
	 * ElasticSearch Client Instance
	 *
	 * @var	object
	 */
	private $client;

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

        // Load ElasticSearch API for PHP
        require APPPATH . 'third_party/vendor/autoload.php';

        $this->client = Elasticsearch\ClientBuilder::create()->build();
    }

    // --------------------------------------------------------------------

    /**
	 * Index Workflow Metadata in ElasticSearch
	 *
	 * The workflow metadata is indexed by ElasticSearch using the data 
	 * stored in the database. The metadata comprises identificators, 
	 * titles, descriptions and tags.
	 *
	 * @return	array
	 */
    public function index_metadata()
    {
    	$this->db->select('id, title, description, swms');
    	$query_workflows = $this->db->get('workflow');

    	foreach ($query_workflows->result() as $workflow)
    	{
    		// Read the workflow metadata
    		$id_workflow = $workflow->id;
    		$workflow_title = $workflow->title;
    		$workflow_description = $workflow->description;
    		$workflow_swms = $workflow->swms;
    		$workflow_tags = array();

    		// Find the workflow tags
    		$this->db->select('name');
    		$this->db->where('id_workflow', $id_workflow);
			$this->db->from('tag_wf');
			$this->db->join('tag', 'tag.id = tag_wf.id_tag');
			$query_tags = $this->db->get();

			// Unify all the tags in an array
			foreach ($query_tags->result() as $tag)
			{
				$workflow_tags[] = $tag->name;
			}

			// Seting up the metadata
			$params = [
			    'index' => 'underworld_index', // Hades' kingdom
			    'type' => 'metadata',
			    'id' => $id_workflow,
			    'body' => [
			    	'title' => $workflow_title,
			    	'description' => $workflow_description,
			    	'tags' => $workflow_tags,
			    	'swms' => $workflow_swms
			    ]
			];

			// Indexing each workflow
			$response = $this->client->index($params);
    	}

    	return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Restart Index in ElasticSearch
	 *
	 * Due to some problems like uncomplete metadata in the indexes, it is
	 * necessary to destroy the current index and recreate it.
	 *
	 * @return	array
	 */
    public function restart_index_metadata()
    {
    	// Delete the index
    	$deleteParams = [
		    'index' => 'underworld_index'
		];

		$response = $this->client->indices()->delete($deleteParams);

		// Create the index
		$params = [
		    'index' => 'underworld_index',
		    'body' => [
		        'settings' => [
		            'number_of_shards' => 2,
		            'number_of_replicas' => 0
		        ]
		    ]
		];

		$response = $this->client->indices()->create($params);

		return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Search Workflows based on Metadata via ElasticSearch
	 *
	 * This is a keyword-based approach and ElasticSearch uses the workflow
	 * metadata to return relevant results based on the query and the TF-IDF 
	 * matrix. The workflow metadata comprises identificators, 
	 * titles, descriptions and tags.
	 *
	 * @param	int	$query	User's query in the interface
	 * @param 	int $offset Offset of the results
	 * @param 	int $size 	Size of the results
	 * @return	array
	 */
    public function search_in_metadata($query, $offset = 0, $size = 10)
    {
    	if($query != '')
    	{
    		// Seting up the query
			$params = [
			    'index' => 'underworld_index', // Hades' kingdom
			    'type' => 'metadata',
			    'body' => [
			    	'from' => $offset,
			    	'size' => $size,
			    	'query' => [
			    		'simple_query_string' => [
				    		'fields' => ['title', 'description', 'tags'],
				    		'query' => $query,
				    		'minimum_should_match' => 1
			    		]
			    	]
			    ]
			];

			// Searching workflows with metadata similar to the query
			$response = $this->client->search($params);
			$total = $response['hits']['total'];
			$results = $response['hits']['hits'];

			if($offset < $total)
			{
				return array(
					'status' => 'OK',
					'results' => $results,
					'total' => $total
				);
			}
    	}
    	
    	return array(
					'status' => 'BAD',
					'msg' => 'There are not results.'
				);
		
    }

}

/* End of file Elasticsearch_model.php */
/* Location: ./application/models/Elasticsearch_model.php */