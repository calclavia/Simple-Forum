<?php

abstract class ProcessRequest
{
	/**
	 * @param ForumUser $user - The current user doing this request.
	 */
	public $user;

	/**
	 * @param ForumElement $element - The element the request is being done to.
	 */
	public $element;

	/**
	 * @param Array $data - An array of data to be proccessed.
	 */
	public $data = array();

	public $con;

	function __construct($user, $element, $data, $con = null)
	{
		$this->user = $user;
		$this->element = $element;
		$this->data = $data;
		$this->con = $con;
	}

	public static function processRequest($currentUser, $request_type, $elementID, $con)
	{
		if ($request_type == 1)
		{
			return (new NewBoard($currentUser, Category::getByID(intval($elementID)), array($_POST["data1"], $_POST["data2"], $_POST["data3"]), $con))->request();
		}
		else if ($request_type == 2)
		{
			return (new NewBoard($currentUser, Board::getByID(intval($elementID)), array($_POST["data1"], $_POST["data2"], $_POST["data3"]), $con))->request();
		}
		else if ($request_type == 3)
		{
			return (new EditThread($currentUser, $elementID, array($_POST["data1"], $_POST["data2"], $_POST["data3"]), $con))->request();
		}
		else if ($request_type == 4)
		{
			return (new EditCategory($currentUser, $elementID, array($_POST["data1"]), $con))->request();
		}

		return false;
	}

	public function request()
	{
		if ($this->user != null && $this->element != null)
		{
			return $this->doRequest();
		}
		return "Failed to process request.";
	}

	protected abstract function doRequest();
}

class EditCategory extends ProcessRequest
{
	function __construct($user, $elementID, $data, $con)
	{
		parent::__construct($user, Category::getByID(intval($elementID)), $data, $con);
	}

	public function doRequest()
	{
		$this->data[0] = clean($this->data[0], true);

		if (!empty($this->data[0]))
		{
			if ($category->edit($this->user, $this->data[0], $this->con))
			{
				return "Edited category name to: " . $this->data[0];
			}
			else
			{
				return "Failed to edit category";
			}
		}

		return "Invalid category name: ".$this->data[0];
	}
}

class EditThread extends ProcessRequest
{
	function __construct($user, $elementID, $data, $con)
	{
		parent::__construct($user, Thread::getByID(intval($elementID)), $data, $con);
	}

	public function doRequest()
	{
		$this->data[0] = clean($this->data[0], true);

		if (!empty($this->data[0]))
		{
			$this->element->editTitle($this->user, $this->data[0]);
			$this->element->stickThread($this->user, ($this->data[1] == "true" ? true : false));
			$this->element->lockThread($this->user, ($this->data[2] == "true" ? true : false));
			$this->element->save($this->con);
			return "Edited Thread!";
		}
		return "Invalid thread name: ".$this->data[0];
	}
}

class NewBoard extends ProcessRequest
{
	public function doRequest()
	{
		global $permission;

		$this->data[0] = clean($this->data[0], true);
		$this->data[1] = clean($this->data[1], true);

		if (!empty($this->data[0]))
		{
			$board = $this->element->createBoard($this->user, $this->data[0], $this->data[1]);

			if ($board != null)
			{
				$board->save($this->con);

				$this->user->moderate($board);
				$this->user->save($this->con);

				foreach (explode(",", strtolower($this->data[2]) . ",") as $username)
				{
					$user = getUserByUsername(trim($username));

					if ($user != null)
					{
						if ($user->id > 0)
						{
							$user->moderate($board);
							$user->save($this->con);
						}
					}
				}

				return "Created new board!";
			}
		}

		return "Failed to create a new board.";
	}
}

/**
 * Process different GET and POST submitted actions.
 * a = Adding
 * e = Editing
 * d = Deleting
 * o = Reordering
 * m = Moving
 */

$order = $_GET["o"];

if (!empty($order))
{
	if (strstr($order, "c"))
	{
		$category = Category::getByID(intval(str_replace("c", "", $order)));

		if ($category != null)
		{
			$category->moveDown($currentUser, $con);
		}
	}
	else if (strstr($order, "b"))
	{
		$board = Board::getByID(intval(str_replace("b", "", $order)));

		if ($board != null)
		{
			$board->moveDown($currentUser, $con);
		}
	}

	header("Location: forum.php");
	die();
}

