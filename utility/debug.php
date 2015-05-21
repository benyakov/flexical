<?php
/********************
** Debugging helps **
********************/
// print_r($_GET); // Useful for debugging
if ($_GET['debug']=="filter") {
    print_r($_SESSION[$sprefix]['filters']);
    exit(0);
} elseif ($_GET['debug']=="session") {
    print_r($_SESSION[$sprefix]);
    exit(0);
}


