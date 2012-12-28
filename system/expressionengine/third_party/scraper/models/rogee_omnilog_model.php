<?php

if (!defined('APP_VER') || !defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * ==============================================
 * Rogee_omnilog_model class
 * ==============================================
 *
 * Provides methods for connecting to OmniLog and creating a log entry
 *
 * @package RogEE
 * @author Michael Rog <michael@michaelrog.com>
 *
 */
class Rogee_omnilog_model extends CI_Model {


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
		
		// Load the OmniLogger class.
		if (file_exists(PATH_THIRD.'omnilog/classes/omnilogger'.EXT))
		{
			include_once PATH_THIRD.'omnilog/classes/omnilogger'.EXT;
		}
	
	} // END Constructor


	/**
	* ==============================================
	* log_message()
	* ==============================================
	*
	* Logs a message to OmniLog.
	*
	* @access	public
	* @param	string $source: The source of the message.
	* @param	string $message: The log entry message.
	* @param	int $severity: The log entry 'level'.
	* @param	boolean $notify: Whether to nofity the site admin
	* @param	array $emails: An array of "admin" email addresses.
	* @param	string $extended_data: Additional data.
	* @return	void
	*
	*/
	public function log_message(
		$source,
		$message,
		$severity = 1,
		$notify = FALSE,
		Array $emails = array(),
		$extended_data = ''
	)
	{
		
		if (class_exists('Omnilog_entry') && class_exists('Omnilogger'))
		{
		
			switch ($severity)
			{
				case 3:
				$type = Omnilog_entry::ERROR;
				break;
				
				case 2:
				$type = Omnilog_entry::WARNING;
				break;
				
				case 1:
				default:
				$type = Omnilog_entry::NOTICE;
				break;
			}
			
			$omnilog_entry = new Omnilog_entry(array(
				'addon_name'    => $source,
				'admin_emails'  => $emails,
				'date'          => time(),
				'extended_data' => $extended_data,
				'message'       => $message,
				'notify_admin'  => $notify,
				'type'          => $type
			));
			
			Omnilogger::log($omnilog_entry);
			
		}
		
	} // END make_log_entry()


}


/* End of file      : rogee_omnilog_model.php */
/* File location    : system/expressionengine/third_party/scraper/models/rogee_omnilog_model.php */