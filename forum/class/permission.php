<?php

class Permission
{

	public $id;
	public $name;
	public $description;
	public $default;

	function __construct($id, $name, $description, $default = false)
	{
		$this->$id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->default = $default;
	}

}

$permission = array();

//Category Permissions
$permission["category_create"] = $create_categories = new Permission(0, "Create Categories",
		"Allows the user to create categories.");
$permission["category_edit"] = $edit_categories = new Permission(1, "Edit Categories",
		"Allows the user to edit categories.");
$permission["delete_delete"] = $delete_categories = new Permission(2, "Delete Categories",
		"Allows the user to delete categories.");

//Board Permissions
$permission["board_create"] = $create_boards = new Permission(3, "Create Boards",
		"Allows the user to create boards.");
$permission["board_edit"] = $edit_boards = new Permission(4, "Edit Boards",
		"Allows the user to edit boards.");
$permission["board_delete"] = $delete_boards = new Permission(5, "Delete Boards",
		"Allows the user to delete boards.");

//Thread Permissions
$permission["thread_create"] = $create_threads = new Permission(6, "Create Threads",
		"Allows the user to create threads.", true);
$permission["thread_edity"] = $topic_sticky = new Permission(7, "Edit Threads",
		"Allows the user to edit thread titles.");
$permission["thread_sticky"] = $topic_sticky = new Permission(8, "Sticky",
		"Allows the user to make threads sticky.");
$permission["thread_lock"] = new Permission(9, "Lock", "Allows the user to lock threads.");

//Post Permissions
$permission["post_create"] = $create_posts = new Permission(10, "Create Posts",
		"Allows the user to posts.", true);
$permission["post_edit"] = $edit_posts = new Permission(11, "Edit Posts",
		"Allows the user to edit all posts.");
$permission["post_delete"] = $delete_posts = new Permission(12, "Delete Posts",
		"Allows the user to delete posts.");

//Miscs
$permission["signature_edit"] = $edit_siganture = new Permission(13, "Edit Signature",
		"Allows the user to edit their signature.");
$permission["board_hide"] = new Permission(14, "View Hidden Board",
		"Allows the user to access hidden boards.");
?>