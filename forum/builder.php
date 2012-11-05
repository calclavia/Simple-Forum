<?php

/*
 * This page is to be included and is responsible for building all the forum layout into HTML.
 * Feel free to customize the layout to what you want or need.
 * @Calclavia
 */

function clean($string, $veryClean = false)
{
	if ($veryClean)
	{
		return mysql_real_escape_string(htmlspecialchars(trim($string)));
	}
	else
	{
		return mysql_real_escape_string(trim($string));
	}
}

/**
 * Returns all categories
 * @return string - The HTML content.
 */
function getAllCategories()
{
	$printContent = "
		<br />
		<form  action='{$_SERVER['PHP_SELF']}?a=new' method='post'>
			<input type='text' name='title'>
			<input type='submit' value='Add Category'>
		</form>
		<br/><br/><br/>";

	$categories = Category::getAll();

	foreach ($categories as $category)
	{
		$printContent .= getCategory($category) . "</br></br>";
	}

	return $printContent;
}

/**
 * @param Category $category - The Category Class.
 * @return string - The HTML content.
 */
function getCategory($category)
{
	if ($category != null)
	{
		$printContent = "
		<div class='title'>" . $category->name . "</div>
		<div class='forum_menu'>
			<a href=\"javascript:void(0)\" onclick = \"lightBox('newBoard{$category->getID()}')\">Add Board</a> | 
			<a href=\"javascript:void(0)\" onclick = \"lightBox('editCategory{$category->getID()}')\">Edit Category</a> | 
			<a href='{$_SERVER['PHP_SELF']}?d=c{$category->getID()}'>Delete</a>
		</div>";

		$printContent .= "<table class='forum_table'><tr><td>Status</td><td>Board</td><td>Stats</td><td>Last Post</td></tr>";

		if (count($category->getChildren()) > 0)
		{
			foreach ($category->getChildren() as $board)
			{
				$printContent .= getSingleBoard($board);
			}
		}
		else
		{
			$printContent .= "<tr class='forum_element'><td colspan='4'>No boards avaliable.</td></tr>";
		}

		$printContent .= "</table>";
		$printContent .= getEditCategoryForm($category);
		$printContent .= getNewBoardForm($category);


		return $printContent;
	}
}

function getSingleBoard($board)
{
	if ($board != null)
	{
		$stats = count($board->getPosts()) . " posts<br />" . $board->getViews() . " views";

		$latestPost = "No posts.";

		if ($board->getLatestPost()->fields["User"] != null)
		{
			$userdetails = fetchUserDetails(null, null, $board->getLatestPost()->fields["User"]);
			$latestPost = "Last post <a href='{$_SERVER['PHP_SELF']}?p=t{$board->getLatestPost()->fields["Parent"]}'>\"" . $board->getLatestPost()->name . "\"</a> by " . $userdetails["display_name"] . " on " . $board->getLatestPost()->getDate();
		}

		$printContent .= "
		<tr class='forum_element'>
			<td class='read_status'>
				<img src='forum/img/off.png'/>
			</td>
			<td class='element_content'>
				<a class='title_link' href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a>
				<br/>
				{$board->fields["Description"]}
			</td>
			<td class='element_stats'>
				$stats
			</td>
			<td>
				$latestPost
			</td>
		</tr>";

		return $printContent;
	}
}

function getEditCategoryForm($category)
{
	return "
	<div id='editCategory{$category->getID()}' class='white_content'>
		<h1>Edit Post</h1>
		<form action='{$_SERVER['PHP_SELF']}?e=c{$category->getID()}' method='post'>
			<b>Category Name:</b> <input type='text' name='title' size='80' maxlength='80' value='{$category->name}'/>
			<input type='submit' value='Edit'/>					
		</form>
	</div>";
}

