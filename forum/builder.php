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
        return mysql_real_escape_string(htmlspecialchars(strip_tags(trim($string))));
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
function getAllCategories($user)
{
    global $create_categories;

    $printContent = "";

    if ($user->hasPermission($create_categories))
    {
        $printContent .= "
			<div class='forum_menu'>
				<form  action='{$_SERVER['PHP_SELF']}?a=new' method='post'>
					<input type='text' name='title'>
					<input type='submit' value='Add Category'>
				</form>
			</div><br />";
    }

    $categories = Category::getAll();

    for ($i = 0; $i < count($categories); $i++)
    {
        $printContent .= getCategory($user, $categories[$i], $i);
    }
    
    return $printContent;
}

/**
 * @param Category $category - The Category Class.
 * @return string - The HTML content.
 */
function getCategory($user, $category, $i)
{
    global $edit_categories, $delete_categories, $create_boards;
    
    $categories = Category::getAll();

    if ($category != null)
    {
        if ($category->fields["Hidden"] != "yes")
        {
        	if ($user->hasPermission($edit_categories, $category))
        	{
        		$categoryTitle = "
        		<div>
	        		<h2 class='inlineEdit' style='display:inline; margin-right:5px;' contenteditable='true'>
        				{$category->name}
	        		</h2>
	        		<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?e=c{$category->getID()}&data='+encodeURI($(this).prev('.inlineEdit').html())\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
        			";
        			
        			if($categories[$i+1])
        			{
        				$categoryTitle .= "<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?p=c{$category->getID()}&o=c{$categories[$i+1]->getID()}'\" class='inline_form tsc_awb_small tsc_awb_silver tsc_flat'>&darr;</a>";
        			}
        			
        			if($i > 1)
        			{
        				if($categories[$i-2])
        				{
        					$categoryTitle .= "<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?p=c{$category->getID()}&o=c{$categories[$i-2]->getID()}'\" class='inline_form tsc_awb_small tsc_awb_silver tsc_flat'>&uarr;</a>";
        				}
        			}
        			
        		$categoryTitle .= "</div><div class='clear'></div>";
        	}
        	else
        	{
        		$categoryTitle = "
		        	<div id='c{$category->getID()}'>
						<h2 id='category{$category->getID()}' style='display:inline'>{$category->name}</h2>
					</div>
        			";
        	}
        		
            $printContent = "
            <div style='margin-bottom: 15px;'>
            $categoryTitle
			<div class='forum_menu'>";

            if ($user->hasPermission($create_boards, $category))
            {
                $printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('newBoard{$category->getID()}')\" class=\"tsc_awb_small tsc_awb_white tsc_flat\">+ Board</a> ";
            }

            if ($user->hasPermission($delete_categories, $category))
            {
                $printContent .= "<a href='{$_SERVER['PHP_SELF']}?d=c{$category->getID()}' class=\"tsc_awb_small tsc_awb_white tsc_flat\">Delete</a>";
            }

            $printContent .= "</div><div class='clear'></div>";

            $printContent .= "<div class='elements_container'>";

            if (count($category->getChildren()) > 0)
            {
                foreach ($category->getChildren() as $board)
                {
                    $printContent .= $board->printBoard($user);
                }
            }
            else
            {
                $printContent .= "No boards avaliable.";
            }

            $printContent .= "</div>";

            if ($user->hasPermission($create_boards))
            {
                $printContent .= getNewBoardForm($category);
            }


            return $printContent."</div>";
        }
    }
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
				<b>Title:</b<br />
				<input type='text' name='board_name' size='80' maxlength='80'/>
				<br />
				<textarea id='editableContentNewBoard{$parent->getID()}' name='editableContent' wrap=\"virtual\" style=\"width:550px; height:200px\"></textarea>
				<br/>
				<input type='submit' value='Post'/>					
			</form>
		</div>";
    }
}

function getNewThreadForm($board)
{
    return "
	<div id='newThread' class='white_content'>
		<h1>New Thread</h1>
		<form action='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}&a=new' method='post'>
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

function getUserProfile($user)
{
	return "
	<a href='http://www.gravatar.com/' target='_blank'>
		<img src='http://www.gravatar.com/avatar/" . md5($user->email) . "?d=mm&s=160'/>
	</a>
	<br/>
	<b>{$user->username}</b>
	<br />
    {$user->title}
    <br />
    {$user->posts} Post(s)";
}
?>
