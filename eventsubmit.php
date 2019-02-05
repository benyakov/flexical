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
    header("Location: {$SDir()}/index.php");
    exit(0);
}

if (!auth()) {
    setMessage(__('accessdenied'));
    header("Location: {$SDir()}/index.php");
    exit(0);

} else {

	$flag = getGET('flag');
	$id = (int) getGET('id');

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
        $qs = $dbh->prepare("SELECT DATE_FORMAT(`date`, \"%Y-%m-%d\")
            as `date`, `related`
            FROM `{$tablepre}eventstb` WHERE `id` = :id");
        $qs->bindParam(':id', $id);
        $qs->execute() or die(array_pop($q->errorInfo()));
        $row = $qs->fetch(PDO::FETCH_ASSOC);
        $datesAffected[] = $origdate = $row['date'];
        $related_id = $row['related'];
        if (getIndexOr($_GET, 'include_related', false)) {
            if ($related_id) {
                $related_txt = "`related` = :related_id";
                if (getIndexOr($_GET, 'future_only', false)) {
                    $future_only = " AND date >= :date";
                } else $future_only = "";
                $related_txt = " OR ({$related_txt}{$future_only})";
            } else $related_txt = "";
            $qr = $dbh->prepare("SELECT DATE_FORMAT(`date`, \"%Y-%m-%d\") as `date`
                FROM `{$tablepre}eventstb` WHERE FALSE {$related_txt}");
            $qr->bindParam(':related_id', $related_id);
            if ($future_only) $qr->bindValue(':date', $origdate);
            $qr->execute() or die(array_pop($qr->errorInfo()));
            $datesAffected = array_map(function($t) { return $t[0]; },
                $qr->fetchAll(PDO::FETCH_NUM));
        }
        $q = $dbh->prepare("DELETE FROM `{$tablepre}eventstb`
            WHERE `id` = :id {$related_txt}");
        $q->bindValue(':id', $id);
        if ($related_txt && $related_id) {
            $q->bindParam(':related_id', $related_id);
            if ($future_only) $q->bindValue(':date', $origdate);
        }
		$q->execute() or die(array_pop($q->errorInfo()));
        $dbh->commit() or die(array_pop($q->errorInfo()));
        $affected = $q->rowCount();
        if ("ajax" == $_GET['use']) {
            touch("timestamp.txt");
            echo json_encode(array(true, $datesAffected));
            exit(0);
        }
        setMessage("{$_('event deleted')} ({$affected})");
		header("Location: {$SDir()}/index.php?action={$action}&length={$l}&unit={$u}");
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
    $include_related = getPOST('include_related');
    $leave_in_place = getPOST('leave_in_place');
	$uid = getPOST('uid');
	$evmonth = getPOST('evmonth');
	$evday = getPOST('evday');
	$evyear = getPOST('evyear');
	$shour = intval(getPOST('start_hour'));
	$sminute = intval(getPOST('start_minute'));
	$s_ampm = getPOST('start_am_pm');
	$ehour = intval(getPOST('end_hour'));
	$eminute = intval(getPOST('end_minute'));
	$e_ampm = getPOST('end_am_pm');
    $all_day = getPOST('all_day');
    $timezone = getPOST('timezone');
    $datesAffected = array();
    $thisDate = strftime("%Y-%m-%d", mktime(0,0,0,$evmonth,$evday,$evyear));

    $dbh->beginTransaction();
    /* Determine how much the date has changed */
    $q = $dbh->prepare("SELECT DATE_FORMAT(`date`, \"%Y-%m-%d\")
        AS `date`, DATEDIFF(date, :selecteddate) AS `diff`
        FROM `{$tablepre}eventstb`
        WHERE id=:id");
    $q->bindValue(":selecteddate",
        "{$evyear}-{$evmonth}-{$evday}");
    $q->bindParam(":id", $id);
    $q->execute() or die("Problem getting datediff");
    $row = $q->fetch(PDO::FETCH_ASSOC);
    $origDate = $row['date'];
    $datediff = $row['diff'];

    if (0 != $datediff) {
        $datesAffected[] = $origDate;
    }

    // Check that Title is filled.
    if (!getPOST('title') && !getPOST('related')) {
        setMessage(__('blanktitle'));
        header("Location: {$SDir()}/index.php");
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
    if (mktime($shour,$sminute,0,0,0,0)>mktime($ehour,$eminute,0,0,0,0)) {
        $endtime = $starttime;
    } else {
        $endtime = "$ehour:$eminute:00";
    }

    if (getPOST('all_day') == "1") {
        $all_day = 1;
        $starttime = $endtime = "00:00:00";
    } else {
        $all_day = 0;
    }

    if (getPOST('category') == __("new-category")) {
        $cat = preg_replace("/[^- A-Za-z0-9]+/", "", getPOST('newcategory'));
        $cat = preg_replace("/ +/", " ", $cat);
        $category = $cat;
    } else {
        $category = getPOST('category');
    }

    /* If we are NOT shifting related events across dates... */
    if (! ($include_related && (0!=$datediff))) {
        /* Make sure the specified category exists */
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
            if (isset($_SESSION[$sprefix]['categories'])) {
                array_push($_SESSION[$sprefix]['categories'], $category);
            } else {
                $_SESSION[$sprefix]['categories'] = array($category);
            }
            $qc = $dbh->prepare("SELECT `category` FROM `{$tablepre}categories`
                WHERE `name`=:category");
            $qc->bindParam(":category", $category);
            $qc->execute() or die(array_pop($qc->errorInfo()));
        }
        $row = $qc->fetch(PDO::FETCH_ASSOC);
        $categoryid = $row["category"];
    }

	if ($id) {
        if ($include_related) {
            if (getIndexOr($_POST, 'future_only', false)) {
                $future_only = " AND date >= :thisdate";
            } else $future_only = "";
            if (0 != $datediff) {
                if ($leave_in_place) {
                    $q = $dbh->prepare("CREATE TEMPORARY TABLE `{$tablepre}tmpevents`
                        SELECT * FROM `{$tablepre}eventstb` WHERE `related`=:related
                        {$future_only}");
                    if ($future_only) $q->bindParam(":thisdate", $origDate);
                    $q->bindValue(':related', getPOST('related'));
                    $q->execute() or die("Creating temp table> ".array_pop($q->errorInfo()));
                    $q = $dbh->prepare("UPDATE `{$tablepre}tmpevents` SET
                        `id`=NULL, `uid`=:uid, `date` = DATE_SUB(date, INTERVAL :diff DAY)");
                    $q->bindValue(':uid', getPOST('uid'));
                    $q->bindParam(':diff', $datediff);
                    $q->execute() or die("Updating temp table> ".array_pop($q->errorInfo()));
                    $q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb`
                        SELECT * FROM `{$tablepre}tmpevents`");
                    $q->execute() or die("Moving temp rows> ".array_pop($q->errorInfo()));
                    $q = $dbh->prepare("DROP TEMPORARY TABLE IF EXISTS `{$tablepre}tmpevents`");
                    $q->execute() or die("Dropping temp table> ".array_pop($q->errorInfo()));
                } else {
                    // If we're moving to another date, don't set the other stuff.
                    // There may be related events with different data in them.
                    $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` SET
                        `date` = DATE_SUB(date, INTERVAL :diff DAY),
                        `uid`=:uid
                        WHERE `related`=:related {$future_only}");
                    $q->bindParam(':diff', $datediff);
                }
            } else {
                $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` SET
                    `uid`=:uid,
                    `start_time`=:starttime, `end_time`=:endtime,
                    `title`=:title, `category`=:categoryid, `text`=:text,
                    `all_day`=:all_day, `timezone`=:timezone
                    WHERE `related`=:related {$future_only}");
                $q->bindParam(':starttime', $starttime);
                $q->bindParam(':endtime', $endtime);
                $q->bindValue(':title', getPOST('title'));
                $q->bindParam(':categoryid', $categoryid);
                $q->bindValue(':text', getPOST('text'));
                $q->bindParam(':all_day', $all_day);
                $q->bindParam(':timezone', $timezone);
            }
            $q->bindValue(':related', getPOST('related'));
            $q->bindValue(':uid', getPOST('uid'));
            if ($future_only) $q->bindParam(":thisdate", $origDate);
            $datesAffected[] = "all";
        } else {
            $q = $dbh->prepare("UPDATE `{$tablepre}eventstb` SET
                `uid`=:uid, `date`=:date,
                `start_time`=:starttime, `end_time`=:endtime,
                `title`=:title, `category`=:categoryid, `text`=:text,
                `all_day`=:all_day, `timezone`=:timezone
                WHERE `id`=:id");

            $q->bindValue(':date', $thisDate);
            $datesAffected[] = $thisDate;
            $q->bindValue(':uid', getPOST('uid'));
            $q->bindParam(':starttime', $starttime);
            $q->bindParam(':endtime', $endtime);
            $q->bindValue(':title', getPOST('title'));
            $q->bindParam(':categoryid', $categoryid);
            $q->bindValue(':text', getPOST('text'));
            $q->bindParam(':all_day', $all_day);
            $q->bindParam(':timezone', $timezone);
            $q->bindParam(':id', $id);
        }
        $result = __('updated');
	} else {
		$q = $dbh->prepare("INSERT INTO `{$tablepre}eventstb` SET
            `uid`=:uid, `date`=:date,
            `start_time`=:starttime, `end_time`=:endtime,
            `title`=:title, `category`=:categoryid, `text`=:text,
            `all_day`=:all_day, `timezone`=:timezone");
        $q->bindValue(':date', $thisDate);
        $datesAffected[] = $thisDate;
        $q->bindValue(':uid', getPOST('uid'));
        $q->bindParam(':starttime', $starttime);
        $q->bindParam(':endtime', $endtime);
        $q->bindValue(':title', getPOST('title'));
        $q->bindParam(':categoryid', $categoryid);
        $q->bindValue(':text', getPOST('text'));
        $q->bindParam(':all_day', $all_day);
        $q->bindParam(':timezone', $timezone);
		$result = __('added');
	}
    if (!$leave_in_place) {
        $q->execute() or die(array_pop($q->errorInfo()));
    }
    $dbh->commit();
    $rowcount = $q->rowCount();
    unset($_SESSION[$sprefix]['allcategories']);
    if ("ajax" == getPOST('use')) {
        touch("timestamp.txt");
        echo json_encode(array(true, $datesAffected));
        exit(0);
    }
    return getPOST('title') . " {$result} ({$rowcount})";
}

function copyEvent($id)
{
    global $tablepre, $dbh;
    if (is_numeric(getPOST('repeatskip'))) {
        $repeatskip = intval($_POST['repeatskip']);
    } elseif (array_key_exists('repeatskip', $_POST)) {
        setMessage(__('repeatskipnan')." : {$_POST['repeatskip']} numeric? ".is_numeric($_POST['repeatskip']));
        header("Location: {$SDir()}/index.php");
        exit(0);
    }
    $repeattype = $_POST['repeattype'];
	$month = $_POST['evmonth'];
	$day = $_POST['evday'];
	$year = $_POST['evyear'];
    $repeatcount = getPOST('repeatcount');
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

    $inserted = array();
    if ($include_related) {
        if (! $row['related']) {
            $row['related'] = $row['id'];
            $inserted[] = strftime("%Y-%m-%d", mktime(0,0,0,$m, $d, $y));
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
        $inserted[] = strftime("%Y-%m-%d", mktime(0,0,0,$month, $day, $year));

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
    if ("ajax" == $_POST['use']) {
        touch("timestamp.txt");
        echo json_encode(array(true, $inserted));
        exit(0);
    }
    return __('datescopied') . implode(", ", $inserted);
}

touch("timestamp.txt");
if ($action=="eventdisplay") $action="eventdisplay&id={$id}";
header("Location: {$SDir()}/index.php?month={$month}&year={$year}&day={$day}&length={$length}&unit={$unit}&action={$action}");
// vim: set foldmethod=indent :
