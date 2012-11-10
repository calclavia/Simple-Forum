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
			</div>";
    }

    $categories = Category::getAll();

    foreach ($categories as $category)
    {
        $printContent .= getCategory($user, $category);
        
        if($user->hasPermission($edit_categories, $category))
        {
        	$dropData = "ondrop=\"drop(event, 'c{$category->getID()}')\" ondragover='allowDrop(event)'";
    	}
    	
    	$printContent .= "<div style='height:60px; width: 100%;' {$dropData}></div>";
    }
    
    return $printContent;
}

/**
 * @param Category $category - The Category Class.
 * @return string - The HTML content.
 */
function getCategory($user, $category)
{
    global $edit_categories, $delete_categories, $create_boards;

    if ($category != null)
    {
        if ($category->fields["Hidden"] != "yes")
        {
        	if($user->hasPermission($edit_categories, $category))
        	{
        		$dropData = "class='draggable' draggable='true' ondragstart=\"drag(event, 'c{$category->getID()}')\"";
        	}
        	
            $printContent = "
            <span id='c{$category->getID()}' $dropData>
				<h2 id='category{$category->getID()}' style='display:inline'>{$category->name}</h2>
				<span class='dragText'>Drag to Reorder</span>
			</span>
			
			<div class='forum_menu'>";

            if ($user->hasPermission($create_boards, $category))
            {
                $printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('newBoard{$category->getID()}')\">Add Board</a> | ";
            }

            if ($user->hasPermission($edit_categories, $category))
            {
                $printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('editCategory{$category->getID()}')\">Edit Category</a> | ";
            }

            if ($user->hasPermission($delete_categories, $category))
            {
                $printContent .= "<a href='{$_SERVER['PHP_SELF']}?d=c{$category->getID()}'>Delete</a>";
            }

            $printContent .= "</div>";

            $printContent .= "<table class='forum_table'><tr><td>Status</td><td>Board</td><td>Stats</td><td>Last Post</td></tr>";

            if (count($category->getChildren()) > 0)
            {
                foreach ($category->getChildren() as $board)
                {
                    $printContent .= getSingleBoard($user, $board);
                }
            }
            else
            {
                $printContent .= "<tr class='forum_element'><td colspan='4'>No boards avaliable.</td></tr>";
            }

            $printContent .= "</table>";
            global $create_boards;

            if ($user->hasPermission($edit_categories))
            {
                $printContent .= getEditCategoryForm($category);
            }

            if ($user->hasPermission($create_boards))
            {
                $printContent .= getNewBoardForm($category);
            }


            return $printContent;
        }
    }
}

