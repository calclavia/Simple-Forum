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
	
	public function hasPermission($element, $permission)
	{
		if(in_array($element->prefix.$element->getID(), $moderate))
		{
			return true;
		}
		
		return $permission->default;
	}
}

?>
