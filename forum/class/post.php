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
    	if ($user->hasPermission($edit_posts, $this))
    	{
    		$editPost = "
    		<div class='inlineEdit' style='margin-right:5px;' contenteditable='true'>
				{$this->fields["Content"]}
    		</div>
    		<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?e=p{$this->getID()}&data='+$(this).prev('.inlineEdit').html()\" class='inline_form btn_small btn_white btn_flat'>Edit</a>
    		<div class='clear'></div>";
    	}
    	else
    	{
    		$editPost = "<div>{$this->fields["Content"]}</div>";
    	}
    	 
    	if($user->hasPermission($edit_signature, $this))
    	{
			$editSignature = "
			<div>
				<div class='forum_signature inlineEdit' contenteditable='true'>
					{$user->signature}
				</div>
				<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?p={$_GET["p"]}&e=u{$user->id}&signature='+$(this).prev('.inlineEdit').html()\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
			</div>";
		}
		else
		{
			$editSignature = "<div>{$user->signature}</div>";
		}
		 
		$lastEdit = "";
		 
		if($this->fields["LastEditTime"] > 0 && !empty($this->fields["LastEditUser"]))
		{
			$editUser = getUserByID($this->fields["LastEditUser"]);

			if($editUser != null)
			{
				$lastEdit = "Last edit: <b>".$editUser->username."</b>, ".date("F j, Y, g:i a", $this->fields["LastEditTime"]);
			}
    	}
    		
    	if ($user->hasPermission($delete_posts, $this))
    	{
    		$removePost = "<a href='#' onclick=\"if(confirm('Delete Post?')) {window.location='{$_SERVER['PHP_SELF']}?p=t{$this->fields["Parent"]}&d=p{$this->getID()}';}\" class=\"forum_menu tsc_awb_small tsc_awb_white tsc_flat\">Delete</a>";
    	}
    	
    	return "
		<div class='post'>
			<a rel='".$this->getID()."'></a>
			".$user->printProfile()."
			<div class='comment_box'>
				<div class='comment_inner'>
					$removePost
					$editPost
					<div class='hrline_silver'></div>
					$editSignature
					<span class='last_edit'>$lastEdit</span>
					<span class='date'>{$this->getDate()}</span>
				</div>
			</div>
			<div class='clear'></div>
		</div>
    	";
    			
    }

}

?>