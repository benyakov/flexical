<?php
$installroot = dirname(dirname($_SERVER['SCRIPT_NAME']));
$includeroot = dirname(dirname(__FILE__));
chdir("..");
require("./utility/initialize-entrypoint.php");
chdir("./help");

$name = getGET('n');
$message = getGET('info');

if (is_dir($name)) {
    getlisting($name, $message);
} elseif (is_file($name)) {
    showfile($name, $message);
} else {
    $info = "Redirecting from '{$name}' to Contents";
    header ("Location: {$SDir()}/index.php?n=Contents.{$configuration['language']}.txt&info=".urlencode($info));
    exit(0);
}

function breadcrumbs($pathname) {
    global $configuration;
    ?> <div class="breadcrumbs"> <?php
        echo "<a href=\"..\">&lt;&lt;</a> | ";
        echo "<a href=\"?n=Contents.{$configuration['language']}.txt\">[O]</a> &gt; ";
        foreach (explode('/', $pathname) as $element) {
            if ($element == ".") { continue; }
            $breadcrumbs[] = $element;
            $elemname = nameonly($element);
            echo '<a href="?n='.urlencode(implode('/', $breadcrumbs)).
                "\">{$elemname}</a> &gt; ";
        }?>
    </div> <?php
}

function nameonly($withending) {
    global $configuration;
    if (strpos($withending, ".{$configuration['language']}.txt")) {
        return substr($withending, 0, strpos($withending, ".{$configuration['language']}.txt"));
    } else {
        return $withending;
    }
}

function getlisting($prefix, $message) {?>
    <html>
        <head>
            <title><?=$prefix?></title>
            <link rel="stylesheet" type="text/css" href="../css/help.css">
        </head>
        <body>
    <?php
    global $configuration;
    if ($message) {
        echo "<div class=\"message\">{$message}</div>";
    }
    breadcrumbs($prefix);
    $direntries = scandir("{$prefix}");
    $dirs = array();
    $files = array();
    foreach ($direntries as $item) {
        if (is_dir("{$prefix}/{$item}") &&
            $item != '.' && $item != '..') {
            $dirs[] = "{$prefix}/{$item}";
        } elseif (strpos($item, ".{$configuration['language']}.txt")!==false) {
            $files[] = "{$prefix}/{$item}";
        }
    }
    if ($dirs) {
        echo "<ol class=\"dirs\">\n";
        foreach ($dirs as $d) {
            echo "<li><a href=\".?n=".urlencode($d)."\">{$d}</a></li>";
        }
        echo "</ol>\n";
    }
    if ($files) {
        echo "<ol class=\"files\">\n";
        foreach ($files as $f) {
            $parts = explode('/', $f);
            $fname = nameonly($parts[count($parts)-1]);
            echo "<li><a href=\".?n=".urlencode($f)."\">{$fname}</a></li>";
        }
        echo "</ol>\n";
    }?>
    </body>
    </html><?php
}

function showfile($path, $message) { ?>
    <html>
        <head>
            <title><?=nameonly($path)?></title>
            <link rel="stylesheet" type="text/css" href="../css/help.css">
        </head>
        <body>
    <?php
    if ($message) {
        echo "<div class=\"message\">{$message}</div>";
    }
    breadcrumbs($path);
    $fh = fopen($path, "r");
    $mdtext = "";
    while (! feof($fh)) {
        $mdtext .= fread($fh, 8192);
    }
    fclose($fh);
    if (strpos($mdtext, "Suppress Formatting\n") === 0) {
        echo "<pre>\n";
        echo substr($mdtext, 21);
        echo "</pre>\n";
    } else {
        $mdtext = preg_replace('/<\\{([^}]+)\\}>/e', '__("$1")', $mdtext);
        echo Markdown($mdtext);
    }?>
        </body>
    </html>
    <?php
}
// vim: set tags+=../../**/tags :
