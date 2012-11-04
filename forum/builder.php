<?php

/*
 * This page is to be included and is responsible for building all the forum layout into HTML.
 * Feel free to customize the layout to what you want or need.
 * @Calclavia
 */

function clean($string)
{
	return trim($string);
}

/**
 * Returns all categories
 * @return string - The HTML content.
 */
function getAllCategories()
{
	$printContent = "";

	$categories = Category::getAll();

	foreach ($categories as $category)
	{
		$printContent .= getCategory($category);
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


		$printContent .= "<table class='forum_table'><tr><td>Status</td><td>Board</td><td></td><td>Last Post</td></tr>";

		if (count($category->getChildren()) > 0)
		{
			foreach ($category->getChildren() as $board)
			{
				$printContent .= "
				<tr class='forum_element'>
					<td class='read_status'>
						<img src='forum/img/off.png'/>
					</td>
					<td>
						<a href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a>
						<br/>
						{$board->fields["Description"]}
					</td>
					<td>
					</td>
					<td>Last Post</td>
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

function getThread($thread)
{
	$printContent .= "
		<div class='title'>" . $thread->name . "</div>
		<div class='forum_menu'>
			<a href='{$_SERVER['PHP_SELF']}?p=t{$thread->getID()}&a=new'>Create Post</a>
		</div>";

	$printContent .= "<table class='forum_table'>";

	if (count($thread->getChildren()) > 0)
	{
		foreach ($thread->getChildren() as $post)
		{
			$userdetails = fetchUserDetails(null, null, $post->fields["User"]);

			$printContent .= "
				<tr><td class='forum_post'>
				<img src='http://www.gravatar.com/avatar/" . md5($userdetails["email"]) . "?d=mm&s=160'/>
				<br/>
				<b><a rel='t{$post->getID()}'>{$userdetails["display_name"]}</a></b>
				<br />
				{$userdetails["title"]}
				</br>
				<a href='{$_SERVER['PHP_SELF']}?p=p{$post->getID()}&a=edit'>Edit</a> |
				<a href='{$_SERVER['PHP_SELF']}?p=p{$post->getID()}&a=del'>Remove</a>
				<br />
				<small>Posted on {$post->getDate()}</small></td>
				<td>{$post->fields["Content"]}</td></tr>";
		}
	}
	else
	{
		$printContent .= "<tr class='forum_element'><td colspan='4'>No posts avaliable.</td></tr>";
	}

	$printContent .= "</table>";
}
?>
