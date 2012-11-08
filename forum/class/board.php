<?php

/**
 * A board can contain multiple threads and board within it.
 * @author Calclavia
 */
class Board extends ForumElement
{

    function __construct($id, $parent, $order, $name, $description, $subBoard)
    {
        $this->id = $id;
        $this->name = $name;

        $this->element_name = "boards";
        $this->prefix = "b";

        $this->fields["Parent"] = $parent;
        $this->fields["ForumOrder"] = $order;
        $this->fields["Description"] = $description;
        $this->fields["SubBoard"] = $subBoard;
    }

    public static function setUp($con)
    {
        global $table_prefix;

        mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}boards (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, ForumOrder int, Description TEXT, SubBoard varchar(5))", $con) or die(mysql_error());
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
            return new Board($row["ID"], $row["Parent"], $row["ForumOrder"], $row["Name"], $row["Description"], $row["SubBoard"]);
        }
    }

    public function getChildren()
    {
        global $table_prefix;

        $threads = array();

        $result = mysql_query("SELECT * FROM {$table_prefix}threads WHERE Parent={$this->id}");

        while ($row = mysql_fetch_array($result))
        {
            $threads[] = new Thread($row["ID"], $row["Parent"], $row["Name"], $row["Sticky"], $row["LockThread"], $row["Views"]);
        }

        usort($threads, function($a, $b)
                {
                    if ($a->fields["Sticky"] == "yes" && $b->fields["Sticky"] == "no")
                    {
                        return -1;
                    }
                    else if ($a->fields["Sticky"] == "no" && $b->fields["Sticky"] == "yes")
                    {
                        return 1;
                    }

                    return $a->getLatestPost()->fields["Time"] < $b->getLatestPost()->fields["Time"];
                });

        $result = mysql_query("SELECT * FROM {$table_prefix}boards WHERE Parent={$this->id} AND SubBoard='yes'");

        $boards = array();

        while ($row = mysql_fetch_array($result))
        {
            $boards[] = new Board($row["ID"], $row["Parent"], $row["ForumOrder"], $row["Name"], $row["Description"], $row["SubBoard"]);
        }
        
        usort($boards, function($a, $b)
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

        return array_merge($boards, $threads);
    }

    public function createThread($user, $name, $content, $time = -1, $con = false)
    {
        global $create_threads;

        if ($user != null)
        {
            if ($user->hasPermission($create_threads))
            {
                if ($time > 0 && $con && !empty($content))
                {
                    $thread = $this->createThread($user, $name);
                    $thread->save($con);

                    $post = $thread->createPost($content, $user->id, $time);
                    $post->save($con);
                }
                else
                {
                    return new Thread(-1, $this->id, $name, "no", "no", 1);
                }
            }
        }
    }

    public function createBoard($user, $name, $description)
    {
        global $create_boards;

        if ($user->hasPermission($create_boards))
        {
            return new Board(-1, $this->id, -1, $name, $description, "yes");
        }
        return null;
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

        foreach ($this->getChildren() as $child)
        {
            if ($child instanceof Thread)
            {
                $views += intval($child->fields["Views"]);
            }
            else if ($child instanceof Board)
            {
                $views += $child->getViews();
            }
        }

        return intval($views);
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

        while ($board != null && $board instanceof Board)
        {
            $tree = " -> <a href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a>" . $tree;

            if ($board->fields["SubBoard"] == "yes")
            {
                $board = Board::getByID(intval($board->fields["Parent"]));
            }
            else
            {
                $board = null;
            }
        }

        $tree = "<a href='{$_SERVER['PHP_SELF']}'>Main</a>" . $tree;
        return $tree;
    }

    public function edit($name, $description)
    {
        $this->name = $name;
        $this->fields["Description"] = $description;
    }
    
    public function move($user, $id, $con)
    {
    	global $edit_boards;
    
    	if($user->hasPermission($edit_boards, $this))
    	{
    		if ($id == $this->id)
    		{
    			$id = -1;
    		}
    		
    		$newParent = Board::getByID($id)->fields["Parent"];
    		
    		if(Category::getByID($newParent) != null)
    		{
    			$this->fields["Parent"] = $newParent;
    		}
    		
    		$this->fields["ForumOrder"] = $id;
    		$this->save($con);
    	}
    }

}

?>