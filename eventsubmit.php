<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");
$_ = '__';

$d = $_SESSION[$sprefix]['day'];
$m = $_SESSION[$sprefix]['month'];
$y = $_SESSION[$sprefix]['year'];
$l = $_SESSION[$sprefix]['length'];
$u = $_SESSION[$sprefix]['unit'];
$action = $_SESSION[$sprefix]['action'];

if (array_key_exists('cancel', $_GET)) {
    setMessage(__('operationcancelled'));
    header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    exit(0);
}

if (!auth()) {
    setMessage(__('accessdenied'));
    header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    exit(0);

} else {

	$flag = $_GET['flag'];
	$id = (int) $_GET['id'];

	if ($flag == "add") {
		setMessage(submitEventData());

	} elseif ($flag == "edit") {
		setMessage(submitEventData($id));

    } elseif ($flag == "copy") {
        setMessage(copyEvent($id));

	} elseif ($flag == "delete") {
        $dbh->beginTransaction();
        $related_txt = "";
        $related_id = false;
        if (getIndexOr($_GET, 'include_related', false)) {
            $qs = $dbh->prepare("SELECT DATE_FORMAT(`date`, \"%Y-%m-%d\")
                as `date`, `related`
                FROM `{$tablepre}eventstb` WHERE `id` = :id");
            $qs->bindParam(':id', $id);
            $qs->execute() or die(array_pop($q->errorInfo()));
            $row = $qs->fetch(PDO::FETCH_ASSOC);
            $origdate = $row['date'];
            $related_id = $row['related'];
            if ($related_id) {
                $related_txt = "`related` = :related_id";
                if (getIndexOr($_GET, 'future_only', false)) {
                    $future_only = " AND date >= :date";
                } else $future_only = "";
                $related_txt = " OR ({$related_txt}{$future_only})";
            } else $related_txt = "";
        }
        $q = $dbh->prepare("DELETE FROM `{$tablepre}eventstb`
            WHERE `id` = :id $related_txt");
        $q->bindValue(':id', $id);
        if ($related_id) {
            $q->bindParam(':related_id', $related_id);
            if ($future_only) $q->bindValue(':date', $origdate);
        }
		$q->execute() or die(array_pop($q->errorInfo()));
        $dbh->commit() or die(array_pop($q->errorInfo()));
        $affected = $q->rowCount();
        setMessage("{$_('event deleted')} ({$affected})");
		header("Location: http://{$_SERVER['HTTP_HOST']}".dirname($_SERVER['PHP_SELF'])."/index.php?action={$action}&day={$d}&month={$m}&year={$y}&length={$l}&unit={$u}");
        exit(0);

	} else {
		__('accesswarning');
	}
}

function submitEventData ($id="") {
    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
	global $sprefix;
    $rv = "";
    $include_related = getIndexOr($_POST, 'include_related', '');
	$uid = $_POST['uid'];
	$month = $_POST['month'];
	$day = $_POST['day'];
	$year = $_POST['year'];
	$shour = intval($_POST['start_hour']);
	$sminute = intval($_POST['start_minute']);
	$s_ampm = $_POST['start_am_pm'];
	$ehour = intval($_POST['end_hour']);
	$eminute = intval($_POST['end_minute']);
	$e_ampm = $_POST['end_am_pm'];
    $all_day = $_POST['all_day'];
    $timezone = $_POST['timezone'];

    // Check that Title is filled.
    if (!$_POST['title']) {
        setMessage(__('blanktitle'));
        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?month={$_POST['month']}&year={$_POST['year']}&day={$_POST['day']}");
        exit(0);
    }
    // If hour is less than 12, apply "pm" by adding 12
    if ($shour < 12 && $s_ampm == 1) {
        $shour = $shour + 12;
    }
	if ($ehour < 12 && $e_ampm == 1) {
        $ehour = $ehour + 12;
    }
	$starttime = "$shour:$sminute:00";
    if (mktime($shour,$minute,0,0,0,0)>mktime($ehour,$eminute,0,0,0,0)) {
        $endtime = $starttime;
    } else {
        $endtime = "$ehour:$eminute:00";
    }

    if ($_POST['all_day'] == "1") {
        $all_day = 1;
        $starttime = $endtime = "00:00:00";
    } else {
        $all_day = 0;
    }

    if ($_POST['category'] == __("new-category")) {
        $cat = preg_replace("/[^- A-Za-z0-9]+/", "", $_POST['newcategory']);
        $cat = preg_replace("/ +/", " ", $cat);
        $category = $cat;
    } else {
        $category = $_POST['category'];
    }

    $dbh->beginTransaction();
    /* Make sure the category exists */
    $qc = $dbh->prepare("SELECT `category` FROM `{$tablepre}categories`
        WHERE `name`=:category");
    $qc->bindParam(':category', $category);
    $qc->execute() or die(array_pop($q->errorInfo()));
    if (!$qc->rowCount()) {
        $rv.="Inserting category";
        $qc = $dbh->prepare("INSERT INTO `{$tablepre}categories`
            SET `name` = :category");
        $qc->bindParam(":category", $category);
        $qc->execute() or die(array_pop($qc->errorInfo()));
        array_push($_SESSION[$sprefix]['categories'], $category);
        $qc = $dbh->prepare("SELECT `category` FROM `{$tablepre}categories`
            WHERE `name`=:category");
        $qc->bindParam(":category", $category);
        $qc->execute() or die(array_pop($qc->errorInfo()));
    }
    $row = $qc->fetch(PDO::FETCH_ASSOC);
    $categoryid = $row["category"];

	if ($id) {
        if ($include_related) {
            /* Determine how much the date has changed */
            $q = $dbh->prepare("SELECT DATE_FORMAT(`date`, \"%Y-%m-%d\")
                AS `date`, DATEDIFF(date, :selecteddate) AS `diff`
                FROM `{$tablepre}eventstb`
                WHERE id=:id");
            $q->bindValue(":selecteddate",
                "{$_POST['year']}-{$_POST['month']}-{$_POST['day']}");
            $q->bindParam(":id", $id);
            $q->execute() or die("Problem getting datediff");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $thisdate = $row['date'];
            $datediff = $row['diff'];
            if (getIndexOr($_POST, 'future_only', false)) {
                $future_only = " AND date >= :thisdate";
            } else $future_only = "";
            $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` SET
                `date` = DATE_SUB(date, INTERVAL :diff DAY),
                `uid`=:uid,
                `start_time`=:starttime, `end_time`=:endtime,
                `title`=:title, `category`=:categoryid, `text`=:text,
                `all_day`=:all_day, `timezone`=:timezone
                WHERE `related`=:related {$future_only}");
            $q->bindParam(':diff', $datediff);
            $q->bindParam(':uid', $_POST['uid']);
            $q->bindParam(':starttime', $starttime);
            $q->bindParam(':endtime', $endtime);
            $q->bindParam(':title', $_POST['title']);
            $q->bindParam(':categoryid', $categoryid);
            $q->bindParam(':text', $_POST['text']);
            $q->bindParam(':all_day', $all_day);
            $q->bindParam(':timezone', $timezone);
            $q->bindparam(':related', $_POST['related']);
            if ($future_only) $q->bindParam(":thisdate", $thisdate);
        } else {
            $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` SET
                `uid`=:uid, `date`=:date,
                `start_time`=:starttime, `end_time`=:endtime,
                `title`=:title, `category`=:categoryid, `text`=:text,
                `all_day`=:all_day, `timezone`=:timezone
                WHERE `id`=:id");

            $q->bindValue(':date', "{$_POST['year']}-{$_POST['month']}-{$_POST['day']}");
            $q->bindParam(':uid', $_POST['uid']);
            $q->bindParam(':starttime', $starttime);
            $q->bindParam(':endtime', $endtime);
            $q->bindParam(':title', $_POST['title']);
            $q->bindParam(':categoryid', $categoryid);
            $q->bindParam(':text', $_POST['text']);
            $q->bindParam(':all_day', $all_day);
            $q->bindParam(':timezone', $timezone);
            $q->bindParam(':id', $id);
            $result = __('updated');
        }
	} else {
		$q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb` SET
            `uid`=:uid, `date`=:date,
            `start_time`=:starttime, `end_time`=:endtime,
            `title`=:title, `category`=:categoryid, `text`=:text,
            `all_day`=:all_day, `timezone`=:timezone");
        $q->bindValue(':date', "{$_POST['year']}-{$_POST['month']}-{$_POST['day']}");
        $q->bindParam(':uid', $_POST['uid']);
        $q->bindParam(':starttime', $starttime);
        $q->bindParam(':endtime', $endtime);
        $q->bindParam(':title', $_POST['title']);
        $q->bindParam(':categoryid', $categoryid);
        $q->bindParam(':text', $_POST['text']);
        $q->bindParam(':all_day', $all_day);
        $q->bindParam(':timezone', $timezone);
		$result = __('added');
	}
    $q->execute() or die(array_pop($q->errorInfo()));
    $dbh->commit();
    $rowcount = $q->rowCount();
    unset($_SESSION[$sprefix]['allcategories']);
    return $_POST['title'] . " {$result} ({$rowcount})";
}

function copyEvent($id)
{
    global $tablepre, $dbh;
    if (is_numeric($_POST['repeatskip'])) {
        $repeatskip = intval($_POST['repeatskip']);
    } elseif (array_key_exists('repeatskip', $_POST)) {
        setMessage(__('repeatskipnan')." : {$_POST['repeatskip']} numeric? ".is_numeric($_POST['repeatskip']));
        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
        exit(0);
    }
    $repeattype = $_POST['repeattype'];
	$month = $_POST['month'];
	$day = $_POST['day'];
	$year = $_POST['year'];
    $repeatcount = $_POST['repeatcount'];
    if ($repeatcount == 0) {
        // This allows us to ignore it in our copy loop
        $repeatcount = -1;
    } else {
        // This date allows the repetition through the Unix epoch
        $year = 2038;
        $month = 1;
        $day = 18;
    }
    $include_related = getIndexOr($_POST, 'include_related', false);
    $dbh->beginTransaction();
    $q = $dbh->prepare("SELECT `id`, DAY(`date`) AS `d`,
        MONTH(`date`) AS `m`, YEAR(`date`) AS `y`,
        `all_day`, `start_time`, `end_time`, `title`, `category`,
        `text`, `related`, `timezone`
        FROM `{$tablepre}eventstb` WHERE `id`=:id");
    $q->bindParam(':id', $id);
    $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));

    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $dbh->rollback();
        return __("missingevent");
    }
    $m = $row['m']; unset($row['m']);
    $d = $row['d']; unset($row['d']);
    $y = $row['y']; unset($row['y']);

    if ($include_related) {
        if (! $row['related']) {
            $row['related'] = $row['id'];
        }
        $qr = $dbh->prepare("UPDATE `{$tablepre}eventstb`
            SET `related` = :related
            WHERE `id`=:id");
        $qr->bindParam(':related', $row['related']);
        $qr->bindParam(':id', $id);
        $qr->execute() or die(__LINE__ . array_pop($q->errorInfo()));
    } else {
        unset($row['related']);
    }
    unset($row["id"]);

    $inserted = array();
    $original = mktime(0, 0, 0, $m, $d, $y);

    if ($repeattype == "single") {
        $row["date"] = "{$year}-{$month}-{$day}";
        list($tokenl, $names, $keyl) = assocToSQLInsert($row);
        $tokens = implode(',', $tokenl);
        $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb` ({$names})
            VALUES ({$tokens})");
        for ($i=0; $i<count($keyl); $i++) {
            $q->bindParam($tokenl[$i], $row[$keyl[$i]]);
        }
        $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));
        $inserted[] = "$year-$month-$day";

    } elseif ($repeattype == "daily") {
        $end = mktime(23, 59, 59, $month, $day, $year);
        $increment = 1 + $repeatskip;
        list($tokenl, $names, $keyl) = assocToSQLInsert($row);
        $tokens = implode(',', $tokenl);
        $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb`
            ({$names},`date`) VALUES ({$tokens},:date)");
        $q->bindParam(':date', $row["date"]);
        for ($i=0; $i<count($keyl); $i++) {
            $q->bindParam($tokenl[$i], $row[$keyl[$i]]);
        }
        for ($date=time_add($original, 0, 0, 0, $increment, 0, 0);
             $date<=$end and $repeatcount != 0;
             $date=time_add($date, 0, 0, 0, $increment, 0, 0)) {
            $repeatcount--;
            $row["date"] = time_sqldate($date);
            $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));
            $inserted[] = strftime("%Y-%m-%d", $date);
        }

    } elseif ($repeattype == "weekly") {
        $end = mktime(23, 59, 59, $month, $day, $year);
        $increment = 7 + 7 * $repeatskip;
        list($tokenl, $names, $keyl) = assocToSQLInsert($row);
        $tokens = implode(',', $tokenl);
        $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb`
            ({$names},`date`) VALUES ({$tokens},:date)");
        $q->bindParam(':date', $row['date']);
        for ($i=0; $i<count($keyl); $i++) {
            $q->bindParam($tokenl[$i], $row[$keyl[$i]]);
        }
        for ($date=time_add($original, 0, 0, 0, $increment, 0, 0);
             $date<=$end and $repeatcount != 0;
             $date=time_add($date, 0, 0, 0, $increment, 0, 0)) {
            $repeatcount--;
            $row["date"] = time_sqldate($date);
            $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));
            $inserted[] = strftime("%Y-%m-%d", $date);
        }

    } elseif ($repeattype == "monthonday") {
        $end = mktime(23, 59, 59, $month, $day, $year);
        $weekday = time_dayOfWeek($original);
        $n = ceil($d / 7);
        $previous = $original;
        $increment = 1 + $repeatskip;
        list($tokenl, $names, $keyl) = assocToSQLInsert($row);
        $tokens = implode(',', $tokenl);
        $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb`
            ({$names},`date`) VALUES ({$tokens},:date)");
        $q->bindParam(':date', $row['date']);
        for ($i=0; $i<count($keyl); $i++) {
            $q->bindParam($tokenl[$i], $row[$keyl[$i]]);
        }
        for ($date=time_nthWeekDay(time_add($original, 0,0,0,0,$increment,0),
                                   $n, $weekday);
             $date<=$end and $repeatcount != 0;
             $date=time_nthWeekDay(time_add($date, 0,0,0,0,$increment,0),
                                   $n, $weekday)) {
            $repeatcount--;
            if (!$date) {
                $date=time_firstDayOfMonth($previous);
                $date=time_add($date, 0,0,0,0,$increment,0);
                $previous=$date;
                continue;
            } else { $previous = $date; }
            $row["date"] = time_sqldate($date);
            $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));
            $inserted[] = strftime("%Y-%m-%d", $date);
        }

    } elseif ($repeattype == "monthlydate") {
        $end = mktime(23, 59, 59, $month, $day, $year);
        $increment = 1 + $repeatskip;
        list($tokenl, $names, $keyl) = assocToSQLInsert($row);
        $tokens = implode(',', $tokenl);
        $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb`
            ({$names},`date`) VALUES ({$tokens},:date)");
        $q->bindParam(':date', $row['date']);
        for ($i=0; $i<count($keyl); $i++) {
            $q->bindParam($tokenl[$i], $row[$keyl[$i]]);
        }
        for ($date=time_add($original, 0, 0, 0, 0, $increment, 0);
             $date<=$end and $repeatcount != 0;
             $date=time_add($date, 0, 0, 0, 0, $increment, 0)) {
            $repeatcount--;
            $row["date"] = time_sqldate($date);
            $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));
            $inserted[] = strftime("%Y-%m-%d", $date);
        }

    } elseif ($repeattype == "annual") {
        $end = mktime(23, 59, 59, $month, $day, $year);
        $increment = 1 + $repeatskip;
        list($tokenl, $names, $keyl) = assocToSQLInsert($row);
        $tokens = implode(',', $tokenl);
        $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb`
            ({$names},`date`) VALUES ({$tokens},:date)");
        $q->bindParam(':date', $row['date']);
        for ($i=0; $i<count($keyl); $i++) {
            $q->bindParam($tokenl[$i], $row[$keyl[$i]]);
        }
        for ($date=time_add($original, 0, 0, 0, 0, 0, $increment);
             $date<=$end and $repeatcount != 0;
             $date=time_add($date, 0, 0, 0, 0, 0, $increment)) {
            $repeatcount--;
            $row["date"] = time_sqldate($date);
            $q->execute() or die(__LINE__ . array_pop($q->errorInfo()));
            $inserted[] = strftime("%Y-%m-%d", $date);
        }
    }
    $dbh->commit();
    return __('datescopied') . implode(", ", $inserted);
}

touch("timestamp.txt");
if ($action=="eventdisplay") $action="eventdisplay&id={$id}";
header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?month={$month}&year={$year}&day={$day}&length={$length}&unit={$unit}&action={$action}");
// vim: set tags+=../**/tags :
