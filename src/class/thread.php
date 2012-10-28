<?php
/**
 * A thread contains various posts within it.
 * @author Calclavia
 */
class Thread extends ForumElement
{
	function __construct($name, $moderators, $post)
	{
		parent::$name = $name;
		parent::moderators = $moderators;
		parent::addPost($post);
	}
	
	//@post - A Post object.
	function addPost($post)
	{
		$this->content = $this->content.",".$post->id;
	}
	
	function getPosts()
	{
		return explode($this->content, ",");
	}
}
?>