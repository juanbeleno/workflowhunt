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
 * WorkflowHunt Semantic Helper
 *
 * @category	Helpers
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */

if(!function_exists('get_semantic_annotations_from_text'))
{
	/**
	 * Extracts a list of semantic annotations that appear in the text
	 *
	 * @param	array	$dictionary		a dictionary with ontology terms
	 * @param	string	$text 			a free text that usually belongs
	 *									to workflow metadata
	 * @return	array
	 */
    function get_semantic_annotations_from_text($dictionary, $text){
    	$annotations = array();
        // This is hacky
        $text = " ".$text." ";
    	foreach ($dictionary as $string => $concept) {
            // This is hacky
            $string = " ".$string." ";

    		$pos = stripos($text, $string);
    		if ($pos !== false) {
    			$annotations[] = $concept;
    			$wildcard = str_repeat(" ", strlen($string));
    			$text = str_ireplace($string, $wildcard, $text);
    		}
    	}

    	return array_unique($annotations);
    }
}

if(!function_exists('replace_semantic_concepts_in_text'))
{
    /**
     * Replaces the semantic concepts that appear in the text by their 
     * concept identifiers
     *
     * @param   array   $dictionary     a dictionary with ontology terms
     * @param   string  $text           a free text that usually belongs
     *                                  to workflow metadata
     * @return  array
     */
    function replace_semantic_concepts_in_text($dictionary, $text){
        // This is hacky
        $text = " ".$text." ";
        foreach ($dictionary as $string => $concept) {
            // This is hacky
            $string = " ".$string." ";
            $concept = " ".$concept." ";

            $pos = stripos($text, $string);
            if ($pos !== false) {
                $text = str_ireplace($string, $concept, $text);
            }
        }

        return trim($text);
    }
}
