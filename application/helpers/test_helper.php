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

/**
 * WorkflowHunt Test Helper
 *
 * @category	Helpers
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */

if(!function_exists('get_search_comparison'))
{
	/**
	 * Compares the workflows retrieved by myExperiment search, WH keyword 
	 * search and WH semantic search, using the same query. WH: WorkflowHunt.
	 * Given a query q, we consider:
	 * A = set of results retrieved by myExperiment search given a query q
	 * B = set of results retrieved by WH keyword search given a query q
	 * C = set of results retrieved by WH semantic search given a query q
	 *
	 * @param	array	$myexp_results				myExperiment results
	 * @param	array	$keyword_search_results		WH keyword search results
	 * @param	array	$semantic_search_results	WH semantic search results
	 * @return	array
	 */
    function get_search_comparison(	$myexp_results, 
    								$keyword_search_results, 
    								$semantic_search_results){
    	$A = array();
    	$B = array();
    	$C = array();

        if(isset($myexp_results['results'])){
        	foreach ($myexp_results['results'] as $workflow) {
        		$A[] = $workflow['_id'];
        	}
        }

        if(isset($keyword_search_results['results'])){
            foreach ($keyword_search_results['results'] as $workflow) {
        		$B[] = $workflow['_id'];
        	}
        }

        if(isset($semantic_search_results['results'])){
        	foreach ($semantic_search_results['results'] as $workflow) {
        		$C[] = $workflow['_id'];
        	}
        }

    	// Intersection
    	$Int_A_B = array_intersect($A, $B);
    	$Int_B_C = array_intersect($B, $C);
    	$Int_C_A = array_intersect($C, $A);
    	$Int_A_B_C = array_intersect($A, $B, $C);


    	// Diference
    	$A_B = array_diff($A, $B);
    	$A_C = array_diff($A, $C);
    	$B_C = array_diff($B, $C);
    	$C_B = array_diff($C, $B);
    	$C_A = array_diff($C, $A);
    	$B_A = array_diff($B, $A);
    	$A_BC = array_diff($A, $B, $C);
    	$B_CA = array_diff($B, $C, $A);
    	$C_AB = array_diff($C, $A, $B);

    	$operations = array(
    		'A ∩ B' => $Int_A_B,
    		'B ∩ C' => $Int_B_C,
    		'C ∩ A' => $Int_C_A,
    		'A ∩ B ∩ C' => $Int_A_B_C,
    		'A - B' => $A_B,
    		'A - C' => $A_C,
    		'B - C' => $B_C,
    		'C - B' => $C_B,
    		'C - A' => $C_A,
    		'B - A' => $B_A,
    		'A - (B U C)' => $A_BC,
    		'B - (C U A)' => $B_CA,
    		'C - (A U B)' => $C_AB
    	);

    	$sizes = array(
    		'A' => count($A),
    		'B' => count($B),
    		'C' => count($C),
    		'A ∩ B' => count($Int_A_B),
    		'B ∩ C' => count($Int_B_C),
    		'C ∩ A' => count($Int_C_A),
    		'A ∩ B ∩ C' => count($Int_A_B_C),
    		'A - B' => count($A_B),
    		'A - C' => count($A_C),
    		'B - C' => count($B_C),
    		'C - B' => count($C_B),
    		'C - A' => count($C_A),
    		'B - A' => count($B_A),
    		'A - (B U C)' => count($A_BC),
    		'B - (C U A)' => count($B_CA),
    		'C - (A U B)' => count($C_AB)
    	);

    	return array(
    		'sizes' => $sizes,
    		'operations' => $operations
    	);
    }
}