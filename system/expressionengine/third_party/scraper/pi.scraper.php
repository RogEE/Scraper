<?php

/*
=====================================================

RogEE "Scraper"
a plugin for ExpressionEngine 2
by Michael Rog

Contact Michael with questions, feedback, suggestions, bugs, etc.
>> http://rog.ee/template_omnilogger

=====================================================
*/

if (!defined('APP_VER') || !defined('BASEPATH')) { exit('No direct script access allowed'); }

// -----------------------------------------
//	Here goes nothin...
// -----------------------------------------

require_once PATH_THIRD.'scraper/config.php';

$plugin_info = array(
	'pi_name'		=> ROGEE_SCRAPER_NAME,
	'pi_version'	=> ROGEE_SCRAPER_VERSION,
	'pi_author'		=> ROGEE_SCRAPER_AUTHOR,
	'pi_author_url'	=> ROGEE_SCRAPER_AUTHOR_URL,
	'pi_description'=> ROGEE_SCRAPER_DESC,
	'pi_usage'		=> Scraper::usage()
);


/**
 * ==============================================
 * Simplehtmldom library, created by S. C. Chen
 * ==============================================
 *
 * @author S. C. Chen
 * @see http://simplehtmldom.sourceforge.net/
 *
 */

require_once PATH_THIRD.'scraper/libraries/simplehtmldom/simple_html_dom.php';

/**
 * ==============================================
 * Scraper class, for ExpressionEngine 2
 * ==============================================
 *
 * @package Scraper
 * @author Michael Rog <michael@michaelrog.com>
 * @copyright 2012 Michael Rog
 * @see http://rog.ee/scraper
 *
 */
class Scraper {

	private $EE;
	private $H;
	private $S;

	public $return_data;
	
	private $_url;
	private $_selector;
	private $_index;
	private $_limit;
	
	private $_prefix;
	private $_debug;
    
