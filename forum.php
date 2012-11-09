<?php

require_once("models/config.php");
require_once("forum/config.php");

$printContent = "";

$title = "Forum";

/**
 * Check if the user wants to go to a specified page.
 */
if (!empty($_GET["p"]))
{
    if(strstr($_GET["p"], "p"))
    {
    	$post = Post::getByID(intval(str_replace("p", "", $_GET["p"])));
    	$_GET["p"] = "t".$post->fields["Parent"];
    }
    
    if (strstr($_GET["p"], "b"))
    {
        $board = Board::getByID(intval(str_replace("b", "", $_GET["p"])));

        if ($board != null)
        {
            if ($_GET["a"] == "new")
            {
                if (!empty($_POST["title"]) && !empty($_POST["editableContent"]))
                {
                    $thread = $board->createThread($currentUser, clean($_POST["title"], true), clean($_POST["editableContent"]), time(), $con);
                    $successes[] = "Created forum thread!";
                }
                else if ($_POST["board_name"] || $_POST["editableContent"])
                {
                    $board->createBoard($currentUser, clean($_POST["board_name"]), clean($_POST["editableContent"]))->save($con);
                }
            }

            $printContent .= getNewThreadForm($board);
            $printContent .= getBoard($currentUser, $board);
        }
    }
    else if (strstr($_GET["p"], "t"))
    {
        $thread = Thread::getByID(intval(str_replace("t", "", $_GET["p"])));

        if ($thread != null)
        {
            if ($_GET["a"] == "new" && $_POST["editableContent"])
            {
                $post = $thread->createPost(clean($_POST["editableContent"]), $currentUser, time(), $con);
                $post->save($con);
            }

            $printContent .= getNewPostForm($thread);
            $printContent .= getThread($currentUser, $thread);

            $thread->view($currentUser, $con);
        }
    }
    else if (strstr($_GET["p"], "c"))
    {
        $category = Category::getByID(intval(str_replace("c", "", $_GET["p"])));

        if ($category != null)
        {
            if ($_GET["a"] == "new" && !empty($_POST["title"]))
            {
                if (empty($_POST["editableContent"]))
                    $_POST["editableContent"] = " ";

                $category->createBoard($currentUser, clean($_POST["title"], true), clean($_POST["editableContent"], true))->save($con);
            }

            $printContent .= getCategory($currentUser, $category);
        }
    }

    if (empty($printContent))
    {
        header("Location: forum.php");
        die();
    }
}
else
{
    if ($_GET["a"] == "new" && $_POST["title"])
    {
        $category = new Category(-1, clean($_POST["title"]), -1, false);
        $category->save($con);
    }

    $printContent .= getAllCategories($currentUser);
}

$content = "<span class='forum'>
                <span style='float:right'>Current Time: " . date("F j, Y, g:i a", time()) . "</span><br />" . $printContent . "
                </span>			
                <br/><br/>
                <div id='fade' class='black_overlay' onclick=\"closeLightBox()\"></div>		
                ";

$head = "<link href=\"forum/style.css\" rel=\"stylesheet\" type=\"text/css\" />
            <script type='text/javascript'>			
                    function lightBox(targetID)
                    {
                            document.getElementById(targetID).style.display='block';
                            document.getElementById('fade').style.display='block';
                    }

                    function closeLightBox()
                    {				
                            if (document.getElementsByClassName)
                            {
                                    var elements = document.getElementsByClassName('white_content');

                                    for (var i = 0; i < elements.length; i++)
                                    {
                                            elements[i].style.display='none';
                                    }

                                    document.getElementById('fade').style.display='none';
                            }
                            else
                            {
                                    alert ('Your browser does not support the getElementsByClassName method. Please update your browser!');
                            }
                    }

                    function allowDrop(ev)
                    {
                            ev.preventDefault();
                    }

                    function drag(ev, id)
                    {
                            ev.dataTransfer.setData('id', id);
                    }

                    function drop(ev, targetID)
                    {
                            ev.preventDefault();
		
							if(targetID != ev.dataTransfer.getData('id'))
							{
					        	window.location = 'forum.php?p='+ev.dataTransfer.getData('id')+'&o='+targetID;
							}
                    }
		
					function move(ev, targetID)
                    {
                            ev.preventDefault();
		
							if(targetID != ev.dataTransfer.getData('id'))
							{
					        	window.location = 'forum.php?p='+ev.dataTransfer.getData('id')+'&m='+targetID;
							}
                    }
		
					$(document).ready(function() {
						
						$('.draggable').hover(function(){
							$(this).find('.dragText').stop(true, true).fadeIn('slow');
						},
						function(){
							$(this).find('.dragText').stop(true, true).fadeOut('slow');
						});
						
						$('.editSignature').dblclick(function()
						{
							window.location = 'forum.php?p={$_GET["p"]}&e=u{$currentUser->id}&signature='+$(this).html();
						});

						$('.inlineEdit').dblclick(function()
						{
							window.location = 'forum.php?e='+$(this).attr('name')+'&data='+$(this).html();
						});
					});
            </script>
            ";

/**
 * Echo the variable $head in your head and $content in the place where you have your main body.
 */
require_once("template.php");
?>