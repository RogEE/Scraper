<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------
 
/**
 * Scraper Accessory
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Accessory
 * @author		Michael Rog
 * @link		http://rog.ee
 */
 
class Scraper_acc {
	
	public $name			= 'Scraper';
	public $id				= 'scraper';
	public $version			= '0.0';
	public $description		= 'Instantly view scraped remote content inside the control panel.';
	public $sections		= array();
	
	/**
	 * Set Sections
	 */
	public function set_sections()
	{
		$EE =& get_instance();
		
		
		$this->sections['Instant Scraper'] = $EE->load->view('accessory_instant_scraper', '', TRUE);
		
	}
	
	// ----------------------------------------------------------------
	
}
 
/* End of file acc.scraper.php */
/* Location: /system/expressionengine/third_party/scraper/acc.scraper.php */