<?php

class UserData extends ForumUser
{
	/*
	 * The display name of the user.
	 */
	public $username;

	/*
	 * The email of the user.
	 */
	public $email;
	
	function __construct($id, $username, $email)
	{
		$this->id = $id;
		$this->username = $username;
		$this->email = $email;
	}
}
?>
