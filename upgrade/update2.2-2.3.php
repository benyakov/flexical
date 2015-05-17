<?php
$installroot = dirname(dirname(__FILE__));
require("../utility/setup-session.php");
require("../db.php");
require("../functions.php");
$authlevel = auth();
if (is_int($authlevel) && $authlevel <= 3) {
    echo "Access denied";
    exit(0);
}
$dbh->query("ALTER TABLE `{$tablepre}config`
    ADD COLUMN `default_timezone` int default 0 AFTER `timezone_offset`")
        or die (mysql_error());
$dbh->query("ALTER TABLE `{$tablepre}eventstb`
    ADD COLUMN `timezone` tinyint NOT NULL default 0 AFTER `related`")
        or die (mysql_error());
$dbh->query("ALTER TABLE `{$tablepre}users`
    ADD COLUMN `timezone` tinyint NOT NULL default 0 AFTER `resetexpiry`")
        or die (mysql_error());

unlink("./update0-1.3.php");
require("./write_table_descriptions.php");
echo "Update 2.2->2.3 Successful.";

// vim: set tags+=../../**/tags :
