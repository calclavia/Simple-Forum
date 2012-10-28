<?php
/**
 * A post is what a user will post in a thread.
 * @author Calclavia
 */
class Post extends ForumElement
{
	public $id = 0;
	private $timePosted;
	
	function __construct($name, $moderators, $content)
	{
		$this->name = $name;
		$this->moderators = $moderators;
		$this->content = $content;
		$this->timePosted = time();
	}
	
	function getDate()
	{
		return date("F j, Y, g:i a", $this->timePosted);
	}
}
?>