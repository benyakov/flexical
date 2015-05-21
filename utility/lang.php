<?
// Override and fallback for language.
if (array_key_exists('language', $_GET)) {
    $language = $_GET['language'];
} elseif (! isset($language)) {
    $language = "en";
}

if (isset($translator)) unset($translator);
$translator = new Translate($includeroot, $language);
require_once($includeroot.'/lib/lang.php');

// vim: set tags+=../../**/tags :
