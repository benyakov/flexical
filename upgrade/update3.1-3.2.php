<?php
/* Setup */
$installroot = dirname(__DIR__);
require("{$installroot}/utility/setup-session.php");
$dbh = new DBConnection("..");
$tablepre = $dbh->getPrefix();
require("{$installroot}/functions.php");
$authlevel = $_SESSION[$sprefix]["userlevel"];
if (is_int($authlevel) && $authlevel <= 3) {
    echo "Access denied";
    exit(0);
}
/* The change */
$dbh->query("ALTER TABLE `{$tablepre}config`
    ADD COLUMN `remotes` text
    AFTER `authcookie_path`") or die(array_pop($dbh->errorInfo()));

/* Update version in filesystem. */
$configfile = new Configfile("{$installroot}/config.ini", false, true, true);
require("{$installroot}/version.php");
$configfile->set("dbversion", "{$version['major']}.{$version['minor']}.{$version['tick']}");
$configfile->save();
unset($configfile);

/* Update table descriptions in filesystem. */
require("{$installroot}/upgrade/write_table_descriptions.php");

echo "Update 3.1->3.2 Successful.";

// vim: set tags+=../../**/tags :

