<?php
/*
 * Run this file to set up your database.
 * @author Calclavia
 */

require_once("config.php");

$con = mysql_connect($mysql_host, $mysql_username, $mysql_password);

if(!$con)
{
    die('Could not connect: ' . mysql_error());
}

mysql_select_db($db_name, $con);	

// Create table
mysql_query("CREATE TABLE {$table_prefix}categories (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Children varchar(65535), Moderators varchar(65535), Description varchar(65535))", $con);
mysql_query("CREATE TABLE {$table_prefix}boards (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Children varchar(65535)), Moderators varchar(65535)", $con);
mysql_query("CREATE TABLE {$table_prefix}threads (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Children varchar(65535)), Moderators varchar(65535)", $con);
mysql_query("CREATE TABLE {$table_prefix}posts (ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), Name varchar(255), Parent int, Children varchar(65535), Time int), Moderators varchar(65535)", $con);

mysql_close($con);

die("Successfully set up databases!")
?>
