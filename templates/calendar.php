<?php
if ("day" == $_GET["json"]) {
    list($html, $categories) = getDayJSON($year, $month, $day, "true"==$_GET['short']);
    echo json_encode(array($html, $categories));
    exit(0);
} elseif ("days" == $_GET["json"]) {
    $dates = json_decode($_POST['dates']);
    $html = array();
    $categories = array();
    foreach ($dates as $date) {
        list($y, $m, $d) = explode("-", $date);
        list($h, $c) = getDayJSON($y, $m, $d, "true"==$_GET['short']);
        $html[$date] = $h;
        $categories = array_merge($categories, $c);
    }
    $rv = json_encode(array(true, $html, array_unique($categories)));
    echo json_encode($rv);
    exit(0);
}
ob_start();
// Allow showing remote installation categories
require_once("./lib/remote.php");
if (needsRemoteRows()) {
    $mode = "remote";
} else {
    $mode = "normal";
}
?>
<!DOCTYPE html>
<html lang="<?=$language?>">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"></meta>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <?php
    jqueryCDN();
    jqueryuiCDN();
    javaScript();
    ?>
    <script type="text/javascript" src="templates/calendar/js/calendar.js"></script>
    <script type="text/javascript" src="lib/ajax.js"></script>
    <script type="text/javascript" language="JavaScript">
    </script>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="templates/calendar/css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/categorystyles.css">
    <link rel="stylesheet" type="text/css" media="print" href="css/print.css">
    <style type="text/css" media="print">
        <!--
        @page { size: landscape; }
        body { size: 8.5in 11in landscape;
               margin: 0.25in; }
        -->
    </style>
</head>
<body>

<?php echo topMatter($action, $sitetabs); ?>

<div id="page">
<?php
    if (is_numeric(getGET('monthcount'))) {
        $addmonths = intval($_GET['monthcount'])-1;
        if ($addmonths < 0) $addmonths = 0;
    } else $addmonths = 0;
    foreach (range(0, $addmonths) as $extramonthnum) {
        $monthcalc = mktime(0, 0, 0, $m+$extramonthnum, 1, $y);
        $thismonth = time_getMonth($monthcalc);
        $thisyear = time_getYear($monthcalc);
        if (0 == $extramonthnum % 2) $monthbg = "oddrow";
        else $monthbg = "evenrow";
?>
<div class="monthcalendarrow <?=$monthbg?>">
    <table class="monthheading">
        <tr>
            <td align="left">
                <?= scrollArrows($d, $m, $y, $length, $unit, $action) ?><span class="date_header">&nbsp;<?= __('months', $thismonth-1) ?>&nbsp;<?= $thisyear ?>&nbsp;</span>
            </td>
            <td align="center" class="generationdate">
                <div><?= __('generationdate') . gmdate("Y-m-d H:i:s") ?> </div>
                <div><?= __('modificationdate') . gmdate("Y-m-d H:i:s", $lastmodtime)?></div>
            </td>

            <td align="center" class="gotoday">
            <a href="index.php?current=1"><?= __('current')?></a>
            </td>
            <td align="right" id="monthYear">
            <!-- form tags must be outside of <td> tags -->
            <form name="monthYear" action="<?=$_SERVER['PHP_SELF']?>" method="GET">
            <span class="monthcount"><?=__('month count')?></span> <input type="number" min="1" max="60" name="monthcount" value="1" class="lengthinput">
            <?php  monthPullDown($thismonth, __('months')); yearPullDown($thisyear); ?>
            <input type="submit" value="GO">
            </form>
            </td>

        </tr>
        <?php if (array_key_exists("filters", $_SESSION[$sprefix]) && $_SESSION[$sprefix]['filters']) { ?>
        <tr class="filternotice">
            <td colspan="4"><a href="filter.php?unfilter=1"><?= __('filternotice') ?></a></td>
        </tr>
        <?php } ?>
    </table>
    <div class="calendarbox-container">
    <?php
        if ("remote" == $mode) {
            ob_end_clean();
            writeCalendar($thismonth, $thisyear, "remote");
            exit(0);
        } else {
            echo writeCalendar($thismonth, $thisyear);
        }
    ?>
    </div>
</div>
    <?php } ?>

</div>

<?=footprint($auth)?>

<?=monthmenu()?>
</body>
</html>

<?php
    echo ob_get_clean();
?>

<!-- vim: set tags+=../../**/tags : -->
