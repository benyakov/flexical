<?php

/*** Standardizations ***/
function filter_set() {
    global $sprefix;
    return isset($_SESSION[$sprefix]["filters"]) && $_SESSION[$sprefix]["filters"];
}
function jqueryCDN() {
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script type="text/javascript" src="jquery/jquery-migrate-1.4.1.min.js"></script>
    <script type="text/javascript" src="jquery/jquery.mobile.custom.min.js"></script>
<? // <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
    // <script type="text/javascript" src="jquery-1.6.3.js"></script>
}

function jqueryuiCDN() {
?>
    <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<?
    // <script type="text/javascript" src="jquery-1.6.3.js"></script>
}

function handlebarsCDN() {
?>
    <script type="text/javascript" src="https://cdnjs.com/libraries/handlebars.js"></script>
<?
}

function SProtocol() {
    /* Return the server protocol string for this request, without colon. */
    if (empty($_SERVER['HTTPS']) || 'off' == $_SERVER['HTTPS']) {
        return "http";
    } else {
        return "https";
    }
}

function SDir() {
    /* Return the server hostname and the directory of the currently executing script. */
    return dirname($_SERVER['PHP_SELF']);
}
$SDir = "SDir";

function upfromhere() {
    // Redirect one level up from the currently-called script
    $dir = dirname($_SERVER['PHP_SELF']);
    $exploded = explode("/", $dir);
    $imploded = implode("/", array_slice($exploded, 0, count($exploded)-1));
    return($imploded);
}

