<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

$action = $_GET['action'];
/* Have to use "view" as a get_var, because "action" is login/logout. */
$view = $_GET['view']? $_GET['view'] : $_SESSION[$sprefix]['action'];
$d = $_GET['day']? $_GET['day'] : $_SESSION[$sprefix]['day'];
$m = $_GET['month']? $_GET['month'] : $_SESSION[$sprefix]['month'];
$y = $_GET['year']? $_GET['year'] : $_SESSION[$sprefix]['year'];
$l = $_GET['length']? $_GET['length'] : $_SESSION[$sprefix]['length'];
$u = $_GET['unit']? $_GET['unit'] : $_SESSION[$sprefix]['unit'];
$id = $_GET['id'];

if ( $action == "login" ) {
    if (false !== ($auth = auth($_POST['username'], $_POST['password']))) {
        setMessage(__("logged in-$auth"));
        if ($_SESSION[$sprefix]["destination"]) {
            header("Location: {$_SESSION[$sprefix]["destination"]}");
            unset($_SESSION[$sprefix]["destination"]);
        } else {
            header("Location: {$SDir()}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u&id=$id");
        }
    } else {
        setMessage(__("wronglogin"));
        header ("Location: {$SDir()}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u&id=$id");
    }
} elseif ($action == "logout") {
    $ak = new AuthKeeper();
    $ak->logout();
    session_destroy();
    require('./utility/setup-session.php');
    setMessage(__('logged out'));
	header("Location: {$SDir()}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u&id=$id");

} elseif ($action == "loginform") {
?>
    <!DOCTYPE html>
    <html lang="<?=$language?>">
	<head>
	<title><?=__('logintitle')?></title>
	<link rel="stylesheet" type="text/css" href="css/popwin.css">
	</head>
	<body>

    <?php showMessage();?>

<?php
	if( isset( $_POST['username'] ) ) {
		echo "<span class=\"login_auth_fail\">" . __('wronglogin') . "</span><p>\n";
	}
?>
	<span class="login_header"><?=__('loginheader')?></span>

	<table>
    <form action="<?= $_SERVER['PHP_SELF'] ?>?action=login&view=<?=$view?>&month=<?=$m?>&day=<?=$d?>&year=<?=$y?>&length=<?=$l?>&unit=<?=$u?>&id=<?=$id?>" method="post">
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="login_label"><?=__('username')?></span></td>
				<td><input type="text" name="username" size="29" maxlength="15"></td>
			</tr>
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="login_label"><?=__('password')?></span></td>
				<td><input type="password" name="password" size="29" maxlength="15"></td>
			</tr>
			<tr><td colspan="2" align="right"><input type="submit" value="<?=__('login')?>"><td><tr>
	</form>
	</table>

    <p><a title="<?=__('forgot password')?>" href="resetpw.php"><?=__('forgot password')?></a></p>

	</body></html>
<?php

}
// vim: set tags+=../**/tags :
