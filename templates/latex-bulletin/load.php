<?php

function cmpEvents($a, $b) {
    if ($a['date'] == $b['date']) {
        return ($a['time'] < $b['time']) ? -1 : 1;
    } else {
        return ($a['date'] < $b['date']) ? -1 : 1;
    }
}

function writeLaTeXevents($day, $month, $year, $length, $unit, $mode="normal") {
    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
    global $language;
    session_start();
    // determine user's authorization status
    $auth = auth();
    $output = "";
    $categoryMatches = categoryMatchString();
    $time = formattime('mysql-tex');

    // Set up Where clause
    $lowdate = "{$year}-{$month}-{$day}";
    $highdate = time_add(mktime(0,0,0,$month,$day,$year),0,0,0,
      $unit==1?$length:       // "days"; see __('units')
      ($unit==2?$length*7:0),   // "weeks"
      $unit==3?$length:0,     // "months"
      $unit==4?$length:0);     // "years"

    $filterclause .= getfilterclause(" AND ");
    $q = $dbh->prepare("SELECT `m`.`id`, `m`.`title`,
        `m`.`all_day`, `m`.`related`,
          DATE_FORMAT(`m`.`date`, '%a') AS `weekday`,
          DATE_FORMAT(`m`.`date`, '%m/%d') AS `day`,
          `m`.`date` AS `date`,
          TIME_FORMAT(`m`.`start_time`, {$time}) AS `stime`,
          `m`.`start_time` AS `time`,
          `c`.`name` AS `category`,
          `c`.`restricted`
          FROM `{$tablepre}eventstb` AS `m`
          LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
          WHERE `date` >= :lowdate AND `date` <= :highdate
          $filterclause $categoryMatches
          ORDER BY `m`.`date`, `start_time`");
    $q->bindParam(':lowdate', $lowdate);
    $q->bindParam(':highdate', time_sqldate($highdate));
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    require_once("./lib/remote.php");
    if ("remote" == $mode) {
        provideRemoteRows($rows);
        exit(0);
    }
    $remoterows = getRemoteRows('latex-bulletin', array($day, $month, $year, $length, $unit));
    $rows = array_merge($rows, $remoterows);
    usort($rows, cmpEvents);
    foreach ($rows as $row) {
        if ($row['restricted'] && !$auth)  continue;
        if ($row['all_day'])  continue;
        $title = str_replace('&', '\&', stripslashes($row['title']));
        $stime = $row['stime'];
        $stime = str_replace('AM', '\textsc{am}', $stime);
        $stime = str_replace('PM', '\textsc{pm}', $stime);
        if ($lastday == $row['day']) {
            $weekday = "";
        } else {
            $weekday = $row['weekday'];
            $lastday = $row['day'];
        }
        if (strpos($row['category'], 'Bethany') !== false) {
            $category = '\BTDn';
        } elseif (strpos($row['category'], 'Concordia') !== false) {
            $category = '\CHRn';
        } elseif (strpos($row['category'], 'Services') !== false
            || strpos($row['category'], 'Joint') !== false) {
            $category = '\Bothn';
        } elseif (strpos($row['category'], 'School') !== false) {
            $category = '\SCLn';
        } else {
            $category = '';
        }
        $output .= "{$weekday} & {\\small {$stime}} & {$category} & {\\small\\RaggedRight {$title}}\\\\\n";
    }
    return $output;
}
// vim: set tags+=../../../**/tags :

?>
