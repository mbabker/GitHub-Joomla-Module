<?php
/**
* Tweet Display Back Module for Joomla!
*
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

// Variables for the foreach
$i		= 0;
?>
<ul class="GH-commit<?php echo $moduleclass_sfx;?>">
	<?php foreach ($github as $o) { ?>
	<li><?php echo $o->commit->message.$o->commit->author;
	if (isset($o->commit->committer)) {
		echo $o->commit->committer;
	} ?>
	</li>
	<?php $i++;
	} ?>
</ul>
