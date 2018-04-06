<?php
$dbconfig = new Configfile("./dbconnection.ini", false, true, false);
if (auth() < 3) {
    header("Location: {$SDir()}/index.php?message=".urlencode(__('accessdenied')));
    exit(0);
}
$tabledescfile = "tabledesc.sql";
if (! file_exists($tabledescfile)) {
    $GEN_TABLEDESC = true;
    require_once("./utility/createtables.php");
}
$tabledesclines = file($tabledescfile, FILE_IGNORE_NEW_LINES);
function gettablename ($line) {
    if (preg_match('/TABLE.*? `([-\w]+)/', $line, $matches))
    {
        return $matches[1];
    } else {
        return False;
    }
}
$tablenamelines = array_filter($tabledesclines, gettablename);
$finaltablenames = array_map(gettablename, $tablenamelines);
$tablenamestring = implode(" ", array_unique($finaltablenames));
header("Content-type: text/plain");
$timestamp = date("dMY-Hi");
header("Content-disposition: attachment; filename=flexical-{$timestamp}.dump");
touch(".my.cnf");
chmod(".my.cnf", 0600);
$fp = fopen(".my.cnf", "w");
fwrite($fp, "[client]
user=\"{$dbconfig->get('dbuser')}\"
password=\"{$dbconfig->get('dbpassword')}\"\n") ;
fclose($fp);
$mysqldumpcmd = "mysqldump --defaults-file=.my.cnf -h {$dbconfig->get('dbhost')} {$dbconfig->get('dbname')} --tables {$tablenamestring}";
passthru($mysqldumpcmd);
@unlink(".my.cnf");
// vim: set tags+=../../**/tags :
