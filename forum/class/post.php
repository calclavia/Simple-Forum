<?php

/**
 * A post is what a user will post in a thread.
 * @author Calclavia
 */
class Post extends ForumElement
{

    function __construct($id, $parent, $name, $content, $userID, $time, $lastEditTime, $lastEditUser)
    {
        $this->id = $id;
        $this->name = stripslashes(str_replace("\\r\\n", "", $name));

        $this->element_name = "posts";
        $this->prefix = "p";

        $this->fields["Parent"] = $parent;
        $this->fields["User"] = $userID;
        $this->fields["Content"] = str_replace("\\r\\n", "", $content);
        //$this->fields["Content"] = stripslashes("Â", "", str_replace(str_replace("\\r\\n", "", $content)));
        $this->fields["Time"] = $time;
        $this->fields["LastEditTime"] = $lastEditTime;
        $this->fields["LastEditUser"] = $lastEditUser;
    }

    function getDate()
    {
        return date("F j, Y, g:i a", $this->fields["Time"]);
    }

    public static function setUp($con)
    {
        global $table_prefix;

        mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}posts (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Content TEXT, User int, Time int, LastEditTime int, LastEditUser int)", $con) or die(mysql_error());
    }

    public static function getByID($id)
    {
        global $table_prefix;

        $result = mysql_query("SELECT * FROM {$table_prefix}posts
		WHERE ID={$id} LIMIT 1");

        $row = mysql_fetch_array($result);

        if ($row["ID"] <= 0)
        {
            return null;
        }
        else
        {
            return new Post($row["ID"], $row["Parent"], $row["Name"], $row["Content"], $row["User"], $row["Time"], $row["LastEditTime"], $row["LastEditUser"]);
        }
    }

    public function getChildren()
    {
        return null;
    }

    public function edit($newContent, $user, $time)
    {
        global $edit_posts;

        if ($user->hasPermission($edit_posts, $this))
        {
            $this->fields["Content"] = $newContent;
            $this->fields["LastEditUser"] = $user->id;
            $this->fields["LastEditTime"] = $time;
        }
    }

    public function isUnread($user)
    {
        return !$user->isRead($this);
    }

    /**
     * 
     * @param unknown $user - Current User
     * @param unknown $postUser - Person who posted the post.
     * @return string
     */
    public function printPost($user, $postUser)
    {
        global $permission;

        if ($user->hasPermission($permission["signature_edit"], $this))
        {
            $editSignature = "
			<div class='forum_signature quick_edit' name='{$postUser->id}' data-type='signature' contenteditable='true'>
				{$postUser->signature}
			</div>";
        }
        else
        {
            $editSignature = "<div class='forum_signature'>{$postUser->signature}</div>";
        }

        $lastEdit = "";

        if ($this->fields["LastEditTime"] > 0 && !empty($this->fields["LastEditUser"]))
        {
            $editUser = getUserByID($this->fields["LastEditUser"]);

            if ($editUser != null)
            {
                $lastEdit = "Last edit: <b>" . $editUser->username . "</b>, " . date("F j, Y, g:i a", $this->fields["LastEditTime"]);
            }
        }

        if ($user->hasPermission($permission["post_edit"], $this))
        {
            $editPost = "<a href=\"javascript:void(0);\" data-forum-target=\"" . $this->getID() . "\" class=\"post_edit btn_small btn_white btn_flat\">Edit</a>";
        }

        if ($user->hasPermission($permission["post_delete"], $this))
        {
            $removePost = "<a href=\"javascript:if(confirm('Delete Post?')) {window.location='{$_SERVER['PHP_SELF']}?p=t{$this->fields["Parent"]}&d=p{$this->getID()}';}\" class=\"btn_small btn_white btn_flat\">Delete</a>";
        }

        if ($user->hasPermission($permission["post_create"], $this))
        {
            $quotePost = "<a href=\"javascript: postEditor.insertHtml('<blockquote>'+$('#post_content_" . $this->getID() . "').html()+'<cite>Quoted from {$postUser->username}</cite>
                </blockquote><p></p>');\" class=\"btn_small btn_white btn_flat\">Quote</a>";
        }

        return "
            <div class='post'>
                <a id='" . $this->getID() . "'></a>
                " . $postUser->printProfile() . "
                <div class='comment_box'>
                    <div class='comment_inner'>
                        <div class='forum_menu'>
                            $quotePost
                            $editPost
                            $removePost
                        </div>
                        <div class='clear'></div>
                        <div id='post_content_" . $this->getID() . "'>{$this->fields["Content"]}</div>
                        <div class='hrline_silver'></div>
                        $editSignature
                        <span class='last_edit'>$lastEdit</span>
                        <span class='date'>{$this->getDate()}</span>
                    </div>
                </div>
            </div>
            <div class='clear'></div>";
    }

}

?>