<?php

/**
 * A category contains various forum boards within.
 * @author Calclavia
 */
class Category extends ForumElement
{

	function __construct($id, $name, $description)
	{
		$this->id = $id;
		$this->name = $name;

		$this->element_name = "categories";
		$this->fields["Description"] = $description;
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}categories (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Description TEXT)", $con) or die(mysql_error());
	}

	public static function getByID($id)
	{
		global $table_prefix;

		$result = mysql_query("SELECT * FROM {$table_prefix}categories WHERE ID={$id} LIMIT 1");

		$row = mysql_fetch_array($result);

		if ($row["ID"] <= 0)
		{
			return null;
		}
		else
		{
			return new Category($row["ID"], $row["Name"], $row["Description"]);
		}
	}

	public static function getAll()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}categories");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Category($row["ID"], $row["Name"], $row["Description"]);
		}

		return $returnArray;
	}

	public function createBoard($name, $description)
	{
		return new Board(-1, $this->id, $name, $description);
	}

	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}boards WHERE Parent={$this->id}");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Board($row["ID"], $row["Parent"], $row["Name"], $row["Description"]);
		}

		return $returnArray;
	}
}

?>