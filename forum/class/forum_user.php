<?php

class ForumUser
{

	/**
	 * @int The ID of the user.
	 */
	public $id;

	/**
	 * @int Posts posted.
	 */
	public $posts;

	/**
	 * @array array Forum elements this user is moderating.
	 */
	public $moderate = array();

	/**
	 * @string The display name of the user.
	*/
	public $username;

	/**
	 * @string The email of the user.
	 */
	public $email;

	/**
	 * @array An array of unread posts.
	 */
	public $unreadPosts = array();

	/**
	 * @param int $id
	 * @param String $username
	 * @param String $email
	*/
	function __construct($id, $username, $email)
	{
		$this->id = $id;
		$this->username = $username;
		$this->email = $email;
	}

	public function hasPermission($permission, $element = null)
	{
		if ($this->id == -1)
		{
			return false;
		}

		if ($element != null)
		{
			if (in_array($element->prefix . $element->getID(), $moderate))
			{
				return true;
			}
			else if ($element instanceof Post)
			{
				if ($element->fields["User"] == $this->id)
				{
					return true;
				}
			}
			else if ($element instanceof Thread)
			{
				if ($element->getFirstPost()->fields["User"] == $this->id)
				{
					return true;
				}
			}
		}

		return $permission->default || hasPermission($permission, $element);
	}
	
	public function isRead($post)
	{
		if(in_array($post->getID(), $this->unreadPosts))
		{
			return false;
		}
		
		return true;
	}

}

?>
