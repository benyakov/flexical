<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require('./utility/setup-session.php');
$params = json_decode($_GET["p"]);
$template = $_GET["template"];
require("templates/{$template}/ajax.php");
