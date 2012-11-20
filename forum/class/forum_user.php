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
	 * @String The title of this user for display.
	 */
	public $title;

	/**
	 * @int The date this user registered.
	 */
	public $dateRegistered;

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
	 * @int - Email Privacy Status. 1 - Protected, 2 - Allow Email, 3 - Show Email.
	 */
	public $privacy = 1;

	public $watching = array();

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

				if (!is_array($this->moderate))
				{
					$this->moderate = array();
				}
				$this->unreadPosts = unserialize($row["Unread"]);

				if (!is_array($this->unreadPosts))
				{
					$this->unreadPosts = array();
				}
				$this->watching = unserialize($row["Watching"]);

				if (!is_array($this->watching))
				{
					$this->watching = array();
				}

				$this->privacy = $row["Privacy"];

				$this->signature = clean(str_replace("\\r\\n", "", $row["Signature"]));
			}

			$this->save($con);
		}
	}

	public static function setUp($con)
	{
		global $table_prefix;
		mysql_query("CREATE TABLE IF NOT EXISTS {$table_prefix}users (ID int NOT NULL, Moderate text, Unread text, Posts int, Signature text, Watching text, Privacy int)", $con) or die(mysql_error());
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
				$query = "INSERT INTO {$table_prefix}users (ID, Moderate, Unread, Posts, Signature, Watching, Privacy) VALUES ({$this->id}, '" . mysql_real_escape_string(serialize($this->moderate)) . "', '" . mysql_real_escape_string(serialize($this->unreadPosts)) . "', {$this->posts}, '" . mysql_real_escape_string($this->signature) . "', '" . mysql_real_escape_string(serialize($this->watching)) . "', " . $this->privacy . ")";
				mysql_query($query, $con) or die("Failed to create user data: " . mysql_error() . ", Q = " . $query);
				return true;
			}
			else
			{
				$query = "UPDATE {$table_prefix}users SET Moderate='" . serialize($this->moderate) . "', Unread='" . mysql_real_escape_string(serialize($this->unreadPosts)) . "', Posts={$this->posts}, Signature='" . mysql_real_escape_string($this->signature) . "', Watching='" . mysql_real_escape_string(serialize($this->watching)) . "', Privacy=" . $this->privacy . " WHERE ID={$this->id} LIMIT 1";
				mysql_query($query, $con) or die("Failed to save forum element: " . mysql_error() . ", Q = " . $query);
				return true;
			}
		}

		return false;
	}

	public static function getAll($con)
	{
		global $table_prefix;

		$returnArray = array();
		$result = mysql_query("SELECT * FROM {$table_prefix}users", $con) or die("ForumUser: Failed to retrieve data!");

		while ($row = mysql_fetch_array($result))
		{
			$user = getUserByID($row["ID"]);

			if ($user != null)
			{
				if ($user->id > 0)
				{
					$returnArray[] = $user;
				}
			}
		}

		return $returnArray;
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

	public function isModerating($element)
	{
		return in_array($element->prefix . $element->getID(), $this->moderate);
	}

	public function unModerate($element)
	{
		if ($this->isModerating($element))
		{
			$this->moderate = array_diff($this->moderate, array($element->prefix . $element->getID()));
			return true;
		}
		return false;
	}

	public function toggleWatch($thread, $con)
	{
		if (!$this->unWatch($thread, null))
		{
			$this->watching[] = $thread->getID();
		}

		if ($con != null)
		{
			$this->save($con);
		}

		return $this->isWatching($thread);
	}

	public function unWatch($thread, $con)
	{
		if ($this->isWatching($thread))
		{
			$this->watching = array_diff($this->watching, array($thread->getID()));

			if ($con != null)
			{
				$this->save($con);
			}

			return true;
		}

		return false;
	}

	public function isWatching($thread)
	{
		if (in_array($thread->getID(), $this->watching))
		{
			return true;
		}

		return false;
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
		$this->moderate($post);
		$this->posts++;
		$this->save($con);

		/**
		 * Make all other user have this post set as unread.
		 */
		$users = self::getAll($con);

		foreach ($users as $user)
		{
			$user->unreadPosts[] = $post->getID();
			$user->save($con);
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

	public function email($subject, $message)
	{
		global $websiteName, $emailAddress;

		$header = "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		$header .= "From: " . $websiteName . " <" . $emailAddress . ">\r\n";

		$message = wordwrap($message, 70);
		return mail($this->email, $subject, $message, $header);
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
