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
// Make a config that does not include default_tabular_month
require("../utility/configdb.php");
$Config = new Configdb($tablepre, "../version.php");
$configuration = $Config->newest();
unset($configuration['default_tabular_month']);

mysql_query("ALTER TABLE {$tablepre}config
    DROP COLUMN `default_tabular_month`") or die (mysql_error());
// Save the new config
$Config->newconfig($configuration);

require("./write_table_descriptions.php");
echo "Update 2.0->2.1 Successful.";

// vim: set tags+=../../**/tags :
