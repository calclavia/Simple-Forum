<?php

class ForumUser
{
	//GENERAL USER DATA
	/**
	 * @int The ID of the user.
	 */
	public $id;

	/**
	 * @string The display name of the user.
	 */
	public $username;

	/**
	 * @string The email of the user.
	 */
	public $email;

	/**
	 * @String - The title of this user for display.
	 */
	public $title;

	//Forum Data
	/**
	 * @int Posts posted.
	 */
	public $posts = 0;

	/**
	 * @array array Forum elements this user is moderating.
	 */
	public $moderate = array();

	/**
	 * @array An array of unread posts.
	 */
	public $unreadPosts = array();

	/**
	 * @int - Email Privacy Status. 0 - Protected, 1 - Allow Email, 2 - Show Email.
	 */
	public $emailPrivacy = 0;

	/**
	 * @string - The signature of the user.
	 */
	public $signature = "I am a new member of this forum.";

	/**
	 * @param int $id
	 * @param String $username
	 * @param String $email
	 */
	function __construct($id, $username, $email, $con)
	{
		global $table_prefix;

		$this->id = $id;
		$this->username = $username;
		$this->email = $email;

		if ($this->id > 0)
		{
			$result = mysql_query("SELECT * FROM {$table_prefix}users WHERE ID={$this->id} LIMIT 1", $con);
			$row = mysql_fetch_array($result);

			if ($row["ID"] > 0)
			{
				$this->posts = intval($row["Posts"]);
				$this->moderate = unserialize($row["Moderate"]);
				$this->unreadPosts = unserialize($row["Unread"]);

				if (!is_array($this->moderate))
				{
					$this->moderate = array();
				}

				if (!is_array($this->unreadPosts))
				{
					$this->unreadPosts = array();
				}

				$this->signature = stripslashes(str_replace("\\r\\n", "", $row["Signature"]));
			}

			$this->save($con);
		}
	}

	public static function setUp($con)
	{
		global $table_prefix;
		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}users (ID int NOT NULL, Moderate varchar(255), Unread varchar(255), Posts int, Signature TEXT, EmailPrivacy int)", $con) or die(mysql_error());
		return true;
	}

	public function save($con)
	{
		global $table_prefix;

		if ($this->id > 0)
		{
			$result = mysql_query("SELECT * FROM {$table_prefix}users WHERE ID={$this->id} LIMIT 1", $con);
			$row = mysql_fetch_array($result);

			if ($row["ID"] <= 0 || empty($row))
			{
				$query = "INSERT INTO {$table_prefix}users (ID, Moderate, Unread, Posts, Signature) VALUES ({$this->id}, '" . mysql_real_escape_string(serialize($this->moderate)) . "', '" . mysql_real_escape_string(serialize($this->unreadPosts)) . "', {$this->posts}, '" . mysql_real_escape_string($this->signature) . "')";
				mysql_query($query, $con) or die("Failed to create user data: " . mysql_error() . ", Q = " . $query);
				return true;
			}
			else
			{
				$query = "UPDATE {$table_prefix}users SET Moderate='" . serialize($this->moderate) . "', Unread='" . serialize($this->unreadPosts) . "', Posts={$this->posts}, Signature='" . mysql_real_escape_string($this->signature) . "' WHERE ID={$this->id} LIMIT 1";
				mysql_query($query, $con) or die("Failed to save forum element: " . mysql_error() . ", Q = " . $query);
				return true;
			}
		}

		return false;
	}

	public function hasPermission($permission, $element = null)
	{
		if ($this->id == -1)
		{
			return false;
		}

		if ($element != null)
		{
			if (in_array($element->prefix . $element->getID(), $this->moderate))
			{
				return true;
			}
			else if ($element instanceof Post)
			{
				if ($element->fields["User"] == $this->id)
				{
					return true;
				}
			}
			else if ($element instanceof Thread)
			{
				if ($element->getFirstPost()->fields["User"] == $this->id)
				{
					return true;
				}
			}
		}

		return $permission->default || hasPermission($this, $permission, $element);
	}

	public function isRead($post)
	{
		if (in_array($post->getID(), $this->unreadPosts))
		{
			return false;
		}

		return true;
	}

	public function moderate($element)
	{
		$this->moderate[] = $element->prefix . $element->getID();
	}

	public function unmoderate($element)
	{
		if (in_array($element->prefix . $element->getID(), $this->moderate))
		{
			return false;
		}

		return true;
	}

	/**
	 * Reads a post.
	 * @param unknown $post - The post to read.
	 * @param unknown $con - MySQL Connection
	 */
	public function read($post, $con)
	{
		$this->unreadPosts = array_diff($this->unreadPosts, array($post->getID()));
		$this->save($con);

		return true;
	}

	public function onCreatePost($post, $con)
	{
		global $table_prefix;

		$this->moderate($post);
		$this->posts++;
		$this->save($con);

		/**
		 * Make all other user have this post set as unread.
		 */
		$result = mysql_query("SELECT * FROM {$table_prefix}users", $con);

		while ($row = mysql_fetch_array($result))
		{
			$unread = unserialize($row["Unread"]);

			if (!is_array($unread))
			{
				$unread = array();
			}

			$unread[] = $post->getID();

			$query = "UPDATE {$table_prefix}users SET Unread='" . serialize($unread) . "' WHERE ID={$row["ID"]} LIMIT 1";
			mysql_query($query, $con) or die("Failed to save other user data: " . mysql_error() . ", Q = " . $query);
		}

		return true;
	}

	public function editSignature($newSig, $con)
	{
		if (strlen($newSig) < 500)
		{
			$this->signature = $newSig;
			$this->save($con);
			return true;
		}

		return false;
	}

	public function printProfile()
	{
		return "
		<div class='forum_profile'>
			<img src='http://www.gravatar.com/avatar/" . md5($this->email) . "?d=mm&s=160' alt='Avatar' class='avatar'/>
			<div class='profile_info'>
				<p class='username'>{$this->username}</p>
				<p class='element_info'>
					{$this->title}<br />
					{$this->posts} Post(s)
				</p>
			</div>
		</div>";
	}

}

?>
