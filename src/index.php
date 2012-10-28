<?php
include("config.php");

//Some test code
$testPost = new Post("TEST", array("calclavia"), "");
$thread = new Thread("TEST", array("calclavia"), $testPost);
die($testPost);
echo $testPost->getName() .": ". $testPost->getDate();
?>