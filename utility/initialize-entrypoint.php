<?php
// Sets up session, database, configuration and translation for root-level entry point scripts.
require_once("./version.php");
require('./utility/setup-session.php');
require("./utility/dbconnection.php");
try {
    $dbh = new DBConnection();
} catch (PDOException $e) {
    $error = true;
    require("./lang/Translate.php");
    require("./utility/setup-dbconfig.php");
    exit(0);
}
$tablepre = $dbh->getPrefix();
require("./utility/calendarconfig.php");
$Config = new CalendarConfig($version);
$configuration = $Config->getConfig();
$language = $configuration['language'];
require_once("./lang/Translate.php");
require_once("./utility/configfile.php");
require("./functions.php");
// vim: set tags+=../../**/tags :
