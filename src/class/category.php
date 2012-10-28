<?php
/**
 * A category contains various forum boards within.
 * @author Calclavia
 */
class Category extends ForumElement
{	
	function __construct($name, $moderators)
	{
		$this->name = $name;
		$this->moderators = $moderators;
	}
}
?>