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
	
	private $variable_prefix;
	private $debug;
	
	private $url;
	
	private $selector;
	private $limit;
    
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
		
		// Defaults, initializations, params

		$this->return_data = "";

		$this->url = $this->H->param("url");
		
		$this->selector = $this->H->param("selector");
		$this->limit = $this->H->param("limit", "100");
		
		$this->variable_prefix = $this->H->param("variable_prefix", "");
		$this->debug = $this->H->param("debug", FALSE, TRUE);
	
	}


	/**
	* ==============================================
	* parse_test()
	* ==============================================
	*
	* Playing with parsing
	*
	* @access  public
	* @return  void
	*
	*/
	public function parse_test()
	{
	
		return "parse_test";
	
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
		
		if ($this->url !== FALSE && $this->selector !== FALSE)
		{

			// Create DOM from URL or file
			$dom = file_get_html( $this->url );
	
			// Find the selected elements
			if ($this->index !== FALSE)
			{
				$results = $dom->find( $this->selector, $this->index);
			}
			else
			{
				$results = $dom->find( $this->selector );
			}
			
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
				
			}
	
			$this->return_data .= $element->src . '<br>';
	
			// clean up memory
			$dom->clear();
			unset($dom);
			
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

}


/* End of file pi.scraper.php */
/* Location: /system/expressionengine/third_party/scraper/pi.scraper.php */