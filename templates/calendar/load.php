<?php

function minicalday($day) {
    $rv = "<td class='minicalday'>" ;
    if ($day == "") {
        $rv .= "&nbsp;";
    } else {
        $rv .= $day;
    }
    return $rv .= "</td>" ;
}

function genMiniCal($month, $year, $shortcells) {
	// get number of days in month
	$days = 31-((($month-(($month<8)?1:0))%2)+(($month==2)?((!($year%((!($year%100))?400:4)))?1:2):0));

	// get week position of first day of month.
	$weekpos = date("w",mktime(0,0,0,$month,1,$year));
    if ($weekpos == 0) {
        $day = $wday = 1;
    } else {
        $day = $wday = 0;
    }
    //$day = ($weekpos == 0) ? 1 : 0;
    //$wday = 0;
    // If this month requires six rows, make it a "shortcell".
    if (6 == ceil(($days + $weekpos) / 7))
        $shortcells = " shortcell";
    else
        $shortcells = "";
    $months = __('months');
    $rv = "<div class='minimonth_name{$shortcells}'>".$months[$month-1]."</div>\n";
    $rv .= "<table class=\"minimonth{$shortcells}\">
        <tr class=\"minicalweek\">";
    $wkdays = explode(" ",__("Su Mo Tu We Th Fr Sa"));
    $wkdays = array_map('minicalday', $wkdays) ;
    $rv .= implode("", $wkdays)."</td>\n";
    $rv .= "<tr class=\"minicalweek\">";
    while ($day<=$days) {
        while ($day == 0) {
            if ($wday < $weekpos) {
                $rv .= minicalday("");
                $wday++;
            } else {
                $day = 1;
                $wday++;
            }
        }
        $rv .= minicalday($day);
        $day++;
        $wday++;
        if ($wday > 7) {
            $rv .= "</tr>\n<tr class=\"minicalweek\">";
            $wday = 1;
        }
    }
    while ($wday<=7) {
        $rv .= minicalday("");
        $wday++;
    }
    $rv .= "</tr></table>";
    return $rv;
}

function genCatKey($categories) {
    if (count($categories) > 0) {
        sort($categories);
        $rv = "<div class=\"catkey_name\">".__('catkeytitle')."</div>\n";
        $rv .= "<ul class=\"categorykey\">\n";
        foreach ($categories as $cat) {
            if ($cat) {
                $rv .= " <li><span class=\"".toCSSID($cat)."\">{$cat}</span></li>\n";
            }
        }
        $rv .= "</ul>";
    } else {
        $rv = "<td class=\"empty-day-cell\">&nbsp;</td>\n";
    }
    return $rv;
}

function cmpEvents($a, $b) {
    if ($a['start_time'] == $b['start_time'])
        return strcmp($a['category'], $b['category']);
    else
        return ($a['start_time'] < $b['start_time'])? -1 : 1;
}

