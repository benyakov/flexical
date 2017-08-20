<?php
if (! is_numeric($auth)) {
    setMessage(__('subscribers must log in'));
    header("location:login.php");
    exit(0);
}

if ($_POST) { // Called via ajax
    $params = $_POST;
    require("./templates/require/ajax.php");
    exit(0);
}
$uid = $_SESSION[$sprefix]["authdata"]["uid"];
// Show the user's reminder list.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv=Content-Type content=text/html;charset=utf-8>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <link rel=stylesheet href=css/styles-pop.css>
    <?php
    jqueryCDN();
    jqueryuiCDN();
    handlebarsCDN();
    ?>
    <script type="text/javascript" language="JavaScript" src="<?=$installroot?>/templates/remind/javascript.js">
    </script>
</head>
<body>
<?php echo topMatter($action, $sitetabs); ?>

<header>
<span class=add_new_header><?= __('Remind Header') ?></span>
</header>
<form name=remindForm method=post onsubmit=formSubmit();>
<input form=remindForm type=hidden name=uid value="<?=$uid?>" >
</form>
    <table border=0 cellspacing=7 cellpadding=0>
    <thead><tr>
        <th><?= __('Delete') ?></th>
        <th><?= __('Event') ?></th>
        <th><?= __('Type') ?></th>
        <th><?= __('Advance') ?></th>
    </tr></thead>
    <tfoot><tr>
    <td colspan="4"><button form=remindForm type=submit value="delete" id="delete_checked"><?=__('Delete') ?></button>
    <button form=remindForm type=submit value="submit"><?= __('Submit') ?></button></td>
    </tr></tfoot>
    <tbody id="reminder-list">
    </tbody>
    </table>
</body>
</html>
<?php

