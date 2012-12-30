<?php
//Include your own login information files here.
require_once (dirname(__FILE__) . "/../models/config.php");

/**
 * Include this at the very beginning of your PHP page.
 * @author Calclavia
 */

$websiteName = "Simple Forum";
$emailAddress = "example@example.com";

//MySQL Variables
$mysql_host = $db_host;
$mysql_username = $db_user;
$mysql_password = $db_pass;
$db_name = $db_general;
$table_prefix = "forum_";

$con = mysql_connect($mysql_host, $mysql_username, $mysql_password) or die('Could not connect: ' . mysql_error());
mysql_select_db($db_name, $con);

$posts_per_page = 8;

/**
 * Set library paths relative to this.
 */
$jquery_path = "";
$editor_js_path = "ckeditor/ckeditor.js";

//The directory in which this forum will be saved in.
$DATA_DIRECTORY = dirname(__FILE__) . "/data/";

require_once (dirname(__FILE__) . "/class/htmlfixer.php");
require_once (dirname(__FILE__) . "/class/forum_element.php");

require_once (dirname(__FILE__) . "/class/category.php");
require_once (dirname(__FILE__) . "/class/board.php");
require_once (dirname(__FILE__) . "/class/thread.php");
require_once (dirname(__FILE__) . "/class/post.php");
require_once (dirname(__FILE__) . "/class/permission.php");
require_once (dirname(__FILE__) . "/class/forum_user.php");

/**
 * A global function required to determine if a permission is granted for this user using the forum.
 * @param Permission $permission
 * @param ForumElement $element
 * @return boolean True if permission is granted.
 */
function hasPermission($user, $comparePermission, $element)
{
	global $loggedInUser, $permission;

	if (!isUserLoggedIn())
	{
		return false;
	}

	if ($loggedInUser -> checkPermission(array(2)))
	{
		return true;
	}

	/**
	 * UE Modder Special Permission
	 */
	if ($loggedInUser -> checkPermission(array(4)))
	{
		return $comparePermission == $permission["thread_lock"];
	}

	return false;
}

/**
 * @param int $id - The User ID
 * @return ForumUser Object
 */
function getUserByID($id)
{
	global $con;

	$userdetails = fetchUserDetails(null, null, $id);

	if ($userdetails != null && !empty($userdetails) && count($userdetails) > 0)
	{
		$newUser = new ForumUser($userdetails["id"], $userdetails["user_name"], $userdetails["email"], $con);
		$newUser -> title = $userdetails["title"];
		$newUser -> dateRegistered = $userdetails["sign_up_stamp"];

		return $newUser;
	}

	return new ForumUser(-1, "Annoynomous", "No email", $con);
}

function getUserByUsername($username)
{
	$user = fetchUserDetails($username);

	if ($user != null)
	{
		return getUserByID($user["id"]);
		return getUserByID($user["id"]);
	}

}

/**
 * A simple function to clean a string to be SQL safe.
 */
function clean($string, $veryClean = false)
{
	$htmlFixer = new HtmlFixer();

	if ($veryClean)
	{
		return stripslashes(htmlentities(strip_tags($htmlFixer -> getFixedHtml(trim($string)))));
	}
	else
	{
		return stripslashes(strip_tags($htmlFixer -> getFixedHtml(trim($string)), "<img><u><i><p><span><div><strong><q><blockquote><b><a><ul><ol><li><h1><h2><h3><h4><h5><h6>"));
	}
}

/**
 * Returns a string that is limited by the limit amount.
 */
function limitString($string, $limit = 25)
{
	return strlen($string) > $limit ? substr($string, 0, $limit) . "..." : $string;
}

if ($_SERVER['PHP_SELF'] != "/forum/setup.php")
	$currentUser = getUserByID($loggedInUser -> user_id);

require_once (dirname(__FILE__) . "/process.php");
?>