function getSingleBoard($user, $board)
{
	global $edit_boards;
	
    if ($board != null)
    {
        $stats = count($board->getPosts()) . " posts<br />" . $board->getViews() . " views";

        $latestPost = "No posts.";

        if ($board->getLatestPost()->fields["User"] != null)
        {
            $userdetails = fetchUserDetails(null, null, $board->getLatestPost()->fields["User"]);
            $latestPost = "Last post <a href='{$_SERVER['PHP_SELF']}?p=t{$board->getLatestPost()->fields["Parent"]}'>\"" . $board->getLatestPost()->name . "\"</a> by " . $userdetails["display_name"] . " on " . $board->getLatestPost()->getDate();
        }

        $subBoards = "";

        foreach ($board->getChildren() as $child)
        {
            if ($child instanceof Board)
            {
                $subBoards .= "<a href='{$_SERVER['PHP_SELF']}?p=b{$child->getID()}'>{$child->name}</a> ";
            }
        }

        if (!empty($subBoards))
        {
            $subBoards = "Sub-Boards: " . $subBoards;
        }
        
        if($user->hasPermission($edit_boards, $board))
        {
        	$dropData = "
        		class='draggable' draggable='true' ondragstart=\"drag(event, 'b{$board->getID()}')\"
        		ondrop=\"drop(event, 'b{$board->getID()}')\" ondragover='allowDrop(event)'
        	";
        	
        	$dropData2 = "
        		class='draggable' draggable='true' ondragstart=\"drag(event, 'b{$board->getID()}')\"
        		ondrop=\"move(event, 'b{$board->getID()}')\" ondragover='allowDrop(event)'
        	";
        }
        
        if($board->isUnread($user))
        {
        	$boardImage = "<img src='forum/img/on.png'/>";
        }
        else
        {
        	$boardImage = "<img src='forum/img/off.png'/>";
        }

        $printContent .= "
		<tr class='forum_element'>
			<td class='read_status'>
				<span {$dropData}>
					$boardImage
					<span class='dragText'>Drag</span>
				</span>
			</td>
			<td class='element_content'>
				<span {$dropData2}>
					<h2><a href='{$_SERVER['PHP_SELF']}?p=b{$board->getID()}'>{$board->name}</a></h2>
					<br/>
					{$board->fields["Description"]}
					<br />
					<span style='font:9'>
						{$subBoards}
					</span>
				</span>
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

function getBoard($user, $board)
{
    global $create_boards, $edit_boards, $delete_boards, $create_threads;

    if ($board != null)
    {
        $printContent .= "
			<span>" . $board->getTreeAsString() . "</span>
			<span class=\"forum_menu\">";

        if ($user->hasPermission($create_boards, $board))
        {
            $printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('newBoard{$board->getID()}')\">Add Board</a> | ";
        }
        if ($user->hasPermission($edit_boards, $board))
        {
            $printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('editBoard{$board->getID()}')\">Edit Board</a> | ";
        }

        if ($user->hasPermission($delete_boards, $board))
        {
            $printContent .= "<a href='{$_SERVER['PHP_SELF']}?d=b{$board->getID()}'>Delete Board</a> | ";
        }

        if ($user->hasPermission($create_threads, $board))
        {
            $printContent .= "<a href=\"javascript:void(0)\" onclick = \"lightBox('newThread')\">Create Thread</a>";
        }

        $printContent .= "</span>";

        $printContent .= "<table class='forum_table'><tr><td>Thread</td><td>Stats</td><td>Last Post</td></tr>";

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
                    
                    $extraText = "";
                    
                    if($thread->isUnread($user))
                    {
                    	$extraText = "!!";
                    }

                    $printContent .= "
					<tr class='forum_element'>
						<td class='element_content'>
							<a href='{$_SERVER['PHP_SELF']}?p=t{$thread->getID()}'>$extraText {$thread->name}</a>
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
                    $printContent .= getSingleBoard($user, $child);
                }
            }
        }
        else
        {
            $printContent .= "<tr class='forum_element'><td colspan='4'>No threads avaliable.</td></tr>";
        }

        $printContent .= "</table>";

        if ($user->hasPermission($create_boards))
        {
            $printContent .= getNewBoardForm($board);
        }

        if ($user->hasPermission($edit_boards))
        {
            $printContent .= getEditBoardForm($board);
        }

        return $printContent;
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

function getThread($user, $thread)
{
    global $create_posts, $delete_posts, $edit_posts, $edit_threads, $edit_signature;

    if ($thread != null)
    {
    	if ($user->hasPermission($edit_threads, $thread))
    	{
    		$threadTitle = "
    				<div>
	    				<h2 class='inlineEdit' style='display:inline; margin-right:5px;' contenteditable='true'>
	    					{$thread->name}
	    				</h2>
	    				<a href='javascript:void(0)' onclick=\"window.location='forum.php?e=t{$thread->getID()}&data='+$(this).prev('.inlineEdit').html()\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
    				</div>";
    	}
    	else
    	{
    		$threadTitle = "<h2 style='display:inline'>{$thread->name}</h2>";
    	}
    	
        $printContent .= "
        $threadTitle
        <br />
		<span>" . $thread->getTreeAsString() . "</span>
		<span class=\"forum_menu\">";

        if ($user->hasPermission($create_posts, $thread) && $thread->fields["LockThread"] != "yes")
        {
            $printContent .= "<a href = \"javascript:void(0)\" onclick = \"$('html, body').animate({scrollTop:  $(document).height()})\" class='tsc_awb_small tsc_awb_white tsc_flat'>+ Post</a>";
        }

        $printContent .= "</span>";

        $printContent .= "<table class='forum_table' border='1'>";

        if (count($thread->getChildren()) > 0)
        {
        	/**
        	 * Print out each and every post.
        	 */
            foreach ($thread->getChildren() as $post)
            {
                $tempUser = getUserByID($post->fields["User"]);

                $printContent .= "
				<tr><td class='post_profile'>".getUserProfile($tempUser)."</td>";

                if ($user->hasPermission($edit_posts, $post))
                {
                	$editPost = "
	                	<div>
		    				<div class='inlineEdit' style='display:inline; margin-right:5px;' contenteditable='true'>
              					{$post->fields["Content"]}
		    				</div>
		    				<br />
		    				<a href='javascript:void(0)' onclick=\"window.location='forum.php?e=p{$thread->getID()}&data='+$(this).prev('.inlineEdit').html()\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
	    				</div>
                			";
                }
                else
                {
                	$editPost = "<div>{$post->fields["Content"]}</div>";
                }

                if ($user->hasPermission($delete_posts, $post))
                {
                    $printContent .= "<a href='{$_SERVER['PHP_SELF']}?p=t{$post->fields["Parent"]}&d=p{$post->getID()}'>Remove Post</a>";
                }
				
                if($user->hasPermission($edit_signature, $post))
                {
                	$editSignature = "class='editSignature' contenteditable='true' name='{$tempUser->id}'";
                }
                
                $lastEdit = "";
                
               	if($post->fields["LastEditTime"] > 0 && !empty($post->fields["LastEditUser"]))
                {
                	$lastEdit = "Last Edited By ".$post->fields["LastEditUser"]." on ".$post->fields["LastEditTime"];
                }
                
                $printContent .= "
				<td class='forum_content'>
					<article>
						$editPost
						<hr />
	                	<div $editSignature style='height:50px; width:100%'>{$tempUser->signature}</div>
	                	<small style='post_date'>{$lastEdit} Posted on {$post->getDate()}</small>
                	</article>
                </td>
                </tr>";
            }
            
            /**
             * Print out add new post form.
             */
            if ($user->hasPermission($create_posts, $thread) && $thread->fields["LockThread"] != "yes")
            {       
            	$printContent .= "<tr><td class='post_profile'>".getUserProfile($user)."</td>";
	            $printContent .= "
            			<td class='forum_content'>
            				<form action='{$_SERVER['PHP_SELF']}?p=t{$thread->getID()}&a=new' method='post'>
								<textarea id='editableContentNewPost' name='editableContent' wrap=\"virtual\"></textarea>
								<script type='text/javascript'>
									CKEDITOR.replace('editableContentNewPost', {height:'250', width:'600'});
								</script>
								<input type='submit' value='Post'/>					
							</form>
            			</td>
            		</tr>";
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