function getDayJSON($month, $day, $year, $short) {
    // Return the json-encoded html to refresh a specific day
    // Same query as used in writeCalendar, adding date to where clause

	// determine user's authorization
	$auth = auth();
    $categoryMatches = categoryMatchString();

    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
    // Get filter clauses, if any
    $filterclause = getfilterclause(" AND ");
    $month = intval($month); // Security vs. sql injection
    $year = intval($year);
    $day = intval($day);
    $q = $dbh->prepare("SELECT `m`.`id`, DAYOFMONTH(`m`.`date`) AS `d`,
                   `m`.`title`, `m`.`all_day`, `m`.`related`,
                   `m`.`timezone`,
                   `c`.`name` AS `category`, `c`.`restricted` AS `restricted`,
                   `c`.`suppresskey` AS `suppress_key`,
                   ADDTIME(`m`.`date`,`m`.`start_time`) AS `start_time`,
                   ADDTIME(`m`.`date`,`m`.`end_time`) AS `end_time`
                   FROM `{$tablepre}eventstb` AS `m`
                   LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
                   WHERE MONTH(`m`.`date`) = :month
                   AND YEAR(`m`.`date`) = :year
                   AND DAYOFMONTH(`m`.`date`) = :day
                   $filterclause
                   $categoryMatches
                   ORDER BY `m`.`start_time`, `category`");
    $q->bindparam(':month', $month);
    $q->bindparam(':year', $year);
    $q->bindparam(':day', $day);
    $q->execute() or die(array_pop($q->errorInfo()));
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    list($events, $usedcategories) = prepareDBResults($rows, "normal");
    global $includeroot;
    require_once("{$includeroot}/templates/calendar/CalendarDay.php");
    $cd = new \flexical\calendar\CalendarDay(
            mktime(0,0,0, $month, $day, $year),
            getIndexOr($events, $day, array(), $short));
    $html = $cd->write($auth);
    echo json_encode(array($html, $usedcategories));
}

function prepareDBResults($rows, $mode) {
    /* Change data to PHP objects, add TZ data */
	// determine user's authorization
	$auth = auth();

    // Create an array of arrays, indexed by the day number.
    $events = array();
    $usedcategories = array();
    foreach ($rows as $row) {
        // Convert start and end times to PHP[5.2] DateTime objects
        try
        {
            $row['start_time'] = new DateTime($row['start_time'],
                new DateTimeZone($row['timezone']));
        }
        catch (Exception $e)
        {
            print_r($row);
            exit(0);
        }
        $row['end_time'] = new DateTime($row['end_time'],
            new DateTimeZone($row['timezone']));
        // Handle user timezone conversions
        $row['usertz_start_time'] = clone $row['start_time'];
        $row['usertz_start_time']->setTimezone(\userTimeZone());
        $row['usertz_end_time'] = clone $row['end_time'];
        $row['usertz_end_time']->setTimezone(\userTimeZone());
        if (getIndexOr($_SESSION[$sprefix], "usertz", "off") == "on")
            $rowdate = $row['usertz_start_time']->format('d');
        else
            $rowdate = $row['d'];
        $rowdate = (int) $rowdate;
        unset($row['d']);
        if ((! $row['suppress_key'])
            && (! ($row['restricted'] && ! $auth)))
            $usedcategories[$row['category']]=1;
        if (! array_key_exists($rowdate, $events))
            $events[$rowdate] = array();
        array_push($events[$rowdate], $row);
    }
    $usedcategories = array_keys($usedcategories);
    return array($events, $usedcategories);
}

function writeCalendar($month, $year, $mode="normal") {
    global $sprefix;
	// determine user's authorization
	$auth = auth();
    $Config = new CalendarConfig();
    $configuration = $Config->getConfig();
    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
    $categoryMatches = categoryMatchString();

    // Get filter clauses, if any
    $filterclause = getfilterclause(" AND ");
    $month = intval($month); // Security vs. sql injection
    $year = intval($year);

    $q = $dbh->prepare("SELECT `m`.`id`, DAYOFMONTH(`m`.`date`) AS `d`,
                   `m`.`title`, `m`.`all_day`, `m`.`related`,
                   `m`.`timezone`,
                   `c`.`name` AS `category`, `c`.`restricted` AS `restricted`,
                   `c`.`suppresskey` AS `suppress_key`,
                   ADDTIME(`m`.`date`,`m`.`start_time`) AS `start_time`,
                   ADDTIME(`m`.`date`,`m`.`end_time`) AS `end_time`
                   FROM `{$tablepre}eventstb` AS `m`
                   LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
                   WHERE MONTH(`m`.`date`) = :month
                   AND YEAR(`m`.`date`) = :year
                   $filterclause
                   $categoryMatches
                   ORDER BY `m`.`start_time`, `category`");
    $q->bindparam(':month', $month);
    $q->bindparam(':year', $year);
    $q->execute() or die(array_pop($q->errorInfo()));

    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    if ("remote" == $mode) {
        provideRemoteRows($rows);
        exit(0);
    }
    $rangedata = array("", $month, $year, "", "");
    if (! filter_set()
        && $remoterows = getRemoteRows('calendar', $rangedata))
    {
        foreach ($remoterows as $rrow) {
            $rrow['remote'] == true;
            $rows[] = $rrow;
        }
        usort($rows, cmpEvents);
    }

    list($events, $usedcategories) = prepareDBResults($rows, $mode);

    $specialcontent = array();
    while (count($usedcategories)) {
        $getcategories = array_slice($usedcategories, 0, $configuration['category_key_limit']);
        if ($configuration['category_key_limit'] < count($usedcategories)) {
            $usedcategories = array_slice($usedcategories, $configuration['category_key_limit']);
        } else {
            $usedcategories = array();
        }
        $specialcontent[] = array(genCatKey($getcategories), "catkey-cell");
    }

    global $includeroot;
    require_once("{$includeroot}/templates/calendar/CalendarMonth.php");
    $cm = new \flexical\calendar\CalendarMonth($events, $month, $year);
    if ($month>1) {
        $minimonth = $month-1;
        $miniyear = $year;
    } else {
        $minimonth = 12;
        $miniyear = $year-1;
    }
    $specialcontent[] = array(genMiniCal($minimonth,$miniyear,$cm->shortcells),
        "minimonth-cell");
    if ($month<12) {
        $minimonth = $month+1;
        $miniyear = $year;
    } else {
        $minimonth = 1;
        $miniyear = $year+1;
    }
    $specialcontent[] = array(genMiniCal($minimonth,$miniyear,$cm->shortcells),
        "minimonth-cell");
    return $cm->write($auth, $specialcontent);
}

// vim: set tags+=../../../**/tags foldmethod=indent :

?>
