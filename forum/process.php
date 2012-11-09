<?php

/**
 * Process different GET and POST submitted actions.
 * a = Adding
 * e = Editing
 * d = Deleting
 * o = Reordering
 * m = Moving
 */

if (!empty($_GET["o"]))
{
	if (strstr($_GET["p"], "c") && strstr($_GET["o"], "c"))
	{
		$category = Category::getByID(intval(str_replace("c", "", $_GET["p"])));

		if ($category != null)
		{
			$category->move($currentUser, str_replace("c", "", $_GET["o"]), $con);
		}
	}
	else if (strstr($_GET["p"], "b") && strstr($_GET["o"], "b"))
	{
		$board = Board::getByID(intval(str_replace("b", "", $_GET["p"])));

		if ($board != null)
		{
			$board->move($currentUser, str_replace("b", "", $_GET["o"]), $con);
		}
	}

	header("Location: forum.php");
	die();
}

if (!empty($_GET["e"]))
{
	if (strstr($_GET["e"], "c") && $_POST["title"])
	{
		$category = Category::getByID(intval(str_replace("c", "", $_GET["e"])));

		if ($category != null)
		{
			$category->edit(clean($_POST["title"]), $con);
		}
	}
	else if (strstr($_GET["e"], "b") && $_POST["title"])
	{
		$board = Board::getByID(intval(str_replace("b", "", $_GET["e"])));

		if ($board != null)
		{
			$board->edit(clean($_POST["title"]), clean($_POST["editableContent"], true));
			$board->save($con);
		}
	}
	else if (strstr($_GET["e"], "t"))
	{
		$title = clean($_GET["data"], true);

		if(!empty($title))
		{
			$thread = Thread::getByID(intval(str_replace("t", "", $_GET["e"])));

			if($thread != null)
			{
				if($thread != null)
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

				$_GET["p"] = "t".$thread->getID();
			}
		}
	}
	else if (strstr($_GET["e"], "p"))
	{
		$post = Post::getByID(intval(str_replace("p", "", $_GET["e"])));
		$content = clean($_GET["data"]);

		if ($post != null)
		{
			$thread = Thread::getByID($post->fields["Parent"]);

			if(!empty($content))
			{
				$post->edit($content, $currentUser, time());
				$post->save($con);
			}

			if($thread != null)
			{
				$_GET["p"] = "t".$thread->getID();
			}
		}
	}
	else if (strstr($_GET["e"], "u") && !empty($_GET["signature"]))
	{
		$user = getUserByID(str_replace("u", "", $_GET["e"]));

		if($user != null && $user instanceof ForumUser)
		{
			if($currentUser->hasPermission($edit_siganture))
			{
				$user->editSignature(clean($_GET["signature"]), $con);
			}
		}
	}

	header("Location: forum.php?p=".$_GET["p"]);
	die();
}

if (!empty($_GET["d"]))
{
	if (strstr($_GET["d"], "c"))
	{
		$category = Category::getByID(intval(str_replace("c", "", $_GET["d"])));

		if ($category != null)
		{
			if($currentUser->hasPermission($delete_categories, $category))
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
			if($currentUser->hasPermission($delete_boards, $board))
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
			if($currentUser->hasPermission($delete_posts, $post))
			{
				if ($post->getID() == Thread::getByID($post->fields["Parent"])->getFirstPost()->getID())
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
}

?>