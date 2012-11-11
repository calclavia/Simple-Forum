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
        $this->fields["Content"] = stripslashes(str_replace("\\r\\n", "", $content));
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
    	
    	if($user->hasPermission($edit_posts, $this))
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
    
    public function printPost($user)
    {
    	$printContent .= "<tr><td class='post_profile'>".getUserProfile($tempUser)."</td>";
    	
    	if ($user->hasPermission($edit_posts, $this))
    	{
    		$editPost = "
    		<div>
    		<div class='inlineEdit' style='margin-right:5px;' contenteditable='true'>
    		{$this->fields["Content"]}
    		</div>
    		<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?e=p{$this->getID()}&data='+$(this).prev('.inlineEdit').html()\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
    		</div>
    		";
    	}
    	else
    	{
    		$editPost = "<div>{$this->fields["Content"]}</div>";
    	}
    	 
    	if($user->hasPermission($edit_signature, $this))
    	{
    			$editSignature = "
    			<div>
    			<div class='inlineEdit' style='height:80px; width:100%' contenteditable='true'>
    			{$tempUser->signature}
    			</div>
    			<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?p={$_GET["p"]}&e=u{$tempUser->id}&signature='+$(this).prev('.inlineEdit').html()\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
    			</div>";
    			}
    			else
    			{
    			$editSignature = "<div style='height:80px; width:100%'>{$tempUser->signature}</div>";
    			}
    			 
    			$lastEdit = "";
    			 
    			if($this->fields["LastEditTime"] > 0 && !empty($this->fields["LastEditUser"]))
    			{
    			$editUser = getUserByID($this->fields["LastEditUser"]);
    	
    			if($editUser != null)
    			{
    			$lastEdit = "Last Edited By ".$editUser->username." on ".date("F j, Y, g:i a", $this->fields["LastEditTime"]);
    	}
    	}
    		
    	if ($user->hasPermission($delete_posts, $this))
    	{
    			$removePost = "<a href='#' onclick=\"if(confirm('Delete Post?')) {window.location='{$_SERVER['PHP_SELF']}?p=t{$this->fields["Parent"]}&d=p{$this->getID()}';}\" class=\"forum_menu tsc_awb_small tsc_awb_white tsc_flat\">Delete</a>";
    	}
    	
    	$printContent .= "
    	<td class='forum_content'>
    		<article>
    		$removePost
    		<br />
    			$editPost
    			<hr />
    			$editSignature
    			<small class='post_date'>{$lastEdit} Posted on {$this->getDate()}</small>
    			</article>
    			</td>
    			</tr>";
    }
}

?>