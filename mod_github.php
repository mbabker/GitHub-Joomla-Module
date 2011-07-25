<?php
/**
* GitHub Module for Joomla!
*
* @copyright	Copyright (C) 2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

// Check if cURL is loaded; if not, proceed no further
if (!extension_loaded('curl')) {
	echo JText::_('MOD_GITHUB_ERROR_NOCURL');
	return;
}

// Include the helper
require_once dirname(__FILE__).'/helper.php';

// Check if caching is enabled
if ($params->get('cache') == 1) {
	// Set the cache parameters
	$options = array(
		'defaultgroup' => 'mod_github');
	$cache		= JCache::getInstance('callback', $options);
	$cacheTime	= $params->get('cache_time');
	// J! 1.5 and 1.6 cache is set in seconds, 1.7 caches in minutes
	if (version_compare(JVERSION,'1.7.0','ge')) {
		$cacheTime	= round($cacheTime / 60);
	}
	$cache->setLifeTime($cacheTime);
	$cache->setCaching(true);

	// Call the cache; if expired, pull new data
	$twitter = $cache->call(array('modGithubHelper', 'compileData'), $params);
} else {
	// Pull new data
	$twitter = modGithubHelper::compileData($params);
}

if ((!$github) || (isset($github->error))) {
	echo JText::_('MOD_GITHUB_ERROR_UNABLETOLOAD');
	return;
}

require(JModuleHelper::getLayoutPath('mod_github', $params->get('templateLayout', 'default')));
