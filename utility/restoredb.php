<?php
/*
 * Select a dump file to upload, then execute it.
 */
if (function_exists("auth")) { // Running from the index.php entry point
    $authlevel = auth();
    $serverdir = SDir();
} else {
    chdir("..");
    $installroot = dirname($_SERVER['SCRIPT_NAME']);
    $includeroot = dirname(__FILE__);
    require("./setup-session.php");
    require("./utility/configfile.php");
    if (isset($_SESSION[$sprefix]["authdata"]["userlevel"])) {
        // Called directly with established session
        $authlevel = $_SESSION[$sprefix]["authdata"]["userlevel"];
        require("../lang/Translate.php");
    } else { // Called directly, no established session
        $authlevel = 0;
        require("../lang/Translate.php");
    }
    if (isset($_SESSION[$sprefix]["serverdir"])) {
        $serverdir = $_SESSION[$sprefix]["serverdir"];
    } else {
        $serverdir = upfromhere();
    }
}
if ($authlevel < 3) {
    setMessage(__('accessdenied'));
    header("Location: {$serverdir}/index.php");
    exit(0);
}
$this_script = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'] ;
if (! array_key_exists("stage", $_GET)) {
?>
    <html>
    <head><title><?=__('restoretitle')?></title></head>
    <body>
    <h1><?=__('restoretitle')?></h1>
    <p><?=__('selectfile')?></p>
    <form action="<?=$serverdir?>/utility/restoredb.php?stage=2" enctype="multipart/form-data"
        method="POST">
    <input type="file" name="backup_file" size="50">
    <input type="submit" value="<?=__('send')?>"><input type="reset">
    </form>
    </body>
    </html>
    <?php
} elseif (2 == $_GET['stage']) {
    $dbconfig = new Configfile("./dbconnection.ini", false, true, false);
    $dumpfile = "restore-{$dbconnection['dbname']}.txt";
    if (move_uploaded_file($_FILES['backup_file']['tmp_name'], $dumpfile)) {
        touch(".my.cnf");
        chmod(".my.cnf", 0600);
        $fp = fopen(".my.cnf", "w");
        fwrite($fp, "[client]
        user=\"{$dbconfig->get('dbuser')}\"
        password=\"{$dbconfig->get('dbpassword')}\"\n") ;
        fclose($fp);
        $cmdline = "mysql --defaults-file=.my.cnf ".
            "-h {$dbconfig->get('dbhost')} {$dbconfig->get('dbname')} ".
            "-e 'source {$dumpfile}';";
        $result = system($cmdline, $return);
        @unlink(".my.cnf");
        @unlink($dumpfile);
        unset($dbconfig);
        if (0 == $return) {
            setMessage(__('restoresucceeded'));
            header("Location: {$installroot}/index.php");
        } else {
            ?>
            <html><head><title>Problem Executing Restore</title></head>
            <body><h1>Problem Executing Restore</h1>
            <p>Command: <pre><?=$cmdline?></p>
            <p>Exit code: <?=$return?></p>
            <p>Output: <pre><?=$result?></pre></p>
            </body></html>
            <?php
        }
    } else {
        setMessage(__('problemuploadingbackup'));
        header("Location: {$installroot}/index.php");
    }
    exit(0);
}
// vim: set tags+=../../**/tags :
