<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

if (!auth()) {
    setMessage(__('accessdenied'));
    header("Location: {$SDir()}/index.php");
    exit(0);
} else {

	$id = $_GET['id'];
	$authdata = $_SESSION[$sprefix]['authdata'];
	$uid = $authdata['uid'];

    $q = $dbh->prepare("SELECT YEAR(`date`) AS `y`,
        MONTH(`date`) AS `m`, DAY(`date`) as `d`,
        `start_time`, `end_time`, `all_day`, `title`,
        `{$tablepre}categories`.`name` AS `category`, `text` FROM
        `{$tablepre}eventstb` LEFT JOIN `{$tablepre}categories`
            USING (`category`) WHERE `id` = :id");
    $q->bindParam(":id", $id);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
        $qstr = "?flag=copy&id=$id";
        $headerstr = __('copyheader');
        $buttonstr = __('editbutton');
        $pgtitle = __('editeventtitle');
        $title = stripslashes($row["title"]);
        $text = stripslashes($row["text"]);
        $body = Markdown(stripslashes($row["text"]));
        $m = $row["m"]; $d = $row["d"]; $y = $row["y"];

        $starttime = explode(":", $row["start_time"]);
        $shour = $starttime[0];
        $sminute = $starttime[1];

        if ($shour > 12) {
            $shour = ($shour - 12);
            $spm = true;
        } elseif ($shour == 12) {
            $spm = true;
        }

        $endtime = explode(":", $row["end_time"]);
        $ehour = $endtime[0];
        $eminute = $endtime[1];

        if ($ehour > 12) {
            $ehour = ($ehour - 12);
            $epm = true;
        } elseif ($ehour == 12) {
            $epm = true;
        }

        $all_day = $row['all_day'];
        $category = $row['category'];

        if (! "ajax" == $_POST['use']) {
	?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?= $pgtitle ?></title>
		<link rel="stylesheet" type="text/css" href="css/styles-pop.css">
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
    <?php js_checkrepeat();
       js_zeroTime();
    ?>
    $(function(){
        $(".jsonly").css("visibility", "visible");
        $("#CopyDatePicker").datepicker({
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

	<span class="add_new_header"><?= $headerstr ?></span>
		<form name="eventForm" method="POST" action="eventsubmit.php<?= $qstr ?>">
		<table border=0 cellspacing=7 cellpadding=0>
		<input type="hidden" name="uid" value="<?=$uid?>">
			<tr>
				<td nowrap valign="top" align="right" nowrap>
                <span class="form_labels"><?=__('datetext')?></span></td>
				<td><?="$y-$m-$d"?></td>
			</tr>
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="form_labels"><?=__('title')?></span></td>
				<td><?= $title ?></td>
			</tr>
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="form_labels"><?=__('text')?></span></td>
				<td><?= $body ?></td>
			</tr>
        <?php if ($all_day) { ?>
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="form_labels"><?=__('duration')?></span></td>
				<td><?= __('All Day')?></td>
			</tr>
        <?php } else { ?>
            <tr>
				<td nowrap valign="top" align="right" nowrap>
                <span class="form_labels"><?=__('starttime')?></span></td>
				<td><?= ($spm)?"$shour:$sminute PM":"$shour:$sminute AM"?></td>
            </tr>
			<tr>
				<td nowrap valign="top" align="right" nowrap>
                <span class="form_labels"><?=__('endtime')?></span></td>
				<td><?= ($epm)?"$ehour:$eminute PM":"$ehour:$eminute AM"?></td>
			</tr>
        <?php } ?>
            <tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('category')?></span></td>
                <td><?= $category?></td>
            </tr>
            <tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('repeattype')?></span></td>
                <td><select name="repeattype" onchange="checkrepeattype();">
                    <option value="single"><?=__('singlerepeat')?></option>
                    <option value="daily"><?=__('dailyrepeat')?></option>
                    <option value="weekly"><?=__('weeklyrepeat')?></option>
                    <option value="monthlydate"><?=__('monthlydaterepeat')?></option>
                    <option value="monthonday"><?=__('monthlydayrepeat')?></option>
                    <option value="annual"><?=__('annualrepeat')?></option>
                    </select></td>
            </tr><tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('repeatcount')?></span></td>
                <td><input type="text" name="repeatcount" value="0" size="2" maxlength="2" onchange="checkrepeatcount();" disabled="disabled"> <?=__('Zero = Use date instead')?>
                </td>
            </tr><tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('repeatskip')?></span></td>
                <td><input type="text" name="repeatskip" value="0" size="2" maxlength="2" disabled="disabled"> <?=__('Extra time spans between each repetition.')?>
                </td>
            </tr><tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('repeatcutoff')?></span></td>
                <td>
            <input type="hidden" id="CopyDatePicker" value="<?="{$m}-{$d}-{$y}"?>">
            <?php monthPullDown($m, __('months')); dayPullDown($d); yearPullDown($y); ?></td>
            </tr>
            <tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('make copies related')?></span></td>
                <td><input type="checkbox" name="include_related" value="1"></td>
            </tr>
			<tr><td></td><td><br>
        <input type="submit" name="submit" value="<?= $buttonstr ?>" >&nbsp;
        <input type="reset" name="reset" id="resetbutton" value="<?=__("resetbutton")?>">
        <a id="cancelButton" class="tinybutton" href="eventsubmit.php?cancel=1" title="Cancel"><?= __('cancel') ?></a>
		</table>
		</form>
        <p><a href="help/index.php?n=basic/Creating,+Editing+and+Copying+Events.en.txt"><?=__('help')?></a></p>
    <? if ('ajax' == $_POST['use']) {
        $dialog = ob_get_clean();
        echo json_encode(array(1, $dialog));
        exit(0);
    } ?>

	</body>
	</html>

<?
    }
}
// vim: set tags+=../**/tags :
