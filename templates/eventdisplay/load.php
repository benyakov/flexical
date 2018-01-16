<?php

function writePosting($row, $auth)
{
	global $tablepre, $dbh;
    $_ = "__";

    // Do not write the posting if the item is restricted and the user is not authorized to see it
    if (isset($row["restricted"]) && !$auth) { return; }

	$title = stripslashes($row["title"]);
	//$body = stripslashes(str_replace("\n", "<br />", $row["text"]));
    $body = markdown(stripslashes($row["text"]));
    $cat = $row["category"];
    $fname = $row['fname'];
    $lname = $row['lname'];
    $editstr = "";

	if ($row["all_day"]) {
		$timestr = __('allday');
	} else {
		$timestr = $row["stime"] . " - " . $row["etime"];
	}
    if ($row['current']) {
        $eventclass = "current-event";
        $currentanchor = "<a id=\"current\"/>";
    } else {
        $eventclass = "other-event";
        $currentanchor = "";
    }

	if ( $auth ) {
        $editstr .= "[<a class=\"copyform\" href=\"copyform.php?id={$row['id']}\">copy</a>]&nbsp;"
            ."[<a class=\"eventform\" href=\"eventform.php?id={$row['id']}\">edit</a>]&nbsp;"
            ."[<a href=\"#\" onClick=\"deleteConfirm({$row['id']});\">delete</a>]";
	}

    if (array_key_exists('related', $row)) {
        $relatedstr = "";
       if ($row["related"]) {
           $q = $dbh->prepare("SELECT
           (SELECT `id` FROM `{$tablepre}eventstb`
               WHERE `related` = :related
                AND `id` != :id
                AND `date` > :date
                ORDER BY `date` ASC, `start_time` ASC LIMIT 1),
           (SELECT `id` FROM `{$tablepre}eventstb`
               WHERE `related` = :related
               AND `id` != :id
               AND `date` < :date
               ORDER BY `date` DESC, `start_time` DESC LIMIT 1)");
            $q->bindParam(':related', $row['related']);
            $q->bindParam(':id', $row['id']);
            $q->bindParam(':date', $row['date']);
            $q->execute();
            if ($row = $q->fetch(PDO::FETCH_NUM)) {
                $next_link = $row[0];
                $prev_link = $row[1];
            }
            $relatedstr = "";
            if ($prev_link) {
                $relatedstr .= "[<a class=\"related-link\" href=\"index.php?action=eventdisplay&id={$prev_link}\">{$_('previous related')}</a>] &lt;";
            }
            if ($next_link) {
                $relatedstr .= "&gt; [<a class=\"related-link\" href=\"index.php?action=eventdisplay&id={$next_link}\">{$_('next related')}</a>]";
            }
       }
    } else {
        $relatedstr = "";
    }

?>  <?=$currentanchor?>
    <div class="<?=$eventclass?>">
        <div class="title_bar">
            <div class="display_title"><?= $title ?></div>
            <div class="display_time"><?= $timestr ?></div>
        </div>
        <div class="display_area">
            <?php if ($relatedstr) { ?>
            <div class="display_related"><?= $relatedstr ?></div>
            <?php } ?>
            <?= $body ?>
            <div class="display_user"><?="{$_('postedby')}: {$fname} {$lname}"?></div>
            <div class="display_category"><?="Category: "?><span class="<?=toCSSID($cat)?>"><?=$cat?></span></div>
            <div class="display_edit"><?= $editstr ?></div>
        </div>
    </div>
<?php
}
// vim: set tags+=../../../**/tags :

?>
