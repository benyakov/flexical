<?php
// Review or alter the configuration history.
// Determine authorization to configure the calendar
if (function_exists("auth")) { // Running from the index.php entry point
    $authlevel = auth();
} else {
    $installroot = dirname(dirname($_SESSION['SCRIPT_NAME']));
    $includeroot = dirname(dirname(__FILE__));
    require("./setup-session.php");
    chdir("..");
    if (isset($_SESSION[$sprefix]["authdata"]["userlevel"])) { // Called directly with established session
        $authlevel = $_SESSION[$sprefix]["authdata"]["userlevel"];
        require("./utility/initialize-entrypoint.php");
    } else { // Called directly, no established session
        $authlevel = 0;
        require("./lang/Translate.php");
    }
}
if (3 > $authlevel) { ?>
<html>
<head><title><?=__('Unauthorized')?></title></head>
<body>
    <h1><?=__('Unauthorized')?></h1>
    <p>Unauthorized to access configuration page. (<?=$authlevel?>)</p>
</body>
</html> <?php
    exit(0);
}
if (array_key_exists('delete', $_POST)) {
    $serverdir = $_SESSION[$sprefix]['serverdir'];
    require_once("./utility/dbconnection.php");
    $dbh = new DBConnection();
    unset($_POST['delete']);
    $todelete = array_keys($_POST);
    $whereclause = "`timestamp` = '" . implode("' OR `timestamp` = '", $todelete) . "'";
    $sql = "DELETE FROM `{$dbh->getPrefix()}config` WHERE ({$whereclause})";
    $dbh->exec($sql);
    setMessage(__('config history deleted'));
    header("Location: http://{$serverdir}/index.php");
    exit(0);
} elseif (array_key_exists('restore', $_GET)) {
    $serverdir = $_SESSION[$sprefix]['serverdir'];
    require("../db.php");
    require("./configdb.php");
    $Config = new Configdb($version);
    $Config->restore($_GET['restore']);
    setMessage(__('configuration selected'));
    header("Location: {$installroot}/index.php");
    exit(0);
} else {
    // Obtain the history to display
    $history = $Config->all();
    $timestamps = array_keys($history);
    $fields = array_keys($history[$timestamps[0]]);
    $display = array();
    foreach ($fields as $f) {
        foreach ($history as $savepoint) {
            if (is_array($savepoint[$f])) {
                $display[$f][] = implode("<br/>", $savepoint[$f]);
            } else {
                $display[$f][] = $savepoint[$f];
            }
        }
    }
?>
<html>
<head>
    <title><?=__("configuration history")?></title>
    <link rel="stylesheet" type="text/css" href="css/styles-pop.css">
</head>
<body><h1><?=__("configuration history")?></h1>

<p class="helptext"><?=__("config history helptext")?><p>

<p><a href="index.php?admin=configure"><?=__("backtoconfiguration")?></a></p>
<p><a href="index.php"><?=__("backtocalendar")?></a></p>
<table border=1 cellspacing=2 cellpadding=2>
<form name="configHistoryDelete" method="POST" action="utility/config-history.php">
<tr><td><input type="submit" name="delete" value="<?=__("delete")?>"/></td>
<?php
    $columncount = 0;
    foreach ($display['timestamp'] as $ts) {
        $columncount += 1;
        echo "<th><input type=\"checkbox\" name=\"{$ts}\" value=\"1\"/> ";
        if ($columncount > 1) {
            echo "<a href=\"utility/config-history.php?restore=".urlencode($ts)."\">{$ts}</a>";
        } else {
            echo $ts;
        }
        echo "</th>";
    }
    echo "</tr></form>\n";
    unset($display['timestamp']);
    foreach ($display as $label => $values) {
        echo "<tr><th class=\"history-field\">".__($label)."</th>";
        $lastvalue = "";
        foreach ($values as $v) {
            if ($lastvalue != $v) {
                echo "<td>{$v}</td>";
                $lastvalue = $v;
            } else {
                echo "<td></td>";
            }
        }
        echo "</tr>\n";
    }
?>
</table>
</body>
</html>
<?php
}
// vim: set tags+=../../**/tags :
