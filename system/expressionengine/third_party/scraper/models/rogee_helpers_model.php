<?php

if (!defined('APP_VER') || !defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * ==============================================
 * Rogee_helpers_model class
 * ==============================================
 *
 * Provides various helper methods
 *
 * @package RogEE
 * @author Michael Rog <michael@michaelrog.com>
 *
 */
class Rogee_helpers_model extends CI_Model {

	private $EE;

	/**
	* ==============================================
	* Constructor
	* ==============================================
	*
	* @access	public
	* @return	void
	*
	*/
	public function __construct()
	{
	
		parent::__construct();
		
		$this->EE =& get_instance();
	
	} // END Constructor


	/**
	* ==============================================
	* param()
	* ==============================================
	*
	* Fetches a parameter
	*
	* @access	public
	* @param
	* @return	void
	*
	*/
	public function param($param, $default = FALSE, $boolean = FALSE, $required = FALSE)
	{
	
		$name = $param;
		$param = $this->EE->TMPL->fetch_param($param);
	
		if(!$param && $required)
		{
			show_error('You must define a "'.$name.'" parameter.');
		}
	
		if($param === FALSE && $default !== FALSE)
		{
			$param = $default;
		}
		
		if($param !== FALSE && $boolean)
		{				
			$param = strtolower($param);
			$param = ($param == 'true' || $param == 'yes' || $param == 'y' || $param == 'on' || $param == '1') ? TRUE : FALSE;
		}
	
		return $param;
		
	} // END param()


}


/* End of file      : rogee_helpers_model.php */
/* File location    : system/expressionengine/third_party/scraper/models/rogee_helpers_model.php */