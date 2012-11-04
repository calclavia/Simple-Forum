<?php

/**
 * A board can contain multiple threads and board within it.
 * @author Calclavia
 */
class Board extends ForumElement
{

	function __construct($id, $parent, $name, $description)
	{
		$this->id = $id;
		$this->name = $name;

		$this->element_name = "boards";
		$this->fields["Parent"] = $parent;
		$this->fields["Description"] = $description;
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}boards (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Description TEXT)", $con) or die(mysql_error());
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
			return new Board($row["ID"], $row["Parent"], $row["Name"], $row["Description"]);
		}
	}
	
	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}threads WHERE Parent={$this->id}");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Thread($row["ID"], $row["Parent"], $row["Name"], $row["Time"]);
		}
		
		return $returnArray;
	}
	
	public function createThread($name)
	{
		return new Thread(-1, $this->id, $name);
	}
	
	public function getLatestPost()
	{
		$threads = $this->getChildren();
		
		if(count($threads) > 0)
		{
			$latestThread = $threads[0];

			foreach($threads as $thread)
			{
				if($thread->getLastestPost()->fields["Time"] > $latestThread->getLastestPost()->fields["Time"])
				{
					$latestThread = $thread;
				}
			}
			
			return $latestThread->getLastestPost();
		}
		
		return null;
	}
}

?>