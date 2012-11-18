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

	public static function getAll($con)
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}categories");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Category($row["ID"], $row["Name"], $row["ForumOrder"], $row["Hidden"]);
		}

		usort($returnArray, function ($a, $b)
		{
			if ($a->fields["ForumOrder"] > $b->fields["ForumOrder"])
			{
				return 1;
			}

			return -1;
		});

		$i = 0;

		foreach ($returnArray as $category)
		{
			$category->fields["ForumOrder"] = $i;
			$category->save($con);
			$i++;
		}

		return $returnArray;
	}

	public function getChildren()
	{
		$returnArray = $this->getChildrenUnsorted();

		usort($returnArray, function ($a, $b)
		{
			if ($a->fields["ForumOrder"] > $b->fields["ForumOrder"])
			{
				return 1;
			}

			return -1;
		});

		$i = 0;

		foreach ($returnArray as $board)
		{
			$board->fields["ForumOrder"] = $i;
			$board->save(null);
			$i++;
		}

		return $returnArray;
	}

	public function getChildrenUnsorted()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}boards WHERE Parent={$this->id} AND SubBoard='no'");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Board($row["ID"], $row["Parent"], $row["ForumOrder"], $row["Name"], $row["Description"], $row["SubBoard"]);
		}

		return $returnArray;
	}

	public function edit($user, $title, $con = null)
	{
		global $permission;

		if ($user->hasPermission($permission["category_edit"]))
		{
			$this->name = $title;
		}

		if ($con != null)
		{
			$this->save($con);
			return true;
		}
		
		return false;
	}

	public function createBoard($user, $name, $description)
	{
		global $permission;

		if ($user->hasPermission($permission["board_create"], $this))
		{
			return new Board(-1, $this->id, -1, $name, $description, "no");
		}

		return null;
	}

	public function moveDown($user, $con)
	{
		global $permission;

		if ($user->hasPermission($permission["category_edit"], $this))
		{
			$categories = Category::getAll($con);

			if ($this->fields["ForumOrder"] >= count($categories) - 1)
			{
				$this->fields["ForumOrder"] = -1;
			}
			else
			{
				foreach ($categories as $category)
				{
					if ($category->fields["ForumOrder"] == $this->fields["ForumOrder"] + 1)
					{
						$category->fields["ForumOrder"]--;
						$category->save($con);
					}
					else if ($category->fields["ForumOrder"] > $this->fields["ForumOrder"])
					{
						$category->fields["ForumOrder"]++;
						$category->save($con);
					}
				}

				$this->fields["ForumOrder"]++;
			}
			$this->save($con);
		}
	}

	/**
	 * @return string - The HTML content.
	 */
	public function printCategory($user, $i)
	{
		global $permission;

		$categories = Category::getAll($con);

		if ($this != null)
		{
			if ($this->fields["Hidden"] != "yes")
			{

				$printContent = "
                    <div class='category'>
                        <div class='forum_menu'>";

				if ($user->hasPermission($permission["category_edit"], $this))
				{
					$printContent .= "<a href=\"{$_SERVER['PHP_SELF']}?&o=c{$this->getID()}\" class='btn_small btn_silver btn_flat'>&darr;</a> ";
				}

				if ($user->hasPermission($permission["category_edit"], $this))
				{
					$printContent .= "<a href=\"javascript:void(0)\" data-forum-target='{$this->getID()}' class='category_edit btn_small btn_silver btn_flat'>Edit</a> ";
				}

				if ($user->hasPermission($permission["board_create"], $this))
				{
					$printContent .= "<a href=\"javascript: $('#newBoard{$this->getID()}').stop(true, true).slideToggle();\" class=\"btn_small btn_silver btn_flat\">+ Board</a> ";
				}

				if ($user->hasPermission($permission["category_delete"], $this))
				{
					$printContent .= "<a href='{$_SERVER['PHP_SELF']}?d=c{$this->getID()}' class=\"btn_small btn_silver btn_flat\">Delete</a>";
				}

				$printContent .= "</div>";

				$printContent .= "<h2 id='category_title_{$this->getID()}' class='editable_title category_title'>{$this->name}</h2>";

				$printContent .= "<div class='clear'></div><div class='elements_container'>";

				if ($user->hasPermission($permission["board_create"]))
				{
					$printContent .= $this->printNewBoardForm();
				}

				if (count($this->getChildren()) > 0)
				{
					foreach ($this->getChildren() as $board)
					{
						$printContent .= $board->printBoard($user);
					}
				}
				else
				{
					$printContent .= "No boards avaliable.";
				}

				$printContent .= "</div>";

				return $printContent . "</div><div class='clear'></div>";
			}
		}
	}

	/**
	 * Returns all categories
	 * @return string - The HTML content.
	 */
	public static function printAll($user)
	{
		$printContent = "";

		$categories = Category::getAll($con);

		for ($i = 0; $i < count($categories); $i++)
		{
			$printContent .= $categories[$i]->printCategory($user, $i);
		}

		return $printContent;
	}

	public function printNewBoardForm()
	{
		return "
		<div id='newBoard{$this->getID()}' style='display:none;' class='forum_element drop-shadow'>
	    		<div class='two_third'>
	    			<span class='icon_on'></span>
	    			<div class='board_content'>
	    				<h3 class='element_title' id='title_{$this->getID()}' contenteditable='true'>Title</h3>
	    				<div class='element_text' id='content_{$this->getID()}' contenteditable='true'>Enter your description for the board.</div>
	    			</div>
	    		</div>
	    		<div class='forum_element_info one_third column-last'>
	    			Moderators (separate by comma):<br />
	    			<input type='text' id='moderators_{$this->getID()}' value=''><br />
	    			<a href=\"javascript: void(0);\" data-element='{$this->getID()}' class=\"new_board btn_small btn_silver btn_flat\">Confirm</a>
	    		</div>
            <div class='clear'></div>
	   	</div>";
	}

}

?>