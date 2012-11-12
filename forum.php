<?php

 /*
  * A simple function to clean a string to be SQL safe.
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

require_once("models/config.php");
require_once("forum/config.php");

$printContent = "";

$title = "Forum";

/**
 * Takes the user to a specific page on the forum.
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
            $printContent .= $board->printBoardContent($currentUser);
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
            }

            $printContent .= $thread->printThreadContent($currentUser);

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

$content = "<div class='forum'>
                <span style='float:right'>Current Time: " . date("F j, Y, g:i a", time()) . "</span><br />" . $printContent . "</span>
               	<div style='clear'></div>
           </div>			
                <br/><br/>
                <div id='fade' class='black_overlay' onclick=\"closeLightBox()\"></div>		
                ";

$head = "<link href=\"forum/css/forum.css\" rel=\"stylesheet\" type=\"text/css\" />
		<link href=\"forum/css/buttons.css\" rel=\"stylesheet\" type=\"text/css\" />
		<script type='text/javascript' src='forum/js/forum.js'></script>";

/**
 * Echo the variable $head in your head and $content in the place where you have your main body.
 */
require_once("template.php");
?>