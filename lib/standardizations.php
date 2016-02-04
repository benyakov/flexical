<?php

/*** Standardizations ***/
function jqueryCDN() {
?>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<?
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
