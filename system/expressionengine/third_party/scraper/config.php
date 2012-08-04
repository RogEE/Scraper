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

if (!defined('ROGEE_SCRAPER_NAME'))
{
	define('ROGEE_SCRAPER_NAME', 'Scraper [RogEE]');
	define('ROGEE_SCRAPER_VERSION',  '0.0.1');
	define('ROGEE_SCRAPER_AUTHOR', 'Michael Rog');
	define('ROGEE_SCRAPER_AUTHOR_URL', 'http://rog.ee');
	define('ROGEE_SCRAPER_DESC', 'Easily scrape HTML content from external pages and parse its elements into your EE template.');
	define('ROGEE_SCRAPER_DOCS', 'http://rog.ee/scraper');
}

// NSM Addon Updater
$config['name'] = ROGEE_SCRAPER_NAME;
$config['version'] = ROGEE_SCRAPER_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://rog.ee/versions/scraper';

/*
End of file:	config.php
File location:	system/expressionengine/third_party/scraper/config.php
*/