<?php
/* Set up configuration and save to the db, may be run directly at installation
 * or as needed via require().
 */

// Determine authorization to configure the calendar
if (function_exists("auth")) { // Running from the index.php entry point
    $authlevel = auth();
    $serverdir = SDir();
} else { // Run directly
    chdir("..");
    require("./functions.php");
    $installroot = dirname(dirname($_SERVER["SCRIPT_NAME"]));
    $includeroot = dirname(dirname(__FILE__));
    require("./utility/setup-session.php");
    if (isset($_SESSION[$sprefix]["authdata"]["userlevel"])) {
        // Called directly with established session
        $authlevel = $_SESSION[$sprefix]["authdata"]["userlevel"];
        require("./lang/Translate.php");
    } else {
        // Called directly, no established session
        $authlevel = 0;
        require("./lang/Translate.php");
    }
    if (isset($_SESSION[$sprefix]["serverdir"])) {
        $serverdir = $_SESSION[$sprefix]["serverdir"];
    } else {
        $serverdir = upfromhere();
    }
}
if (3 > $authlevel) {
    setMessage(__('Unauthorized'));
    header("Location: {$serverdir}/login.php?action=loginform");
    exit(0);
}
if (array_key_exists('cancel', $_POST)) {
    setMessage(__('operationcancelled'));
    header("Location: {$serverdir}/index.php");
    exit(0);
}
if (array_key_exists("step", $_POST) && 2 == $_POST['step']) {
    // Process form
    unset($_POST['step']);
    unset($_POST['submit']);
    // Use default settings to identify missing form values
    require('./utility/default-settings.php');
    foreach ($default as $k => $v) {
        if (! array_key_exists($k, $_POST)) {
            $_POST[$k] = '0';
        }
    }
    // Convert fields of lines into arrays
    $sitetabs = explode("\r\n", $_POST['sitetabs']);
    $_POST['sitetabs'] = array();
    foreach ($sitetabs as $st) {
        if (trim($st)) {
            $_POST['sitetabs'][] = trim($st);
        }
    }
    // Write the new configuration to the database
    require('./utility/dbconnection.php');
    require('./utility/configdb.php');
    require('./version.php');
    $Config = new Configdb($version);
    $Config->newconfig($_POST);
    // Put new config into session?
    setMessage(__('New Flexical configuration saved.'));
    header("Location: {$serverdir}/index.php");

} else { // Generate form

    // Set up from default settings (for when there are config changes)
    $c = array();
    $defaulted = false;
    require('./utility/default-settings.php');
    foreach ($default as $k => $v) {
        if (! isset($configuration[$k])) $defaulted = true;
        $c[$k] = $v;
    }
    // Add actual settings
    if ($configuration) {
        foreach ($configuration as $k => $v) $c[$k] = $v;
    }
    // Turn arrays into lines
    foreach ($c as $k => $v) {
        if (is_array($v)) {
            $c[$k] = implode("\n", $v);
        }
    }
    $time_array = array("twelve", "twenty-four");
?>
<html>
    <head>
        <title><?=__('Configuration')?></title>
        <link rel="stylesheet" type="text/css" href="css/styles-pop.css">
        <!-- FIXME: Add Javascript form checking -->
    </head>
    <body><h1><?=__('Configuration')?></h1>

    <?php if ($defaulted) { ?>
    <p class="warning">Some values listed here are not saved, because the
configuration database has been upgraded, and the old values did not exist.
Please check the settings and save the configuration to accept the given
configuration values.</p>
    <?php } ?>

    <p><a href="./index.php?admin=configuration-history"><?=__("configuration history")?></a></p>

<table border=0 cellspacing=7 cellpadding=0>
<form name="configForm" method="POST" action="utility/config.php">
    <input type="hidden" name="step" value="2"/>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('language')?></span></td>
        <td><input type="text" name="language" size="2"
                value="<?=$c['language']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('site_title')?></span></td>
        <td><input type="text" name="site_title" size="25" value="<?=$c['site_title']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('sitetabs')?></span></td>
        <td><textarea cols="25" rows="8" name="sitetabs"><?=$c['sitetabs']?></textarea></td>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('default_action')?></span></td>
        <td><input type="text" name="default_action" size="25" value="<?=$c['default_action']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('title_limit')?></span></td>
        <td><input type="text" name="title_limit" size="25" value="<?=$c['title_limit']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('compact_title_limit')?></span></td>
        <td><input type="text" name="compact_title_limit" size="25" value="<?=$c['compact_title_limit']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('title_char_limit')?></span></td>
        <td><input type="text" name="title_char_limit" size="25" value="<?=$c['title_char_limit']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('category_key_limit')?></span></td>
        <td><input type="text" name="category_key_limit" size="25" value="<?=$c['category_key_limit']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('show_category_key')?></span></td>
        <td><?=checkbox("show_category_key", $c['show_category_key'])?></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('include_end_times')?></span></td>
        <td><?=checkbox("include_end_times", $c['include_end_times'])?></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('default_time')?></span></td>
        <td><?=pullDown($c['default_time'], $time_array, "default_time", $time_array)?></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('default_open_time')?></span></td>
        <td><?=checkbox("default_open_time", $c['default_open_time'])?></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('cross_links')?></span></td>
        <td><textarea name="cross_links" rows="8" cols="60"><?=$c['cross_links']?></textarea></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('email_from_address')?></span></td>
        <td><input type="text" name="email_from_address" size="25" value="<?=$c['email_from_address']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('google_user')?></span></td>
        <td><input type="text" name="google_user" size="25" value="<?=$c['google_user']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('google_password')?></span></td>
        <td><input type="text" name="google_password" size="25" value="<?=$c['google_password']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('default_timezone')?></span></td>
        <td><?=timezoneDropDown("default_timezone", $c['default_timezone'])?></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('local_php_library')?></span></td>
        <td><input type="text" name="local_php_library" size="25" value="<?=$c['local_php_library']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('authcookie_max_age')?></span></td>
        <td><input type="text" name="authcookie_max_age" size="25" value="<?=$c['authcookie_max_age']?>"/></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('authcookie_path')?></span></td>
        <td><input type="text" name="authcookie_path" size="25"
value="<?=$c['authcookie_path']?>"/><br>
        <p>If this is not set to "authcookies," then the path for the
extended-auth cookies will be set to "/", so that multiple installations may
share them.</p></td>
    </tr>
    <tr>
        <td valign="top" align="right" nowrap>
        <span class="form_labels"><?=__('remotes')?></span></td>
        <td><textarea name="remotes" rows="8" cols="60"><?=$c['remotes']?></textarea><br>
        <p>A linewise list of remote urls and categories. The format for each line<br>
           http://somedomain.com/location::category 1,category 2,category 3::localcategory<br>
           All remote events will be placed into localcategory for formatting purposes.</p></td>
    </tr>
    <tr>
        <td><input type="submit" name="submit" value="<?= __('submitbutton') ?>"/>
            <input type="submit" name="cancel" value="<?= __('cancel') ?>"></td>
    </tr>
</form>
</table>
</body>
</html>
<?php
}
// vim: set tags+=../../**/tags :