if (!empty($_GET["e"]))
{
	if (strstr($_GET["e"], "c"))
	{
		$title = clean($_GET["data"], true);

		$category = Category::getByID(intval(str_replace("c", "", $_GET["e"])));

		if ($category != null && !empty($title))
		{
			$category->edit($currentUser, $title, $con);
		}
	}
	else if (strstr($_GET["e"], "b"))
	{
		$title = clean($_GET["data"], true);
		$content = clean($_GET["content"], true);

		$board = Board::getByID(intval(str_replace("b", "", $_GET["e"])));

		if ($board != null && !empty($title))
		{
			$board->edit($title, $content);
			$board->save($con);
		}
	}
	else if (strstr($_GET["e"], "t"))
	{
		$title = clean($_GET["data"], true);

		if (!empty($title))
		{
			$thread = Thread::getByID(intval(str_replace("t", "", $_GET["e"])));

			if ($thread != null)
			{
				if ($thread != null)
				{
					if ($_GET["sticky"])
					{
						$sticky = "yes";
					}
					else
					{
						$sticky = "no";
					}

					if ($_GET["lock"])
					{
						$lockTopic = "yes";
					}
					else
					{
						$lockTopic = "no";
					}

					$thread->edit($currentUser, $title, $sticky, $lockTopic);
					$thread->save($con);
				}

				$_GET["p"] = "t" . $thread->getID();
			}
		}
	}
}

if (!empty($_GET["d"]))
{
	if (strstr($_GET["d"], "c"))
	{
		$category = Category::getByID(intval(str_replace("c", "", $_GET["d"])));

		if ($category != null)
		{
			if ($currentUser->hasPermission($delete_categories, $category))
			{
				$category->delete($con);
				$successes[] = "Removed category: " . $category->name;
			}
		}
	}
	else if (strstr($_GET["d"], "b"))
	{
		$board = Board::getByID(intval(str_replace("b", "", $_GET["d"])));

		if ($board != null)
		{
			if ($currentUser->hasPermission($delete_boards, $board))
			{
				$board->delete($con);
				$successes[] = "Removed board: " . $board->name;
			}
		}
	}
	else if (strstr($_GET["d"], "p"))
	{
		$post = Post::getByID(intval(str_replace("p", "", $_GET["d"])));

		if ($post != null)
		{
			if ($currentUser->hasPermission($delete_posts, $post))
			{
				$thread = Thread::getByID($post->fields["Parent"]);

				if ($post->getID() == $thread->getFirstPost()->getID())
				{
					$thread = Thread::getByID($post->fields["Parent"]);
					$thread->delete($con);
					$successes[] = "Removed thread: " . $thread->name;
				}
				else
				{
					$successes[] = "Removed post from thread: " . $post->name;
					$post->delete($con);
				}
			}
		}
	}

	header("Location: " . $_SERVER['PHP_SELF']);
	die();
}

$request_type = $_POST["ajax"];

/**
 * If this is an Ajax request, then print out the results.
 */
if (!empty($request_type))
{

	require_once("../models/config.php");
	require_once("config.php");

	/**
	 * Data being received from the Ajax request.
	 */
	$data = html_entity_decode($_POST["data"]);

	if ($data == "true")
	{
		$data = true;
	}
	else if ($data == "false")
	{
		$data = false;
	}

	$edit = $_POST["e"];

	$request = ProcessRequest::processRequest($currentUser, $request_type, $_POST["element"], $con);

	if ($request)
	{
		$successes[] = $request;
	}
	else if (!empty($edit))
	{
		if (strstr($edit, "c"))
		{
			$title = clean($data, true);

			$category = Category::getByID(intval(str_replace("c", "", $edit)));

			if ($category != null && !empty($title))
			{
				$category->edit($currentUser, $title, $con);
				$successes[] = "Changed category name to: " . $title;
			}
		}
		else if (strstr($edit, "b"))
		{
			$data = clean($data, true);
			$board = Board::getByID(intval(str_replace("b", "", $edit)));

			if ($board != null && !empty($data))
			{
				if ($request_type == "title")
				{
					$board->editTitle($currentUser, $data);
					$successes[] = "Changed board name to: " . $board->name;
				}
				else if ($request_type == "description")
				{
					$board->editDescription($currentUser, $data);
					$successes[] = "Changed board description to: " . $board->fields["Description"];
				}

				$board->save($con);
			}
		}
		else if ($request_type == "post_edit")
		{
			$post = Post::getByID(intval($edit));
			$data = clean($data);

			if ($post != null && !empty($data))
			{
				$post->edit($data, $currentUser, time());
				$post->save($con);
				$successes[] = "Edited Post!";
			}
		}
		else if ($request_type == "signature")
		{
			$data = clean($data);

			$user = getUserByID($edit);

			if ($user != null && $user instanceof ForumUser && !empty($data))
			{
				if ($currentUser->hasPermission($permission["signature_edit"]) || $currentUser->id == $user->id)
				{
					$user->editSignature($data, $con);
					$successes[] = "Changed signature to: " . $data;
				}
			}
		}
	}

	if (count($successes) > 0)
	{
		echo json_encode($successes);
	}
	else
	{
		echo json_encode(array("Invalid " . $request_type . " Request: " . $edit . ", " . strip_tags($data)));
	}
}
?>