<?php

function writeEvents($day, $month, $year, $length, $unit, $showopen, $mode) {
    global $sprefix;

    $Config = new CalendarConfig();
    $configuration = $Config->getConfig();
    $language = $configuration['language'];
    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
    $_ = "__";

    // determine user's authorization status
    $auth = auth();

    if ($auth >= 3
        && array_key_exists('filters', $_SESSION[$sprefix])
        && $_SESSION[$sprefix]['filters'])
    {
        $str = "<ul class=\"hbuttons\">
            <li><a href=\"javascript:void(0);\" onClick=\"batchDeleteConfirm();\" title=\"{$_('deletefiltered')}\">{$_('deletestr')}</a></li>
            <li><a href=\"javascript:void(0);\" onClick=\"relateConfirm();\" title=\"{$_('relatefiltered')}\">{$_('relatestr')}</a></li>
            <li><a href=\"batch.php?flag=title\" title=\"{$_('retitlefiltered')}\">{$_('title')}</a></li>
            <li><a href=\"batch.php?flag=text\" title=\"{$_('retextfiltered')}\">{$_('text')}</a></li>
            <li><a href=\"batch.php?flag=time\" title=\"{$_('retimefiltered')}\">{$_('time')}</a></li>
            <li><a href=\"batch.php?flag=category\" title=\"{$_('recategoryfiltered')}\">{$_('category')}</a></li>\n";
        if ($configuration['google_user'] && $configuration['google_password']) {
            $str .= "<li><a href=\"batch.php?flag=google\" title=\"{$_('sendtogoogle')}\">Google</a></li>\n";
        }
        $str .= "</ul>\n";
    } else {
        $str = "";
    }

	$str .= "<table id=\"eventlisting\" >
            <tr class=\"columnheader\"><td>{$_('date')}</td>
            <td>{$_('starttime')}</td>
            <td>{$_('endtime')}</td>
            <td>{$_('title')}</td>
            <td>{$_('description')}</td>
            <td>{$_('category')}</td></tr>";

    $categoryMatches = categoryMatchString();

    // Set up Where clause
    $lowdate = "{$year}-{$month}-{$day}";
    $highdate = time_sqldate(time_add(mktime(0,0,0,$month,$day,$year),0,0,0,
        $unit==1?$length:       // "days"; see __('units')
        ($unit==2?$length*7:0),   // "weeks"
        $unit==3?$length:0,     // "months"
        $unit==4?$length:0));     // "years"

    $filterclause = getfilterclause(" AND ");

    $dbh->beginTransaction();
    if (relatedFilter()) {
        // Override categories if a "related" filter has been imposed.
        $categorymatches = "";
        // Reset the low and high dates to include all related events.
        $q = $dbh->prepare("SELECT MIN(`m`.`date`), MAX(`m`.`date`)
            FROM `{$tablepre}eventstb` AS `m`
            WHERE `m`.`related` = :related
            GROUP BY `m`.`related`
            ");
        $q->bindParam(':related', $_SESSION[$sprefix]['filters']['related']);
        $q->execute() or die(array_pop($q->errorInfo()));
        if ($q->rowCount()) {
            list($lowdate, $highdate) = $q->fetch(PDO::FETCH_NUM);
        }
    }

    $q = $dbh->prepare("SELECT `m`.`id`,
               `m`.`title`, `m`.`all_day`, `m`.`related`,
               /* The following are for displaying overlaps */
               TIME_FORMAT(`m`.`start_time`, '%k') AS `start_hour`,
               TIME_FORMAT(`m`.`start_time`, '%i') AS `start_minute`,
               TIME_FORMAT(`m`.`end_time`, '%k') AS `end_hour`,
               TIME_FORMAT(`m`.`end_time`, '%i') AS `end_minute`,
               DATE_FORMAT(`m`.`date`, '%m') AS `month`,
               DATE_FORMAT(`m`.`date`, '%d') AS `day`,
               DATE_FORMAT(`m`.`date`, '%Y') AS `year`,
               /* The following are converted to PHP Datetime objects */
               ADDTIME(`m`.`date`,`m`.`start_time`) AS `start_time`,
               ADDTIME(`m`.`date`,`m`.`end_time`) AS `end_time`,
               `m`.timezone,
               `m`.`text`,
               `c`.`name` AS `category`,
               `c`.`restricted`
               FROM `{$tablepre}eventstb` AS `m`
               LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
               WHERE `date` >= :lowdate
               AND `date` <= :highdate
               {$filterclause}
               {$categoryMatches}
               ORDER BY `m`.`date`, `start_time`");
    $q->bindParam(':lowdate', $lowdate);
    $q->bindParam(':highdate', $highdate);
    //print_r($q); // Useful for debugging
    $q->execute() or die(array_pop($q->errorInfo()));
    $dbh->commit();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    if ("remote" == $mode) {
        provideRemoteRows($rows);
        exit(0);
    }
    $rangedata = array($day, $month, $year, $length, $unit);
    require_once("./lib/remote.php");
    if (! filter_set()
        && $remoterows = getRemoteRows('eventlist', $rangedata))
    {
        foreach ($remoterows as $rrow) {
            $rrow['remote'] == true;
            $rows[] = $rrow;
        }
        usort($rows, cmpEvents);
    }
    $rowcount = 0;
    $lastday = null;
    $lasttime = new DateTime("$year-$month-$day 00:00");
    $lasttime->setTimezone(userTimeZone());
    $fmt = formattime("php");
    foreach ($rows as $row) {
        if ($row['restricted'] && !$auth) { continue; }
        $rowcount += 1;
        $rowclasses = array();
        if ($rowcount % 2 == 1) $rowclasses[] = "oddrow";
        if ($row['all_day']) $rowclasses[] = "allday";
        $tr = buildopentag('tr', $rowclasses);
        $start_time = new DateTime($row['start_time'],
            new DateTimeZone($row['timezone']));
        $end_time = new DateTime($row['end_time'],
            new DateTimeZone($row['timezone']));
        if (! $row['all_day']) {
            $start_time->setTimezone(userTimeZone());
            $end_time->setTimezone(userTimeZone());
        }
        $thisday = clone $start_time;
        $thisday->setTime(0, 0);
        $thisdayfmt = $thisday->format('D j M Y');
        if ($lastday != null && $thisday == $lastday) {
            $thisdayfmt = "";
        } elseif ($lastday != null) {
            // open time at end of last day
            $endoflastday = clone $lastday;
            $endoflastday->setTime(23, 59, 59);
            if ($showopen && $lastday != "" && $lasttime < $endoflastday)
            {
                $str .= "<tr><td class=\"eventdate\"></td>
                    <td class=\"eventstarttime open-time\">{$lasttime->format($fmt)}</td>
                    <td class=\"eventendtime open-time\">{$endoflastday->format($fmt)}</td>
                    <td colspan=\"3\" class=\"open-time\">{$_('opentime')}</td></tr>";
                $lasttime->setTime(0, 0, 0);
            }
            // open time between days
            if ($showopen) {
                $daydiff = $lastday->diff($thisday)->d;
                $intervalday = clone $lastday;
                for ($i=1; $i<$daydiff; $i++) {
                    $intervalday->add(new DateInterval("P1D"));
                    $datedisp = $intervalday->format('D j M Y');
                    $str .= "<tr><td class=\"eventdate open-time\">{$datedisp}</td>
                        <td class=\"eventstarttime hbar open-time\"></td>
                        <td class=\"eventendtime hbar open-time\"></td>
                        <td colspan=\"3\" class=\"open-time\">{$_('opentime')}</td></tr>";
                }
            }
        }
        $lastday = clone $thisday;
        $title = stripslashes($row['title']);
        $description = Markdown(stripslashes($row['text']));
        $stime = $row['all_day']?"":$start_time->format($fmt);
        $etime = $row['all_day']?"":$end_time->format($fmt);
        if ($row['related']) {
            $related = $row["related"];
        } else {
            $related = "";
        }
        if ($auth > 1 && 0 != $row["id"]) {
            $aicons = "<div class=\"actionicons\"><a class=\"copyform\" href=\"copyform.php?id={$row['id']}\" title=\"{$_('copy')}\"><img src=\"images/copy.png\"/ alt=\"{$_('copy')}\"/></a>
                <a class=\"eventform\" href=\"eventform.php?id={$row['id']}\" title=\"{$_('edit')}\"><img src=\"images/edit.png\" alt=\"{$_('edit')}\"/></a>
                <a href=\"javascript:void(0);\" onClick=\"deleteConfirm({$row['id']});\" title=\"{$_('delete')}\"><img src=\"images/trash.png\" alt=\"{$_('delete')}\"></a>";
            if ($related) {
                $aicons .= " <a href=\"filter.php?filterrelated={$related}\" title=\"{$_('show related')}\"><img src=\"images/showall.png\" alt=\"{$_('show related')}\"></a>
                    <a href=\"javascript:void(0);\" onClick=\"deleteRelated({$row['id']});\" title=\"{$_('delete related')}\"><img src=\"images/multitrash.png\" alt=\"{$_('delete related')}\"></a>";
            }
            $aicons .= "</div>";
        } else {
            $aicons = "";
        }

        $str .= ($thisdayfmt=="")?
            "{$tr}\n<td class=\"eventdate\"></td>":
            "{$tr}\n<td class=\"eventdate hbar\">$thisdayfmt</td>";
        if ($showopen && (! $row['all_day'])
            && $start_time > $lasttime)
        {
            $str .= "<td class=\"eventstarttime hbar open-time\">{$lasttime->format($fmt)}</td>
                <td class=\"eventendtime hbar open-time\">{$start_time->format($fmt)}</td>
                <td colspan=\"3\" class=\"open-time\">{$_('opentime')}</td></tr>
                {$tr}<td class=\"eventdate\"></td>";
        }
        // Check for coincidence
        if ($start_time < $lasttime)
            $stcoincidence = " coincident";
        else
          $stcoincidence = "";
        if ($end_time < $lasttime)
            $endcoincidence = " coincident";
        else
          $endcoincidence = "";
        $urlbase = getIndexOr($row, "urlbase", "")."/";
        $str .= "<td class=\"eventstarttime hbar$stcoincidence\">{$stime}</td>
                 <td class=\"eventendtime hbar$endcoincidence\">{$etime}</td>
                 <td class=\"eventtitle\"><a href=\"{$urlbase}index.php?action=eventdisplay&id={$row['id']}\">{$title}</a></td>
                 <td class=\"eventdescription\">{$description}{$aicons}</td>
                 <td class=\"eventcategory\">
                 <span class=\"".toCSSID($row['category'])."\">
                 {$row['category']}</span></td></tr>\n";
        if (!$row['all_day'] && $end_time > $lasttime) {
            $lasttime = clone $end_time;
        }
    }
    // Check for open time at the end of the last day in our range
    if ($showopen) {
        $endofthisday = clone $lasttime;
        $endofthisday->setTime(23, 59, 59);
        if ($endofthisday > $lasttime) {
            $str .= "<tr><td class=\"eventdate\"></td>
                <td class=\"eventstarttime open-time\">{$lasttime->format($fmt)}</td>
                <td class=\"eventendtime open-time\">{$endofthisday->format($fmt)}</td>
                <td colspan=\"3\" class=\"open-time\">{$_('opentime')}</td></tr>";
        }
    }
    $str .= "</table>\n\n";
    $str .= "<p class=\"rowcount\">{$_('totalfound')} {$rowcount}</p>\n";
    return $str;
}

function opentimeBox($ot) {
    checkBox("opentime", $ot);
}

// vim: set tags+=../../../**/tags :
?>
