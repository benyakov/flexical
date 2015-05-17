<?php
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
$dbh->query("ALTER TABLE `{$tablepre}config`
    ADD COLUMN `cross_links` varchar(512)
    AFTER `default_open_time`") or die(array_pop($dbh->errorInfo()));
$dbh->query("UPDATE `{$tablepre}config` SET `cross_links`= CONCAT('{\"home\":\"', `home_link`, '\"}')") or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}config`
    DROP COLUMN `home_link`") or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}config`
    ADD COLUMN `authcookie_path` varchar(255) default 'authcookies'
    AFTER `authcookie_max_age`") or die(array_pop($dbh->errorInfo()));

$configfile = new Configfile("{$installroot}/config.ini", false, true, true);
require("{$installroot}/version.php");
$configfile->set("dbversion", "{$version['major']}.{$version['minor']}.{$version['tick']}");
$configfile->save();
unset($configfile);
require("{$installroot}/upgrade/write_table_descriptions.php");
echo "Update 3.0->3.1 Successful.";

// vim: set tags+=../../**/tags :

