<?php
$sprefix = realpath(dirname(__FILE__));
session_name("FlexicalSession");
session_set_cookie_params(604800, $installroot);
session_start();
if (! (array_key_exists($sprefix, $_SESSION) && is_array($_SESSION))) {
    $_SESSION[$sprefix] = array();
}
// vim: set tags+=../../**/tags :
