<?php

function escapeTeX($in) {
    // Escape the string for TeX output
    return str_replace(array("\\", '&', ". "),
                       array("\\\\", "\\&", ".\\ "), $in);
}

function writeLaTeXevents($day, $month, $year, $length, $unit) {
    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
    global $language;
    session_start();
    // determine user's authorization status
    $auth = auth();
    $output = '\textbf{When} & \textbf{Category} & \textbf{Event Title}\\\\'."\n";
    $categoryMatches = categoryMatchString();

    $time = formattime('mysql-tex');

    // Set up Where clause
    $lowdate = "{$year}-{$month}-{$day}";
    $highdate = time_add(mktime(0,0,0,$month,$day,$year),0,0,0,
      $unit==1?$length:       // "days"; see __('units')
      ($unit==2?$length*7:0),   // "weeks"
      $unit==3?$length:0,     // "months"
      $unit==4?$length:0);     // "years"

    $whereclause = "WHERE `date` >= '{$lowdate}' AND `date` <= '" . time_sqldate($highdate) . "'";
    $whereclause .= getfilterclause(" AND ");
    $sql = "SELECT `m`.`id`,
          DATE_FORMAT(`m`.`date`, '%a %e %b %y') AS `date`,
          `m`.`title`, `m`.`all_day`, `m`.`related`,
          TIME_FORMAT(`m`.`start_time`, {$time}) AS `stime`,
          `c`.`name` AS `category`,
          `c`.`restricted` AS `restricted`
          FROM `{$tablepre}eventstb` AS `m`
          LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
          $whereclause $categoryMatches
          ORDER BY `m`.`date`, `start_time`");
    $result = $dbh->query($sql);

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if ($row['restricted'] && !$auth) { continue; }
        $thisday = $row['date'];
        if ($thisday == $lastday) {
            $thisday = "";
        } else {
            $lastday = $thisday;
        }
        $title = escapeTeX(stripslashes($row['title']));
        $stime = $row['all_day']?"":$row['stime'];
        $output .= ($thisday=="")?$stime:"{$thisday} {$stime}";
        $output .= "& {$row['category']} & {$title}\\\\\n";
    }
    return $output;
}

// vim: set tags+=../../../**/tags :
?>
