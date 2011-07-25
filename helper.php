<?php
/**
* GitHub Module for Joomla!
*
* @copyright	Copyright (C) 2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

class modGithubHelper {

	/**
	 * Function to compile the data to render a formatted object
	 *
	 * @param	object	$params		The module parameters
	 *
	 * @return	object	$github		A formatted object with the requested information
	 * @since	1.0
	 */
	static function compileData($params) {
		// Load the parameters
		$uname		= $params->get('username', '');
		$repo		= $params->get('repo', '');

		// Convert the list name to a useable string for the JSON
		if ($repo) {
			$frepo	= self::toAscii($repo);
		}

		// Initialize the array
		$github	= array();

		$req = 'https://api.github.com/repos/'.$uname.'/'.$frepo.'/commits';

		// Fetch the decoded JSON
		$obj = self::getJSON($req);

		if (is_null($obj)) {
			$github->error	= 'Error';
			return $github;
		}

		// Process the filtering options and render the feed
		$github = self::processData($obj, $params);

		return $github;
	}

	/**
	 * Function to fetch a JSON feed
	 *
	 * @param	string	$req	The URL of the feed to load
	 *
	 * @return	array	$obj	The fetched JSON query
	 * @since	1.0
	 */
	static function getJSON($req) {
		// Create a new CURL resource
		$ch = curl_init($req);

		// Set options
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// Grab URL and pass it to the browser and store it as $json
		$json = curl_exec($ch);

		// Close CURL resource
		curl_close($ch);

		// Decode the fetched JSON
		$obj = json_decode($json, true);

		return $obj;
	}

	/**
	 * Function to process the Twitter feed into a formatted object
	 *
	 * @param	object	$obj		The data object
	 * @param	object	$params		The module parameters
	 *
	 * @return	object	$github		The data object ready for output
	 * @since	1.0
	 */
	static function processData($obj, $params) {
		// Initialize
		$github = array();
		$i = 0;

		// Process the feed
		foreach ($obj as $o) {
			// Initialize a new object
			$github[$i]->commit	= new stdClass();

			// The commit message
			$github[$i]->commit->message = $o['commit']['message'];

			$i++;
		}
		return $github;
	}

	/**
	 * Function to convert a static time into a relative measurement
	 *
	 * @param	string	$date	The date to convert
	 *
	 * @return	string	$date	A text string of a relative time
	 * @since	1.0
	 */
	static function renderRelativeTime($date) {
		$diff = time() - strtotime($date);
		// Less than a minute
		if ($diff < 60) {
			return JText::_('MOD_TWEETDISPLAYBACK_CREATE_LESSTHANAMINUTE');
		}
		$diff = round($diff/60);
		// 60 to 119 seconds
		if ($diff < 2) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_MINUTE', $diff);
		}
		// 2 to 59 minutes
		if ($diff < 60) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_MINUTES', $diff);
		}
		$diff = round($diff/60);
		// 1 hour
		if ($diff < 2) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_HOUR', $diff);
		}
		// 2 to 23 hours
		if ($diff < 24) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_HOURS', $diff);
		}
		$diff = round($diff/24);
		// 1 day
		if ($diff < 2) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_DAY', $diff);
		}
		// 2 to 6 days
		if ($diff < 7) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_DAYS', $diff);
		}
		$diff = round($diff/7);
		// 1 week
		if ($diff < 2) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_WEEK', $diff);
		}
		// 2 or 3 weeks
		if ($diff < 4) {
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_WEEKS', $diff);
		}
		return JHTML::date($date);
	}

	/**
	 * Function to convert a formatted repo name into it's URL equivilent
	 *
	 * @param	string	$repo	The user inputted repo name
	 *
	 * @return	string	$repo	The repo name converted
	 * @since	1.0
	 */
	static function toAscii($repo) {
		$clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $repo);
		$clean = strtolower(trim($clean, '-'));
		$repo  = preg_replace("/[\/_|+ -']+/", '-', $clean);

		return $repo;
	}
}
