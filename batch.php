<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");
if ($configuration['local_php_library']) {
    set_include_path(get_include_path().":{$configuration['local_php_library']}");
}
$action = $_SESSION[$sprefix]['action'];
$auth = auth();

if (array_key_exists('cancel', $_POST)) {
    setMessage(__('operationcancelled'));
    header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    exit(0);
}

if (($auth < 3) || (! array_key_exists('flag', $_GET))
    || (! array_key_exists('filters', $_SESSION[$sprefix])) || (! $_SESSION[$sprefix]['filters']))
{
    setMessage(__('accessdenied'));
    header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    exit(0);
} else {
	$authdata = $_SESSION[$sprefix]['authdata'];
	$uid = $authdata['uid'];
}

if ("delete" == $_GET['flag']) {
    if ($_SESSION[$sprefix]['filters']['related']) {
        $q = $dbh->prepare("DELETE `m` FROM `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            WHERE `m`.`related` = :related");
        $q->bindParam(":related", $_SESSION[$sprefix]['filters']['related']);
    } else {
        list($lowdate, $highdate) = getDateRange();
        $filterclause = getfilterclause(" AND ");
        $categorymatches = categoryMatchString();
        $q = $dbh->prepare("DELETE FROM `m` USING `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            WHERE `m`.`date` >= :lowdate
            AND `m`.`date` <= :highdate
            {$filterclause}
            {$categorymatches}") ;
        $q->bindParam(":lowdate", $lowdate);
        $q->bindParam(":highdate", $highdate);
    }
    if ($q->execute()) {
        setMessage($q->rowCount() . __('events deleted'));
    } else {
        $dberror = array_pop($q->errorInfo());
        setMessage("Problem deleting events: {$dberror}");
    }
} elseif ("relate" == $_GET['flag']) {
    if ($_SESSION[$sprefix]['filters']['related']) {
        setMessage(__('already related'));
        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
        exit(0);
    }
    list($lowdate, $highdate) = getDateRange();
    $cats = categoryMatchString();
    $filterclause = getfilterclause(" AND ");
    $dbh->beginTransaction();
    $q = $dbh->prepare("SELECT `id` FROM `{$tablepre}eventstb` as `m`
                LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
                WHERE `m`.`date` >= :lowdate
                AND `m`.`date` <= :highdate
                {$filterclause}
                {$cats}
                ORDER BY `date` ASC LIMIT 1");
    $q->bindParam(":lowdate", $lowdate);
    $q->bindParam(":highdate", $highdate);
    $q->execute() or die(array_pop($q->errorInfo()));
    $masterid = $q->fetchColumn(0);
    $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` as `m`
                LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
                SET `m`.`related` = '{$masterid}'
                WHERE `m`.`date` >= :lowdate
                AND `m`.`date` <= :highdate
                {$filterclause}
                {$cats}");
    $q->bindParam(":lowdate", $lowdate);
    $q->bindParam(":highdate", $highdate);
    if ($q->execute()) {
        setMessage(__('events related').$q->rowCount());
        $dbh->commit();
    } else {
        $dberror = array_pop($q->errorInfo());
        setMessage("Problem relating events: {$dberror}");
        $dbh->rollback();
    }
} elseif ("title" == $_GET['flag']) {
    ?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?= __('retitlefiltered') ?></title>
		<link rel="stylesheet" type="text/css" href="css/styles-pop.css">
	</head>
	<body>
	<span class="add_new_header"><?= __('retitlefiltered') ?></span>
		<table border=0 cellspacing=7 cellpadding=0>
        <form name="batchForm" method="POST" action="<?=$_SERVER['PHP_SELF']?>?flag=submit">
        <input type="hidden" name="uid" value="<?=$uid?>">
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('title')?></span></td>
            <td>
            <input type="text" name="title" size="25" maxlength="50"></td>
        </tr>
        <tr><td colspan="2">
                <input type="submit" name="submit" value="<?= __('submitbutton') ?>">&nbsp;
                <input type="submit" name="cancel" value="<?= __('cancel') ?>">
            </td>
        </tr>
        </form>
        </table>
    </body>
    </html>
    <?php exit(0);
} elseif ("text" == $_GET['flag']) {
    ?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?= __('retextfiltered') ?></title>
		<link rel="stylesheet" type="text/css" href="css/styles-pop.css">

	</head>
	<body>
	<span class="add_new_header"><?= __('retextfiltered') ?></span>
		<table border=0 cellspacing=7 cellpadding=0>
        <form name="batchForm" method="POST" action="<?=$_SERVER['PHP_SELF']?>?flag=submit">
        <input type="hidden" name="uid" value="<?=$uid?>">
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('text')?></span></td>
            <td>
            <textarea cols=44 rows=12 name="text"></textarea></td>
        </tr>
        <tr><td colspan="2">
                <input type="submit" name="submit" value="<?= __('submitbutton') ?>">&nbsp;
                <input type="submit" name="cancel" value="<?= __('cancel') ?>">
            </td>
        </tr>
        </form>
        </table>
    </body>
    </html>
    <?php exit(0);
} elseif ("time" == $_GET['flag']) {
    ?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?= __('retimefiltered') ?></title>
		<link rel="stylesheet" type="text/css" href="css/styles-pop.css">
		<script type="text/javascript" language="JavaScript">
        <?php js_zeroTime('batchForm'); js_revealjsonly(); ?>
		</script>

	</head>
	<body onload="revealjsonly(['start_hour-spinner', 'start_minute-spinner',
                                'end_hour-spinner', 'end_minute-spinner']);">
	<span class="add_new_header"><?= __('retimefiltered') ?></span>
		<table border=0 cellspacing=7 cellpadding=0>
        <form name="batchForm" method="POST" action="<?=$_SERVER['PHP_SELF']?>?flag=submit">
        <input type="hidden" name="uid" value="<?=$uid?>">
        <input type="hidden" name="time" value="1">
        <tr>
            <td nowrap valign="top" align="right">
            <span class="form_labels"><?=__('All Day')?></span></td>
            <td><input type="checkbox" name="all_day" value="1" onClick="zeroTime()"></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('starttime')?></span></td>
            <td><?php hourBox("0", "batchForm", "start_hour"); ?><b>:</b><?php
                   minuteBox("0", "batchForm", "start_minute");
                   amPmPullDown("", "start", true, false); ?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('endtime')?></span></td>
            <td colspan=2><?php hourBox("0", "batchForm", "end_hour"); ?><b>:</b><?php
                   minuteBox("0", "batchForm", "end_minute");
                   amPmPullDown("", "end", true, false); ?></td>
        </tr>
        <tr><td colspan="2">
                <input type="submit" name="submit" value="<?= __('submitbutton') ?>">&nbsp;
                <input type="submit" name="cancel" value="<?= __('cancel') ?>">
            </td>
        </tr>
        </form>
        </table>
    </body>
    </html>
    <?php exit(0);

} elseif ("category" == $_GET['flag']) {
    ?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?= __('recategoryfiltered') ?></title>
		<link rel="stylesheet" type="text/css" href="css/styles-pop.css">
	</head>
	<body>
	<span class="add_new_header"><?= __('recategoryfiltered') ?></span>
    <table border=0 cellspacing=7 cellpadding=0>
        <form name="batchForm" method="POST" action="<?=$_SERVER['PHP_SELF']?>?flag=submit">
        <input type="hidden" name="uid" value="<?=$uid?>">
        <input type="hidden" name="categorychoice" value="1">
        <tr>
            <td>
                <?php categoryPullDown(); ?>
            </td>
            <td>
                <input type="text" name="newcategory" size="14" value="" maxlength="50">
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submit" value="<?= __('submitbutton') ?>">&nbsp;
                <input type="submit" name="cancel" value="<?= __('cancel') ?>">
            </td>
        </tr>
        </form>
        </table>
    </body>
    </html>
    <?php exit(0);
} elseif ("google" == $_GET['flag']) {
    if ($_SESSION[$sprefix]['filters']['related']) {
        $q = $dbh->prepare("SELECT `m`.`id`,
            DATE_FORMAT(`m`.`date`, '%Y-%m-%d') AS `date`,
            `m`.`title`, `m`.`text`, `m`.`all_day`,
            TIME_FORMAT(`m`.`start_time`, '%H:%i') AS `stime`,
            TIME_FORMAT(`m`.`end_time`, '%H:%i') AS `etime`,
            `c`.`name` AS `category`
            FROM `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            WHERE `m`.`related` = :related
            ORDER BY `m`.`date`, `start_time");
        $q->bindParam(":related", $_SESSION[$sprefix]['filters']['related']);
        $categorymatches = "";
    } else {
        list($lowdate, $highdate) = getDateRange();
        $filterclause = getfilterclause(" AND ");
        $categorymatches = categoryMatchString();
        $q = $dbh->prepare("SELECT `m`.`id`,
            DATE_FORMAT(`m`.`date`, '%Y-%m-%d') AS `date`,
            `m`.`title`, `m`.`text`, `m`.`all_day`,
            TIME_FORMAT(`m`.`start_time`, '%H:%i') AS `stime`,
            TIME_FORMAT(`m`.`end_time`, '%H:%i') AS `etime`,
            `c`.`name` AS `category`
            FROM `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            WHERE `m`.`date` >= :lowdate
            AND `m`.`date` <= :highdate
            {$filterclause}
            {$categorymatches}
            ORDER BY `m`.`date`, `start_time");
        $q->bindParam(":lowdate", $lowdate);
        $q->bindParam(":highdate", $highdate);
    }
    $q->execute();
    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
    $client = "";
    try {
        $client = Zend_Gdata_ClientLogin::getHttpClient($configuration['google_user'],
            $configuration['google_password'], $service);
    } catch (Zend_Gdata_App_AuthException $ae) {
        setMessage("Problem Authenticating: " . $ae->exception());
    }
    if ($client) {
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $created[] = createGoogleEvent($client, $title=$row['title'],
                $desc=$row['text'], $category=$row['category'],
                $date=$row['date'], $startTime=$row['stime'],
                $endTime=$row['etime'], $allday=$row['all_day'],
                $TZO=$TZoffset);
        }
        setMessage(implode($created, ', '));
    }
/****  Submit actions below here ****/
} elseif ("submit" == $_GET['flag'] && array_key_exists("title", $_POST)) {
    // Submit changes in title
    if (array_key_exists('cancel', $_POST)) {
        setMessage(__('operationcancelled'));
        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
        exit(0);
    }
    if ($_SESSION[$sprefix]['filters']['related']) {
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid` = :uid,
            `m`.`title` = :title
            WHERE `m`.`related` = :related");
        $q->bindParam(":related", $_SESSION[$sprefix]['filters']['related']);
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":title", $_POST['title']);
    } else {
        list($lowdate, $highdate) = getDateRange();
        $categorymatches = categoryMatchString();
        $filterclause = getfilterclause(" AND ");
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid` = :uid,
            `m`.`title` = :title
            WHERE `m`.`date` >= :lowdate
            AND `m`.`date` <= :highdate
            {$filterclause}
            {$categorymatches}");
        $q->bindParam(":lowdate", $lowdate);
        $q->bindParam(":highdate", $highdate);
        $q->bindParam(":title", $_POST['title']);
        $q->bindParam(":uid", $_POST['uid']);
    }
    if ($q->execute()) {
        setMessage($q->rowCount() . __('events retitled')
            . "'{$_POST['title']}'.");
    } else {
        $dberror = array_pop($q->errorInfo());
        setMessage("Problem retitling events: {$dberror}");
    }
} elseif ("submit" == $_GET['flag'] && array_key_exists("text", $_POST)) {
    // Submit changes in text
    if (array_key_exists('cancel', $_POST)) {
        setMessage(__('operationcancelled'));
        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
        exit(0);
    }
    if ($_SESSION[$sprefix]['filters']['related']) {
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid` = :uid,
            `m`.`text` = :text
            WHERE `m`.`related` = :related");
        $q->bindParam(":related", $_SESSION[$sprefix]['filters']['related']);
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":text", $_POST['text']);
    } else {
        list($lowdate, $highdate) = getDateRange();
        $categorymatches = categoryMatchString();
        $filterclause = getfilterclause(" AND ");
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid` = :uid,
            `m`.`text` = :text
            WHERE `m`.`date` >= :lowdate
            AND `m`.`date` <= :highdate
            {$filterclause}
            {$categorymatches}");
        $q->bindParam(":lowdate", $lowdate);
        $q->bindParam(":highdate", $highdate);
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":text", $_POST['text']);
    }
    if ($q->execute()) {
        setMessage($q->rowCount() . __('events retexted'));
    } else {
        $dberror = array_pop($q->errorInfo());
        setMessage("Problem retexting events: {$dberror}");
    }
} elseif ("submit" == $_GET['flag'] && array_key_exists("time", $_POST)) {
    // Submit changes in text
    if (array_key_exists('cancel', $_POST)) {
        setMessage(__('operationcancelled'));
        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
        exit(0);
    }
	$shour = intval($_POST['start_hour']);
	$sminute = intval($_POST['start_minute']);
	$s_ampm = $_POST['start_am_pm'];
	$ehour = intval($_POST['end_hour']);
	$eminute = intval($_POST['end_minute']);
	$e_ampm = $_POST['end_am_pm'];
    $all_day = $_POST['all_day'];
	if ($shour < 12 && $s_ampm == 1) {
        $shour = $shour + 12; }
	if ($ehour < 12 && $e_ampm == 1) {
        $ehour = $ehour + 12; }
	$starttime = "$shour:$sminute:00";
	$endtime = "$ehour:$eminute:00";
    if ($_SESSION[$sprefix]['filters']['related']) {
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid` = :uid,
            `m`.`start_time` = :starttime,
            `m`.`end_time` = :endtime,
            `m`.`all_day` = :allday
            WHERE `m`.`related` = :related");
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":starttime", $starttime);
        $q->bindParam(":endtime", $endtime);
        $q->bindParam(":allday", $all_day);
        $q->bindParam(":related", $_SESSION[$sprefix]['filters']['related']);
    } else {
        list($lowdate, $highdate) = getDateRange();
        $categorymatches = categoryMatchString();
        $filterclause = getfilterclause(" AND ");
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid` = :uid,
            `m`.`start_time` = :starttime,
            `m`.`end_time` = :endtime,
            `m`.`all_day` = :allday
            WHERE `m`.`date` >= :lowdate
            AND `m`.`date` <= :highdate
            {$filterclause}
            {$categorymatches}");
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":starttime", $starttime);
        $q->bindParam(":endtime", $endtime);
        $q->bindParam(":allday", $all_day);
        $q->bindParam(":lowdate", $lowdate);
        $q->bindParam(":highdate", $highdate);
    }
    if ($all_day == "1") {
        $all_day = 1;
        $starttime = $endtime = "00:00:00";
    } else {
        $all_day = 0;
    }
    if ($q->execute()) {
        setMessage($q->rowCount() . __('events retimed') .
            " {$starttime}-{$endtime} " .
            (($all_day)?"({$_('All Day')})":"")) ;
    } else {
        $dberror = array_pop($q->errorInfo());
        setMessage("Problem retiming events: {$dberror}");
    }
} elseif ("submit" == $_GET['flag'] &&
    array_key_exists("categorychoice", $_POST)) {
    // Set new category
    if ($_POST['category'] == __("new-category")) {
        $category = $_POST['newcategory'];
    } else {
        $category = $_POST['category'];
    }
    if ($category == "") { $category = __("unspecified"); }
    $dbh->beginTransaction();
    $q = $dbh->prepare("SELECT `category` FROM `{$tablepre}categories`
        WHERE name=:category");
    $q->bindParam(":category", $category);
    $q->execute();
    $num_rows = $q->rowCount();
    if (!$num_rows) {
        $q = $dbh->prepare("INSERT INTO `{$tablepre}categories`
            SET `name`=:category");
        $q->bindParam(":category", $category);
        $q->execute();
        $q = $dbh->prepare("SELECT `category` FROM `{$tablepre}categories`
            WHERE name=:category");
        $q->bindParam(":category", $category);
        $q->execute();
    }
    $row = $q->fetch(PDO::FETCH_ASSOC);
    $categoryid = $row["category"];


    if ($_SESSION[$sprefix]['filters']['related']) {
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid`=:uid, `m`.`category`=:categoryid
            WHERE `m`.`related` = :related");
        $q->bindParam(":related", $_SESSION[$sprefix]['filters']['related']);
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":categoryid", $categoryid);
    } else {
        list($lowdate, $highdate) = getDateRange();
        $categorymatches = categoryMatchString();
        $filterclause = getfilterclause(" AND ");
        $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` AS `m`
            LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
            SET `m`.`uid`=:uid, `m`.`category`=:categoryid
            WHERE `m`.`date` >= :lowdate
            AND `m`.`date` <= :highdate
            {$filterclause}
            {$categorymatches}");
        $q->bindParam(":lowdate", $lowdate);
        $q->bindParam(":highdate", $highdate);
        $q->bindParam(":uid", $_POST['uid']);
        $q->bindParam(":categoryid", $categoryid);
    }
    if ($q->execute()) {
        setMessage($q->rowCount() . __('events recategoried') .
            " '{$category}'.");
        $dbh->commit();
    } else {
        $dberror = array_pop($q->errorInfo());
        setMessage("Problem recategorizing events: {$dberror}");
        $dbh->rollback();
    }
}

touch("timestamp.txt");
header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?action=eventlist");

function getDateRange() {
    global $sprefix;
    $d = $_SESSION[$sprefix]['day'];
    $m = $_SESSION[$sprefix]['month'];
    $y = $_SESSION[$sprefix]['year'];
    $l = $_SESSION[$sprefix]['length'];
    $u = $_SESSION[$sprefix]['unit'];
    $lowdate = "{$y}-{$m}-{$d}";
    $highdate = time_add(mktime(0,0,0,$m,$d,$y),0,0,0,
        $u==1?$l:       // "days"; see __('units')
        ($u==2?$l*7:0),   // "weeks"
        $u==3?$l:0,     // "months"
        $u==4?$l:0);     // "years"
    return array($lowdate, time_sqldate($highdate));
}

// vim: set tags+=../**/tags :
