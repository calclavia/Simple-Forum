<?php

/*
 * Run this file to set up your database.
 * @author Calclavia
 */

require_once("config.php");

$con = mysql_connect($mysql_host, $mysql_username, $mysql_password);

if (!$con)
{
    die('Could not connect: ' . mysql_error());
}

mysql_select_db($db_name, $con);

// Create table
Category::setUp($con);
Board::setUp($con);
Thread::setUp($con);
Post::setUp($con);

mysql_close($con);

die("Successfully set up databases!")
?>
