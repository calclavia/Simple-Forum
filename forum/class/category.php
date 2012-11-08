<?php

/**
 * A category contains various forum boards within.
 * @author Calclavia
 */
class Category extends ForumElement
{

	function __construct($id, $name, $order, $hidden)
	{
		$this->id = $id;
		$this->name = $name;

		$this->element_name = "categories";
		$this->prefix = "c";

		$this->fields["ForumOrder"] = $order;
		$this->fields["Hidden"] = $hidden;
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}categories (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), ForumOrder int, Hidden varchar(5))", $con) or die(mysql_error());
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
			return new Category($row["ID"], $row["Name"], $row["ForumOrder"], $row["Hidden"]);
		}
	}

	public static function getAll()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}categories");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Category($row["ID"], $row["Name"], $row["ForumOrder"], $row["Hidden"]);
		}

		usort($returnArray, function($a, $b)
		{
			if ($a->fields["ForumOrder"] == $b->fields["ForumOrder"] || $a->fields["ForumOrder"] == -1)
			{
				return -1;
			}

			if ($b->fields["ForumOrder"] == $a->getID())
			{
				return -1;
			}

			return 1;
		});

		return $returnArray;
	}

	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}boards WHERE Parent={$this->id} AND SubBoard='no'");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Board($row["ID"], $row["Parent"], $row["ForumOrder"], $row["Name"], $row["Description"], $row["SubBoard"]);
		}
		
		usort($returnArray, function($a, $b)
		{
			if ($a->fields["ForumOrder"] == $b->fields["ForumOrder"] || $a->fields["ForumOrder"] == -1)
			{
				return -1;
			}
		
			if ($b->fields["ForumOrder"] == $a->getID())
			{
				return -1;
			}
		
			return 1;
		});

		return $returnArray;
	}

	public function edit($user, $title, $con = null)
	{
		global $edit_categories;

		if ($user->hasPermission($edit_categories))
		{
			$this->name = $title;
		}

		if($con != null)
		{
			$this->save($con);
		}
	}

	public function createBoard($user, $name, $description)
	{
		global $create_boards;

		if ($user->hasPermission($create_boards))
		{
			return new Board(-1, $this->id, -1, $name, $description, "no");
		}

		return null;
	}

	public function move($user, $id, $con)
	{
		global $edit_categories;

		if($user->hasPermission($edit_categories, $this))
		{
			if ($id == $this->id)
			{
				$id = -1;
			}

			$this->fields["ForumOrder"] = $id;
			$this->save($con);
		}
	}

}

?>