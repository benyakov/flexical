<?php

/*** Messages ***/

function showMessage() {
    global $sprefix;
    if (array_key_exists('message', $_SESSION[$sprefix])) { ?>
        <div id="message"><?php
        foreach ($_SESSION[$sprefix]['message'] as $msg)
            echo "<div>".formatmessage($msg)."</div>\n";
        ?></div>
        <?php unset($_SESSION[$sprefix]['message']);
    }
}

function formatmessage($message) {
    // Format the given text message for HTML display
    return str_replace("\n", "<br/>", $message);
}

function setMessage($text) {
    global $sprefix;
    $_SESSION[$sprefix]['message'][] = $text;
}

