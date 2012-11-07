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
$topic_promote = new Permission(1, "Promote", "Allows the user to make promote topics.");

$create_categories = new Permission(2, "Create Categories", "Allows the user to create categories.");
$edit_categories = new Permission(3, "Edit Categories", "Allows the user to edit categories.");
$delete_categories = new Permission(4, "Delete Categories", "Allows the user to delete categories.");

$create_boards = new Permission(5, "Create Boards", "Allows the user to create boards.");
$edit_boards = new Permission(6, "Edit Boards", "Allows the user to edit boards.");
$delete_boards = new Permission(7, "Delete Boards", "Allows the user to delete boards.");

$create_threads = new Permission(8, "Create Threads", "Allows the user to create threads.", true);

$create_posts = new Permission(9, "Create Posts", "Allows the user to create posts.", true);
$edit_posts = new Permission(10, "Edit Posts", "Allows the user to create posts.");
$edit_posts_any = new Permission(11, "Edit anyones Posts", "Allows the user to edit anyones posts. Excluding Admin posts");
$delete_posts = new Permission(12, "Delete Posts", "Allows the user to delete posts.");
$delete_posts_any = new Permission(13, "Delete anyones Posts", "Allows the user to delete posts. of any user Excluding Admin posts ");

$edit_news = new Permission(14, "Edit News", "Allows the user to edit the New Section.");

$view_Board_admin = new Permission(15, "View admin board", "Allows the user to see and post in the admin catergory.");
$view_board_dev = new Permission(16, "View dev only board", "Allows the user to see and view the dev only category.");


?>