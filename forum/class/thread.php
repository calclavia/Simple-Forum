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
		/*
		$this->fields["Views"] = 0;
		 */
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}threads (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Sticky varchar(5), LockThread varchar(5), Views int)", $con) or die(mysql_error());
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
			return new Thread($row["ID"], $row["Parent"], $row["Name"]);
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
		
		uasort($returnArray, function($a, $b){ return $a->fields["Time"] > $b->fields["Time"];});

		return $returnArray;
	}

	public function createPost($content, $userID, $time)
	{
		return new Post(-1, $this->id, $this->name, $content, $userID, $time);
	}
	
	public function getFirstPost()
	{
		$threads = $this->getChildren();
		
		if(count($threads) > 0)
		{
			$earliestThread = $threads[0];

			foreach($threads as $thread)
			{
				if($thread->fields["Time"] < $earliestThread->fields["Time"])
				{
					$earliestThread = $thread;
				}
			}
			
			return $earliestThread;
		}
		
		return null;
	}
	
	public function getLastestPost()
	{
		$posts = $this->getChildren();
		
		if(count($posts) > 0)
		{
			$latestThread = $posts[0];

			foreach($posts as $thread)
			{
				if($thread->fields["Time"] > $latestThread->fields["Time"])
				{
					$latestThread = $thread;
				}
			}
			
			return $latestThread;
		}
		
		return null;
	}
}

?>