function getNewBoardForm($parent)
{
	if ($parent instanceof Category)
	{
		return "
		<div id='newBoard{$parent->getID()}' class='white_content'>
			<h1>New Board</h1>
			<form action='{$_SERVER['PHP_SELF']}?p=c{$parent->getID()}&a=new' method='post'>
				<table>
					<tr><td>
					<b>Title:</b>
					</td><td>
					<input type='text' name='title' size='80' maxlength='80'/>
					</td></tr>
				</table>
				<textarea id='editableContentNewBoard{$parent->getID()}' name='editableContent' wrap=\"virtual\" style=\"width:550px; height:200px\"></textarea>
				<br />
				<input type='submit' value='Post'/>					
			</form>
		</div>";
	}
	else if ($parent instanceof Board)
	{
		return "
		<div id='newBoard{$parent->getID()}' class='white_content'>
			<h1>New Board</h1>
			<form action='{$_SERVER['PHP_SELF']}?p=b{$parent->getID()}&a=new' method='post'>
				<table>
					<tr><td>
					<b>Title:</b>
					</td><td>
					<input type='text' name='board_name' size='80' maxlength='80'/>
					</td></tr>
				</table>
				<br />
				<textarea id='editableContentNewBoard{$parent->getID()}' name='editableContent' wrap=\"virtual\" style=\"width:550px; height:200px\"></textarea>
				<input type='submit' value='Post'/>					
			</form>
		</div>";
	}
}

function getEditBoardForm($board)
{
	return "
	<div id='editBoard{$board->getID()}' class='white_content'>
		<h1>Edit Board</h1>
		<form action='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}&e=b{$board->getID()}' method='post'>
			<table>
				<tr><td>
				<b>Name:</b>
				</td><td>
				<input type='text' name='title' size='80' maxlength='80' value='{$board->name}'/>
				</td></tr>
			</table>
			<textarea id='editableContentEditBoard{$board->getID()}' name='editableContent' wrap=\"virtual\"  style=\"width:550px; height:200px\">{$board->fields["Description"]}</textarea>
			<input type='submit' value='Edit'/>					
		</form>
	</div>";
}

function getBoard($board)
{
	if ($board != null)
	{
		$printContent .= "
			<h2>" . $board->name . "</h2>
			<span>" . $board->getTreeAsString() . "</span>
			<span class=\"forum_menu\">
				<a href=\"javascript:void(0)\" onclick = \"lightBox('newBoard{$board->getID()}')\">Add Board</a> | 
				<a href=\"javascript:void(0)\" onclick = \"lightBox('editBoard{$board->getID()}')\">Edit Board</a> | 
				<a href=\"javascript:void(0)\" onclick = \"lightBox('newThread')\">Create Thread</a>
			</span>
			";

		$printContent .= "<table class='forum_table'><tr><td>Status</td><td>Thread</td><td>Stats</td><td>Last Post</td></tr>";

		if (count($board->getChildren()) > 0)
		{
			foreach ($board->getChildren() as $child)
			{
				if ($child instanceof Thread)
				{
					$thread = $child;

					$stats = count($thread->getChildren()) . " posts<br />" . $thread->fields["Views"] . " views";

					$threadOwner = "Annoymous";

					if ($thread->getFirstPost()->fields["User"] != null)
					{
						$userdetails = fetchUserDetails(null, null, $thread->getFirstPost()->fields["User"]);
						$threadOwner = $userdetails["display_name"];
					}

					$latestPost = "No posts.";

					if ($thread->getLatestPost()->fields["User"] != null)
					{
						$userdetails = fetchUserDetails(null, null, $thread->getLatestPost()->fields["User"]);
						$latestPost = "Last post <a href='{$_SERVER['PHP_SELF']}?p=t{$thread->getLatestPost()->fields["Parent"]}'>\"" . $thread->getLatestPost()->name . "\" by " . $userdetails["display_name"] . " on " . $thread->getLatestPost()->getDate();
					}

					$printContent .= "
					<tr class='forum_element'>
						<td class='read_status'><img src='forum/img/off.png'/></td>
						<td class='element_content'>
							<a class='title_link' href='{$_SERVER['PHP_SELF']}?p=t{$thread->getID()}'>{$thread->name}</a>
							<br/>
							Started by $threadOwner</td>
						<td class='element_stats'>
							$stats
						</td>
						<td>
							$latestPost
						</td>
					</tr>";
				}
				else if ($child instanceof Board)
				{
					$printContent .= getSingleBoard($child);
				}
			}
		}
		else
		{
			$printContent .= "<tr class='forum_element'><td colspan='4'>No threads avaliable.</td></tr>";
		}

		$printContent .= "</table>";
		$printContent .= getNewBoardForm($board);
		$printContent .= getEditBoardForm($board);

		return $printContent;
	}
}

function getNewThreadForm($board)
{
	return "
	<div id='newThread' class='white_content'>
		<h1>New Thread</h1>
		<form action='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}' method='post'>
			<table>
				<tr><td>
				<b>Title:</b>
				</td><td>
				<input type='text' name='title' size='80' maxlength='80'/>
				</td></tr>
			</table>
			<textarea id='editableContentNewThread' name='editableContent' wrap=\"virtual\"></textarea>
			<script type='text/javascript'>
				CKEDITOR.replace('editableContentNewThread', {height:'300'});
			</script>
			<input type='submit' value='Post'/>					
		</form>
	</div>";
}

