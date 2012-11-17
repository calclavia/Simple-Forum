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
		}
	}

	public function createBoard($user, $name, $description)
	{
		global $permission;

		if ($user->hasPermission($permission["board_create"]))
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
				if ($user->hasPermission($permission["category_edit"], $this))
				{
					$title = "
                        <div class='category_title'>
                            <h2 class='quick_edit' name='c{$this->getID()}' data-type='ajax' style='display:inline; margin-right:5px;' contenteditable='true'>
                                    {$this->name}
                            </h2>
                        </div>";
				}
				else
				{
					$title = "<h2 id='category{$this->getID()}' class='category_title'>{$this->name}</h2>";
				}

				$printContent = "
                    <div style='margin-bottom: 15px;'>
                        <div class='elements_container'>
                            <span class='forum_menu'>";

				if ($user->hasPermission($permission["category_edit"], $this))
				{
					$printContent .= "<a href=\"{$_SERVER['PHP_SELF']}?&o=c{$this->getID()}\" class='btn_small btn_silver btn_flat'>&darr;</a> ";
				}

				if ($user->hasPermission($permission["board_create"], $this))
				{
					$printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('newBoard{$this->getID()}')\" class=\"btn_small btn_silver btn_flat\">+ Board</a> ";
				}

				if ($user->hasPermission($permission["category_delete"], $this))
				{
					$printContent .= "<a href='{$_SERVER['PHP_SELF']}?d=c{$this->getID()}' class=\"btn_small btn_silver btn_flat\">Delete</a>";
				}

				$printContent .= "</span>$title<div class='clear'></div>";

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

				if ($user->hasPermission($permission["board_create"]))
				{
					$printContent .= $this->printNewBoardForm();
				}

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
            <div id='newBoard{$this->getID()}' class='white_content'>
                 	<h1>New Board</h1>
                    <form action='{$_SERVER['PHP_SELF']}?p=c{$this->getID()}&a=new' method='post'>
                            <table>
                                    <tr><td>
                                    <b>Title:</b>
                                    </td><td>
                                    <input type='text' name='title' size='80' maxlength='80'/>
                                    </td></tr>
                            </table>
                            <textarea id='editableContentNewBoard{$this->getID()}' name='editableContent' wrap=\"virtual\" style=\"width:550px; height:200px\"></textarea>
                            <br />
                            <input type='submit' value='Post'/>					
                    </form>
            </div>";
	}

}

?>