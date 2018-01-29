<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

$action = $_SESSION[$sprefix]['action'];
if (array_key_exists('cancel', $_POST)) {
    setMessage(__('operationcancelled'));
    header("Location: {$SDir()}/index.php");
    exit(0);
} elseif (array_key_exists('unfilter', $_POST) || array_key_exists('unfilter', $_GET)) {
    if (! $_SESSION[$sprefix]['filters']) {
        setMessage(__('already unfiltered'));
    } else {
        setMessage(__('filterremoved'));
    }
    $_SESSION[$sprefix]['filters'] = array();
    header("Location: {$SDir()}/index.php");
    exit(0);
} elseif (array_key_exists('filterrelated', $_GET)) {
    $_SESSION[$sprefix]['filters'] = array("related" => $_GET['filterrelated']);
    setMessage(__('showing related events'));
    header("Location: {$SDir()}/index.php?action=eventlist");
    exit(0);
} elseif (getPOST('step') == 2) {
    $filters = array();
    if ($_POST['title']) {
        // Prepare for using LIKE
        //$title = str_replace("\\", "\\\\\\\\", $_POST['title']);
        //$title = str_replace("'", "\\'", $title);
        $filters['title'] = $_POST['title'];
    }
    if ($_POST['text']) {
        // Prepare for REGEXP
        // $text = str_replace("\\", "\\\\\\\\", $_POST['text']);
        // $text = str_replace("'", "\\'", $text);
        $filters['text'] = $_POST['text'];
    }
    if (is_numeric($_POST['start_hour']) &&
        is_numeric($_POST['start_minute'])) {
        $shour = intval($_POST['start_hour']);
        if ($shour < 12 && $_POST['start_am_pm']=='1') {
            $shour = $shour + 12;
        }
        $filters['start_time'] = $shour.":".$_POST['start_minute'].":00";
    }
    if (is_numeric($_POST['end_hour']) &&
        is_numeric($_POST['end_minute'])) {
        $ehour = intval($_POST['end_hour']);
        if ($ehour < 12 && $_POST['end_am_pm']=='1') {
            $ehour = $ehour + 12;
        }
        $filters['end_time'] = $ehour.":".$_POST['end_minute'].":00";
    }
    if ($_POST['all_day']) $filters['all_day'] = 1;

    if ($_POST['weekday']) $filters['weekday'] = $_POST['weekday'];

    if ($filters) {
        setMessage(__('filterset'));
        $_SESSION[$sprefix]['filters'] = $filters;
    } else {
        setMessage(__('emptyfilter'));
    }
    header("Location: {$SDir()}/index.php");
    exit(0);
} else {
    if ('ajax' == $_POST['use'])
        $ajax = true;
    else
        $ajax = false;
    if (isset($_SESSION[$sprefix]['filters'])) {
        $all_day = array_key_exists("all_day", $_SESSION[$sprefix]['filters'])?
            $_SESSION[$sprefix]['filters']['all_day']:false;
        if (array_key_exists('start_time', $_SESSION[$sprefix]['filters'])
            && (! $all_day)) {
            $timepieces = explode(":", $_SESSION[$sprefix]['filters']['start_time']);
            $shour = $timepieces[0];
            $sminute = $timepieces[1];
            if (11 < $shour) {
                $spm = 1;
                $shour = $shour - 12;
            } else {
                $shour = $sminute = $spm = "";
            }
        }
        if (array_key_exists('end_time', $_SESSION[$sprefix]['filters'])
            && (! $all_day)) {
            $timepieces = explode(":", $_SESSION[$sprefix]['filters']['end_time']);
            $ehour = $timepieces[0];
            $eminute = $timepieces[1];
            if (11 < $shour) {
                $epm = 1;
                $ehour = $ehour - 12;
            } else {
                $ehour = $eminute = $epm = "";
            }
        }
        $title = $_SESSION[$sprefix]['filters']['title']?$_SESSION[$sprefix]['filters']['title']:"";
        $text = $_SESSION[$sprefix]['filters']['text']?$_SESSION[$sprefix]['filters']['text']:"";
        $all_day = false;
    } else {
        $title = "";
        $text = "";
        $shour = $sminute = $spm = "";
        $ehour = $eminute = $epm = "";
        $all_day = false;
    }
    $headerstr = __('filtertitle');
    $buttonstr = __('addbutton');
    $pgtitle = __('filtertitle');
    if ($all_day) {
        $adchecked = " checked ";
    } else {
        $adchecked = "";
    }

    if (! $ajax) { ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title><?= $pgtitle ?></title>
        <link rel="stylesheet" type="text/css" href="css/styles-pop.css">

        <script type="text/javascript" language="JavaScript">
        <?php js_zeroTime(); js_revealjsonly(); ?>
        </script>

    </head>
    <body onload="revealjsonly(['start_hour-spinner', 'start_minute-spinner',
                                    'end_hour-spinner', 'end_minute-spinner']);">
<? } if ($ajax) { ob_start();
?> <script type="text/javascript"><?=js_zeroTime()?></script>
<? } ?>

    <p class="helptext"><?= __('regexphelptext') ?></p>
    <span class="add_new_header"><?= $headerstr ?></span>
        <form name="filterForm" id="filterForm" method="POST" action="filter.php">
        <table border=0 cellspacing=7 cellpadding=0>
        <input type="hidden" name="step" value="2">
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
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('All Day')?></span></td>
                <td><input type="checkbox" name="all_day" <?=$adchecked?> value="1" onClick="zeroTime()"></td>
            </tr>
            <tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('starttime')?></span></td>
                <td><?php hourBox($shour, "filterForm", "start_hour", false); ?><b>:</b><?php
                       minuteBox($sminute, "filterForm", "start_minute", false);
                       amPmPullDown($spm, "start", true, false); ?></td>
            </tr>
            <tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('endtime')?></span></td>
                <td colspan=2><?php hourBox($ehour, "filterForm", "end_hour", false); ?><b>:</b><?php
                       minuteBox($eminute, "filterForm", "end_minute", false);
                       amPmPullDown($epm, "end", true, false); ?></td>
            </tr>
            <tr>
                <td nowrap valign="top" align="right">
                <span class="form_labels"><?=__('weekday')?></span></td>
                <td colspan=2><?php
                       weekdayPullDown("", __("days"),
                        [6, 0, 1, 2, 3, 4, 5], "multiple"); ?></td>
            </tr>
            <tr><td></td><td colspan="2"><br>
        <input type="submit" name="submit" value="<?= __('filtersubmitstr') ?>">&nbsp;
        <input type="submit" name="unfilter" value="<?= __('filterremovestr') ?>">&nbsp;
        <input id="cancelButton" type="submit" name="cancel" value="<?= __('cancel') ?>">
        </td></tr>
        </table>
        </form>
        <p><a href='help/index.php?n=filtering'><?= __('help') ?></a></p>
<? if ($ajax) {
    $dialog = ob_get_clean();
    echo json_encode(array(1, $dialog));
    } else { ?>
    </body>
    </html>
<?    }
}
//  vim: set foldmethod=indent :
