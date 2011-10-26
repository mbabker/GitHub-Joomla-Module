<?php
/**
 * GitHub Module for Joomla!
 *
 * @package    GitHubModule
 *
 * @copyright  Copyright (C) 2011 Michael Babker. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

// Check if cURL is loaded; if not, proceed no further
if (!extension_loaded('curl'))
{
	echo JText::_('MOD_GITHUB_ERROR_NOCURL');
	return;
}

// Include the helper
require_once dirname(__FILE__).'/helper.php';

// Check if caching is enabled
if ($params->get('cache') == 1)
{
	// Set the cache parameters
	$options = array(
		'defaultgroup' => 'mod_github');
	$cache		= JCache::getInstance('callback', $options);
	$cacheTime	= round($cacheTime / 60);
	$cache->setLifeTime($cacheTime);
	$cache->setCaching(true);

	// Call the cache; if expired, pull new data
	$github = $cache->call(array('ModGithubHelper', 'compileData'), $params);
}
else
{
	// Pull new data
	$github = modGithubHelper::compileData($params);
}

if ((!$github) || (isset($github->error)))
{
	echo JText::_('MOD_GITHUB_ERROR_UNABLETOLOAD');
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_github', $params->get('templateLayout', 'default'));
