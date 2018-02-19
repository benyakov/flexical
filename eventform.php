<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

if (!auth()) {
    setMessage(__('accessdenied'));
    header("Location: {$SDir()}/index.php");
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

if (! "ajax" == $_POST['use']) {
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

</head>
<body>
<? }
if ("ajax" == $_POST['use']) ob_start();
?>
    <script type="text/javascript" language="JavaScript">
    <?php
    js_zeroTime();
    ?>
    function hideSpecifics() {
        $(".specifics").prop('disabled', true);
    }
    function showSpecifics() {
        $(".specifics").prop('disabled', false);
    }
    function checkOrigDate() {
        var day = $("#evday").val();
        var month = $("#evmonth").val();
        var year = $("#evyear").val();
        if (Boolean($("#include_related:checked").length)) {
            if (year+"-"+month+"-"+day == $("#EditDatePicker").data('orig')) {
                showSpecifics();
            } else {
                hideSpecifics();
            }
        } else {
            showSpecifics();
        }
    }

    $(function(){
        $(".jsonly").css("visibility", "visible");
        $("#EditDatePicker").datepicker({
            buttonImage: 'images/calendarbutton.png',
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            showOn: 'both',
            defaultDate: '<?=$m?>/<?=$d?>/<?=$y?>',
            beforeShow: function (input, inst) {
                    var width = $(window).width();
                    width = width/2;
                    var dpwidth = inst.dpDiv.width();
                    setTimeout(function () {
                        inst.dpDiv.css({ top: 40, left: width - dpwidth/2 });
                    }, 0);
            },
            onSelect: function(chosenDate, picker){
                var dateitems = chosenDate.split('/'); // MM/DD/YYYY
                $("#evmonth").val(dateitems[0].replace(/^0+/g,""));
                $("#evday").val(dateitems[1].replace(/^0+/g,""));
                $("#evyear").val(dateitems[2]);
                checkOrigDate();
            } }).css("visibility", "visible");
        $("#year").change(checkOrigDate);
        $("#month").change(checkOrigDate);
        $("#day").change(checkOrigDate);
        $("#include_related").change(checkOrigDate);
        $("#resetbutton").click(showSpecifics);
    });
    </script>

<span class="add_new_header"><?= $headerstr ?></span>
    <form name="eventForm" id="eventForm" method="POST" action="eventsubmit.php<?= $qstr ?>">
    <table border=0 cellspacing=7 cellpadding=0>
    <input type="hidden" name="uid" value="<?=$uid?>">
    <input type="hidden" name="related" value="<?=$related?>">
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('datetext')?></span></td>
            <td>
            <input type="hidden" id="EditDatePicker" data-orig="<?="{$y}-{$m}-{$d}"?>"
                value="<?="{$y}-{$m}-{$d}"?>">
            <?php monthPullDown($m, __('months'), "evmonth"); dayPullDown($d, "evday"); yearPullDown($y, "evyear"); ?></td>
            <?php if ($related) { ?>
            <td rowspan="2" class="related-options">
                <span class="form_labels"><?=__('Include Related')?></span>&nbsp;
                <input type="checkbox" name="include_related" id="include_related" value="1"><br>
                <span class="form_labels"><?=__('future only')?></span>&nbsp;
                <input type="checkbox" name="future_only" id="future_only" value="1"><br>
            </td>
            <?php } ?>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('title')?></span></td>
            <td colspan=2>
            <input required class="specifics" type="text" name="title" size="25"
                    value="<?= $title ?>" maxlength="50"></td>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('text')?></span></td>
            <td colspan=2>
            <textarea class="specifics" cols=44 rows=6 name="text"><?= $text ?></textarea></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right"><span class="form_labels"><?=__('All Day')?></span></td>
            <td><input type="checkbox" class="specifics" name="all_day" value="1" <?=$alldaystr?>
            onClick="zeroTime('batchform')"></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right"><span class="form_labels"><?=_('Time Zone')?></span></td>
            <td><?=timezoneDropDown("timezone", $timezone, "specifics")?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('starttime')?></span></td>
            <td><?php hourBox($shour, "eventForm", "start_hour", $time_disabled, "specifics");
                    ?><b>:</b><?php
                   minuteBox($sminute, "eventForm", "start_minute", $time_disabled, "specifics");
                   amPmPullDown($spm, "start", $ampmnone, $time_disabled, "specifics"); ?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('endtime')?></span></td>
            <td><?php hourBox($ehour, "eventForm", "end_hour", $time_disabled, "specifics"); ?><b>:</b><?php
                   minuteBox($eminute, "eventForm", "end_minute", $time_disabled, "specifics");
                   amPmPullDown($epm, "end", $ampmnone, $time_disabled, "specifics"); ?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right">
            <span class="form_labels"><?=__('category')?></span></td>
            <td colspan="2"><?php categoryPullDown($cat, false, "specifics"); ?>
            <input type="text" name="newcategory" size="14" value="" maxlength="50" class="specifics"> </td>
        </tr>
        <tr><td></td><td><br>
        <input type="submit" name="submit" value="<?= $buttonstr ?>">&nbsp;
        <input type="reset" name="reset" id="resetbutton" value="<?=__("resetbutton")?>">
        <a id="cancelButton" class="tinybutton" href="eventsubmit.php?cancel=1" title="Cancel"><?= __('cancel') ?></a>
    </td></tr>
    </table>
    </form>
    <p><a href="help/index.php?n=basic/Creating,+Editing+and+Copying+Events.en.txt"><?=__('help')?></a></p>
<? if ('ajax' == $_POST['use']) {
    $dialog = ob_get_clean();
    echo json_encode(array(1, $dialog));
    exit(0);
}
?>
</body>
</html>


<!-- vim: set foldmethod=indent : -->
