<?php

class ForumUser
{
	/*
	 * The ID of the user.
	*/
	public $id;
	
	/**
	 * @var int Posts posted.
	 */
	public $posts;
	
	/**
	 * @var array Forum elements this user is moderating. 
	 */
	public $moderate = array();
	
	public function hasPermission($permission)
	{
		return true;
	}
}

?>
