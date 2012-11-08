<?php

require_once("models/config.php");
require_once("forum/config.php");

/**
 * A global function required to determine if a permission is granted for this user using the forum.
 * @param Permission $permission
 * @param ForumElement $element
 * @return boolean True if permission is granted.
 */
function hasPermission($permission, $element)
{
    global $loggedInUser;

    if (!isUserLoggedIn())
    {
        return false;
    }

    return $loggedInUser->checkPermission(array(2, 4));
}

if (!isUserLoggedIn())
{
    $currentUser = new ForumUser(-1, "Annoynomous", "No email");
}
else
{
    $currentUser = new ForumUser($loggedInUser->user_id, $loggedInUser->username, $loggedInUser->email);
}

$printContent = "";

$title = "Forum";

/**
 * Different actions.
 * a = Adding
 * e = Editing
 * d = Deleting
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

        header("Location: forum.php");
        die();
    }
}

if (!empty($_GET["e"]) && !empty($_POST))
{
    if (strstr($_GET["e"], "c") && $_POST["title"])
    {
        $category = Category::getByID(intval(str_replace("c", "", $_GET["e"])));

        if ($category != null)
        {
            $category->edit(clean($_POST["title"]));
            $category->save($con);
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
    else if (strstr($_GET["e"], "p") && $_POST["editableContent"])
    {
        $post = Post::getByID(intval(str_replace("p", "", $_GET["e"])));

        if ($post != null)
        {
            if ($post->getID() == Thread::getByID($post->fields["Parent"])->getFirstPost()->getID())
            {
                $thread = Thread::getByID($post->fields["Parent"]);

                if ($_POST["sticky"])
                {
                    $sticky = "yes";
                }
                else
                {
                    $sticky = "no";
                }

                if ($_POST["lockTopic"])
                {
                    $lockTopic = "yes";
                }
                else
                {
                    $lockTopic = "no";
                }

                $thread->edit($_POST["title"], $sticky, $lockTopic);
                $thread->save($con);
            }

            $post->edit(clean($_POST["editableContent"]), $currentUser->id, time());
            $post->save($con);
        }
    }
}

if (!empty($_GET["d"]))
{
    if (strstr($_GET["d"], "c") && $currentUser->hasPermission($delete_categories))
    {
        $category = Category::getByID(intval(str_replace("c", "", $_GET["d"])));

        if ($category != null)
        {
            $category->delete($con);
            $successes[] = "Removed category: " . $category->name;
        }
    }
    else if (strstr($_GET["d"], "b") && $currentUser->hasPermission($delete_boards))
    {
        $board = Board::getByID(intval(str_replace("b", "", $_GET["d"])));

        if ($board != null)
        {
            $board->delete($con);
            $successes[] = "Removed board: " . $board->name;
        }
    }
    else if (strstr($_GET["d"], "p") && $currentUser->hasPermission($delete_posts))
    {
        $post = Post::getByID(intval(str_replace("p", "", $_GET["d"])));

        if ($post != null)
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

/**
 * Check if the user wants to go to a specified page.
 */
if (!empty($_GET["p"]))
{
    /**
     * If it is a specific board.
     */
    if (strstr($_GET["p"], "b"))
    {
        $board = Board::getByID(intval(str_replace("b", "", $_GET["p"])));

        if ($board != null)
        {
            $title .= " - " . $board->name;

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
            $title .= " - " . $thread->name;

            if ($_GET["a"] == "new" && $_POST["editableContent"])
            {
                $post = $thread->createPost(clean($_POST["editableContent"]), $loggedInUser->user_id, time());
                $post->save($con);
            }

            $printContent .= getNewPostForm($thread);
            $printContent .= getThread($currentUser, $thread);

            $thread->view();
            $thread->save($con);
        }
    }
    else if (strstr($_GET["p"], "c"))
    {
        $category = Category::getByID(intval(str_replace("c", "", $_GET["p"])));

        if ($category != null)
        {
            $title .= " - " . $category->name;

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
                            
                            if(ev != ev.dataTransfer.getData('id'))
                            {
                                window.location = 'forum.php?p='+ev.dataTransfer.getData('id')+'&o='+targetID;
                            }
                    }
            </script>
            ";

require_once("template.php");

/*
  $postUser = new loggedInUser();
  $postUser->email = $userdetails["email"];
  $postUser->user_id = $userdetails["id"];
  $postUser->hash_pw = $userdetails["password"];
  $postUser->title = $userdetails["title"];
  $postUser->displayname = $userdetails["display_name"];
  $postUser->username = $userdetails["user_name"]; */
?>