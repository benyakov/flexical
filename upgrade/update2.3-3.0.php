<?php
$installroot = dirname(dirname(__FILE__));
require("{$installroot}/utility/setup-session.php");
require("{$installroot}/db.php");
require("{$installroot}/functions.php");
$authlevel = $_SESSION[$sprefix]["userlevel"];
if (is_int($authlevel) && $authlevel <= 3) {
    echo "Access denied";
    exit(0);
}
$dbh->query("ALTER TABLE `{$tablepre}config`
    DROP COLUMN `timezone_offset`") or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}config`
    ADD COLUMN `default_timezone` varchar(20) NOT NULL default 'UTC'
    AFTER `google_password`") or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}config`
    ADD COLUMN `authcookie_max_age` int NOT NULL default 0
    AFTER `local_php_library`") or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}eventstb`
    ADD COLUMN `timezone` varchar(20) NOT NULL default 'UTC'")
    or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}users`
    ADD COLUMN `timezone` varchar(20) NOT NULL default 'UTC'")
    or die(array_pop($dbh->errorInfo()));
$dbh->query("ALTER TABLE `{$tablepre}users`
    MODIFY COLUMN `password` varchar(255) NOT NULL default ''")
    or die(array_pop($dbh->errorInfo()));

$dbconfig = new Configfile("{$installroot}/dbconnection.ini", false, true, true);
$dbconfig->set("dbhost", $dbconnection['dbhost']);
$dbconfig->set("dbname", $dbconnection['dbname']);
$dbconfig->set("dbuser", $dbconnection['dbuser']);
$dbconfig->set("dbpassword", $dbconnection['dbpassword']);
$dbconfig->set("prefix", $tablepre);
$dbconfig->save();
unset($dbconfig);
chmod("{$installroot}/dbconnection.ini", 0600);

$configfile = new Configfile("{$installroot}/config.ini", false, true, true);
$configfile->set("hasuser", 1);
require("{$installroot}/version.php");
$configfile->set("dbversion", "{$version['major']}.{$version['minor']}.{$version['tick']}");
$configfile->save();
unset($configfile);
require("./write_table_descriptions.php");
echo "Update 2.3->3.0 Successful.";

// vim: set tags+=../../**/tags :

