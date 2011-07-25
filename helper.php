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
	 * Function to process the GitHub data into a formatted object
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

		// Load the parameters
		$uname		= $params->get('username', '');
		$repo		= $params->get('repo', '');
		$count		= $params->get('count', 3) - 1;

		// Convert the list name to a useable string for the JSON
		if ($repo) {
			$frepo	= self::toAscii($repo);
		}

		// Process the feed
		foreach ($obj as $o) {
			if ($i <= $count) {
				// Initialize a new object
				$github[$i]->commit	= new stdClass();

				// The commit message linked to the commit
				$github[$i]->commit->message = '<a href="https://github.com/'.$uname.'/'.$frepo.'/commit/'.$o['sha'].'">'.$o['commit']['message'].'</a>';

				// Check if the committer information
				if ($o['author']['id'] != $o['committer']['id']) {
					// The committer name formatted with link
					$github[$i]->commit->committer	= JText::_('MOD_GITHUB_AND_COMMITTED_BY').'<a href="https://github.com/'.$o['committer']['login'].'">'.$o['commit']['committer']['name'].'</a>';

					// The author wasn't the committer
					$github[$i]->commit->author		= JText::_('MOD_GITHUB_AUTHORED_BY');
				} else {
					// The author is also the committer
					$github[$i]->commit->author		= JText::_('MOD_GITHUB_COMMITTED_BY');
				}

				// The author name formatted with link
				$github[$i]->commit->author .= '<a href="https://github.com/'.$o['author']['login'].'">'.$o['commit']['author']['name'].'</a>';

				$i++;
			}
		}
		return $github;
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
