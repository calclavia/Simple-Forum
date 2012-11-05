<?php

class ForumUser
{
	/**
	 * @var int ID of the user. 
	 */
	public $userID;
	
	/**
	 * @var int Posts posted.
	 */
	public $posts;
	
	/**
	 * @var array Forum elements this user is moderating. 
	 */
	public $moderate = array();
}

?>
