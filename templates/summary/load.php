<?php

/**
 * Convenience class for handling event descriptions.
 */
class SummaryEvent
{
    public function __construct($eventdata) {
        $eventdata['day'] = time_getDay(strtotime($eventdata['date']));
        $this->data = $eventdata;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
}

/**
 * Return html for showing the first half of the needed calendars,
 * marking days with events specially.
 */
function leftCalendars($start, $end, $eventdays) {
    // Normalize to the first of the month
    $monthcount = ceil(time_monthDiff($start, $end)/2);
    if (0 == $monthcount) $monthcount = 1;
    for ($inc=0; $inc<$monthcount; $inc++) {
        $thismonth = time_add($start, 0, 0, 0, 0, $inc, 0);
        $month = time_getMonth($thismonth);
        $year = time_getYear($thismonth);
        showMiniMonth($month, $year, $eventdays);
        echo "<br>\n";
    }
}

/**
 * Same thing for the second half of the calendars
 */
function rightCalendars($start, $end, $eventdays) {
    // Normalize to the first of the month
    $startmonth = ceil(time_monthDiff($start, $end)/2);
    $monthcount = time_monthDiff($start, $end)+1;
    $monthmax = time_getMonth($end);
    for ($inc = $startmonth; $inc<$monthcount; $inc++) {
        $thismonth = time_add($start, 0, 0, 0, 0, $inc, 0);
        $month = time_getMonth($thismonth);
        $year = time_getYear($thismonth);
        showMiniMonth($month, $year, $eventdays);
        echo "<br>\n";
    }
}

/**
 * Show a list of days in included categories,
 * including extra "categories" configured by event description syntax.
 */
function dayCount($results) {
    /**
     * collect events by category
     */
    $data = array();
    foreach ($results as $event) {
        $thiscategory = $event['category'];
        $data[$thiscategory][] = $event;
    }
    $counts = array();
    foreach ($data as $category => $events) {
        $counts[$category] = count($events);
    }
    /**
     * Collect excludes
     */
    $beginmarks = array();
    $pmatch = array();
    $startpreg = "/^StartCount \"([^)]+?)\"/";
    foreach ($results as $event) {
        if ($result = preg_match($startpreg, $event['text'], $pmatch)) {
            $beginmarks[$pmatch[1]] = array(
                'startdate' => strtotime($event['date']),
                'category' => $event['category'],
                'text' => $event['text'],
                'enddate' => 0);
        } elseif ($result === false)
            throw new SummaryError("Error in preg_match;");
    }
    foreach ($beginmarks as $markname=>$whatwhen) {
        $endpreg = "/^EndCount \"{$markname}\"/";
        foreach($data[$whatwhen["category"]] as $event) {
            if ($result = preg_match($endpreg, $event['text'])) {
                $beginmarks[$markname]['enddate'] = strtotime($event['date']);
            } elseif ($result === false)
                throw new SummaryError("Error in preg_match;");
        }
    }
    $excludepreg = '/ExcludeCount "([^)]+?)"/';
    foreach ($beginmarks as $markname=>$whatwhen) {
        $begin = $whatwhen['startdate'];
        $end = $whatwhen['enddate'];
        if ($begin >= $end) continue;
        $daydiff = time_dayDiff($end, $begin);
        // Process excludes
        $excludes = array();
        if ($result=preg_match_all($excludepreg,
            $whatwhen['text'], $pmatches, PREG_PATTERN_ORDER)) {
            $excludes = $pmatches[1];
        } elseif ($result === false)
            throw new SummaryError("Error in preg_match;");
        foreach($excludes as $excategory) {
            // Reduce $daydiff by the count of events in $excategory
            // between $begin and $end
            if ($data[$excategory]) {
                foreach ($data[$excategory] as $exdays)
                    if ($begin <= strtotime($exdays['date'])
                        && $end >= strtotime($exdays['date'])) $daydiff--;
            } elseif ("Weekends" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (in_array(time_dayOfWeek($d), array(0, 6)))
                        $daydiff--;
                }
            } elseif ("Weekdays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (in_array(time_dayOfWeek($d), array(1, 2, 3, 4, 5)))
                        $daydiff--;
                }
            } elseif ("Sundays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==0) $daydiff--;
                }
            } elseif ("Mondays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==1) $daydiff--;
                }
            } elseif ("Tuesdays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==2) $daydiff--;
                }
            } elseif ("Wednesdays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==3) $daydiff--;
                }
            } elseif ("Thursdays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==4) $daydiff--;
                }
            } elseif ("Fridays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==5) $daydiff--;
                }
            } elseif ("Saturdays" == $excategory) {
                for ($d=$begin; $d<=$end; $d=time_add($d,0,0,0,1,0,0)) {
                    if (time_dayOfWeek($d)==6) $daydiff--;
                }
            }
        }
        // $beginmarks[$markname]["length"] = $daydiff; // Completeness
        $counts[$markname] = $daydiff;
    }
?>
    <table class="daycount">
        <?php foreach ($counts as $name=>$count) { ?>
        <tr><th><span class="<?=toCSSID($name)?>"><?=$name?></span></th><td><?=$count?></td>
        <?php } ?>
    </table>
<?php
}

/**
 * Show a list of events by month
 */
