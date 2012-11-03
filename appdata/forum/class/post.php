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

		$this->element_name = "threads";
		$this->fields["Parent"] = $parent;
		$this->fields["User"] = $userID;
		$this->fields["Content"] = $content;
		$this->fields["Time"] = $time;
	}
	function getDate()
	{
		return date("F j, Y, g:i a", $this->timePosted);
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE {$table_prefix}posts (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, User int, Time int)", $con) or die(mysql_error());
	}

}

?>