function getThread($thread)
{
	if ($thread != null)
	{
		$printContent .= "
		<h2>" . $thread->name . "</h2>
		<span>" . $thread->getTreeAsString() . "</span>
		<span class=\"forum_menu\">
			<a href=\"javascript:void(0)\" onclick = \"lightBox('newPost')\">Create Post</a>
		</span>";

		$printContent .= "<table class='forum_table' border='1'>";

		if (count($thread->getChildren()) > 0)
		{
			foreach ($thread->getChildren() as $post)
			{
				$userdetails = fetchUserDetails(null, null, $post->fields["User"]);

				$printContent .= "
				<tr><td class='post_profile'>
				<a href='http://www.gravatar.com/' target='_blank'>
					<img src='http://www.gravatar.com/avatar/" . md5($userdetails["email"]) . "?d=mm&s=160'/>
				</a>
				<br/>
				<b><a rel='t{$post->getID()}'>{$userdetails["display_name"]}</a></b>
				<br />
				{$userdetails["title"]}
				</br>
				<a href=\"javascript:void(0)\" onclick = \"lightBox('editPost{$post->getID()}')\">Edit Post</a> |
				<a href='{$_SERVER['PHP_SELF']}?p=b{$post->fields["Parent"]}&d=p{$post->getID()}'>Remove Post</a>
				<br />
				<small>Posted on {$post->getDate()}</small></td>
				<td class='forum_content'>{$post->fields["Content"]}</td></tr>";

				$printContent .= getEditPostForm($post);
			}
		}
		else
		{
			$printContent .= "<tr class='forum_element'><td colspan='4'>No posts avaliable.</td></tr>";
		}

		$printContent .= "</table>";

		return $printContent;
	}
}

function getNewPostForm($thread)
{
	return "
	<div id='newPost' class='white_content'>
		<h1>New Post</h1>
		<form action='{$_SERVER['PHP_SELF']}?p=t{$thread->getID()}&a=new' method='post'>
			<textarea id='editableContentNewPost' name='editableContent' wrap=\"virtual\"></textarea>
			<script type='text/javascript'>
				CKEDITOR.replace('editableContentNewPost', {height:'300'});
			</script>
			<input type='submit' value='Post'/>					
		</form>
	</div>";
}

function getEditPostForm($post)
{
	global $currentUser;

	/**
	 * Check if it is the first post. If so, allow the editing of the title.
	 */
	if (Thread::getByID($post->fields["Parent"]) != null)
	{
		if (Thread::getByID($post->fields["Parent"])->getFirstPost() != null)
		{
			if (Thread::getByID($post->fields["Parent"])->getFirstPost()->getID() == $post->getID())
			{
				$isChecked = "";

				if ($post->fields["LockThread"] == "yes")
				{
					$isChecked = "checked='checked'";
				}
				
				$additionalForm = "
				<b>Title:</b> <input type='text' name='title' size='80' maxlength='80' value='" . Thread::getByID($post->fields["Parent"])->name . "'/>
				<br />
				<input type='checkbox' name='lockTopic' value='Lock Topic' {$isChecked}> Lock Topic
				<br />";

				if ($currentUser->hasPermission($topic_sticky))
				{
					$isChecked = "";

					if ($post->fields["Sticky"] == "yes")
					{
						$isChecked = "checked='checked'";
					}

					$additionalForm .= "
					<input type='checkbox' name='sticky' value='Stick Topic' {$isChecked}> Stick Topic
					<br />
					";
				}
			}
		}
	}
	return "
	<div id='editPost{$post->getID()}' class='white_content'>
		<h1>Edit Post</h1>
		<form action='{$_SERVER['PHP_SELF']}?p=t{$post->fields["Parent"]}&e=p{$post->getID()}' method='post'>
			{$additionalForm}
			<textarea id='editableContentEditPost{$post->getID()}' name='editableContent' wrap=\"virtual\">{$post->fields["Content"]}</textarea>
			<script type='text/javascript'>
				CKEDITOR.replace('editableContentEditPost{$post->getID()}', {height:'300'});
			</script>
			<input type='submit' value='Edit'/>					
		</form>
	</div>";
}

?>
