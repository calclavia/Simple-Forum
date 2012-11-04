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
	$printContent = "
		<span class='category_title'>" . $category->name . "</span>
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

?>
