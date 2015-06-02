<?php
setupMinorErrorLog();
set_error_handler('recordMinorErrors', E_NOTICE | E_STRICT);
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require('./version.php');
require('./utility/setup-session.php');
require('./utility/configfile.php');
$configfile = new Configfile('config.ini');
require("./functions.php");
require("./lang/Translate.php");
$serverdir = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$_SESSION[$sprefix]['serverdir'] = $serverdir;

/******  Check that this installation is initialized  ******/
$dbconfig = new Configfile("./dbconnection.ini", false, true, false);
$error = false;
if (! $dbconfig->exists("dbhost")) {
    unset($dbconfig);
    // The database connection parameters are not set up yet.
    require("./lang/Translate.php");
    require("./utility/setup-dbconfig.php");
    exit(0);
} else {
    unset($dbconfig);
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
}
if (getGET('initialize') == 'Flexical') {
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
    require("./utility/initialuser.php");
    exit(0);
}

/************* Set up configuration *********************/
require("./utility/calendarconfig.php");
$Config = new CalendarConfig($version);
$configuration = $Config->getConfig();
if (! $configuration) {
    // The configuration hasn't been set up yet.
    require("./utility/config.php");
    exit(0);
}
$language = $configuration['language'];
require("./lang/Translate.php");
if (!file_exists("css/categorystyles.css")) {
    refreshcss();
}

/******  Continue Serving Calendar ******/

if ($configuration['local_php_library']) {
    set_include_path(get_include_path().":{$configuration['local_php_library']}");
}

// print_r($_GET); // Useful for debugging
if (getGET('debug')=="filter") {
    print_r($_SESSION[$sprefix]['filters']);
    exit(0);
} elseif (getGET('debug')=="session") {
    print_r($_SESSION[$sprefix]);
    exit(0);
}
$now = getdate();
$day = (getGET('day'))? intval(getGET('day')) :
    getIndexOr($_SESSION[$sprefix], 'day', $now['mday']);
$month = (getGET('month'))? intval(getGET('month')) :
    getIndexOr($_SESSION[$sprefix], 'month', $now['mon']);
$year = (getGET('year'))? intval(getGET('year')) :
    getIndexOr($_SESSION[$sprefix], 'year', $now['year']);
$id = (getGET('id'))? intval(getGET('id')) :
    getIndexOr($_SESSION[$sprefix], 'id', -1);
$length = getGET('length')? intval(getGET('length')) : $_SESSION[$sprefix]['length'];
$unit = getGET('unit')? getGET('unit') : $_SESSION[$sprefix]['unit'];
$action = getGET('action')? getGET('action') :
        getIndexOr($_SESSION[$sprefix], 'action',
            $configuration['default_action']);
$toggle = getGET('toggle');
$current = getGET('current', false);
if (getGET('listsubmit') && ! array_key_exists('opentime', $_GET)) {
    // coming from event view form, lack of opentime means false
    $opentime = 0;
} elseif (getGET('opentime')) {
    $opentime =  intval($_GET['opentime']);
} elseif (array_key_exists('opentime', $_SESSION[$sprefix])) {
    $opentime = $_SESSION[$sprefix]['opentime'];
} else {
    $opentime = $configuration['default_open_time'];
}
// Sanity check
if (! is_numeric($day)) {
    $_SESSION[$sprefix]['day'] = $now['mday'];
    setMessage(__('daynumeric')." ({$day})");
    header ("Location: http://".$serverdir."/index.php?action=$action&day={$now['mday']}&month=$month&year=$year&id=$id&length=$length&unit=$unit&opentime=$opentime");
}
if (! is_numeric($month)) {
    $_SESSION[$sprefix]['month'] = $now['month'];
    setMessage(__('monnumeric')." ({$month})");
    header ("Location: http://".$serverdir."/index.php?action=$action&day=$day&month={$now['mon']}&year=$year&id=$id&length=$length&unit=$unit&opentime=$opentime");
}
if (! is_numeric($year)) {
    $_SESSION[$sprefix]['year'] = $now['year'];
    setMessage(__('yearnumeric')." ({$year}-{$_GET['year']})");
    header ("Location: http://".$serverdir."/index.php?action=$action&day=$day&month=$month&year={$now['year']}&id=$id&length=$length&unit=$unit&opentime=$opentime");
}

