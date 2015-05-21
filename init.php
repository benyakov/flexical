<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
$serverdir = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
require('./version.php');
require('./lib/exceptions.php');
require('./autoload.php');
$configfile = new Configfile('config.ini');
require('./functions.php');
require('./utility/lang.php');
$_SESSION[$sprefix]['serverdir'] = $serverdir;

/******  Check that this installation is initialized  ******/
$dbconfig = new Configfile("./dbconnection.ini", false, true, false);
$error = false;
if (! $dbconfig->exists("dbhost")) {
    unset($dbconfig);
    // The database connection parameters are not set up yet.
    require("./utility/setup-dbconfig.php");
    exit(0);
} else {
    unset($dbconfig);
    try {
        $dbh = new DBConnection();
    } catch (PDOException $e) {
        $error = true;
        require("./utility/setup-dbconfig.php");
        exit(0);
    }
    $tablepre = $dbh->getPrefix();
}
if ($_GET['initialize'] == 'Flexical') {
    // Check for pre-existence of tables
    if (! $configfile->exists("dbversion"))
        require("./utility/createtables.php");
    $dbversion = $configfile->get("dbversion");
    touch("timestamp.txt");
}

/******** Check for pre-existence of users ***************/
if (! $configfile->exists("hasuser")) {
    session_destroy();
    require('./utility/setup-session.php');
    require('./utility/initialuser.php');
    exit(0);
}

/************* Set up configuration *********************/
$Config = new CalendarConfig($version);
$configuration = $Config->getConfig();
if (! $configuration) {
    // The configuration hasn't been set up yet.
    require('./utility/config.php');
    exit(0);
}
$language = $configuration['language'];
require('./utility/lang.php');
if (!file_exists('css/categorystyles.css')) {
    refreshcss();
}

/************** Include local php library, if configured *****/
if ($configuration['local_php_library']) {
    set_include_path(get_include_path().":{$configuration['local_php_library']}");
}

/************** Set up categories for display *****/
$q = $dbh->query("SELECT `name` FROM `{$tablepre}categories`
    ORDER BY `name`");
if ($_GET['categories']) {
    $newcategories = explode(",", $_GET['categories']);
    $categorynames = array();
    while ($row = $q->fetch()) $categorynames[] = $row[0];
    $_SESSION[$sprefix]['categories'] = array_intersect($newcategories, $categorynames);
} elseif (! array_key_exists('categories', $_SESSION[$sprefix])) {
    $_SESSION[$sprefix]['categories'] = array();
    while ($row = $q->fetch()) {
        array_push($_SESSION[$sprefix]['categories'], $row[0]);
    }
}
