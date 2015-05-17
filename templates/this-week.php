<?php
if ('next' == $_GET['week']) {
    $date = mktime(0,0,0,$m,$d,$y);
    $daysToNextSunday = 6 - time_dayOfWeek($date);
    $date = time_add($date, 0, 0, 0, $daysToNextSunday, 0, 0);
    $d = time_getDay($date);
    $m = time_getMonth($date);
    $y = time_getYear($date);
}
?>
<table <?php if ($_GET['classes']) echo "class='{$_GET["classes"]}'";?>>
<?= writeHTML_thisweek($d, $m, $y) ?>
</table>
<!-- vim: set tags+=../../**/tags : -->
