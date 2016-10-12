<?php
// Set up db.php database configuration file.
if (array_key_exists("step", $_POST) && $_POST['step'] == '2') {
    require("../functions.php");
    $serverdir = upfromhere();
    // Process the form (second time around)
    require_once("./configfile.php");
    $dbconfig = new Configfile("../dbconnection.ini", false, true, true);
    $dbconfig->set("dbhost", $_POST['dbhost']);
    $dbconfig->set("dbname", $_POST['dbname']);
    $dbconfig->set("dbuser", $_POST['dbuser']);
    $dbconfig->set("dbpassword", $_POST['dbpassword']);
    $dbconfig->set("prefix", $_POST['dbtableprefix']);
    $dbconfig->save();
    unset($dbconfig);
    chmod("../dbconnection.ini", 0600);

    if (basename(__FILE__) == basename($_SERVER['PHP_SELF']))
        $goto = upfromhere() . "/index.php?initialize=Flexical";
    else
        $goto = "{$SDir()}/index.php?initialize=Flexical";
    header("Location: {$goto}");
    exit(0);
} else {
    // Display the form (first time around)
?>
    <html>
        <head>
            <title>New Flexical Installation</title>
            <link rel="stylesheet" type="text/css" href="css/styles-pop.css">
            <!-- FIXME: Add Javascript form checking -->
        </head>
    <body><h1>New Flexical Installation</h1>

    <?php if ($error) { ?>
    <p class="helptext"><?=__('badsettings')?></p>
    <p class="helptext"><?=$e->getMessage()?></p>
    <?php } ?>

    <table border=0 cellspacing=7 cellpadding=0>
    <form name="configForm" method="POST" action="utility/setup-dbconfig.php">
        <input type="hidden" name="step" value="2"/>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('dbhost')?></span></td>
            <td><input type="text" name="dbhost" size="25" value="<?=$dbconnection['dbhost']?>"/></td>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('dbname')?></span></td>
            <td><input type="text" name="dbname" size="25" value="<?=$dbconnection['dbname']?>"/></td>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('dbuser')?></span></td>
            <td><input type="text" name="dbuser" size="25" value="<?=$dbconnection['dbuser']?>"/></td>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('dbpassword')?></span></td>
            <td><input type="text" name="dbpassword" size="25" value="<?=$dbconnection['dbpassword']?>"/></td>
        </tr>
        <tr>
            <td valign="top" align="right" nowrap>
            <span class="form_labels"><?=__('dbtableprefix')?></span></td>
            <td><input type="text" name="dbtableprefix" size="25" value="<?=$tablepre?>"/></td>
        </tr>
        <tr>
            <td><input type="submit" name="submit" value="<?= __('submit') ?>"/></td>
        </tr>
    </form>
    </table>
    </body></html>
<?php
}
// vim: set tags+=../../**/tags :
