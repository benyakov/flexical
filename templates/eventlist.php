<!DOCTYPE html>
<html lang="<?=$language?>">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/></meta>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <?php javaScript() ?>
    <link rel="stylesheet" type="text/css" href="css/styles.css"/>
    <link rel="stylesheet" type="text/css" href="templates/eventlist/css/styles.css"/>
    <link rel="stylesheet" type="text/css" href="css/categorystyles.css"/>
    <?php jqueryCDN(); ?>
    <?php jqueryuiCDN(); ?>
    <script type="text/javascript" src="lib/ajax.js"></script>
    <script type="text/javascript" src="templates/eventlist/js/eventlist.js"></script>
	<script type="text/javascript" language="JavaScript">
        $(function(){
            $(".jsonly").css("visibility", "visible");
            $("#DatePicker").datepicker({
                buttonImage: 'images/calendarbutton.png',
                buttonImageOnly: true,
                changeMonth: true,
                changeYear: true,
                showOn: 'both',
                onSelect: function(chosenDate, picker){
                    var dateitems = chosenDate.split('/'); // Y/M/D
                    document.displaySpan.month.value = dateitems[0];
                    document.displaySpan.day.value = dateitems[1];
                    document.displaySpan.year.value = dateitems[2];
                } }).css("visibility", "visible");
        });
    </script>
</head>
<body>

<?php echo topMatter($action, $sitetabs); ?>

<div id="page">
<?php if (getGET('format') == "email") {
    echo "<h1>{$d} ".__('months', $m-1)."</h1>";
} else { ?>
    <div id="event-header">
        <?php if (! relatedFilter()) { ?>
            <span class="noprint"><?= scrollArrows($d, $m, $y, $length, $unit, $action) ?></span>
            <span class="date_header"><?=$d?>&nbsp;<?= __('months', $m-1) ?>&nbsp;<?= $y ?>&nbsp;</span>
        <?php } ?>
        <?php if (! relatedFilter()) { ?>

        <span class="gotoday">
        <a href="index.php?current=1"><?= __('current')?></a>
        </span>

        <div id="displayspan">
        <form name="displaySpan" action="<?=$_SERVER['PHP_SELF']?>" method="GET">
        <input type="hidden" name="action" value="eventlist">
        <div><span class="remindersize"><?=__('opentime')?></span><br/> <?php opentimeBox($o);?></div>&nbsp;
        <input type="hidden" id="DatePicker" value="<?="{$_('year')}-{$_('month')}-{$_('date')}"?>">
        <div><span class="remindersize"><?=__('year')?></span><br/>
        <?php yearBox($y, "displaySpan");?> </div>
        <div><span class="remindersize"><?=__('month')?></span><br/> <?php monthBox($m, "displaySpan");?></div>
        <div><span class="remindersize"><?=__('day')?></span><br/> <?php dayBox($d, "displaySpan");?></div>&nbsp;
        <div id="displayspan-span"><span class="remindersize"><?=__('span')?></span><br/> <?php lengthBox($l, "displaySpan"); pullDown($u, __('units'), "unit")?></div>
        <input name="listsubmit" type="submit" value="Go">
        </form>
        </div>
        <?php } else { ?>
        &nbsp;
        <?php }
       if (array_key_exists("filters", $_SESSION[$sprefix]) && $_SESSION[$sprefix]['filters']) { ?>
        <div class="filternotice">
        <a href="filter.php?unfilter=1"><?= __('filternotice') ?></a>
        </div>
        <?php } ?>
    </div>
<?php }
if (relatedFilter()) { $o = false; }
echo writeEvents($d, $m, $y, $l, $u, $o);
?>

<div class="generationdate">
    <div><?= __('generationdate') . gmdate("Y-m-d H:i:s") ?></div>
    <div><?= __('modificationdate') . gmdate("Y-m-d H:i:s", $lastmodtime) ?></div>
</div>
</div>
<?php if (getGET('format') != 'email') echo footprint($auth);?>

</body>
</html>

<?php
function relatedFilter() {
    global $sprefix;
    return (isset($_SESSION[$sprefix]['filters'])
     && getIndexOr($_SESSION[$sprefix]['filters'],'related',false));
}

// vim: set tags+=../../**/tags :
