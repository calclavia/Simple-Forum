<?php
/**
 * A board can contain multiple threads and board within it.
 * @author Calclavia
 */
class Board extends ForumElement
{	
	function __construct($name, $moderators)
	{
		$this->name = $name;
		$this->moderators = $moderators;
	}
}
?>