function eventsByMonth($results) {
    /**
     * Collect events by month
     */
    $month = "";
    $data = array();
    foreach ($results as $event) {
        $thismonth = time_getMonth(strtotime($event['date']));
        if ($month!=$thismonth) {
            $month = $thismonth;
            $label = time_firstDayOfMonth(strtotime($event['date']));
            $data[$label] = array();
        }
        // Load each month's events for later processing
        $data[$label][] = new SummaryEvent($event);
    }
    /**
     * Separate multi-day events for each month
     *  Assume events with same title and category are the same
     */
    $monthsingles = array();
    $mmbyrange = array();
    $monthlabels = array();
    foreach ($data as $monthlabel=>$events) {
        $monthlabels[] = $monthlabel; // Used below for printing
        $reverseassoc = array();
        foreach ($events as $e) {
            if (array_key_exists($e->title, $reverseassoc)) {
                $reverseassoc[$e->title.$e->category][$e->day] = $e;
            } else {
                $reverseassoc[$e->title.$e->category][$e->day] = $e;
            }
        }
        $multievents = array();
        $singleevents = array();
        foreach ($reverseassoc as $title=>$evts) {
            if (count($evts) > 1) {
                $multievents[$title] = $evts;
            } else {
                $ek = array_keys($evts);
                $singleevents[$evts[$ek[0]]->day][] = $evts[$ek[0]];
            }
        }
        /**
         * Separate date ranges from isolated events in multis
         */
        $eventsbyrange = array();
        foreach ($multievents as $title=>$evts) {
            $byday = array();
            foreach ($evts as $e) $byday[$e->day][] = $e;
            $days = array_keys($byday); sort($days);
            $ranges = array();
            $arange = array();
            if (count($days) == 1) { // For multi events on only one day
                $ranges[][] = $days[0];
            } else {
                for ($d=min($days),$max=max($days),$i=0;$d<=$max+1;$d++) {
                    if (getIndexOr($days, $i) == $d) {
                        $arange[] = $days[$i++];
                    } else { // Break in range; Reset arange for a new range
                        if ($arange) {
                            $ranges[] = $arange;
                        }
                        $arange = array();
                    }
                }
            }
            // Move isolated days to $singleevents
            foreach ($ranges as $i=>$r) {
                if (count($r) == 1) {
                    $dayevent = $byday[$r[0]][0];
                    $singleevents[$r[0]][] = $dayevent;
                } else {
                    $eventsbyrange[min($r)][min($r)."-".max($r)][]
                        = $evts[$r[0]];
                }
            }
        }
        $monthsingles[$monthlabel] = $singleevents;
        $mmbyrange[$monthlabel] = $eventsbyrange;
    }
    $rv = array();
    foreach (array_unique($monthlabels) as $monthlabel) {
        $monthnames = __("months");
        $month = time_getMonth($monthlabel);
        $monthname = $monthnames[$month-1];
        $label = "{$monthname} ".time_getYear($monthlabel);
        $rv[] = '<h3 class="monthname">'.$label.'</h3>';
        $multis = $mmbyrange[$monthlabel];
        $singles = $monthsingles[$monthlabel];
        $days = array_merge(array_keys($multis), array_keys($singles));
        $days = array_unique($days);
        sort($days);
        if ($days) {
            $rv[] = "<dl>";
            foreach ($days as $day) {
                if ($singles[$day]) {
                    $rv[] = "<dt>{$day}</dt>";
                    foreach ($singles[$day] as $evt) {
                        $catclass = toCSSID($evt->category);
                        $rv[] = "<dd><span class=\"{$catclass}\">{$evt->title}</span></dd>";
                    }
                }
                if (isset($multis[$day])) {
                    foreach ($multis[$day] as $daylabel => $evts) {
                        $rv[] = "<dt>{$daylabel}</dt>";
                        foreach ($evts as $e) {
                            $catclass = toCSSID($e->category);
                            $rv[] = "<dd><span class=\"{$catclass}\">{$e->title}</span></dd>";
                        }
                    }
                }
            }
            $rv[] = "</dl>";
        }
    }
    echo implode("\n", $rv);
}

/**
 * Mostly borrowed from calendar/load.php -> genMiniCal
 */
function showMiniMonth($month, $year, $edays) {
	// get number of days in month
	$days = 31-((($month-(($month<8)?1:0))%2)+(($month==2)?((!($year%((!($year%100))?400:4)))?1:2):0));

	// get week position of first day of month.
	$weekpos = date("w",mktime(0,0,0,$month,1,$year));
    if ($weekpos == 0) {
        $day = $wday = 1;
    } else {
        $day = $wday = 0;
    }
    $months = __('months');
    $rv = "<div class='minimonth_name'>".$months[$month-1]."</div>\n";
    $rv .= "<table class=\"minimonth\">
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
        $rv .= minicalday($day, specialDayClass($year, $month, $day, $edays));
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
    echo $rv;
}
/**
 * Generate a single day for the above function.
 */
function minicalday($day, $extraclass="") {
    if ($extraclass) $extraclass = " $extraclass";
    $rv = "<td class=\"minicalday$extraclass\">" ;
    if ($day == "") {
        $rv .= "&nbsp;";
    } else {
        $rv .= $day;
    }
    return $rv .= "</td>" ;
}

/**
 * Return the class name "specialday" if this date is in the events
 */
function specialDayClass($year, $month, $day, $edays) {
    if (in_array(sprintf("%d-%02d-%02d", $year, $month, $day)
        , $edays))
        return "specialday";
    else
        return "";
}


?>
