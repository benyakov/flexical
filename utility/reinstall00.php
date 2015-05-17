<?php
// Warning: This script deletes all the database tables and reinitializes the calendar
// Do not run this script unless you want all of that data deleted!  You have been warned!

require("../db.php");
$installroot = dirname(dirname($_SERVER['SCRIPT_NAME']));
$includeroot = dirname(dirname(__FILE__));
require("./setup-session.php");
if (array_key_exists('authdata', $_SESSION[$sprefix]) &&
    $_SESSION[$sprefix][$authdata]['userlevel'] <= 3) {
    echo "Access Denied";
    exit(0);
}
$sql = "drop table `{$tablepre}sitetabs-configa`, `{$tablepre}eventstb`, `{$tablepre}categories`, `{$tablepre}users`,
    `{$tablepre}config`";
$dbh->exec($sql);
header("Location: ../index.php?initialize=Flexical");
// vim: set tags+=../../**/tags :
