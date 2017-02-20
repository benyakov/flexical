<?php
$time=formattime();
$q = $dbh->prepare("SELECT e.`id`, e.date, YEAR(e.date) AS `y`,
        MONTH(e.date) AS `m`, DAY(e.date) AS `d`, e.`title`,
        c.`name` AS `category`, c.`restricted` AS `authonly`,
        e.`text`, TIME_FORMAT(e.`start_time`, {$time}) AS `stime`,
        TIME_FORMAT(e.`start_time`, '%k') as `start_hour`,
        TIME_FORMAT(e.`end_time`, {$time}) AS `etime`,
        u.`uid`, u.`fname`, u.`lname`, e.`all_day`, e.`related`
        FROM `{$tablepre}eventstb` AS e
        LEFT JOIN `{$tablepre}users` AS u
        ON (e.`uid` = u.`uid`)
        LEFT JOIN `{$tablepre}categories` AS c
        ON (e.`category` = c.`category`)
        WHERE e.`id`= ?");
if ($q->execute(array($id))) {
    $row = $q->fetch(PDO::FETCH_ASSOC);
} else die(array_pop($q->errorInfo()));


$d = $row["d"]; $m = $row["m"]; $y = $row["y"];
$dateline = "$d " . __('months', $m-1) . " $y";

// get day of week
$wday = date("w", mktime (0,0,0,$m,$d,$y));
?>

<!DOCTYPE html>
<html lang="<?=$language?>">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"></meta>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/categorystyles.css">
    <?php 	if ($auth) {
    javaScript();
    } ?>
</head>
<body>

<?php echo topMatter($action, $sitetabs); ?>

<div id="page">
<?php
// When no event has been selected yet in this session.
if ($id == -1 || !$row) {
    setMessage(__('no id provided'));
    showMessage();?>
    </div>
    </body>
    </html>
    <?php  return;
}
$currentevent = $row;
$currentevent['current'] = true;

// Get other events

$q = $dbh->prepare("SELECT e.`id`, e.date, YEAR(e.date) AS `y`,
        MONTH(e.date) AS `m`, DAY(e.date) AS `d`, e.`title`,
        c.`name` AS `category`, c.`restricted` AS `authonly`,
        e.`text`, TIME_FORMAT(e.`start_time`, {$time}) AS `stime`,
        TIME_FORMAT(e.`start_time`, '%k') as `start_hour`,
        TIME_FORMAT(e.`end_time`, {$time}) AS `etime`,
        u.`uid`, u.`fname`, u.`lname`, e.`all_day`, e.`related`
        FROM `{$tablepre}eventstb` AS e
        LEFT JOIN `{$tablepre}users` AS u
        ON (e.`uid` = u.`uid`)
        LEFT JOIN `{$tablepre}categories` AS c USING (`category`)
        WHERE YEAR(e.`date`) = :year
        AND MONTH(e.`date`) = :month
        AND DAY(e.`date`) = :day
        AND `id` != :id");

$q->bindParam(':year', $y);
$q->bindParam(':month', $m);
$q->bindParam(':day', $d);
$q->bindParam(':id', $id);
$q->execute() or die(array_pop($q->errorInfo()));
$otherevents = array();
while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
    $row['current'] = false;
    $eventcollector[] = $row;
}
$eventcollector[] = $currentevent;
$events = array();
$alldays = array();
foreach ($eventcollector as $e) {
    if ($e['all_day']) {
        $alldays[] = $e;
    } else {
        $events[$e['start_hour']][] = $e;
    }
}
?>
<div id="eventview-content">

<h1><?php echo __('days', $wday) ?>, <?php echo $dateline ?></h1>

<table cellspadding="0" cellspacing="0" border="0" width="100%"> <?php
foreach ($alldays as $a) {
    if ($a['authonly'] && ! $auth) { continue; }
?>
    <tr>
        <td></td><td>        <?php
    writePosting($a, $auth); ?>
        </td>
    </tr>                    <?php
}                            ?>
    <tr><td class="hourcolumn topborder" align="center"><span class="hourlabel"><?=$_('hour')?></span></td><td></td></tr>
                             <?php
foreach (range(0, 23) as $hour) {
    $evenodd = $hour%2==0?"":" class=\"oddrow\"";
    if ($_SESSION[$sprefix]['timeformat'] == 12) {
        if ($hour > 12) {
            $hourtext = $hour - 12;
        } elseif ($hour == 0) {
            $hourtext = 12;
        } else {
            $hourtext = $hour;
        }
    } else {
        $hourtext = $hour;
    }
    if ($hour == 23) {
        $addborder = "bottomborder";
    } else {
        $addborder = "";
    }                             ?>
    <tr <?=$evenodd?>>
        <td class="hourcolumn <?=$addborder?>" align="center" valign="center"><span class="hournumber"><?=$hourtext?></span></td>
        <td>                      <?php
    if (array_key_exists($hour, $events)) {
        foreach ($events[$hour] as $event) {
            if ($event['authonly'] && ! $auth) { continue; }
            writePosting($event, $auth);
        }
    }                             ?>
    </td>
    </tr>                         <?php
}                                 ?>
</table>
</div>
</div>
<?=footprint($auth)?>
<?=monthmenu()?>
</body>
</html>
<!-- vim: set tags+=../../**/tags : -->
