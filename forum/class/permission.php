<?php

class Permission
{

	public static $permissions = array();
	public $id;
	public $name;
	public $description;
	public $default;

	function __construct($id, $name, $description, $default = false)
	{
		$this->name = $name;
		$this->description = $description;
		$this->default = $default;
		self::$permissions[$id] = $this;
	}
}

$topic_sticky = new Permission(0, "Sticky", "Allows the user to make topics sticky.");

$create_categories = new Permission(1, "Create Categories", "Allows the user to create categories.");
$edit_categories = new Permission(2, "Edit Categories", "Allows the user to edit categories.");

$create_boards = new Permission(3, "Create Boards", "Allows the user to create boards.");
$create_threads = new Permission(4, "Create Threads", "Allows the user to create threads.");
$create_posts = new Permission(5, "Create Posts", "Allows the user to create posts.");


?>