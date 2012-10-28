<?php
/**
 * Include this at the very beginning of your PHP page.
 * @author Calclavia
 */
$DATA_DIRECTORY = "data/";

require_once(dirname(__DIR__)."/class/forum_element.php");

require_once(dirname(__DIR__)."/class/forum.php");
require_once(dirname(__DIR__)."/class/thread.php");
require_once(dirname(__DIR__)."/class/board.php");
require_once(dirname(__DIR__)."/class/post.php");
?>