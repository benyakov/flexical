<?php
if (! (getPOST("start") && getPOST("end"))) {
    if (preg_match('/\d{4}-\d\d?/', getGET("start"))
        && preg_match('/\d{4}-\d\d?/', getGET('end')))
    {
        $_POST['start'] = getGET('start');
        $_POST['end'] = getGET('end');
    } else {
        if (isset($_SESSION[$sprefix]["rangestart"])) {
            $_POST['start'] = $_SESSION[$sprefix]["rangestart"];
            $_POST['end'] = $_SESSION[$sprefix]["rangeend"];
            $_POST['tally'] = getIndexOr($_SESSION[$sprefix], "rangetally");
        } else {
            // TODO: Perhaps make this respect user's timezone?
            $_POST['start'] = time_getYear(time()).'-'.time_getMonth(time());
            $_POST['end'] = $_POST['start'];
        }
    }
}
if (getGET('tally')) $_POST['tally'] = "checked";
if (isset($_POST['tally']))
    $_SESSION[$sprefix]["rangetally"] = $_POST['tally'];
$_SESSION[$sprefix]["rangestart"] = $_POST['start'];
$_SESSION[$sprefix]["rangeend"] = $_POST['end'];
$startparts = explode("-", $_POST["start"]);
$endparts = explode("-", $_POST["end"]);
$lowtime = mktime(0,0,0,(int) $startparts[1], 1, (int) $startparts[0]);
$hightime = mktime(0,0,0,$endparts[1]+1, 1, (int) $endparts[0]);
$hightime = time_sub($hightime,0,0,0,1,0,0); // Subtract a day
$lowdate = time_sqlDate($lowtime);
$highdate = time_sqlDate($hightime);
$q = $dbh->prepare("SELECT date, title, text, c.name AS category,
    c.restricted
    FROM `{$tablepre}eventstb` AS e
    JOIN `{$tablepre}categories` AS c USING (category)
    WHERE e.date >= :lowdate
    AND e.date <= :highdate
    ".categoryMatchString()
    ." ORDER BY e.date");
$q->bindValue(":lowdate", $lowdate);
$q->bindValue(":highdate", $highdate);
if (! $q->execute())
    die(array_pop($q->errorInfo()));
$results = $q->fetchAll(PDO::FETCH_ASSOC);
$results = array_filter($results, function($i) use($auth) {
    return ((!$i['restricted']) || $auth);
});
$eventdays = array();
foreach ($results as $event) {
    $eventdays[$event["date"]][] = $event['category'];
}
$currentcatstring = urlencode(implode(",", $_SESSION[$sprefix]["categories"]));

$customcounts = array();
$daycount = dayCount($results, $customcounts);
foreach ($customcounts as $date => $category) {
    $eventdays[date('Y-m-d', $date)][] = $category;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"></meta>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <?php
    jqueryCDN();
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#minimizesetup").click(function(evt) {
                evt.preventDefault();
                if ($(this).html() == "[-]") {
                    $("#rangesetup").hide();
                    $(this).html("[+]");
                } else if ($(this).html() == "[+]") {
                    $("#rangesetup").show();
                    $(this).html("[-]");
                } else {
                    $(this).html("[-]");
                }
            }).click();
        });
    </script>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/categorystyles.css">
    <link rel="stylesheet" type="text/css" href="templates/summary/css/styles.css">
    <style type="text/css" media="print">
        <!--
        #page { width: 8.5in; height: 11in; margin: 0.25in; }
        -->
    </style>
</head>
<body>
<?php echo topMatter($action, $sitetabs); ?>
<div id="page">
    <h1 id="summarytitle"><?=$configuration['site_title']?></h1>
    <div id="print"><a href="javascript: window.print();" id="printlink"><?=__("print")?></a>
<a href="javascript:void(0);" id="minimizesetup">[-]</a>
<?php  if (getPOST('tally')) $summarytallypart = "&tally={$_POST['tally']}";
    else $summarytallypart = ""; ?>
<div id="shortcuturl"><a href="<?=$serverdir?>?categories=<?=$currentcatstring?>&start=<?=$_POST['start']?>&end=<?=$_POST['end']?>&action=summary<?=$summarytallypart?>"
    title="<?=__('direct link')?>"><?=__('direct link')?></a></div>

<div id="rangesetup">
<form name="rangesetup" method="post">
    <input name="start" required pattern="\d{4}-\d\d?" placeholder="YYYY-MM"
        value="<?=$_POST['start']?>">
    <input name="end" required pattern="\d{4}-\d\d?" placeholder="YYYY-MM"
        value="<?=$_POST['end']?>">
        <label for="tally"><?=__("show day tally")?></label>
        <input name="tally" type="checkbox" value="checked"
        <?=htmlspecialchars(getPOST('tally'))?>>
    <button type="submit"><?=__("set range")?></button>
</form>
</div>
</div>

<div class="leftcolumn">
<?php echo leftCalendars($lowtime, $hightime, $eventdays); ?>
</div>
<div class="rightcolumn">
<?php echo rightCalendars($lowtime, $hightime, $eventdays); ?>
</div>
<div class="eventlist">
<p><?=__('boxed dates appear here')?></p>
<?php echo eventsByMonth($results); ?>
</div>
<?php if (getPOST('tally')) { ?>
<div class="daycount">
<?php echo $daycount;  ?>
</div>
<?php } ?>
</div>
<?=footprint($auth)?>
</body>
</html>
