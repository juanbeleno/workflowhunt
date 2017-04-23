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
 * WorkflowHunt WordNet Model
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Wordnet_model extends CI_Model {

	/**
	 * WordNet EndPoint URL
	 *
	 * @var	string
	 */
	private $WORDNET_URL = "http://wordnetweb.princeton.edu/perl/webwn?c=7&sub=Change&o2=&o0=&o8=1&o1=&o7=1&o5=&o9=&o6=&o3=&o4=&i=-1&h=0&s=";

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

        // Load PHP Simple HTML DOM Parser
        require APPPATH . 'third_party/simple_html_dom.php';
    }

    // --------------------------------------------------------------------

    /**
	 * Store Synonyms from WordNet
	 *
	 * This method will iterate over each ontology term and will find the
	 * synonyms in WordNet database, scrapping their page.
	 *
	 * @return	array
	 */
    public function store_synonyms()
    {
    	$this->db->select('id_ontology_concept, string');
    	$query_term = $this->db->get('ontology_term');

    	foreach ($query_term->result() as $term) 
    	{
    		// Create DOM from URL or file
			$html = file_get_html( $this->WORDNET_URL.urlencode($term->string));

			foreach($html->find('li a[href]') as $element)
			{
       			$wn_synonym = $element->innertext;

       			if($wn_synonym != 'S:' and $wn_synonym != 'W:')
       			{
       				$data = array(
       					'id_ontology_concept' => $term->id_ontology_concept,
       					'string' => $wn_synonym,
                'type' => 'Synonym',
       					'source' => 'WordNet',
       					'created_at' => date("Y-m-d H:i:s")
       				);

       				$this->db->insert('ontology_term', $data);
       			}
			}

    	}

    	return array( 'status' => 'OK' );
    }

}

/* End of file Wordnet_model.php */
/* Location: ./application/models/Wordnet_model.php */