<html>
<head>
<title><?=__('initialusertitle')?></title>
<link rel="stylesheet" type="text/css" href="css/styles-pop.css">
<?php require("./utility/passwordvalidate.php")?>
</head>
<body>
<h1><?=__('initialusertitle')?></h1>
<table>
<form onSubmit="return validate(this, 'inituser');">
        <input type="hidden" name="ulevel" value="3"/>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="login_label"><?=__('fname')?></span></td>
            <td><input type="text" name="fname" size="29" maxlength="20"/></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="login_label"><?=__('lname')?></span></td>
            <td><input type="text" name="lname" size="29" maxlength="20"/></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="login_label"><?=__('email')?></span></td>
            <td><input type="text" name="email" size="29" maxlength="30"/></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="login_label"><?=__('username')?></span></td>
            <td><input type="text" name="username" size="29" maxlength="15"/></td>
        </tr>
        <tr>
            <td align="right"><span class="edit_user_label"><?=__('timezone')?></span></td>
            <td><?=timezoneDropDown()?></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="login_label"><?=__('password')?></span></td>
            <td><input type="password" name="pw" size="29" maxlength="15"/></td>
        </tr>
        <tr>
            <td nowrap valign="top" align="right" nowrap>
            <span class="login_label"><?=__('password')?></span></td>
            <td><input type="password" name="pwconfirm" size="29" maxlength="15"/></td>
        </tr>
        <tr><td colspan="2" align="right"><input type="submit" value="<?=__('commit')?>"><td><tr>
</form>
</table>

</body></html>
<!-- vim: set tags+=../../**/tags : -->
