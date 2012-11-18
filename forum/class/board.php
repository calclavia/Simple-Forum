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
		$this->name = stripslashes(str_replace("\\r\\n", "", $name));

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

		usort($threads, function ($a, $b)
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

		$boards = $this->getBoardsUnsorted();

		usort($boards, function ($a, $b)
		{
			if ($a->fields["ForumOrder"] > $b->fields["ForumOrder"])
			{
				return 1;
			}

			return -1;
		});

		$i = 0;

		foreach ($boards as $board)
		{
			$board->fields["ForumOrder"] = $i;
			$board->save(null);
			$i++;
		}

		return array_merge($boards, $threads);
	}

	public function getBoardsUnsorted()
	{
		global $table_prefix;

		$result = mysql_query("SELECT * FROM {$table_prefix}boards WHERE Parent={$this->id} AND SubBoard='yes'");

		$boards = array();

		while ($row = mysql_fetch_array($result))
		{
			$boards[] = new Board($row["ID"], $row["Parent"], $row["ForumOrder"], $row["Name"], $row["Description"], $row["SubBoard"]);
		}

		return $boards;
	}

	public function createThread($user, $name, $content = "", $time = -1, $con = false)
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

					$post = $thread->createPost($content, $user, $time, $con);
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
		global $permission;

		if ($user->hasPermission($permission["board_create"], $this))
		{
			return new Board(-1, $this->id, -1, $name, $description, "yes");
		}

		return null;
	}

	/**
	 * @return array Returns all the posts in this board.
	 */
	public function getPosts()
	{
		$posts = array();

		$elements = $this->getChildren();

		foreach ($elements as $element)
		{
			$posts = array_merge($posts, $element->getPosts());
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

	/**
	 * @return Post|NULL - Returns the most recent post on this board.
	 */
	public function getLatestPost()
	{
		$posts = $this->getPosts();

		if (count($posts) > 0)
		{
			$latestPost = $posts[0];

			foreach ($posts as $post)
			{
				if ($post->fields["Time"] > $latestPost->fields["Time"])
				{
					$latestPost = $post;
				}
			}

			return $latestPost;
		}

		return null;
	}

	/**
	 * @return string - A unordered list as a breadcrum of the tree of this board.
	 */
	public function getTreeAsString()
	{
		$tree = "";

		$elements = array();

		$board = $this;

		while ($board != null && $board instanceof Board)
		{
			$elements[] = $board;

			if ($board->fields["SubBoard"] == "yes")
			{
				$board = Board::getByID(intval($board->fields["Parent"]));
			}
			else
			{
				$board = null;
			}
		}

		for ($i = 0; $i < count($elements); $i++)
		{
			$element = $elements[$i];

			if ($i == count($elements))
			{
				$tree = "<li><a href='{$_SERVER['PHP_SELF']}?p=b{$element->getID()}' class='current'>" . limitString($element->name, 30) . "</a></li>" . $tree;
			}
			else
			{
				$tree = "<li><a href='{$_SERVER['PHP_SELF']}?p=b{$element->getID()}'>" . limitString($element->name, 30) . "</a></li>" . $tree;
			}
		}

		$tree = "<ul class='breadcrumb'><li><a href='{$_SERVER['PHP_SELF']}'>Main</a></li>" . $tree . "</ul>";
		return $tree;
	}

	public function editTitle($user, $title)
	{
		global $permission;

		if ($user->hasPermission($permission["board_edit"], $this))
		{
			$this->name = $title;
			return true;
		}

		return false;
	}

	public function editDescription($user, $description)
	{
		global $permission;

		if ($user->hasPermission($permission["board_edit"], $this))
		{
			$this->fields["Description"] = $description;
			return true;
		}
		return false;
	}

	public function moveDown($user, $con)
	{
		global $permission;

		if ($user->hasPermission($permission["board_edit"], $this->getParent()))
		{
			if ($this->fields["SubBoard"] == "yes")
			{
				$boards = Board::getByID($this->fields["Parent"])->getBoardsUnsorted();

			}
			else
			{
				$boards = Category::getByID($this->fields["Parent"])->getChildrenUnsorted();

			}
			if ($this->fields["ForumOrder"] >= count($boards) - 1)
			{
				$this->fields["ForumOrder"] = -1;
			}
			else
			{
				foreach ($boards as $board)
				{
					if ($board->fields["ForumOrder"] == $this->fields["ForumOrder"] + 1)
					{
						$board->fields["ForumOrder"]--;
						$board->save($con);
					}
					else if ($board->fields["ForumOrder"] > $this->fields["ForumOrder"])
					{
						$board->fields["ForumOrder"]++;
						$board->save($con);
					}
				}

				$this->fields["ForumOrder"]++;
			}
			$this->save($con);
		}
	}

	public function isUnread($user)
	{
		foreach ($this->getChildren() as $child)
		{
			if ($child->isUnread($user))
			{
				return true;
			}
		}

		return false;
	}

	public function getParent()
	{
		return ($this->fields["SubBoard"] == "yes" ? Board::getByID($this->fields["Parent"]) : Category::getByID($this->fields["Parent"]));
	}

	/**
	 * Prints out the board as a forum block.
	 * @param ForumUser $user - The current user.
	 * @return string To be printed.
	 */
	public function printBoard($user)
	{
		global $posts_per_page, $permission;

		/**
		 * Display the stats.
		 */
		$stats = count($this->getPosts()) . " post(s) " . $this->getViews() . " view(s)";

		$printLatestPost = "No posts.";

		$latestPost = $this->getLatestPost();

		if ($latestPost != null)
		{
			$latestPostUser = getUserByID($latestPost->fields["User"]);
			$thread = Thread::getByID($latestPost->fields["Parent"]);

			if ($latestPostUser != null && $thread != null)
			{
				$printLatestPost = "Lastest: <a href='{$_SERVER['PHP_SELF']}?p=t" . $thread->getID() . "&page=" . ceil(count($thread->getPosts()) / $posts_per_page) . "#" . $latestPost->getID() . "'>" . limitString($latestPost->name) . "</a><br /> By: <b>" . limitString($latestPostUser->username, 20) . "</b>, " . $latestPost->getDate() . ".";
			}
		}

		$subBoards = "";

		foreach ($this->getChildren() as $child)
		{
			if ($child instanceof Board)
			{
				$subBoards .= "<li><a href='{$_SERVER['PHP_SELF']}?p=b{$child->getID()}'>{$child->name}</a></li>";
			}
		}

		if (!empty($subBoards))
		{
			$subBoards = "<ul>Sub-Boards: " . $subBoards . "</ul>";
		}

		if ($user->hasPermission($permission['board_edit'], $this->getParent()))
		{
			$orderHTML = "<a href=\"{$_SERVER['PHP_SELF']}?&o=b{$this->getID()}\" class='btn_small btn_silver btn_flat'>&darr;</a>";
		}

		return "
	    	<div class='board_box forum_element drop-shadow'>
	    		<div class='two_third'>
	    			<span class='" . ($this->isUnread($user) ? "icon_on" : "icon_off") . "'></span>
	    			<div class='board_content'>
	    				<h3 class='element_title'><a href='{$_SERVER['PHP_SELF']}?p=b{$this->getID()}'>{$this->name}</a> $orderHTML</h3>
	    				<div class='element_text'>
	    					<span>{$this->fields["Description"]}</span>
	    					<div class='forum_element_info'>$stats</div>
	    				</div>
	    			</div>
	    		</div>
	    		<div class='forum_element_info one_third column-last'>
	    			<p>$printLatestPost</p>
	    			<div class='sub_boards'>
	    				$subBoards
	    				<div class='clear'></div>
	    			</div>
	    		</div>
                <div class='clear'></div>
	    	</div>";
	}

	public function printBoardContent($user)
	{
		global $permission;

		$printContent = "<div class='board'><div class=\"forum_menu\">";

		if ($user->hasPermission($permission["board_edit"], $this))
		{
			$printContent .= "<a href=\"javascript:void(0)\" data-forum-target='{$this->getID()}' class='board_edit btn_small btn_white btn_flat'>Edit</a> ";
		}

		if ($user->hasPermission($permission["thread_create"], $this))
		{
			$printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('newThread')\" class=\"btn_small btn_white btn_flat\">+ Thread </a> ";
		}

		if ($user->hasPermission($permission["board_create"], $this))
		{
			$printContent .= "<a href=\"javascript:void(0)\" data-forum-target='{$this->getID()}' class=\"new_board_button btn_small btn_white btn_flat\">+ Board</a> ";
		}

		if ($user->hasPermission($permission["board_delete"], $this))
		{
			$printContent .= "<a href=\"javascript: if(confirm('Delete Board and ALL content within?')) {window.location='{$_SERVER['PHP_SELF']}?d=b{$this->getID()}'}\" class=\"btn_small btn_white btn_flat\">Delete</a>";
		}

		$printContent .= "
		</div>
		<h2 class='editable_title header_title' id='board_title_{$this->getID()}'>{$this->name}</h2>
		<div class='editable_title' id='board_description_{$this->getID()}'>{$this->fields["Description"]}</div>
		<div class='clear'></div>
		<div class='elements_container'>" . $this->getTreeAsString();

		if ($user->hasPermission($permission["board_create"], $this))
		{
			$printContent .= $this->printNewBoardForm();
		}

		if (count($this->getChildren()) > 0)
		{
			foreach ($this->getChildren() as $child)
			{
				if ($child instanceof Board)
				{
					$printContent .= $child->printBoard($user);
				}
			}

			$printContent .= "<div class='clear'></div></div><div class='elements_container'>";

			foreach ($this->getChildren() as $child)
			{
				if ($child instanceof Thread)
				{
					$printContent .= $child->printThread($user);
				}
			}
		}
		else
		{
			$printContent .= "<div class='forum_element'>No threads and boards avaliable.</div>";
		}

		$printContent .= $this->getTreeAsString() . "<div class='clear'></div></div></div>";

		return $printContent;
	}

	public function printNewThreadForm()
	{
		return "
		<div id='newThread' class='white_content'>
			<h1>New Thread</h1>
			<form action='{$_SERVER['PHP_SELF']}?p=b{$this->getID()}&a=new' method='post'>
				<table>
					<tr><td>
					<b>Title:</b>
					</td><td>
					<input type='text' name='title' size='80' maxlength='80'/>
					</td></tr>
				</table>
				<textarea id='editableContentNewThread' name='editableContent' wrap=\"virtual\"></textarea>
				<script type='text/javascript'>
					CKEDITOR.replace('editableContentNewThread', {height:'200'});
				</script>
				<input type='submit' value='Post'/>					
			</form>
		</div>";
	}

	public function printNewBoardForm()
	{
		return "
		<div id='newBoard_{$this->getID()}' style='display:none;' class='forum_element drop-shadow'>
	    		<div class='two_third'>
	    			<span class='icon_on'></span>
	    			<div class='board_content'>
	    				<h3 class='element_title' id='title_{$this->getID()}''>Title</h3>
	    				<div class='element_text' id='content_{$this->getID()}'>Enter your description for the board.</div>
	    			</div>
	    		</div>
	    		<div class='forum_element_info one_third column-last'>
	    			Moderators (separate by comma):<br />
	    			<input type='text' id='moderators_{$this->getID()}' value=''><br />
	    			<a href=\"javascript: void(0);\" data-element='{$this->getID()}' class=\"new_subboard btn_small btn_silver btn_flat\">Confirm</a>
	    		</div>
            <div class='clear'></div>
	   	</div>";
	}
}

?>