	/**
	* ==============================================
	* Constructor
	* ==============================================
	*
	* @access public
	* @return void
	*
	*/
	public function __construct()
	{
	
		// Get local EE instance
		$this->EE =& get_instance();
		
		// Load the RogEE helpers model
		$this->EE->load->model('rogee_helpers_model');
		$this->H = $this->EE->rogee_helpers_model;
		
		// Instantiate the Simple HTML DOM library
		$this->S = new simple_html_dom();
		
		// Get params
		$this->_url = $this->H->param("url");
		$this->_selector = $this->H->param("selector");
		$this->_index = $this->H->param("index");
		$this->_limit = intval($this->H->param("limit",100));
		// I support a few prefix param names, in case you're in the habit of using another addon's prefix param.
		($this->_prefix = $this->H->param("variable_prefix")) || ($this->_prefix = $this->H->param("var_prefix")) || ($this->_prefix = $this->H->param("prefix")) || ($this->_prefix = "");
		$this->_debug = $this->H->param("debug", FALSE, TRUE);
		
		// Fetch the MVA and parse the tagdata!
		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $this->fetch_variables());
	
	}

	/**
	* ==============================================
	* fetch_variables()
	* ==============================================
	*
	* Fetch stuff and parse it lol
	*
	* @access public
	* @return array: Master Variables Array
	*
	*/
	public function fetch_variables()
	{	
		
		$return_data = "";
		
		if ($this->_url == FALSE)
		{
			$this->_debug("You must provide a URL parameter.");
			return "";
			// show_error("Scraper error: You must provide a URL parameter.");
		}
		
		if ($this->_selector == FALSE)
		{
			$this->_selector("You must provide a selector parameter.");
			return "";
			// show_error("Scraper error: You must provide a selector parameter.");
		}
		
		// Create the DOM object + Load HTML from our URL
		$dom = new simple_html_dom();
		$dom->load_file($this->_url);
		
		$results = ( $this->_index === FALSE ? $dom->find($this->_selector) : array($dom->find($this->_selector, intval($this->_index))) );
		
		$variables = array();

		// We keep track of our own count/total_results variables, so they can be prefixed.
		$element_count = 1;
		$element_total_results = count($results);
		
		// --- Process each found element --- //
		
		foreach($results as $element)
		{
			
			$result_row = array();
			
			// We have to process the pair variables first, since they contain the same single variables as the root element
			
			// --- {children} --- //
			
			$children = $element->children();
			if (is_null($children))
			{
				$result_row[$this->_prefix.'children'] = array();
			}
			else
			{
				$children_items = array();
				// We're going to provide count/index variables for the children elements, just to be nice...
				$children_count = 1;
				$children_index = 0;
				$result_row[$this->_prefix.'children_total_results'] = count($children);
				foreach($children as $e)
				{
					$a = array();
					$a[$this->_prefix.'tag'] = $e->tag;
					$a[$this->_prefix.'outertext'] = $e->outertext;
					$a[$this->_prefix.'innertext'] = $e->innertext;
					$a[$this->_prefix.'plaintext'] = $e->plaintext;
					foreach($e->attr as $name => $val)
					{
						$a[$this->_prefix.'attr:'.$name] = $val;
					}
					$a[$this->_prefix.'children_count'] = $children_count++;
					$a[$this->_prefix.'children_index'] = $children_index++;
					$children_items[] = $a;
				}
				$result_row[$this->_prefix.'children'] = $children_items;
			}
			
			// --- {parent} --- //
			
			$parent = $element->parent();
			if (is_null($parent))
			{
				$result_row[$this->_prefix.'parent'] = array();
			}
			else
			{
				$e = $parent;
				$a = array();
				$a[$this->_prefix.'tag'] = $e->tag;
				$a[$this->_prefix.'outertext'] = $e->outertext;
				$a[$this->_prefix.'innertext'] = $e->innertext;
				$a[$this->_prefix.'plaintext'] = $e->plaintext;
				foreach($e->attr as $name => $val)
				{
					$a[$this->_prefix.'attr:'.$name] = $val;
				}
				$result_row[$this->_prefix.'parent'] = array($a);
			}
			
			// --- {first_child} --- //
			
			$first_child = $element->first_child();
			if (is_null($first_child))
			{
				$result_row[$this->_prefix.'first_child'] = array();
			}
			else
			{
				$e = $first_child;
				$a = array();
				$a[$this->_prefix.'tag'] = $e->tag;
				$a[$this->_prefix.'outertext'] = $e->outertext;
				$a[$this->_prefix.'innertext'] = $e->innertext;
				$a[$this->_prefix.'plaintext'] = $e->plaintext;
				foreach($e->attr as $name => $val)
				{
					$a[$this->_prefix.'attr:'.$name] = $val;
				}
				$result_row[$this->_prefix.'first_child'] = array($a);
			}
			
			// --- {last_child} --- //
			
			$last_child = $element->last_child();
			if (is_null($last_child))
			{
				$result_row[$this->_prefix.'last_child'] = array();
			}
			else
			{
				$e = $last_child;
				$a = array();
				$a[$this->_prefix.'tag'] = $e->tag;
				$a[$this->_prefix.'outertext'] = $e->outertext;
				$a[$this->_prefix.'innertext'] = $e->innertext;
				$a[$this->_prefix.'plaintext'] = $e->plaintext;
				foreach($e->attr as $name => $val)
				{
					$a[$this->_prefix.'attr:'.$name] = $val;
				}
				$result_row[$this->_prefix.'last_child'] = array($a);
			}
			
			// --- {next_sibling} --- //
			
			$next_sibling = $element->next_sibling();
			if (is_null($next_sibling))
			{
				$result_row[$this->_prefix.'next_sibling'] = array();
			}
			else
			{
				$e = $next_sibling;
				$a = array();
				$a[$this->_prefix.'tag'] = $e->tag;
				$a[$this->_prefix.'outertext'] = $e->outertext;
				$a[$this->_prefix.'innertext'] = $e->innertext;
				$a[$this->_prefix.'plaintext'] = $e->plaintext;
				foreach($e->attr as $name => $val)
				{
					$a[$this->_prefix.'attr:'.$name] = $val;
				}
				$result_row[$this->_prefix.'next_sibling'] = array($a);
			}
			
			// --- {prev_sibling} --- //
			
			$prev_sibling = $element->prev_sibling();
			if (is_null($prev_sibling))
			{
				$result_row[$this->_prefix.'prev_sibling'] = array();
			}
			else
			{
				$e = $prev_sibling;
				$a = array();
				$a[$this->_prefix.'tag'] = $e->tag;
				$a[$this->_prefix.'outertext'] = $e->outertext;
				$a[$this->_prefix.'innertext'] = $e->innertext;
				$a[$this->_prefix.'plaintext'] = $e->plaintext;
				foreach($e->attr as $name => $val)
				{
					$a[$this->_prefix.'attr:'.$name] = $val;
				}
				$result_row[$this->_prefix.'prev_sibling'] = array($a);
			}

			$result_row[$this->_prefix.'tag'] = $element->tag;
			$result_row[$this->_prefix.'outertext'] = $element->outertext;
			$result_row[$this->_prefix.'innertext'] = $element->innertext;
			$result_row[$this->_prefix.'plaintext'] = $element->plaintext;

			foreach($element->attr as $name => $val)
			{
				$result_row[$this->_prefix.'attr:'.$name] = $val;
			}
			
			$result_row[$this->_prefix.'count'] = $element_count++;
			$result_row[$this->_prefix.'total_results'] = $element_total_results;

			$variables[] = $result_row;

		}

		// clean up memory
		$dom->clear();
		unset($dom);
		
		// return Master Variables Array
		return $variables;
			
	}
	
	
	/**
	* ==============================================
	* param_test()
	* ==============================================
	*
	* Experimenting with setting parameters
	*
	* @access public
	* @return string
	*
	*/
	public function param_test()
	{	
	
		$return_data = "";
		
		$return_data .= "<h1>Param_test</h1>";
		
		$return_data .= "<h3>URL</h3>";
		$return_data .= "<pre>VAL: ".print_r($this->_url, TRUE)."</pre>";
		$return_data .= "<pre>IZ FALSE: ".($this->_url === FALSE)."</pre>";
		$return_data .= "<pre>IZ TRUE: ".($this->_url === TRUE)."</pre>";

		$return_data .= "<h3>SELECTOR</h3>";
		$return_data .= "<pre>VAL: ".print_r($this->_selector, TRUE)."</pre>";
		$return_data .= "<pre>IZ FALSE: ".($this->_selector === FALSE)."</pre>";
		$return_data .= "<pre>IZ TRUE: ".($this->_selector === TRUE)."</pre>";
		
		$return_data .= "<h3>INDEX</h3>";
		$return_data .= "<pre>VAL: ".print_r($this->_index, TRUE)."</pre>";
		$return_data .= "<pre>IZ FALSE: ".($this->_index === FALSE)."</pre>";
		$return_data .= "<pre>IZ TRUE: ".($this->_index === TRUE)."</pre>";
		
		$return_data .= "<h3>LIMIT</h3>";
		$return_data .= "<pre>VAL: ".print_r($this->_limit, TRUE)."</pre>";
		$return_data .= "<pre>IZ FALSE: ".($this->_limit === FALSE)."</pre>";
		$return_data .= "<pre>IZ TRUE: ".($this->_limit === TRUE)."</pre>";

		$return_data .= "<h3>VAR PREFIX</h3>";
		$return_data .= "<pre>VAL: ".print_r($this->_prefix, TRUE)."</pre>";
		$return_data .= "<pre>IZ FALSE: ".($this->_prefix === FALSE)."</pre>";
		$return_data .= "<pre>IZ TRUE: ".($this->_prefix === TRUE)."</pre>";
		
		$return_data .= "<h3>DEBUG</h3>";
		$return_data .= "<pre>VAL: ".print_r($this->_debug, TRUE)."</pre>";
		$return_data .= "<pre>IZ FALSE: ".($this->_debug === FALSE)."</pre>";
		$return_data .= "<pre>IZ TRUE: ".($this->_debug === TRUE)."</pre>";
		
		return $return_data;
		
	}
	
	/**
	* ==============================================
	* raw()
	* ==============================================
	*
	* Output the raw Master Variables Array (print_r'd)
	*
	* @access public
	* @return string
	*
	*/
	public function raw()
	{	
	
		$variables = $this->fetch_variables();
		
		return "<h1>Selecting ".
			$this->_selector.
			($this->_index === FALSE ? "" : " [" . $this->_index . "] " ).
			" from ".
			($this->_url === FALSE ? "" : $this->_url).
			"</h1><pre>".
			print_r($variables, TRUE).
			"</pre>"
		;
		
	}
	
	
	/**
	* ==============================================
	* usage()
	* ==============================================
	*
	* Provides usage information for display in the control panel
	*
	* @return string
	*
	*/
	public static function usage()
	{
	
		ob_start();
?>

For more information, see the complete docs:
http://rog.ee/scraper

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
		
	}


	/**
	* ==============================================
	* _debug()
	* ==============================================
	*
	* Publish a debugging message
	*
	* @access public
	* @param string
	* @return string
	*
	*/
	private function _debug($message)
	{	
		return $message;
	}


}


/* End of file pi.scraper.php */
/* Location: /system/expressionengine/third_party/scraper/pi.scraper.php */