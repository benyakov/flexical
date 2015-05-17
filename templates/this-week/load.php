<?php
function writeHTML_thisweek($day, $month, $year) {
    $dbh = new DBConnection();
    $tablepre = $dbh->getPrefix();
    global $language, $sprefix, $serverdir;
    $time = formattime('mysql');

    // Set up Where clause
    $lowdate = "{$year}-{$month}-{$day}";
    $highdate = time_add(mktime(0,0,0,$month,$day,$year),0,0,0,7,0,0);
    $q = $dbh->prepare("SELECT `m`.`id`,
               DATE_FORMAT(`m`.`date`, '%a&nbsp;%e&nbsp;%b') AS `date`,
               `m`.`title`, `m`.`all_day`,
               TIME_FORMAT(`m`.`start_time`, '%k') AS `start_hour`,
               TIME_FORMAT(`m`.`start_time`, '%i') AS `start_minute`,
               DATE_FORMAT(`m`.`date`, '%m') AS `month`,
               DATE_FORMAT(`m`.`date`, '%d') AS `day`,
               DATE_FORMAT(`m`.`date`, '%Y') AS `year`,
               TIME_FORMAT(`m`.`start_time`, {$time}) AS `stime`,
               `c`.`name` AS `category`,
               `c`.`restricted` AS `restricted`
               FROM `{$tablepre}eventstb` AS `m`
               LEFT JOIN `{$tablepre}categories` AS `c` USING (`category`)
               WHERE `date` >= :lowdate AND `date` <= :highdate
               ORDER BY `m`.`date`, `start_time`");
    $q->bindParam(':lowdate', $lowdate);
    $q->bindParam(':highdate', time_sqldate($highdate));
    $q->execute();
    $rowcount = 0;
    $lastday = "";
    $lasttime = mktime(0, 0, 0, $month, $day, $year);
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        if ($row['restricted']) { continue; }
        $rowcount += 1;
        $rowclasses = array();
        if ($rowcount % 2 == 1) $rowclasses[] = "oddrow";
        if ($row['all_day']) $rowclasses[] = "allday";
        $tr = buildopentag('tr', $rowclasses);
        $thisday = $row['date'];
        if ($thisday == $lastday) {
            $thisday = "";
        } else {
            $last = getdate($lasttime);
            $lasttime = mktime(0, 0, 0, $row['month'], $row['day'], $row['year']);
            $lastday = $thisday;
        }
        $title = stripslashes($row['title']);
        $stime = $row['all_day']?"":$row['stime'];
        // Build event row
        $str .= ($thisday=="")?
            "{$tr}\n<td class=\"eventdate\"></td>":
            "{$tr}\n<td class=\"eventdate hbar\">$thisday</td>";
        $str .= "<td class=\"eventtime hbar\">{$stime}</td>
                 <td class=\"eventtitle\"><a href=\"http://{$serverdir}/index.php?action=eventdisplay&id={$row['id']}\">{$title}</a></td>
                 <td class=\"eventcategory\">
                 <span class=\"".toCSSID($row['category'])."\">
                 {$row['category']}</span></td></tr>\n";
    }
    return $str;
}

// vim: set tags+=../../../**/tags :
?>
