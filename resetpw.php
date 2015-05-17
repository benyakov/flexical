<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require("./utility/initialize-entrypoint.php");

$__='__';
$action = $_GET['action'];
$view = $_GET['view']? $_GET['view'] : $_SESSION[$sprefix]['action'];
$d = $_GET['day']? $_GET['day'] : $_SESSION[$sprefix]['day'];
$m = $_GET['month']? $_GET['month'] : $_SESSION[$sprefix]['month'];
$y = $_GET['year']? $_GET['year'] : $_SESSION[$sprefix]['year'];
$l = $_GET['length']? $_GET['length'] : $_SESSION[$sprefix]['length'];
$u = $_GET['unit']? $_GET['unit'] : $_SESSION[$sprefix]['unit'];
$serverdir = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

if ( $_POST ) {
    if ( array_key_exists('cancel', $_POST) ) {
        header("Location: http://{$serverdir}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u");
        exit(0);
    }
    $where = "";
    if ($_POST['username']) {
        $svalue = $_POST['username'];
        $where .= "`username` = :value"; }
    if ($_POST['email']) {
        if ($where) { $where .= " AND "; }
        $svalue = $_POST['email'];
        $where .= "`email` = :value";
    }
    $q = $dbh->prepare("SELECT `email`, `username`
        FROM `{$tablepre}users` WHERE {$where}");
    $q->bindParam(':value', $svalue);
    $q->execute();
    if (0 == $q->rowCount()) {
        setMessage(__("no users found"));
        header("Location: http://{$serverdir}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u");
        exit(0);
    }
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $resetkey = md5($row['username'].date('%c').$row['email']);
        $q1 = $dbh->prepare("UPDATE `{$tablepre}users`
            SET `resetkey` = '{$resetkey}',
            `resetexpiry` = DATE_ADD(NOW(),INTERVAL 6 DAY)
            WHERE {$where}");
        $q1->bindParam(':value', $svalue);
        $q1->execute() or die(array_pop($q->errorInfo()));
        $resetkey = urlencode($resetkey);
        $mailresult = mail($to=$row['email'], $subject=$__('pwresetsubject'),
            $additional_headers="From: {$configuration['email_from_address']}",
            $message=$__('pwresetmessage').
            "\n\nTo reset the password for {$row['username']}, use this link:\n".
            "http://{$serverdir}/useradmin.php?flag=reset&auth={$resetkey}");
        if (! $mailresult)
            die("Problem sending password reset email to {$row['email']}");
        setMessage(__("password reset sent"));
        header("Location: http://{$serverdir}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u");
    }
} else {
?>
	<html>
	<head>
	<link rel="stylesheet" type="text/css" href="css/adminpgs.css">
    </head>
    <body>
	<h1><?=$__('sendpwtitle')?></h1>

    <p class="helptext"><?=$__('sendpwinstruction')?></p>

	<table>
	<form action="<?= $_SERVER['PHP_SELF'] ?>?action=sendpw" method="post">
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="login_label"><?=$__('username')?></span></td>
				<td><input type="text" name="username" maxlength="15"></td>
			</tr>
			<tr>
				<td nowrap valign="top" align="right" nowrap>
				<span class="login_label"><?=$__('email')?></span></td>
				<td><input type="text" name="email"></td>
			</tr>
            <tr><td colspan="2" align="right"><input type="submit" name="submit" value="<?=$__('find and send')?>"><td>
                <input type="submit" name="cancel" value="<?=$__('cancel')?>"><tr>
	</form>
	</table>
	</body></html>
<?php
}
// vim: set tags+=../../**/tags :
