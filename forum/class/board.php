<?php

/**
 * A board can contain multiple threads and board within it.
 * @author Calclavia
 */
class Board extends ForumElement
{

	function __construct($id, $parent, $name, $description, $subBoard)
	{
		$this->id = $id;
		$this->name = $name;

		$this->element_name = "boards";
		$this->fields["Parent"] = $parent;
		$this->fields["Description"] = $description;
		$this->fields["SubBoard"] = $subBoard;
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}boards (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Description TEXT, SubBoard varchar(5))", $con) or die(mysql_error());
	}

	public static function getByID($id)
	{
		global $table_prefix;

		$result = mysql_query("SELECT * FROM {$table_prefix}boards
		WHERE ID={$id} LIMIT 1");

		$row = mysql_fetch_array($result);

		if ($row["ID"] <= 0)
		{
			return null;
		}
		else
		{
			return new Board($row["ID"], $row["Parent"], $row["Name"], $row["Description"], $row["SubBoard"]);
		}
	}

	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}threads WHERE Parent={$this->id}");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Thread($row["ID"], $row["Parent"], $row["Name"], $row["Time"], $row["Sticky"], $row["LockThread"], $row["Views"]);
		}
		
		$result = mysql_query("SELECT * FROM {$table_prefix}boards WHERE Parent={$this->id} AND SubBoard='yes'");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Board($row["ID"], $row["Parent"], $row["Name"], $row["Description"], $row["SubBoard"]);
		}

		return $returnArray;
	}

	public function createThread($name)
	{
		return new Thread(-1, $this->id, $name, "no", "no", 1);
	}
	
	public function createBoard($name, $description)
	{
		return new Board(-1, $this->id, $name, $description, "yes");
	}

	public function getPosts()
	{
		$posts = array();
		
		$threads = $this->getChildren();

		foreach ($threads as $thread)
		{
			$posts = array_merge($posts, $thread->getChildren());
		}
		
		return $posts;
	}
	
	public function getViews()
	{
		$views = 0;
		
		$threads = $this->getChildren();

		foreach ($threads as $thread)
		{
			$views += $thread->fields["Views"];
		}
		
		return $views;
	}

	public function getLatestPost()
	{
		$threads = $this->getChildren();

		if (count($threads) > 0)
		{
			$latestThread = $threads[0];

			foreach ($threads as $thread)
			{
				if ($thread->getLatestPost()->fields["Time"] > $latestThread->getLatestPost()->fields["Time"])
				{
					$latestThread = $thread;
				}
			}

			return $latestThread->getLatestPost();
		}

		return null;
	}

	public function getTreeAsString()
	{
		$tree = "";

		$board = $this;

		while ($board != null && $board instanceof Board && $board->fields["SubBoard"] == "yes")
		{
			$tree = " -> <a href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a>" . $tree;
			$board = Board::getByID(intval($board->fields["Parent"]));
		}

		$tree = "<a href='{$_SERVER['PHP_SELF']}'>Main</a>" . $tree;
		return $tree;
	}
}

?>