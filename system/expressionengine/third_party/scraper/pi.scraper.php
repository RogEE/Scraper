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
	
	private $_prefix;
	private $_debug;
	
	private $_url;
	private $_selector;
	private $_index;
	private $_limit;
	
	private $_ph = array();
    
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
		
		// Defaults, initializations, params

		$this->return_data = "";

		$this->_url = $this->H->param("url", FALSE);
		
		$this->_selector = $this->H->param("selector", FALSE);
		$this->_index = $this->H->param("index", FALSE);
		$this->_limit = $this->H->param("limit", "100");
		
		$this->_prefix = $this->H->param("variable_prefix", FALSE);
		
		$this->_debug = $this->H->param("debug", FALSE, TRUE);
		
		// $this->return_data = $this->fetch();




		// Master Variables Array
		$variables = array();
		
		$this->_url = "http://michaelrog.com";
		$this->_selector = "p";
		$this->_index = -1;
		
		if ($this->_url !== FALSE && $this->_selector !== FALSE)
		{

			// Create DOM from URL or file
			// $dom = file_get_html( $this->_url );
	
			// Find the selected elements
			if ($this->_index !== FALSE)
			{
				// $results = $dom->find( $this->_selector, $this->_index);
				// return $this->_index;
			}
			else
			{
				// $results = $dom->find( $this->_selector );
				// return "nope";
			}
			
			$dom = file_get_html( "http://michaelrog.com/" );
			$results = $dom->find("html",0);
			
			$this->return_data = "<pre>".print_r($results, TRUE)."</pre>";
			
			// Find all images 
			foreach($results as $element)
			{
				
				$result_row = array(
					$this->variable_prefix.'tag' => $element->tag,
					$this->variable_prefix.'outertext' => $element->outertext,
					$this->variable_prefix.'innertext' => $element->innertext,
					$this->variable_prefix.'plaintext' => $element->plaintext
					);
				
				$variables[] = $result_row;
				
				// return $element->plaintext . '<br>';
				
			}
	
			// clean up memory
			$dom->clear();
			unset($dom);
			
		}
		
		// $this->return_data = "<pre>".print_r($variables, TRUE)."</pre>";




	
	}

	/**
	* ==============================================
	* fetch()
	* ==============================================
	*
	* Fetch stuff and parse it lol
	*
	* @access public
	* @return string
	*
	*/
	public function fetch()
	{	
		
		return "<div><pre>".print_r($this->_url, TRUE)."||".print_r($_this->_index, TRUE)."</pre></div>";
		
		return "1";
		if ($this->_url != FALSE && $this->_selector != FALSE)
		{

			// Load the remote document
			$this->S->load_file($this->_url);
		
			// Master Variables Array
			$variables = array();
			$results = array();

			// Find the selected elements
			if ($this->_index !== FALSE)
			{
				// $results = $this->S->find( $this->_selector, $this->_index );
				// in this variant, we get back a single element (or null),
				// and we need it to be in array form...
				// $results = is_null($results) ? array() : array($results);
				$results = 1;
			}
			else
			{
				$results = $this->S->find( $this->_selector );
			}
			
			return "<div><pre>".print_r($results, TRUE)."</pre></div>";
			
			// Find all images 
			foreach($results as $element)
			{
				
				$result_row = array(
					$this->_prefix.'tag' => $element->tag,
					$this->_prefix.'outertext' => $element->outertext,
					$this->_prefix.'innertext' => $element->innertext,
					$this->_prefix.'plaintext' => $element->plaintext
				);
				
				$variables[] = $result_row;
				
			}
	
			return print_r($variables, TRUE);
	
			return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	
			// clean up memory
			$this->S->clear();
			unset($this->S);
			
		}
		else
		{
			return "nope.";
		}
	
	}


	/**
	* ==============================================
	* parse_test()
	* ==============================================
	*
	* Playing with parsing
	*
	* @access public
	* @return string
	*
	*/
	public function parse_test()
	{	
	
		$return_data = "";
		
		$return_data .= "<hr><h1>Single Vars</h1><pre>".print_r($this->EE->TMPL->var_single, TRUE)."</pre>";
		$return_data .= "<hr><h1>Pair Vars</h1><pre>".print_r($this->EE->TMPL->var_pair, TRUE)."</pre>";
		$return_data .= "<hr><h1>Tag Data</h1><pre>".print_r($this->EE->TMPL->tagdata, TRUE)."</pre>";		
	
		$pattern = '#{'.$this->_prefix.'children(?>(?:[^{]++|{(?!\/?'.$this->_prefix.'children[^}]*}))+|(?R))*{\/'.$this->_prefix.'children#si';
		$pattern = '#{children(?>(?:[^{]++|{(?!\/?children[^}]*}))+|(?R))*{\/children#si';
		
		$tagdata = $this->EE->TMPL->tagdata;
		$tagdata = preg_replace_callback($pattern, array(get_class($this), '_placeholders'), $tagdata);
	
		$return_data .= "<hr><h1>New Tag Data</h1><pre>".print_r($tagdata, TRUE)."</pre>";
	
		$return_data .= "<hr><h1>_ph</h1><pre>".print_r($this->_ph, TRUE)."</pre>";		
	
		return $return_data;
		
	}
	
	
	/**
	* ==============================================
	* sd_test()
	* ==============================================
	*
	* Playing with SimpleHTMLDom stuff
	*
	* @access public
	* @return string
	*
	*/
	public function sd_test()
	{	
	
		// Master Variables Array
		$variables = array();
		
		$this->_url = "http://michaelrog.com";
		$this->_selector = "p";
		$this->_index = -1;
		
		if ($this->_url !== FALSE && $this->_selector !== FALSE)
		{

			// Create DOM from URL or file
			$dom = file_get_html( $this->_url );
	
			// Find the selected elements
			if ($this->_index !== FALSE)
			{
				// $results = $dom->find( $this->_selector, $this->_index);
				// return $this->_index;
			}
			else
			{
				// $results = $dom->find( $this->_selector );
				// return "nope";
			}
			
			$results = $dom->find( "p", 1);
			
			// Find all images 
			foreach($results as $element)
			{
				
				$result_row = array(
					$this->variable_prefix.'tag' => $element->tag,
					$this->variable_prefix.'outertext' => $element->outertext,
					$this->variable_prefix.'innertext' => $element->innertext,
					$this->variable_prefix.'plaintext' => $element->plaintext
					);
				
				$variables[] = $result_row;
				
				// return $element->plaintext . '<br>';
				
			}
	
			// clean up memory
			$dom->clear();
			unset($dom);
			
			return "<pre>".print_r($variables, TRUE)."</pre>";
			
		}
		
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