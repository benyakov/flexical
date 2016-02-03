<?php
$installroot = dirname($_SERVER['SCRIPT_NAME']);
$includeroot = dirname(__FILE__);
require('./utility/initialize-entrypoint.php');

$flag = $_GET['flag'];
$authdata = getIndexOr($_SESSION[$sprefix],'authdata', array());
$auth = auth();
$authtype = getIndexOr($authdata, "authtype");
$_ = '__';

if ( $auth == 3 && $authtype!="cookie") {
    if ( $flag=="edit" ) {

        $id = $_GET['id'];

        $q = $dbh->prepare("SELECT * FROM `{$dbh->getPrefix()}users` WHERE `uid`=:id");
        $q->bindParam(":id", $id);
        $q->execute();

        $row = $q->fetch(PDO::FETCH_ASSOC);

        editUserForm($row, "Edit");

    } elseif ( $flag=="update" ) {

        if ($_POST['pw'] == __('no change'))
            { $pwstr = ''; }
        else
            { $pwstr = "`password`='".hashPassword($_POST['pw'])."',"; }

        $q = $dbh->prepare("UPDATE `{$dbh->getPrefix()}users` SET {$pwstr}
            `fname`=:fname, `lname`=:lname, `userlevel`=:ulevel,
            `email`=:email, `timezone`=:timezone
            WHERE `username`=:uname");
        $q->bindParam(':fname', $_POST['fname']);
        $q->bindParam(':lname', $_POST['lname']);
        $q->bindParam(':ulevel', $_POST['userlevel']);
        $q->bindParam(':email', $_POST['email']);
        $q->bindParam(':uname', $_POST['username']);
        $q->bindParam(':timezone', $_POST['timezone']);
        $q->execute() or die(array_pop($q->errorInfo()));

        if ( $_POST['username']==$authdata['login'] ) {
            $_SESSION[$sprefix]['authdata']['password'] = $pw;
        }

        header("location:useradmin.php");

    } elseif ( $flag=="delete" ) {
        $id = $_GET['id'];
        if ($authdata['uid'] != $id) {
            $q = $dbh->prepare("DELETE FROM `{$dbh->getPrefix()}users`
                WHERE `uid`=:id");
            $q->bindParam(':id', $id);
            $q->execute();
        }
        header("location:useradmin.php");

    } elseif ($flag == "checkuser") {
        // Check for existing user name
        $qu = $dbh->prepare("SELECT * FROM `{$dbh->getPrefix()}users`
            WHERE `username`=:uname");
        $qu->bindParam(":uname", $_GET['username']);
        $qu->execute();
        if ( $qu->rowCount() ) {
            echo json_encode(false);
        } else {
            echo json_encode(true);
        }
        exit(0);
    } elseif ($flag == "checkemail") {
        // Check for existing email address
        $qe = $dbh->prepare("SELECT * FROM `{$dbh->getPrefix()}users`
            WHERE `email`=:email");
        $qe->bindParam(":email", $_GET['email']);
        $qe->execute();
        if ( $qe->rowCount() ) {
            echo json_encode(false);
        } else {
            echo json_encode(true);
        }
        exit(0);
    } elseif ( $flag=="add" ) {
        editUserForm();
    } elseif ( $flag=="insert" ) {
        insertFromPost($dbh);
    } else {
        userList();
    }

} elseif ( $flag=="insert" ) {
    insertFromPost($dbh);
} elseif ( $flag=="add" && $authtype != "cookie") {
    editUserForm();
} elseif ( $auth > 0 && $authtype != "cookie") {
    if ( $flag=="changepw" ) {
        changePW($dbh);
    } elseif ( $flag=="updatepw" ) {
        $un = $_POST['un'];
        $pw = hashPassword($_POST['pw']);
        $id = $_POST['id'];
        $q = $dbh->prepare("UPDATE `{$dbh->getPrefix()}users` SET `password`='$pw'
            WHERE `uid`=:id");
        $q->bindParam(':id', $id);
        $q->execute();
        $_SESSION[$sprefix]['authdata']['password'] = $pw;
        setMessage(__('pwchanged').$authdata['login']);
        header("Location: {$SDir()}index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u");
        exit(0);
    } else {
        header("location:index.php");
    }
} else {
    if ( $flag=="inituser") {
        $pw = hashPassword($_POST['pw']);
        $q = $dbh->prepare("INSERT INTO `{$dbh->getPrefix()}users`
            SET `username`=:username, `password`='{$pw}',
            `fname`=:fname, `lname`=:lname, `timezone`=:timezone,
            `userlevel`=:ulevel, `email`=:email");
        $q->bindParam(':username', $_POST['username']);
        $q->bindParam(':fname', $_POST['fname']);
        $q->bindParam(':lname', $_POST['lname']);
        $q->bindParam(':timezone', $_POST['timezone']);
        $q->bindParam(':ulevel', $_POST['ulevel']);
        $q->bindParam(':email', $_POST['email']);
        $q->execute() or die(array_pop($q->errorInfo()));
        session_destroy();
        require("./utility/setup-session.php");
        if (! auth($_POST['username'], $_POST['pw']))
            die("Saved credentials, but could not authenticate.");
        saveConfig(array("hasuser"=>1));
        header("Location: {$SDir()}/index.php");
    } elseif ($flag=="reset" && array_key_exists('auth', $_GET)) {
        changePW($dbh, $_GET['auth']);
    } elseif ($flag=="updatepw" && array_key_exists('auth', $_POST)) {
        $pw = hashPassword($_POST['pw']);
        $q = $dbh->prepare("UPDATE `{$dbh->getPrefix()}users` SET `password`='$pw',
            `resetkey`=DEFAULT
            WHERE `resetkey`=:resetkey AND `resetexpiry` >= NOW()");
        $q->bindParam(':resetkey', $_POST['auth']);
        if ($q->execute()) {
            setMessage(__('pwchanged'));
        } else {
            setMessage(__('problem changing password'));
        }
        header("Location: {$SDir()}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u");
        exit(0);

    } else {
        if ($authtype == "cookie") {
            setMessage(__('accessdenied-cookie'));
            $_SESSION[$sprefix]["destination"] = $_SERVER["REQUEST_URI"].
                $_SERVER["PATH_INFO"].$_SERVER["REQUEST_METHOD"];
            header("Location: {$SDir()}/login.php?action=loginform");
        } else {
            setMessage(__('accessdenied'));
            header("Location: {$SDir()}/index.php?action=$view&day=$d&month=$m&year=$y&length=$l&unit=$u");
        }
    }
}

/***************************************
******** user admin functions **********
***************************************/

function changePW($dbh, $authcode="") {
    global $SDir;
    if ($authcode) { // password reset request
        $q = $dbh->prepare("SELECT `uid`, `username` FROM `{$dbh->getPrefix()}users`
            WHERE `resetkey` = :resetkey
            AND `resetexpiry` >= NOW() LIMIT 1");
        $q->bindParam(':resetkey', $_GET['auth']);
        $q->execute();
        if ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $username = $row['username'];
            $id = $row['uid'];
        } else {
            setMessage(__("invalid or expired reset auth"));
            header("Location: {$SDir()}/index.php");
        }
    } else {
        $username = $_SESSION[$sprefix]['authdata']['login'];
        $id = $_SESSION[$sprefix]['authdata']['uid'];
    }
?>
<!DOCTYPE html>
<html lang="en"><head>
    <title><?=__('changepw')?></title>
    <link rel="stylesheet" type="text/css" href="css/adminpgs.css">
<?php require("./utility/passwordvalidate.php")?>
    </head></body>
    <form onSubmit="return validate(this, 'updatepw');">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="un" value="<?= $username ?>">
    <input type="hidden" name="auth" value="<?= $authcode ?>">
    <table cellpadding="2" cellspacing="2" border="0">
    <tr>
        <td colspan="2" class="user-edit-header"><span class="edit_user_header"><?=__('chpassheader')?></span></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('username')?>:</span></td>
        <td><span class="edit_user_label"><?=$username?></span></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('password')?>:</span></td>
        <td><input type="password" name="pw" size="29" maxlength="25" required value=""></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('pwconfirm')?>:</span></td>
        <td><input type="password" name="pwconfirm" size="29" maxlength="25" required value=""></td>
    </tr>
    <tr>
        <td colspan="2" align="right"><input type="submit" value="<?=__('changepw')?>">
        &nbsp;  <input type="button" value="<?=__('cancel')?>" onClick="location.replace('index.php');">
        </td>
    </tr>
    </table>
    </form>

    </body>
</html>
<?php
}

function editUserForm($elementValues="", $mode="Add") {
    global $authdata, $configuration;

    if ($mode=="Edit") {

        $username = $elementValues['username'];
        $password = "";
        $fname = $elementValues['fname'];
        $lname = $elementValues['lname'];
        $userlevel = $elementValues['userlevel'];
        $email = $elementValues['email'];
        $timezone = $elementValues['timezone'];

        $header = __('edituser');

        $userlevel_selected = array(
            0 => ($userlevel == 0) ? "selected" : "",
            1 => ($userlevel == 1) ? "selected" : "",
            2 => ($userlevel == 2) ? "selected" : "",
            3 => ($userlevel == 3) ? "selected" : "");

        $formaction = "f.action = \"useradmin.php?flag=update\";";
        $unameinput = "<span class=\"edit_user_label\">{$username}</span><input type=\"hidden\" name=\"username\" value=\"{$username}\">\n";

        if ($username == $authdata['login']) { $editorstr=$userstr = ""; }

    } else {

        $username=$password=$fname=$lname=$userlevel=$email="";
        $timezone=$configuration['default_timezone'];
        $header = __('adduser');
        $formaction = "f.action = \"useradmin.php?flag=insert\";";
        $unameinput = "<input id=\"username\" type=\"text\" name=\"username\" size=\"29\" maxlength=\"20\" value=\"\">";
        $userlevel_selected = array(0 => "", 1 => "", 2 => "", 3 => "");

    }
?>
<!DOCTYPE html>
<html lang="en"><head>
    <title>Flexical:  <?=$mode?> Calendar User</title>
    <link rel="stylesheet" type="text/css" href="css/adminpgs.css">
    <?php jqueryCDN(); ?>
    <script language="JavaScript">
        function checkusername(item) {
            $.get("useradmin.php",
            {'flag': "checkuser", 'username': $(item).val()},
            function(rv) {
                if (eval(rv)) {
                    $(item).addClass('exists');
                } else {
                    $(item).removeClass('exists');
                }
            });
        }
        function checkemail(item) {
            $.get("useradmin.php",
            {'flag': "checkemail", 'email': $(item).val()},
            function(rv) {
                if (eval(rv)) {
                    $(item).addClass('exists');
                } else {
                    $(item).removeClass('exists');
                }
            });
        }
        $(document).ready(function() {
            $("#username").keyup(function() {
                $(this).doTimeout('checkusername', 500, function() {
                    checkusername(this);
                });
            });
            $("#email").keyup(function() {
                $(this).doTimeout('checkemail', 500, function() {
                    checkemail(this);
                });
            });
        });
        function validate(f) {
            var regex = /\W+/;
            var un = f.username.value;
            var pw = f.pw.value;

            var str = "";
            if (f.fname.value == "") { str += "\n<?=__('fnameblank')?>"; }
            if (f.lname.value == "") { str += "\n<?=__('lnameblank')?>"; }
            if (f.email.value == "") { str += "\n<?=__('emailblank')?>"; }
            if (un == "") { str += "\n<?=__('unameblank')?>"; }
            if (un.length < 4) { str += "\n<?=__('unamelength')?>"; }
            if (regex.test(un)) { str += "\n<?=__('unameillegal')?>"; }
            if (pw != "<?=__('no change')?>") {
                if (pw == "") { str += "\n<?=__('pwblank')?>"; }
                if (pw != f.pwconfirm.value) { str += "\n<?=__('pwmatch')?>"; }
                if (pw.length < 4) { str += "\n<?=__('pwlength')?>"; }
                if (regex.test(pw)) { str += "\n<?=__('pwchars')?>"; }
            }

            if (str == "") {
                <?= $formaction ?>
                f.submit();
            } else {
                alert(str);
                return false;
            }
        }

    </script>
    </head><body>

<?php
    if ( !empty($unameerror) ) {
        echo "<p><span class=\"bad_user_name\">" . __('userinuse') . "</span></p>";
    }
    if ( !empty($emailerror) ) {
        echo "<p><span class=\"bad_user_name\">" . __('emailinuse') . "</span></p>";
    }
?>
    <form method="post" onSubmit="return validate(this);">
    <table cellpadding="2" cellspacing="2" border="0">
    <tr>
        <td colspan="2" class="user-edit-header"><span class="edit_user_header"><?=$header?>:</span></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('username')?>:</span></td>
        <td><?=$unameinput?></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('password')?>:</span></td>
        <td><input type="password" name="pw" size="29" maxlength="20" required value="<?=($mode=="Add")?"":__('no change')?>"></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('pwconfirm')?>:</span></td>
        <td><input type="password" name="pwconfirm" size="29" required maxlength="20" value="<?=($mode=="Add")?"":__('no change')?>"></td>
    </tr>
    <?php if (getIndexOr($authdata,'userlevel') == 3) { ?>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('userlevel')?>:</span></td>
        <td><select name="userlevel">
            <option value="0" <?=$userlevel_selected[0]?>>
                <?=__('subscriberoption')?></option>
            <option value="1" <?=$userlevel_selected[1]?>>
                <?=__('useroption')?></option>
            <option value="2" <?=$userlevel_selected[2]?>>
                <?=__('editoroption')?></option>
            <option value="3" <?=$userlevel_selected[3]?>>
                <?=__('adminoption')?></option>
            </select>
        </td>
    </tr>
    <?php } else { ?> <input type="hidden" name="userlevel" value="0"> <?php } ?>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('fname')?>:</span></td>
        <td><input type="text" name="fname" size="29" maxlength="20" required value="<?=$fname?>"></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('lname')?>:</span></td>
        <td><input disable type="text" name="lname" size="29" required maxlength="30" value="<?=$lname?>"></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('email')?>:</span></td>
        <td><input type="text" name="email" size="29" maxlength="40" required value="<?=$email?>"></td>
    </tr>
    <tr>
        <td align="right"><span class="edit_user_label"><?=__('timezone')?>:</span></td>
        <td><?=timezoneDropDown("timezone", $timezone)?></td>
    </tr>
    <tr>
        <td colspan="2" align="right"><input type="submit" value="<?=$mode?> User">
        &nbsp;  <input type="button" value="cancel" onClick="location.replace('useradmin.php');">
        </td>
    </tr>
    </table>
    </form>

</body></html>
<?php
}


function userList() {
    global $sprefix;
    $authdata = getIndexOr($_SESSION[$sprefix],'authdata');
?>
<!DOCTYPE html>
<html lang="en"><head><title>Calendar User List</title>
    <link rel="stylesheet" type="text/css" href="css/adminpgs.css">

    <script language="JavaScript">
        function deleteConfirm(user, uid) {
            var msg = "<?=__('deleteconf')?>: \"" + user + "\"?";

            if (user == "<?= $authdata['login'] ?>") {
                alert("<?=__('deleteown')?>");
                return;
            } else if (confirm(msg)) {
                location.replace("useradmin.php?flag=delete&id=" + uid);
            } else {
                return;
            }
        }
    </script>
    </head>

    <body>
    <table cellpadding="0" cellspacing="0" border="0" width="600">
    <tr>
        <td class="user-edit-header"><span class="edit_user_header"><?=__('ulistheader')?></span></td>
        <td align="right" valign="bottom"><span class="user_list_options">[ <a href="useradmin.php?flag=add"><?=__('adduser')?></a> | <a href="index.php"><?=__('return')?></a> ]</span></td>
    </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" width="600" bgcolor="#000000">
    <tr><td>

    <table cellspacing="1" cellpadding="3" border="0" width="100%">
    <tr bgcolor="#666666">
        <td><span class="user_table_col_label"><?=__('username')?></span></td>
        <td><span class="user_table_col_label"><?=__('name')?></span></td>
        <td><span class="user_table_col_label"><?=__('email')?></span></td>
        <td><span class="user_table_col_label"><?=__('userlevel')?></span></td>
        <td><span class="user_table_col_label"><?=__('timezone')?></span></td>
        <td><span class="user_table_col_label"><?=__('edit')?></span></td>
        <td><span class="user_table_col_label"><?=__('delete')?></span></td>
    </tr>

<?php
    $dbh = new DBConnection();
    $q = $dbh->query("SELECT uid, username, fname, lname,
        userlevel, email, timezone FROM `{$dbh->getPrefix()}users`");

    $bgcolor = "#ffffff";
    while( $row = $q->fetch(PDO::FETCH_ASSOC) ) {
        echo "<tr bgcolor=\"$bgcolor\">\n";
        echo "  <td><span class=\"user_table_txt\">{$row['username']}</td>\n";
        echo "  <td><span class=\"user_table_txt\">{$row['fname']} {$row['lname']}</span></td>\n";
        echo "  <td><span class=\"user_table_txt\">{$row['email']}</span></td>\n";
        echo "  <td><span class=\"user_table_txt\">{$row['userlevel']}</span></td>\n";
        echo "  <td><span class=\"user_table_txt\">{$row['timezone']}</span></td>\n";
        echo "  <td><span class=\"user_table_txt\"><a href=\"useradmin.php?flag=edit&id=" . $row["uid"] . "\">" . __('edit') . "</a></span></td>\n";
        echo "  <td><span class=\"user_table_txt\"><a href=\"#\" onClick=\"deleteConfirm('{$row['fname']} {$row['lname']} ({$row['username']})', '{$row["uid"]}');\">".__('delete')."</a></span></td>\n";
        echo "</tr>\n";

    if ( $bgcolor == "#ffffff" )
        $bgcolor = "#dddddd";
    else
        $bgcolor = "#ffffff";
    }

    echo "</table></td></tr></table></body></html>";
}

function insertFromPost($dbh) {
    /* Insert a user record from $_POST, if authorized */

    $auth = auth();
    $authtype = $_SESSION[$sprefix]["authdata"]["authtype"];
    $ulevel = intval($_POST['userlevel']);
    if (($ulevel > 0 && $auth < 3) || $authtype=="cookie") {
        setMessage(__('accesswarning'));
        header("location:index.php");
        exit(0);
    }
    $pw = hashPassword($_POST['pw']);
    $dbh->beginTransaction();
    $q = $dbh->prepare("INSERT INTO {$dbh->getPrefix()}users
        SET `username`=:uname, `password`=:pw, `fname`=:fname,
        `lname`=:lname, `userlevel`=:ulevel, `email`=:email,
        `timezone`=:timezone");
    $q->bindParam(":uname", $_POST['username']);
    $q->bindParam(":pw", $pw);
    $q->bindParam(":fname", $_POST['fname']);
    $q->bindParam(":lname", $_POST['lname']);
    $q->bindParam(":ulevel", $ulevel);
    $q->bindParam(":email", $_POST['email']);
    $q->bindParam(":timezone", $_POST['timezone']);
    $q->execute() or die(array_pop($q->errorInfo()));
    if ($ulevel == 0) {
        setMessage(__('account created'));
        header("location: index.php");
    } else header("location:useradmin.php");
    $dbh->commit();
}
// vim: set tags+=../../**/tags :
