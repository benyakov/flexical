<?php
$dir = dirname($_SERVER['PHP_SELF']);
$exploded = explode("/", $dir);
$imploded = implode("/", array_slice($exploded, 0, count($exploded)-1));
$serverdir = $_SERVER['HTTP_HOST'] .  $imploded;
header("Location: http://$serverdir");
?>
