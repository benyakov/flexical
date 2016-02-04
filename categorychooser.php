<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

if (!(array_key_exists("step", $_POST) && $_POST['step'] == '2')) {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= __('choosecategorytitle') ?></title>
    <?php
    jqueryCDN();
    javaScript();
    ?>
    <link rel="stylesheet" type="text/css" href="css/styles-pop.css">
    <link rel="stylesheet" type="text/css" href="css/categorystyles.css">

</head>
<body>
    <span class="add_new_header"><?= __('categoryheader') ?></span>
    <table border = 0 cellspacing=7 cellpadding=0>
    <form name="categoryForm" method="POST" action="categorychooser.php">
    <input type="hidden" name="step" value="2"/>
    <?= categoryCheckBoxes(array(__('show')), 1) ?>
    <tr><td colspan=2>
    <input type="submit" name="submit" value="<?= __('categorybutton') ?>">
    &nbsp;
    <input type="submit" name="cancel" value="<?= __('cancel') ?>"></td></tr>
    </form></table>
    <p><a href="help/index.php?n=Categories.<?=$configuration['language']?>.txt"><?=__('help')?></a></p>
</body>
</html>
<?php
} else {
    unset($_POST['step']);
    $showstr = __('show');
    if (array_key_exists('cancel', $_POST)) {
        header("Location: {$SDir()}/index.php");
        exit(0);
    }

    $q = $dbh->query("SELECT `name` FROM `{$tablepre}categories`");
    $showboxes = preg_replace("/-{$showstr}/", "", array_keys($_POST));
    $categories = array();
    while ($row = $q->fetch()) {
        if (in_array(toCSSID($row[0]), $showboxes)) {
            array_push($categories, $row[0]);
        }
    }
    $_SESSION[$sprefix]['categories'] = $categories;

    header("Location: {$SDir()}/index.php");
}
// vim: set tags+=../../**/tags :
