<?php
$root = dirname(__FILE__);
require_once("{$root}/lib/auth.php");
require_once("{$root}/lib/date.php");
require_once("{$root}/lib/interface.php");
require_once("{$root}/lib/js.php");
require_once("{$root}/lib/markdown.php");
require_once("{$root}/lib/messages.php");
require_once("{$root}/lib/standardizations.php");
require_once("{$root}/lib/transformations.php");

/*** Convenience Factory & Data Manipulation Functions ***/

function getConfigfile($write) {
    return new Configfile("./config.ini", false, true, $write);
}

function saveConfig($assoc) {
    $configfile = getConfigfile(true);
    foreach ($assoc as $k=>$v)
        $configfile->set($k, $v);
    $saved = $configfile->save();
    unset($configfile);
}

function getConfiguration() {
    $calconfig = new CalendarConfig(); // singleton holding config db
    return $calconfig->getConfig();
}


/*** Utilities ***/

function refreshcss() {
    $dbh = new DBConnection();
    // Refresh db-based static CSS files
    $out = array();
    $q = $dbh->query("SELECT `name`, `style` FROM `{$dbh->getPrefix()}categories`");
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $cssname = toCSSID($row['name']);
        $out[] = "span.{$cssname} { {$row['style']} }";
    }
    $fh = fopen("css/categorystyles.css", "w");
    fwrite($fh, implode("\n", $out));
    fclose($fh);
}

function createGoogleEvent($client, $title, $desc, $category,
    $date, $startTime, $endTime, $allday, $TZO) {
    $gdataCal = new Zend_Gdata_Calendar($client);
    $newEvent = $gdataCal->newEventEntry();
    $newEvent->title = $gdataCal->newTitle($title);
    $newEvent->category = array($gdataCal->newCategory($category));
    $newEvent->content = $gdataCal->newContent($desc);
    $when = $gdataCal->newWhen();
    if ($allday) {
        $when->startTime = $date;
        $when->endTime = $date;
    } else {
        $when->startTime = "{$date}T{$startTime}:00.000{$TZO}:00";
        $when->endTime = "{$date}T{$endTime}:00.000{$TZO}:00";
    }
    $newEvent->when = array($when);

    $createdEvent = $gdataCal->insertEvent($newEvent);
    return $createdEvent->id->text;
}

function getIndexOr($var, $key, $default='') {
    if (isset($var[$key])) return $var[$key];
    else return $default;
}

function getGET($index, $default='') {
    return getIndexOr($_GET, $index, $default);
}

function getPOST($index, $default='') {
    return getIndexOr($_POST, $index, $default);
}

function templateFileToName($file) {
    return basename($file, ".php");
}

function getTemplateNames($includeroot='.') {
    $templates = glob($includeroot.'/templates/*php');
    return array_map("templateFileToName", $templates);
}

// vim: set foldmethod=indent tags+=../../tags :
