<?php

class Permission
{
	public static $permissions = array();
	
	public $id;
	public $name;
	public $description;
	
	function __construct($id, $name, $description)
	{
		$this->name = $name;
		$this->description = $description;
		self::$permissions[$id] = $this;
	}
}

?>