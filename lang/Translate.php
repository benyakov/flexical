<?php
require_once("./lang/Translators.php");

// Override and fallback for language.
if (array_key_exists('language', $_GET)) {
    $language = $_GET['language'];
} elseif (! isset($language)) {
    $language = "en";
}

$translator = new Translate($includeroot, $language);
$_ = "__";

// vim: set tags+=../../**/tags :