// Set up categories for display
if (! isset($_SESSION[$sprefix]['allcategories'])) {
    $q = $dbh->query("SELECT `name` FROM `{$tablepre}categories`
        ORDER BY `name`");
    while ($row = $q->fetch()) $_SESSION[$sprefix]['allcategories'][] = $row[0];
}
if (getGET('categories')) {
    $newcategories = explode(",", $_GET['categories']);
    $_SESSION[$sprefix]['categories'] = array_intersect(
        $_SESSION[$sprefix]['allcategories'], $categorynames);
} elseif (! array_key_exists('categories', $_SESSION[$sprefix])) {
    $_SESSION[$sprefix]['categories'] = $_SESSION[$sprefix]['allcategories'];
}

/******** set up the site tabs ****************/
$sitetabs = array_combine($configuration['sitetabs'], array_fill(0, count($configuration['sitetabs']), 0));

/******** get time of last event modification ***********/
$lastmodtime = filemtime("timestamp.txt");

/*********
 * Set day, month and year to present if
 * "current" is specified
 */
$d = $current ? date("j") : $day;
$m = $current ? date("n") : $month;
$y = $current ? date("Y") : $year;

/*** Set length and unit to given or defaults.  ***/
$l = (!$length) ? 1 : $length;
$u = (!$unit) ? 2 : $unit; // default unit is "month"

$o = $opentime;

/****  Set up time format and respond to toggle command  ****/
if (!isset($_SESSION[$sprefix]['timeformat'])) {
    $time_translation = array('twelve' => 12, 'twenty-four' => 24);
    $_SESSION[$sprefix]['timeformat'] = $time_translation[$configuration['default_time']];
    unset ($time_translation);
}
if ($toggle=="time") {
    if ($_SESSION[$sprefix]['timeformat'] == 24) {
        $_SESSION[$sprefix]['timeformat'] = 12;
    } elseif ($_SESSION[$sprefix]['timeformat'] == 12) {
        $_SESSION[$sprefix]['timeformat'] = 24;
    }
    header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
}
/****  Set up default tz for display and respond to toggle command ****/
if (!isset($_SESSION[$sprefix]['usertz'])) {
    $_SESSION[$sprefix]['usertz'] = "on";
}
if ($toggle=="usertz") {
    if ($_SESSION[$sprefix]['usertz'] == "on")
        $_SESSION[$sprefix]['usertz'] = "off";
    else
        $_SESSION[$sprefix]['usertz'] = "on";
}

$_SESSION[$sprefix]['day'] = $d;
$_SESSION[$sprefix]['month'] = $m;
$_SESSION[$sprefix]['year'] = $y;
$_SESSION[$sprefix]['length'] = $l;
$_SESSION[$sprefix]['unit'] = $u;
$_SESSION[$sprefix]['action'] = $action;
$_SESSION[$sprefix]['opentime'] = $o;
$_SESSION[$sprefix]['id'] = $id;

$auth = auth();

if (getGET('admin') == 'configure') {
    require('./utility/config.php');
    exit(0);
} elseif (getGET('admin') == 'backup') {
    require('./utility/dumpdb.php');
    exit(0);
} elseif (getGET('admin') == 'restore') {
    require('./utility/restoredb.php');
    exit(0);
} elseif (getGET('admin') == 'configuration-history') {
    require('./utility/config-history.php');
    exit(0);
}

$templates = getTemplateNames($includeroot);

if (in_array($action, $templates)) {
    if (file_exists("templates/{$action}")) {
        if (is_file("templates/{$action}/load.php")) {
            $here = getcwd();
            chdir("templates/{$action}");
            require("./load.php");
            chdir($here);
        }
    }
    require("./templates/{$action}.php");
} else {
    echo __("unknown template:").htmlspecialchars($action);
    unset($_SESSION[$sprefix]['action']);
}

function recordMinorErrors($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
    $f = fopen("Error-Notices.log", "a");
    fwrite($f, "{$errfile}:{$errline} ({$errno}) {$errstr}\n");
}

function setupMinorErrorLog()
{
    if (file_exists("Error-Notices.log"))
        unlink("Error-Notices.log");
}

// vim: set tags+=../**/tags :
