<?php

/**
 * A post is what a user will post in a thread.
 * @author Calclavia
 */
class Post extends ForumElement
{

	function __construct($id, $parent, $name, $content, $userID, $time)
	{
		$this->id = $id;
		$this->name = $name;

		$this->element_name = "posts";
		$this->fields["Parent"] = $parent;
		$this->fields["User"] = $userID;
		$this->fields["Content"] = $content;
		$this->fields["Time"] = $time;

		/**
		 * $this->fields["LastEditTime"] = $time;
		 * $this->fields["LastEditUser"] = $time;
		 */
	}

	function getDate()
	{
		return date("F j, Y, g:i a", $this->fields["Time"]);
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}posts (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Content TEXT, User int, Time int)", $con) or die(mysql_error());
	}
	
	public static function getByID($id)
	{
		global $table_prefix;

		$result = mysql_query("SELECT * FROM {$table_prefix}posts
		WHERE ID={$id} LIMIT 1");

		$row = mysql_fetch_array($result);

		if ($row["ID"] <= 0)
		{
			return null;
		}
		else
		{
			return new Post($row["ID"], $row["Parent"], $row["Name"], $row["Content"], $row["User"], $row["Time"]);
		}
	}

	public function getChildren()
	{
		return null;
	}
}

?>