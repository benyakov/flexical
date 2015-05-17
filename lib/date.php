<?php

// Date functions

function timezoneDropDown($name="timezone", $default="") {
    require("./utility/timezones.php");
    // Make a select dropdown with all available timezones.
    $rv[] = "<select name=\"{$name}\" id=\"timezonedropdown\">";
    foreach ($TimeZones as $tzname) {
        if ($tzname == $default) $selected = "selected";
        else $selected = "";
        $rv[] = "<option {$selected} value=\"{$tzname}\">{$tzname}</option>";
    }
    $rv[] = "</select>";
    return implode("\n", $rv);
}

function time_sqldate($timestamp) {
    return date('Y-m-d H:i:s', $timestamp);
}

function time_sub($timestamp, $seconds,$minutes,$hours,$days,$months,$years) {
   $t = getdate($timestamp);
   return mktime($t['hours']-$hours,$t['minutes']-$minutes,
                  $t['seconds']-$seconds,$t['mon']-$months,$t['mday']-$days,
                  $t['year']-$years);
}
function time_add($timestamp,$seconds,$minutes,$hours,$days,$months,$years) {
    $t = getdate($timestamp);
    return mktime($t['hours']+$hours,$t['minutes']+$minutes,
                  $t['seconds']+$seconds,$t['mon']+$months,$t['mday']+$days,
                  $t['year']+$years);
}
function time_dayOfWeek($timestamp) {
   $timepieces = getdate($timestamp);
   return $timepieces['wday'];
}
function time_daysInMonth($timestamp) {
   $timepieces    = getdate($timestamp);
   $thisYear      = $timepieces["year"];
   $thisMonth     = $timepieces["mon"];
   for($thisDay=1;checkdate($thisMonth,$thisDay,$thisYear);$thisDay++);
   return $thisDay;
}
function time_firstDayOfMonth($timestamp) {
   $timepieces        = getdate($timestamp);
   return mktime(    $timepieces["hours"],
                     $timepieces["minutes"],
                     $timepieces["seconds"],
                     $timepieces["mon"],
                     1,
                     $timepieces["year"]);
}
function time_lastDayOfMonth($timestamp) {
    $firstday = time_firstDayOfMonth($timestamp);
    $timestamp = time_add($timestamp, 0, 0, 0, 0, 1, 0); // Next month
    return time_sub($timestamp, 0, 0, 0, 1, 0, 0); // Prev day
}
function time_monthStartWeekDay($timestamp) {
   return time_dayOfWeek(time_firstDayOfMonth($timestamp));
}
function time_weekDayString($weekday) {
   $myArray = Array(       0 => "Sun",
                           1 => "Mon",
                           2 => "Tue",
                           3 => "Wed",
                           4 => "Thu",
                           5 => "Fri",
                           6 => "Sat");
   return $myArray[$weekday];
}
function time_stripTime($timestamp) {
   $timepieces        = getdate($timestamp);
   return mktime(    0,
                     0,
                     0,
                   $timepieces["mon"],
                   $timepieces["mday"],
                   $timepieces["year"]);
}
function time_stripDate($timestamp) {
    $timepieces       = getdate($timestamp);
    return mktime(  $timepieces["hours"],
                    $timepieces["minutes"],
                    $timepieces["seconds"],
                    0,
                    0,
                    0);
}
function time_getDayOfYear($timestamp) {
   // Return the julian day of the year of $timestamp as an integer
   $timepieces        = getdate($timestamp);
   return intval($timepieces["yday"]);
}
function time_getYear($timestamp) {
   // Return the year of $timestamp as an integer
   $timepieces        = getdate($timestamp);
   return intval($timepieces["year"]);
}
function time_getMonth($timestamp) {
    // Return integer month of $timestamp
    $timepieces = getdate($timestamp);
    return intval($timepieces["mon"]);
}
function time_getDay($timestamp) {
    // Return integer day of month of $timestamp
    $timepieces = getdate($timestamp);
    return intval($timepieces["mday"]);
}
function time_dayDiff($timestamp1,$timestamp2) {
   // Return the difference in days between the two timestamps
   $secondsdiff = abs($timestamp1-$timestamp2);
   return $secondsdiff/(24*60*60);
}
/**
 * Return the difference in months between the two timestamps
 */
function time_monthDiff($timestamp1,$timestamp2) {
    $later = $timestamp1>$timestamp2?$timestamp1:$timestamp2;
    $earlier = $timestamp1<$timestamp2?$timestamp1:$timestamp2;
    $year1 = time_getYear($earlier);
    $year2 = time_getYear($later);
    if ($year1 == $year2)
        return time_getMonth($later)-time_getMonth($earlier);
    $yeardiff = $year2-$year1-1;
    return $yeardiff*12 + (13-time_getMonth($earlier)) + time_getMonth($later);
}
function time_nthWeekDay($timestamp, $n, $weekday) {
    // Return the timestamp for the $n-th $weekday in the month
    // of the given $timestamp.
    $dayone = time_monthStartWeekDay($timestamp);
    $diff = $weekday - $dayone;
    if ($diff < 0) { $diff += 7; }
    $timepieces = getdate($timestamp);
    $month = $timepieces["mon"];
    $day = 1 + $diff + (($n-1) * 7);
    $year = $timepieces["year"];
    if (checkdate($month,$day,$year)) {
        return mktime(0,0,0,$month,$day,$year);
    } else {
        // There are not $n $weekday-s in this month
        return 0;
    }
}

