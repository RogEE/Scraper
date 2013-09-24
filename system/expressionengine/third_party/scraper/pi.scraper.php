<?php

/*
=====================================================

RogEE "Scraper"
a plugin for ExpressionEngine 2
by Michael Rog

Contact Michael with questions, feedback, suggestions, bugs, etc.
>> http://rog.ee/scraper

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

	public $return_data;

	private $_url;
	private $_selector;
	private $_index;
	private $_limit;

	private $_placeholders = array();
	private $_there_are_advanced_tags;
	private $_tagdata;

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

		// I support a few prefix param names, in case you're in the habit of using another addon's prefix param.
		($this->_prefix = $this->H->param("variable_prefix")) || ($this->_prefix = $this->H->param("var_prefix")) || ($this->_prefix = $this->H->param("prefix")) || ($this->_prefix = "");
		$this->_debug = $this->H->param("debug", FALSE, TRUE);

		$this->_tagdata = $this->EE->TMPL->tagdata;
		$this->_there_are_advanced_tags = $this->_parse_advanced_tags();

		// Fetch the MVA and parse the tagdata!
		$variables = $this->fetch_variables();
		if (empty($variables))
		{
			$this->return_data = $this->EE->TMPL->no_results();
		}
		else
		{
			$this->return_data = $this->EE->TMPL->parse_variables(rtrim($this->_tagdata), $variables);
		}

		// TODO: Clean up un-parsed attribute variables (maybe add a param to specify replacement value)

		// TODO: HTTP Authentication?

		// TODO: Implement limit param?

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

		if ($this->_url == FALSE)
		{
			$this->_debug("You must provide a URL parameter.");
			return "";
			// show_error("Scraper error: You must provide a URL parameter.");
		}

		if ($this->_selector == FALSE)
		{
			$this->_debug("You must provide a selector parameter.");
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

			// --- {pair_var} --- //
			// (We have to process the pair variables first, since they contain the same single variables as the root element.)

			$pair_vars = array(
				'children'
				, 'parent'
				, 'first_child'
				, 'last_child'
				, 'next_sibling'
				, 'prev_sibling'
			);

			foreach($pair_vars as $var)
			{
	
				switch ($var) {
	
					case "children":
			
						$children = $element->children();
			
						if (is_null($children))
						{
							$result_row[$this->_prefix.'children'] = array();
							$result_row[$this->_prefix.'children_total_results'] = 0;
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
								$a = $this->_get_element_variables($e);
								$a[$this->_prefix.'children_count'] = $children_count++;
								$a[$this->_prefix.'children_index'] = $children_index++;
								$children_items[] = $a;
							}
							$result_row[$this->_prefix.'children'] = $children_items;
						}
			
						break;
	
					default:

						$node = $element->$var();
						if (is_null($node))
						{
							$result_row[$this->_prefix.$var] = array();
						}
						else
						{
							$result_row[$this->_prefix.$var] = array($this->_get_element_variables($node));
						}
			
						break;
	
				}
	
			}

			// --- advanced tags (i.e. {find}) --- //

			$result_row = array_merge($result_row, $this->_process_advanced_variables($element));

			// --- primary element --- //

			$result_row = array_merge($result_row, $this->_get_element_variables($element));

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
	* _get_element_variables()
	* ==============================================
	*
	* Construct a small bit of array containing variables, per-element
	*
	* @access private
	* @param Element
	* @return array
	*
	*/
	private function _get_element_variables($e)
	{

		$a = array();

		if(isset($e->tag))
		{
			$a[$this->_prefix.'tag'] = $e->tag;
			$a[$this->_prefix.'outertext'] = $e->outertext;
			$a[$this->_prefix.'innertext'] = $e->innertext;
			$a[$this->_prefix.'plaintext'] = $e->plaintext;
			foreach($e->attr as $name => $val)
			{
				$a[$this->_prefix.'attr:'.$name] = $val;
			}
		}

		return $a;

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
			"</pre>".
			"<h1>Advanced variables/placeholders</h1>".
			"<pre>".
			print_r($this->_placeholders, TRUE).
			"</pre>".
			"<h1>Tagdata after placeholder replacements</h1>".
			"<pre>".
			print_r($this->_tagdata, TRUE).
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

{exp:scraper url="http://google.com" selector="a" index="" prefix=""}

	{tag}
	{outertext}
	{innertext}
	{plaintext}
	{attr:id}
	{count} / {total_results}

	{if children_total_results}
	{children}
		{tag}
		[etc.]
		{children_count} / {children_total_results}
	{/children}
	{/if}

	{find selector="span" index=""}
		{tag}
		[etc.]
		{found_count} / {found_total_results}
	{/find}

{/exp:scraper}

You can use any valid CSS selector; If you want to use an attribute selector that contains spaces, you need to quote-wrap it.

Leaving the index parameter blank will return the first result (as if you'd set index="0").

An index of -1 will return the last item.

For more information, see the complete docs:
http://rog.ee/scraper

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;

	}

	/**
	* ==============================================
	* _parse_advanced_tags()
	* ==============================================
	*
	* Queues up the {find ...} tags in the _advanced_tags placeholder array
	*
	* @access private
	* @param array: matches
	* @return boolean: Do we need to process advnaced tags?
	*
	*/
	private function _parse_advanced_tags()
	{

		$there_are_advanced_tags = FALSE;

		foreach ($this->EE->TMPL->var_pair as $tag => $tag_details)
		{

			// --- parse {find} tags --- //
			$type = str_replace($this->_prefix, '', strtok($tag, ' '));

			if ($type == 'find')
			{

				$there_are_advanced_tags = TRUE;
	
				$closing_tag = "/".$this->_prefix.$type;
	
				$this_tag = array();
				$this_tag = array_merge
				(
					array
					(
						'tag' => $tag,
						'closing_tag' => $closing_tag,
						'type' => $type
					),
					$tag_details
				);
		
				$ph = $this->_make_placeholder($this_tag);
		
				$pattern = '/'.LD.$tag.RD.'(.*?)'.LD.'\\'.$closing_tag.RD.'/s';
				$tagdata = preg_replace($pattern, '{'.$ph.'}$1{/'.$ph.'}', $this->_tagdata);
				// preg_replace() returns NULL on PCRE error
				if (is_null($tagdata))
				{
					$this->_pcre_error();
				}
				else
				{
					$this->_tagdata = $tagdata;
				}
		
			}
	
		}

		return $there_are_advanced_tags;

	}

	/**
	* ==============================================
	* _make_placeholder()
	* ==============================================
	*
	* Creates an entry for the provided tag item in the _placeholders array
	*
	* @access private
	* @param string: tag type
	* @param array: tag item details
	* @return string: placeholder name
	*
	*/
	private function _make_placeholder($item)
	{
		$ph = __CLASS__.'_ph_'.count($this->_placeholders);
		$this->_placeholders[$ph] = $item;
		return $ph;
	}

	/**
	* ==============================================
	* _pcre_error()
	* ==============================================
	*
	* Log PCRE error for debugging
	*
	* @access private
	* @return void
	*
	*/
	private function _pcre_error()
	{
		// either an unsuccessful match, or a PCRE error occurred
        $pcre_err = preg_last_error();  // PHP 5.2 and above

		if ($pcre_err === PREG_NO_ERROR)
		{
			$this->EE->TMPL->log_item("PCRE Successful non-match");
		}
		else 
		{
            // preg_match error :(
			switch ($pcre_err) 
			{
				case PREG_INTERNAL_ERROR:
					$this->_debug("PREG_INTERNAL_ERROR");
					break;
				case PREG_BACKTRACK_LIMIT_ERROR:
					$this->_debug("PREG_BACKTRACK_LIMIT_ERROR");
					break;
				case PREG_RECURSION_LIMIT_ERROR:
					$this->_debug("PREG_RECURSION_LIMIT_ERROR");
					break;
				case PREG_BAD_UTF8_ERROR:
					$this->_debug("PREG_BAD_UTF8_ERROR");
					break;
				case PREG_BAD_UTF8_OFFSET_ERROR:
					$this->_debug("PREG_BAD_UTF8_OFFSET_ERROR");
					break;
				default:
					$this->_debug("Unrecognized PREG error");
					break;
			}
		}
	}


	/**
	* ==============================================
	* _process_advanced_variables()
	* ==============================================
	*
	* Publish a debugging message
	*
	* @access private
	* @param Element
	* @return array
	*
	*/
	private function _process_advanced_variables($origin_element)
	{

		$advanced_variables = array();

		if ($this->_there_are_advanced_tags)
		{
			foreach ($this->_placeholders as $ph => $tag_details)
			{

				// --- {find} tags --- ///
	
				if ($tag_details['type'] == 'find')
				{
					$s = $tag_details['selector'];
					$i = $tag_details['index'];
   
					if (!empty($s))
					{
			
						if (!is_null($i))
						{
							$found_elements = array($origin_element->find($s, intval($i)));
						}
						else
						{
							$found_elements = $origin_element->find($s);
						}

						if (is_null($found_elements))
						{
							return array();
						}
						else
						{
							$found_elements_tags = array();
							// We're going to provide count/index variables for the children elements, just to be nice...
							$found_count = 1;
							$found_index = 0;
							$found_total_results = count($found_elements);
							foreach($found_elements as $e)
							{
								$a = $this->_get_element_variables($e);
								$a[$this->_prefix.'found_count'] = $found_count++;
								$a[$this->_prefix.'found_index'] = $found_index++;
								$a[$this->_prefix.'found_total_results'] = $found_total_results;
								$found_elements_tags[] = $a;
							}
							$advanced_variables[$ph] = $found_elements_tags;
						}
				
					}
					else
					{
						$advanced_variables[$ph] = array();
					}
	
				}

			}

		}

		return $advanced_variables;

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
		return $this->H->debug("Scraper: ".$message);
	}


}


/* End of file pi.scraper.php */
/* Location: /system/expressionengine/third_party/scraper/pi.scraper.php */