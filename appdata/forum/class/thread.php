<?php

/**
 * A thread contains various posts within it.
 * @author Calclavia
 */
class Thread extends ForumElement
{

	function __construct($id, $parent, $name)
	{
		$this->id = $id;
		$this->name = $name;

		$this->element_name = "threads";
		$this->fields["Parent"] = $parent;
		$this->fields["Sticky"] = "yes";
		$this->fields["LockThread"] = "no";
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE {$table_prefix}threads (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Sticky varchar(5), LockThread varchar(5))", $con) or die(mysql_error());
	}
	
	public static function getByID($id)
	{
		global $table_prefix;

		$result = mysql_query("SELECT * FROM {$table_prefix}threads
		WHERE ID={$id} LIMIT 1");

		$row = mysql_fetch_array($result);

		if ($row["ID"] <= 0)
		{
			return null;
		}
		else
		{
			return new Thread($row["ID"], $row["Parent"], $row["Name"], $row["Time"]);
		}
	}

	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}posts WHERE Parent={$this->id}");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Post($row["ID"], $row["Parent"], $row["Name"], $row["Content"], $row["User"], $row["Time"]);
		}

		return $returnArray;
	}

	public function createPost($content, $userID, $time)
	{
		return new Post(-1, $this->id, $this->name, $content, $userID, $time);
	}

}

?>