<?php
$dir = dirname($_SERVER['PHP_SELF']);
$exploded = explode("/", $dir);
$serverdir = implode("/", array_slice($exploded, 0, count($exploded)-1));
header("Location: $serverdir");
?>
