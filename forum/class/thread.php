<?php

/**
 * A thread contains various posts within it.
 * @author Calclavia
 */
class Thread extends ForumElement
{

	function __construct($id, $parent, $name, $sticky, $lockThread, $views)
	{
		$this->id = $id;
		$this->name = stripslashes(str_replace("\\r\\n", "", $name));

		$this->element_name = "threads";
		$this->prefix = "t";

		$this->fields["Parent"] = $parent;
		$this->fields["Sticky"] = $sticky;
		$this->fields["LockThread"] = $lockThread;
		$this->fields["Views"] = $views;
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
			return new Thread($row["ID"], $row["Parent"], $row["Name"], $row["Sticky"], $row["LockThread"], $row["Views"]);
		}
	}

	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}posts WHERE Parent={$this->id}");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Post($row["ID"], $row["Parent"], $row["Name"], $row["Content"], $row["User"], $row["Time"], $row["LastEditTime"], $row["LastEditUser"]);
		}

		uasort($returnArray, function($a, $b)
		{
			return $a->fields["Time"] > $b->fields["Time"];
		});

		return $returnArray;
	}

	public function createPost($content, $user, $time, $con)
	{
		if ($this->fields["LockThread"] != "yes")
		{
			$post = new Post(-1, $this->id, $this->name, $content, $user->id, $time, $time, $userID);
			$user->createPost($post, $con);
			return $post;
		}

		return null;
	}

	public function getFirstPost()
	{
		$threads = $this->getChildren();

		if (count($threads) > 0)
		{
			$earliestThread = $threads[0];

			foreach ($threads as $thread)
			{
				if ($thread->fields["Time"] < $earliestThread->fields["Time"])
				{
					$earliestThread = $thread;
				}
			}

			return $earliestThread;
		}

		return null;
	}

	public function getLatestPost()
	{
		$posts = $this->getChildren();

		if (count($posts) > 0)
		{
			$latestThread = $posts[0];

			foreach ($posts as $thread)
			{
				if ($thread->fields["Time"] > $latestThread->fields["Time"])
				{
					$latestThread = $thread;
				}
			}

			return $latestThread;
		}

		return null;
	}

	public function getTreeAsString()
	{
		return Board::getByID(intval($this->fields["Parent"]))->getTreeAsString() . " -> <a href='{$_SERVER['PHP_SELF']}?p=t{$this->getID()}'>{$this->name}</a>";
	}

	public function view($user, $con)
	{
		$user->read($this, $con);
		 
		$this->fields["Views"]++;
		$this->save($con);
	}

	public function edit($user, $name, $sticky, $lockThread)
	{
		global $edit_threads;
		 
		if($user->hasPermission($edit_threads))
		{
			$this->name = $name;
			$this->fields["Sticky"] = $sticky;
			$this->fields["LockThread"] = $lockThread;
		}
	}

}

?>