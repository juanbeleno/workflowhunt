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
 * WorkflowHunt Workflow Model
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Workflow_model extends CI_Model {

	/**
	 * Workflow identifier
	 *
	 * @var	int
	 */
	public $id;

	/**
	 * Workflow title
	 *
	 * @var	string
	 */
	public $title;

	/**
	 * Workflow description
	 *
	 * @var	string
	 */
	public $description;

	/**
	 * Workflow tags
	 *
	 * @var	array
	 */
	public $tags;

	/**
	 * Workflow URL from API
	 *
	 * @var	string
	 */
	private $WORKFLOW_SEARCH_URL = "http://www.myexperiment.org/search.xml";

	/**
	 * Workflows URL from API
	 *
	 * @var	string
	 */
	private $WORKFLOWS_URL = "http://www.myexperiment.org/workflows.xml";

	/**
	 * Workflow URL from API
	 *
	 * @var	string
	 */
	private $WORKFLOW_URL = "http://www.myexperiment.org/workflow.xml";

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
	 * Insert Workflow Identifiers in Database
	 *
	 * @param	int	$wf_per_page	Number of workflows per page in the API
	 * @return	array
	 */
    public function insert_workflow_ids($wf_per_page = 50)
    {
    	$flag = true;
    	$page = 1;
    	$workflows = array();

    	while($flag)
    	{
    		// Construct dinamically a URL until reach all the workflows
    		$PARAMS = "sort=id&num=".$wf_per_page."&page=".$page;
    		$url = $this->WORKFLOWS_URL."?".$PARAMS;

    		// Request the content in XML format
    		$context  = stream_context_create(
    						array(
    							'http' => array(
    										'header' => 'Accept: application/xml'
    										)
    							)
    						);

			$xml = file_get_contents($url, false, $context);
			$xml = simplexml_load_string($xml);

			if(!empty($xml))
			{
				// If the content is converted into XML, we'll create the array
				// of workflow ids
				foreach ($xml->children() as $workflow) 
				{
					$workflows[] = array(
										'id' => $workflow['id'],
										'collected_at' => date("Y-m-d H:i:s")
									);
				}
			}
			else
			{
				// If the content isn't converted into XML, we'll exit the loop
				$flag = false;
			}

    		$page++;
    	}

    	$this->db->insert_batch('workflow', $workflows);

    	return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Search Workflows based on Metadata via myExperiment
	 *
	 * This is a keyword-based approach and uses solr; however, we don't
	 * have access to the internal configurations of the server, so we
	 * need to apply reverse engineering to try to figure out.
	 * #Descubra
	 *
	 * @param	int	$query	User's query in the interface
	 * @param 	int $offset Offset of the results
	 * @param 	int $size 	Size of the results
	 * @return	array
	 */
    public function myexp_keyword_search($query, $offset = 0, $size = 10)
    {
    	$results = array();

		// Construct a URL to search the workflows
		$PARAMS = "query=".$query."&num=".$size."&page=".($offset/$size + 1);
		$url = $this->WORKFLOW_SEARCH_URL."?".$PARAMS;

		// Request the content in XML format
		$context  = stream_context_create(
						array(
							'http' => array(
										'header' => 'Accept: application/xml'
										)
							)
						);

		$xml = file_get_contents($url, false, $context);
		$xml = simplexml_load_string($xml);

		if(!empty($xml))
		{
			// If the content is converted into XML, we'll create the array
			// of workflow ids
			foreach ($xml->children() as $workflow) 
			{
				$id_workflow = (int)$workflow['id'];
				if($workflow->getName() == "workflow"){
					$results[] = array('_id' => $id_workflow);
				}
			}
		} else {
			return array(
					'status' => 'BAD',
					'msg' => 'There are not results.'
				);
		}

		return array(
					'status' => 'OK',
					'results' => $results,
					'total' => count($results)
				);
    }

    // --------------------------------------------------------------------

    /**
	 * Update Workflow Metadata in Database
	 *
	 * The workflow metadata is extracted from the API and stored in the 
	 * database. Nevertheless, tags and workflow metadata are store in 
	 * different tables. 
	 *
	 * @return	array
	 */
    public function update_workflow_metadata()
    {
    	$this->db->select('id');
    	$query = $this->db->get('workflow');

    	$workflows = array();
    	$tags = array();
    	$tag_wf = array();

    	$arr_tags = array();

    	foreach ($query->result() as $workflow) 
    	{
    		$id_workflow = $workflow->id;

    		// Construct dinamically a URL for each workflow
    		$PARAMS = "id=".$id_workflow."&elements=title,description,tags,type,created-at";
    		$url = $this->WORKFLOW_URL."?".$PARAMS;

    		// Request the content in XML format
    		$context  = stream_context_create(
    						array(
    							'http' => array(
    										'header' => 'Accept: application/xml'
    										)
    							)
    						);

			$xml = file_get_contents($url, false, $context);
			$xml = simplexml_load_string($xml);

			if(!empty($xml))
			{
				// Cleaning the metadata
				$title = $xml->title;
				$title = strip_tags($title);
				$title = str_replace("\r\n", ' ', $title);
				$title = str_replace('\"', '', $title);

				$description = $xml->description;
				$description = strip_tags($description);
				$description = str_replace("\r\n", ' ', $description);
				$description = str_replace('\"', '', $description);

				$dateUTC = strtotime($xml->{'created-at'});
				$localDate = date("Y-m-d H:i:s", $dateUTC);
	
				// Saving the metadata
				$workflows 	= array(
										'title' => $title,
										'description' => $description,
										'swms' => $xml->type,
										'collected_at' => date("Y-m-d H:i:s"),
										'created_at' => $localDate
									);

				$this->db->where('id', $id_workflow);
				$this->db->update('workflow', $workflows);

				foreach ($xml->tags->children() as $tag) 
				{
					// Insert tags that belong to workflows
					if(!in_array(strval($tag), $arr_tags))
					{
						$arr_tags[] = strval($tag);
						$tags[] = array(
									'id' => $tag['id'], 
									'name' => strval($tag),
									'created_at' => date("Y-m-d H:i:s")
								  );
					}

					$tag_wf[] = array(
									'id_tag' => $tag['id'], 
									'id_workflow' => $id_workflow,
									'created_at' => date("Y-m-d H:i:s")
								  );

				}
			}
    	}

    	// Storing the metadata in database
    	$this->db->insert_batch('tag', $tags);
    	$this->db->insert_batch('tag_wf', $tag_wf);

    	return array('status' => 'OK');
    }

}

/* End of file Workflow_model.php */
/* Location: ./application/models/Workflow_model.php */