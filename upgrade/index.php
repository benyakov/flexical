<?php /* Determine if database upgrades are needed and perform them



*/

class Upgrader {
    function validVersion($version) {
        if (preg_match('/[.\d]+/', $version))
            return true;
        else
            return false;
    }

    function errorAbort($message) {
        $message = urlencode($message);
        header("location: {$_SERVER['SCRIPT_NAME']}?error='{$message}'");
        exit(0);
    }

    function splitversions($input) {
        $input = trim(substr($input, 6), ".php");
        $versions = explode('-', $input);
        $out = array();
        foreach ($versions as $v) {
            $out[] = explode('.', $v);
        }
        return $out;
    }

    function upgrade($oldv, $fileglob) {
        $upgradefiles = glob($fileglob);
        $upgradefiles = array_map(array($this, "splitversions"), $upgradefiles);
        sort($upgradefiles);
        // Use only the first two version parts
        $oldversion = explode(".", $oldv);
        $oldvstring = "{$oldversion[0]}.{$oldversion[1]}";
        // We could check the list of upgrade files here.
        $rv = array();
        $upgrade = false;
        foreach ($upgradefiles as $f) {
            if ($oldversion[0] == $f[0][0] && $oldversion[1] == $f[0][1])
                $upgrade = true; // Flip the switch for all iterations
            if ($upgrade) {
                $rv[] = "Upgrading {$f[0][0]}.{$f[0][1]} to {$f[1][0]}.{$f[1][1]}...";
                require("./update{$f[0][0]}.{$f[0][1]}-{$f[1][0]}.{$f[1][1]}.php");
            }
        }
        $rv[] = "Writing new table descriptions.";
        require("write_table_descriptions.php");
        return implode("\n", $rv);
    }
}

$ug = new Upgrader();

if (file_exists("./upgrade"))
    chdir("upgrade"); // when require()d below
else {
    chdir("..");
    require_once("./utility/dbconnection.php");
    require_once("./utility/configfile.php");
    chdir("./upgrade");
}

// User actions confirmed below...
if ($_GET['action'] == 'pre3') {
    $previous = $_POST['previous'];
    if (! $ug->validVersion($previous)) {
        $ug->errorAbort('Bad version format.');
    }
    $ug->upgrade($previous, "update2*.php");
}

$configfile = new Configfile("../config.ini");
if ($configfile->exists("dbversion")) {
    $previous = $configfile->get("dbversion");
} else {
    $previous = "";
}
unset($configfile);

// Test for pre-2.3 (no automatic upgrades)
if (file_exists("./update0-1.3.php") || (! $previous)) {
    ?><!DOCTYPE html>
<html lang="en">
<head>
<title>Upgrade from Pre-2.3</title>
<style>
p.error { color: black; background-color: red; border: thin solid yellow; }
</style>
</head>
<body>
<?php if ($_GET['error']) echo "<p class=\"error\" >Error: {$_GET['error']}</p>";
?>
<h1>Upgrade from Pre-3 Version</h1>
<p>It appears that you are upgrading Flexical from a version before 3.x.x.
Back then, there was no way to record the exact Flexical version used
to create the existing database tables.  So if you want to perform this
upgrade, I need your help to figure out the version from which we are
upgrading.  If you know what that is, please enter it below.  (If necessary,
you can send the author your createtables.php file, which he can match against
previous versions.)  It's a good idea to have a recent back-up of your
data in case of loss, and hopefully you saved one before upgrading your
calendar installation.  If not, then I suggest you stop this process now and
get your database backed up.  (You may have to downgrade your calendar
installation back to a version compatible with your un-upgraded version.)
</p>

<form action="<?=$_SERVER['SCRIPT_NAME']?>?action=pre3" method="post">
<input pattern="[.\d]+" title="Numbers and periods only" type="text" name="previous" placeholder="Previous Flexical Version" required>
<button type="submit">Submit</button>
</form>

</body>
</html>
<?php  exit(0);
}

// Upgrade from 3.0.0+
$ug->upgrade($previous, "update3*.php");

