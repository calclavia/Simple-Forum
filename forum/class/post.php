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

}

?>