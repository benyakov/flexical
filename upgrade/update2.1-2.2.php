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

$fp = fopen("../db.php", "w");
fwrite($fp, "<?php // Do not change this file unless you know what you are doing.
// This tells Flexical how to connect to your database.
try{
    \$dbh = new PDO('mysql:host={$dbhost};dbname={$dbname}',
        '{$dbuser}', '{$dbpw}');
} catch (PDOException \$e) {
    die(\"Database Error: {\$e->getMessage()} </br>\");
}
\$tablepre = '{$tablepre}';
\$dbconnection = array(
    'dbhost'=>\"{$dbhost}\",
    'dbname'=>\"{$dbname}\",
    'dbuser'=>\"{$dbuser}\",
    'dbpassword'=>\"{$dbpw}\");
?>
");
fclose($fp);
chmod("../db.php", 0600);

echo "Update 2.1->2.2 Successful.";
