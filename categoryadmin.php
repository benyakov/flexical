<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require('./utility/initialize-entrypoint.php');

$_ = "__";

$authdata = $_SESSION[$sprefix]['authdata'];
$auth = auth();

if ( $auth < 2 ) {
    setMessage(__('accessdenied'));
    header("Location: {$SDir()}/index.php");
    exit(0);
}

if (array_key_exists("cancel", $_POST)) {
    if (array_key_exists('categorystyles', $_SESSION[$sprefix])) {
        unset($_SESSION[$sprefix]['categorystyles']);
    }
    header("Location: {$SDir()}/index.php");
    exit(0);
} elseif (! (array_key_exists("submit", $_POST) || array_key_exists("save", $_POST))) {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= __('admincategorytitle') ?></title>
    <?php
    jqueryCDN();
    javaScript();
    ?>
    <link rel="stylesheet" type="text/css" href="css/styles-pop.css">
    <link rel="stylesheet" type="text/css" href="css/categorystyles.css">
</head>
<body>
    <?php showMessage();?>

    <span class="add_new_header"><?= __('admincategoryheader') ?></span>
    <p><?= __('delcategorynote')?></p>
    <p><?= __('hidecategorynote')?></p>
    <p><?= __('categorystylenote')?> <a href="http://www.w3.org/TR/CSS2/colors.html" title="Setting colors">Colors</a>
    <a href="http://www.w3.org/TR/CSS2/fonts.html" title="Setting fonts">Fonts</a></p>
    <table border = 0 cellspacing=7 cellpadding=0>
    <form name="categoryForm" method="POST" action="<?=$_SERVER['PHP_SELF']?>">
    <?= categoryCheckBoxes(array(__("delete"), __("hide"), __("suppress key listing")), $checkCurrent=0,
        $checkHidden=2, $checkKeySuppressed=3, $renameBoxes=True, $styleBoxes=True) ?>
    <tr><td><input type="checkbox" name="confirm"></td>
        <td colspan=4><?=__('confirmdelete')?></td></tr>
    <tr><td colspan=4>
    <input type="submit" name="submit" value="<?= __('submitbutton') ?>">
    &nbsp;
    <input type="submit" name="save" value="<?= __('savebutton') ?>">
    &nbsp;
    <input type="submit" name="cancel" value="<?= __('cancel') ?>">
    </td></tr>
    </form></table>
    <p><a href="help/index.php?n=Categories.<?=$configuration['language']?>.txt"><?=__('help')?></a></p>
</body>
</html>

<?php
} else { // Process the form
    $d = $_SESSION[$sprefix]['day'];
    $m = $_SESSION[$sprefix]['month'];
    $y = $_SESSION[$sprefix]['year'];
    $l = $_SESSION[$sprefix]['length'];
    $u = $_SESSION[$sprefix]['unit'];
    $action = $_SESSION[$sprefix]['action'];
    $deletestr = toCSSID(__('delete'));
    $hidestr = toCSSID(__('hide'));
    $suppressstr = toCSSID(__('suppress key listing'));
    if (array_key_exists('save', $_POST)) {
        $destination = $_SERVER['PHP_SELF'];
    } elseif (array_key_exists('submit', $_POST)) {
        $destination = "{$SDir()}/index.php";
    }
    // Get lists for each checkbox type and for new category names.
    // Also, make strings to report what happened later and convert
    // the category names to db-style category names.
    $deleteboxes = preg_grep("/{$deletestr}/", array_keys($_POST, 'on'));
    foreach ($deleteboxes as $rmbox) {
        unset($_POST[$rmbox]);
    }
    $deleteboxes = preg_replace("/-{$deletestr}/", "", $deleteboxes);
    $deleteboxes = preg_replace("/-{1}/", " ", $deleteboxes);
    $deleteboxes = preg_replace("/--/", "-", $deleteboxes);
    $deleteboxes = array_map($dbh->quote, $deleteboxes);
    $delreportlist = implode(", ", $deleteboxes);
    $hideboxes = preg_grep("/{$hidestr}/", array_keys($_POST, 'on'));
    foreach ($hideboxes as $rmbox) { unset($_POST[$rmbox]); }
    $hideboxes = preg_replace("/-".$hidestr."/", "", $hideboxes);
    $hideboxes = preg_replace("/-{1}/", " ", $hideboxes);
    $hideboxes = array_map($dbh->quote, $hideboxes);
    $hidereportlist = implode(", ", $hideboxes);
    $suppressboxes = preg_grep("/{$suppressstr}/", array_keys($_POST, 'on'));
    foreach ($suppressboxes as $rmbox) { unset($_POST[$rmbox]); }
    $suppressboxes = preg_replace("/-".$suppressstr."/", "", $suppressboxes);
    $suppressboxes = preg_replace("/-{1}/", " ", $suppressboxes);
    $suppressboxes = preg_replace("/--/", "-", $suppressboxes);
    $suppressboxes = array_map($dbh->quote, $suppressboxes);
    $suppressreportlist = implode(", ", $suppressboxes);
    unset($_POST["checkall"]);
    unset($_POST["submit"]);

    // Continue only if confirmation box was checked, or no delete boxes
    if (array_key_exists('confirm', $_POST) and $_POST['confirm'] == 'on') {
        unset($_POST['submit']);
        unset($_POST['confirm']);
    } else {
        if ($deleteboxes) {
            setMessage(__('abortnoconfirm'));
            header("Location: {$destination}");
            exit(0);
        } else {
            unset($_POST['submit']);
        }
    }

    $dbh->beginTransaction();
    // Hide categories that need hiding.
    $inclause = "'" . implode("', '", $hideboxes) . "'";
    if ($hideboxes) {
        // Get category ids
        $dbh->exec("UPDATE `{$tablepre}categories`
            SET `restricted` = '1'
            WHERE `name` IN ({$inclause})");
    }
    // Unhide categories marked unhid
    $dbh->exec("UPDATE `{$tablepre}categories`
        SET `restricted` = '0'
        WHERE `name` NOT IN ({$inclause})");
    if ($hidereportlist) {
        $reportstr .= "{$_('categorieshidden')} {$hidereportlist}\n" ;
    }
    // Set Category List suppression, first the ones suppressed
    $inclause = "'" . implode("', '", $suppressboxes) . "'";
    $dbh->exec("UPDATE `{$tablepre}categories`
        SET `suppresskey` = '1'
        WHERE `name` IN ({$inclause})");
    $dbh->exec("UPDATE `{$tablepre}categories`
        SET `suppresskey` = '0'
        WHERE `name` NOT IN ({$inclause})");
    if ($suppressreportlist) {
        $reportstr .= "{$_('categoriessuppressed')} {$suppressreportlist}\n";
    }
    // Rename categories, set changed styles
    $catnamesquery = $dbh->query("SELECT `name` FROM `{$tablepre}categories`");
    $renamed = array();
    $restyled = array();
    while ($row = $catnamesquery->fetch()) {
        $renamefield = toCSSID($row[0])."-name";
        $stylefield = toCSSID($row[0])."-style";
        if ($_POST[$renamefield]) {
            // Rename category
            $q = $dbh->prepare("UPDATE `{$tablepre}categories`
                    SET `name` = :newname
                    WHERE `name` = :searchname");
            $q->bindParam(':newname', $_POST[$renamefield]);
            $q->bindParam(':searchname', $row[0]);
            $q->execute();
            array_push($renamed, $row[0]);
        }
        if (array_key_exists('categorystyles', $_SESSION[$sprefix])) {
            if ($_POST[$stylefield] != $_SESSION[$sprefix]['categorystyles'][$row[0]]) {
                // Restyle category
                $q = $dbh->prepare("UPDATE `{$tablepre}categories`
                        SET `style` = :stylefield
                        WHERE `name` = :name");
                $q->bindParam(':stylefield', $_POST[$stylefield]);
                $q->bindParam(':name', $row[0]);
                $q->execute();
                array_push($restyled, $row[0]);
            }
        }
    }
    // Delete categories
    if ($deleteboxes) {
        //   Get category ids
        $inclause = "'" . implode("', '", $deleteboxes) . "'";
        $q = $dbh->query("SELECT `category` FROM `{$tablepre}categories`
                WHERE `name` IN ({$inclause})");
        $deletecats = array();
        while ($row = $q->fetch()) {
            array_push($deletecats, $row[0]);
        }
        //   Delete event entries
        $inclause = implode(", ", $deletecats);
        $dbh->exec("DELETE FROM `{$tablepre}eventstb`
                WHERE `category` IN ({$inclause})");
        //   Delete category entries
        $dbh->exec("DELETE FROM `{$tablepre}categories`
                WHERE `category` IN ({$inclause})");
        $reportstr .= "{$_('categoriesdeleted')} {$delreportlist}\n";
    }
    $dbh->commit();
    if (array_key_exists('categorystyles', $_SESSION[$sprefix])) {
        unset($_SESSION[$sprefix]['categorystyles']);
    }
    if ($renamed) {
        $reportstr .= "{$_('categoriesrenamed')} " .
            implode(", ", $renamed) . "\n";
    }
    if ($restyled) {
        $reportstr .= "{$_('categoriesrestyled')} " .
            implode(", ", $restyled) . "\n";
    }

    if ($reportstr) { setMessage($reportstr); }
    refreshcss($dbh, $tablepre);
    header("Location: {$destination}");
}
// vim: set foldmethod=indent :
