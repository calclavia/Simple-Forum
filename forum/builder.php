<?php

/*
 * This page is to be included and is responsible for building all the forum layout into HTML.
 * Feel free to customize the layout to what you want or need.
 * @Calclavia
 */

function clean($string)
{
	return mysql_real_escape_string(trim($string));
}

/**
 * Returns all categories
 * @return string - The HTML content.
 */
function getAllCategories()
{
	$printContent = "
		<br />
		<div class='forum_menu'>
			<form method='post'>
				<input type='text' name='title'>
				<input type='submit' value='Add Category'>
			</form>
		</div>
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
			<a href='{$_SERVER['PHP_SELF']}?p=c{$category->getID()}&a=new'>Add Board</a> | 
			<a href='{$_SERVER['PHP_SELF']}?p=c{$category->getID()}&a=del'>Delete</a>
		</div>";

		$printContent .= "<table class='forum_table'><tr><td>Status</td><td>Board</td><td>Stats</td><td>Last Post</td></tr>";

		if (count($category->getChildren()) > 0)
		{
			foreach ($category->getChildren() as $board)
			{
				$stats = count($board->getPosts()) . " posts<br />" . $board->getViews() . " views";

				$latestPost = "No posts.";

				if ($board->getLatestPost()->fields["User"] != null)
				{
					$userdetails = fetchUserDetails(null, null, $board->getLatestPost()->fields["User"]);
					$latestPost = "<a href='{$_SERVER['PHP_SELF']}?p=t{$board->getLatestPost()->fields["Parent"]}'>Last post</a> by " . $userdetails["display_name"] . " on " . $board->getLatestPost()->getDate();
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
			}
		}
		else
		{
			$printContent .= "<tr class='forum_element'><td colspan='4'>No boards avaliable.</td></tr>";
		}

		$printContent .= "</table>";


		return $printContent;
	}
}

function getBoard($board)
{
	if ($board != null)
	{
		$printContent .= "
			<h2>" . $board->name . "</h2>
			<span class=\"forum_menu\">
				<a href='{$_SERVER['PHP_SELF']}'>Main</a> -> <a href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a> | 
				<a href=\"javascript:void(0)\" onclick = \"lightBox('newThread')\">Create Thread</a>
			</span>
			";

		$printContent .= "<table class='forum_table'><tr><td>Status</td><td>Thread</td><td>Stats</td><td>Last Post</td></tr>";

		if (count($board->getChildren()) > 0)
		{
			foreach ($board->getChildren() as $thread)
			{
				$stats = count($board->getPosts()) . " posts<br />" . $board->getViews() . " views";

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
					$latestPost = "<a href='{$_SERVER['PHP_SELF']}?p=t{$thread->getLatestPost()->fields["Parent"]}'>Last post</a> by " . $userdetails["display_name"] . " on " . $thread->getLatestPost()->getDate();
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
		}
		else
		{
			$printContent .= "<tr class='forum_element'><td colspan='4'>No threads avaliable.</td></tr>";
		}

		$printContent .= "</table>";

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
		$board = Board::getByID($thread->fields["Parent"]);
		
		$printContent .= "
		<div class='title'>" . $thread->name . "</div>
		<span class=\"forum_menu\">
			<a href='{$_SERVER['PHP_SELF']}'>Main</a> -> <a href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a> -> <a href='{$_SERVER['PHP_SELF']}?p=t{$thread->getID()}'>{$thread->name}</a> | 
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
				<a href=\"javascript:void(0)\" onclick = \"lightBox('editPost')\">Edit</a> |
				<a href='{$_SERVER['PHP_SELF']}?p=t{$post->fields["Parent"]}&d=p{$post->getID()}'>Remove</a>
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
	/**
	 * Check if it is the first post. If so, allow the editing of the title.
	 */
	if(Thread::getByID($post->fields["Parent"]) != null)
	{
	if(Thread::getByID($post->fields["Parent"])->getFirstPost() != null)
	{
		if(Thread::getByID($post->fields["Parent"])->getFirstPost()->getID() == $post->getID())
		{
			$additionalForm = "
				<table>
					<tr>
					<td>
						<b>Title:</b>
					</td></tr>
					<td>
						<input type='text' name='title' size='80' maxlength='80' value='".Thread::getByID($post->fields["Parent"])->name."'/>
					</td>
					</tr>
				</table>";
		}
	}}
	return "
	<div id='editPost' class='white_content'>
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
