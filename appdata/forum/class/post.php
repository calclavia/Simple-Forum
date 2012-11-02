<?php
/**
 * A post is what a user will post in a thread.
 * @author Calclavia
 */
class Post extends ForumElement
{
	private $timePosted;
	
	//The contents of this forum post.
	private $content = "";
	
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
	
	protected function getFileName()
	{
		return preg_replace('/\s+/ ', "_", trim($this->forum."_".$this->id.".pst"));
	}
	
	static public function load($forum, $fileName)
	{
		return parent::load($forum."/".$fileName.".pst");
	}
	
	protected function getForumElements()
	{
		return null;
	}
}
?>