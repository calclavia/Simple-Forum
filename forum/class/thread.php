<?php

/**
 * A thread contains various posts within it.
 * @author Calclavia
 */
class Thread extends ForumElement
{

	function __construct($id, $parent, $name, $sticky, $lockThread, $views)
	{
		$this->id = $id;
		$this->name = stripslashes(str_replace("\\r\\n", "", $name));

		$this->element_name = "threads";
		$this->prefix = "t";

		$this->fields["Parent"] = $parent;
		$this->fields["Sticky"] = $sticky;
		$this->fields["LockThread"] = $lockThread;
		$this->fields["Views"] = $views;
	}

	public static function setUp($con)
	{
		global $table_prefix;

		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}threads (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Sticky varchar(5), LockThread varchar(5), Views int)", $con) or die(mysql_error());
	}

	public static function getByID($id)
	{
		global $table_prefix;

		$result = mysql_query("SELECT * FROM {$table_prefix}threads
		WHERE ID={$id} LIMIT 1");

		$row = mysql_fetch_array($result);

		if ($row["ID"] <= 0)
		{
			return null;
		}
		else
		{
			return new Thread($row["ID"], $row["Parent"], $row["Name"], $row["Sticky"], $row["LockThread"], $row["Views"]);
		}
	}

	public function getChildren()
	{
		global $table_prefix;

		$returnArray = array();

		$result = mysql_query("SELECT * FROM {$table_prefix}posts WHERE Parent={$this->id}");

		while ($row = mysql_fetch_array($result))
		{
			$returnArray[] = new Post($row["ID"], $row["Parent"], $row["Name"], $row["Content"], $row["User"], $row["Time"], $row["LastEditTime"], $row["LastEditUser"]);
		}

		uasort($returnArray, function($a, $b)
		{
			return $a->fields["Time"] > $b->fields["Time"];
		});

		return $returnArray;
	}

	public function createPost($content, $user, $time, $con)
	{
		if ($this->fields["LockThread"] != "yes")
		{
			$post = new Post(-1, $this->id, $this->name, $content, $user->id, $time, $time, $userID);
			$post->save($con);
			$user->onCreatePost($post, $con);
			return $post;
		}

		return null;
	}

	public function getFirstPost()
	{
		$threads = $this->getChildren();

		if (count($threads) > 0)
		{
			$earliestThread = $threads[0];

			foreach ($threads as $thread)
			{
				if ($thread->fields["Time"] < $earliestThread->fields["Time"])
				{
					$earliestThread = $thread;
				}
			}

			return $earliestThread;
		}

		return null;
	}

	public function getLatestPost()
	{
		$posts = $this->getChildren();

		if (count($posts) > 0)
		{
			$latestThread = $posts[0];

			foreach ($posts as $thread)
			{
				if ($thread->fields["Time"] > $latestThread->fields["Time"])
				{
					$latestThread = $thread;
				}
			}

			return $latestThread;
		}

		return null;
	}

	public function getTreeAsString()
	{
		return str_replace("</ul>", "<li><a href='{$_SERVER['PHP_SELF']}?p=t{$this->getID()}' class='current'>".limitString($this->name, 30)."</a></li></ul>", str_replace("class='current'", "", Board::getByID(intval($this->fields["Parent"]))->getTreeAsString()));
	}

	public function view($user, $con)
	{
		foreach($this->getChildren() as $post)
		{
			$user->read($post, $con);
		}
		 
		$this->fields["Views"]++;
		$this->save($con);
	}

	public function edit($user, $name, $sticky, $lockThread)
	{
		global $edit_threads;
		 
		if($user->hasPermission($edit_threads))
		{
			$this->name = $name;
			$this->fields["Sticky"] = $sticky;
			$this->fields["LockThread"] = $lockThread;
		}
	}
	
	public function isUnread($user)
	{
		foreach($this->getChildren() as $child)
		{
			if($child->isUnread($user))
			{
				return true;
			}
		}
		 
		return false;
	}
	
	public function getViews()
	{
		return $this->fields["Views"];
	}
    
	public function printThread($user)
	{		
		$stats = count($this->getChildren()) . " post(s) " . $this->getViews() . " view(s)";
		
		$printLatestPost = "No posts.";
		
		$latestPost = $this->getLatestPost();
		
		if ($latestPost->fields["User"] != null)
		{
			$latestPostUser = getUserByID($latestPost->fields["User"]);
			$printLatestPost = "Last Post By: <b>" . $latestPostUser->username . "</b><br />" . $latestPost->getDate();
		}
		
		$thisOwner = "Annoymous";
		
		if ($this->getFirstPost()->fields["User"] != null)
		{
			$userdetails = fetchUserDetails(null, null, $this->getFirstPost()->fields["User"]);
			$thisOwner = $userdetails["display_name"];
		}
				
		return "
			<div class='thread_wrapper ".($this->isUnread($user) ? "thread_unread" : "thread_normal" ) ."'>
	    		<div class='forum_element'>
					<div class='two_third'>
						<div class='thread_content'>
							<h3 class='element_title'><a href='{$_SERVER['PHP_SELF']}?p=t{$this->getID()}'>{$this->name}</a></h3>
						    <div class='element_info'>
						    	$thisOwner, {$this->getFirstPost()->getDate()}
						    </div>
						</div>
					</div>
					<div class='forum_element_info one_third column-last'>
						<p>$printLatestPost <br/> $stats</p>
					</div>
				</div>
				<div class='clear'></div>
			</div>
			<div class='hrline_silver' style='width: 95%'></div>
		";
	}
	
	public function printThreadContent($user)
	{
		global $create_posts, $delete_posts, $edit_posts, $edit_threads, $edit_signature;
	
		if ($this != null)
		{
			if ($user->hasPermission($edit_threads, $this))
			{
				$thisTitle = "
				<div>
				<h2 class='inlineEdit' style='display:inline; margin-right:5px;' contenteditable='true'>
				{$this->name}
				</h2>
				<a href='javascript:void(0)' onclick=\"window.location='{$_SERVER['PHP_SELF']}?e=t{$this->getID()}&data='+$(this).prev('.inlineEdit').html()\" class='inline_form tsc_awb_small tsc_awb_white tsc_flat'>Edit</a>
				</div>";
			}
			else
			{
				$thisTitle = "<h2 style='display:inline'>{$this->name}</h2>";
			}
	
			$printContent .= "
				$thisTitle
				<br />
				<span class=\"forum_menu\">";
	
			if ($user->hasPermission($create_posts, $this) && $this->fields["LockThread"] != "yes")
			{
				$printContent .= "<a href = \"javascript:void(0)\" onclick = \"$('html, body').animate({scrollTop:  $(document).height()})\" class='tsc_awb_small tsc_awb_white tsc_flat'>+ Post</a>";
			}
	
	        $printContent .= "</span><div>" . $this->getTreeAsString() . "</div>";
	
			$printContent .= "<div class='elements_container'>";
	
			if (count($this->getChildren()) > 0)
			{
				/**
				 * Print out each and every post.
				*/
				foreach ($this->getChildren() as $post)
				{
					$printContent .= $post->printPost($user, getUserByID($post->fields["User"]));
				}
				
				/**
				 * Print out add new post form.
				 */
				
				if ($user->hasPermission($create_posts, $this) && $this->fields["LockThread"] != "yes")
				{
					$printContent .= $this->printNewPostForm($user);
				}
			}
			else
			{
				$printContent .= "No posts avaliable.";
			}
			
			$printContent .= "<div>" . $this->getTreeAsString() . "</div>";
				
			return $printContent;
		}
	}

	public function printNewPostForm($user)
    {
    	return "
		<div class='post'>
			<a rel='new'></a>
			".$user->printProfile()."
			<div class='comment_box'>
				<div class='comment_inner'>
					<form action='{$_SERVER['PHP_SELF']}?p=t{$this->getID()}&a=new' method='post'>
						<textarea id='editableContentNewPost' name='editableContent' wrap=\"virtual\"></textarea><br />
						<input type='submit' value='Post'/>
					</form>
				</div>
			</div>
			<div class='clear'></div>
		</div>
		<script type='text/javascript'>
			CKEDITOR.replace('editableContentNewPost', {height:'250', width: '548'});
		</script>
    	";    				 
    }	
}

?>