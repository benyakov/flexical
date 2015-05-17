<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

if (!auth()) {
    setMessage(__('accessdenied'));
    header("Location: http://{$_SESSION[$sprefix]['serverdir']}/index.php");
    exit(0);
}
$id = getGET('id');
$authdata = $_SESSION[$sprefix]['authdata'];
$uid = $authdata['uid'];
$ampmnone = false;
if (empty($id)) {
    // if no id, then set values for add form
    $d = isset($_GET['d'])?$_GET['d']:$_SESSION[$sprefix]['day'];
    $m = isset($_GET['m'])?$_GET['m']:$_SESSION[$sprefix]['month'];
    $y = isset($_GET['y'])?$_GET['y']:$_SESSION[$sprefix]['year'];
    $text = $title = "";
    $shour = $sminute = "";
    $spm = "-";
    $ehour = $eminute = "";
    $epm = "-";
    $all_day = 0;
    $ampmnone = true;
    $cat = "";
    $headerstr = __('addheader');
    $buttonstr = __('addbutton');
    $pgtitle = __('addeventtitle');
    $qstr = "?flag=add";
    $related = "";
    $alldaystr = "";
    $time_disabled = 0;
    $timezone = $configuration['default_timezone'];
} else {
    $q = $dbh->prepare("SELECT YEAR(`date`) AS `y`,
        MONTH(`date`) AS `m`, DAY(`date`) as `d`,
        `start_time`, `end_time`, `title`, `related`,
        `{$tablepre}categories`.`name` AS `category`, `text`,
        `all_day`, `timezone`
        FROM `{$tablepre}eventstb`
        LEFT JOIN `{$tablepre}categories`
        USING (`category`) WHERE `id` = :id");
    $q->bindParam(':id', $id);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!empty($row)) {
        $qstr = "?flag=edit&id=$id";
        $headerstr = __('editheader');
        $buttonstr = __('editbutton');
        $pgtitle = __('editeventtitle');
        $title = stripslashes($row["title"]);
        $text = stripslashes($row["text"]);
        $m = $row["m"]; $d = $row["d"]; $y = $row["y"];

        $starttime = explode(":", $row["start_time"]);
        $shour = $starttime[0];
        $sminute = $starttime[1];
        $spm = $epm = null;

        if ($_SESSION[$sprefix]['timeformat'] == 12) {
            if ($shour > 12) {
                $shour = ($shour - 12);
                $spm = true;
            } elseif (12 == $shour) {
                $spm = true;
            }
        } else {
            $ampmnone = true;
        }

        $endtime = explode(":", $row["end_time"]);
        $ehour = $endtime[0];
        $eminute = $endtime[1];

        if ($_SESSION[$sprefix]['timeformat'] == 12) {
            if ($ehour > 12) {
                $ehour = ($ehour - 12);
                $epm = true;
            } elseif (12 == $ehour) {
                $epm = true;
            }
        } else {
            $ampmnone = true;
        }

        $all_day = $row['all_day'];
        if ($all_day) {
            $alldaystr = "CHECKED";
            $time_disabled = "1";
        } else {
            $alldaystr = "";
            $time_disabled = "0";
        }
        $timezone = $row['timezone'];

        $cat = $row['category'];
        $related = $row['related'];

    } else {
        echo __('missingevent');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $pgtitle ?></title>
    <link rel="stylesheet" type="text/css" href="css/styles-pop.css">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"></meta>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>

<?php
    jqueryCDN();
    jqueryuiCDN();
?>
    <script type="text/javascript" language="JavaScript">
    <?php
    js_zeroTime();
    ?>
    $(function(){
        $(".jsonly").css("visibility", "visible");
        $("#DatePicker").datepicker({
            buttonImage: 'images/calendarbutton.png',
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            showOn: 'both',
            defaultDate: '<?=$m?>/<?=$d?>/<?=$y?>',
            onSelect: function(chosenDate, picker){
                var dateitems = chosenDate.split('/'); // MM/DD/YYYY
                $("#month").val(dateitems[0].replace(/^0+/g,""));
                $("#day").val(dateitems[1].replace(/^0+/g,""));
                $("#year").val(dateitems[2]);
            } }).css("visibility", "visible");
    });
    </script>

</head>
<body>
<span class="add_new_header"><?= $headerstr ?></span>
    <table border=0 cellspacing=7 cellpadding=0>
    <form name="eventForm" id="eventForm" method="POST" action="eventsubmit.php<?= $qstr ?>">
    <input type="hidden" name="uid" value="<?=$uid?>">
    <input type="hidden" name="related" value="<?=$related?>">
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('datetext')?></span></td>
            <td>
            <input type="hidden" id="DatePicker" value="<?="{$y}-{$m}-{$d}"?>">
            <?php monthPullDown($m, __('months')); dayPullDown($d); yearPullDown($y); ?></td>
            <?php if ($related) { ?>
            <td><span class="form_labels"><?=__('Include Related')?></span>&nbsp;
                <input type="checkbox" name="include_related" value="1">
            </td>
            <?php } ?>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('title')?></span></td>
            <td colspan=2>
            <input type="text" name="title" size="25" value="<?= $title ?>" maxlength="50"></td>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('text')?></span></td>
            <td colspan=2>
            <textarea cols=44 rows=6 name="text"><?= $text ?></textarea></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right"><span class="form_labels"><?=__('All Day')?></span></td>
            <td><input type="checkbox" name="all_day" value="1" <?=$alldaystr?>
            onClick="zeroTime('batchform')"></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right"><span class="form_labels"><?=_('Time Zone')?></span></td>
            <td><?=timezoneDropDown("timezone", $timezone)?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('starttime')?></span></td>
            <td><?php hourBox($shour, "eventForm", "start_hour", $time_disabled); ?><b>:</b><?php
                   minuteBox($sminute, "eventForm", "start_minute", $time_disabled);
                   amPmPullDown($spm, "start", $ampmnone, $time_disabled); ?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('endtime')?></span></td>
            <td><?php hourBox($ehour, "eventForm", "end_hour", $time_disabled); ?><b>:</b><?php
                   minuteBox($eminute, "eventForm", "end_minute", $time_disabled);
                   amPmPullDown($epm, "end", $ampmnone, $time_disabled); ?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right">
            <span class="form_labels"><?=__('category')?></span></td>
            <td colspan="2"><?php categoryPullDown($cat, false); ?>
            <input type="text" name="newcategory" size="14" value="" maxlength="50"> </td>
        </tr>
        <tr><td></td><td><br>
    <input type="submit" name="submit" value="<?= $buttonstr ?>">&nbsp;
    <input type="submit" name="cancel" value="<?= __('cancel') ?>">
    </td></tr>
    </form>
    </table>
    <p><a href="help/index.php?n=basic/Creating,+Editing+and+Copying+Events.en.txt"><?=__('help')?></a></p>
</body>
</html>


<!-- vim: set tags+=../**/tags : -->
