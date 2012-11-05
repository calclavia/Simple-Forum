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
$delete_categories = new Permission(3, "Delete Categories", "Allows the user to delete categories.");

$create_boards = new Permission(4, "Create Boards", "Allows the user to create boards.");
$edit_boards = new Permission(5, "Edit Boards", "Allows the user to create boards.");
$delete_boards = new Permission(6, "Delete Boards", "Allows the user to create boards.");

$create_threads = new Permission(7, "Create Threads", "Allows the user to create threads.", true);

$create_posts = new Permission(8, "Create Posts", "Allows the user to create posts.", true);
$edit_posts = new Permission(9, "Create Posts", "Allows the user to create posts.");
$delete_posts = new Permission(10, "Create Posts", "Allows the user to create posts.");

?>