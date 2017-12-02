<?php
namespace flexical\calendar;
if (!isset($includeroot) &&
    basename($_SERVER['PHP_SELF']) == "CalendarItem.php")
{
    echo "Setting includeroot.";
    $includeroot = dirname(dirname(__FILE__));
}
require_once("{$includeroot}/Exceptions.php");
class CalendarItem {
    public $vocabulary = array();
    protected $val = array();
    protected $config;

    public function __construct() {
        $Config = new \CalendarConfig();
        $this->config = $Config->getConfig();
    }
    public function registerVocabulary($new) {
        $this->vocabulary = array_merge($new, $this->vocabulary);
    }
    public function &__get($name) {
        if (in_array($name, $this->vocabulary)) {
            return $this->val[$name];
        } else {
            throw new \TemplateException("Unknown Property: '{$name}' not"
                ." in vocabulary '".print_r($this->vocabulary, true)."'");
        }
    }
    public function __set($name, $value) {
        if (in_array($name, $this->vocabulary)) {
            $this->val[$name] = $value;
        } else {
            throw new \TemplateException("Unknown Property: '{$name}' not"
                ." in vocabulary '".print_r($this->vocabulary, true)."'");
        }
    }
}

if (getGET('test') == 'flexical') {
    echo "<h1>".__FILE__."</h1>";
    echo "<ol>";
    echo "<li> Making an object.</li>";
    $ci = new CalendarItem(array('config'=>'configvalue'));
    echo "<li>Populating vocabulary.</li>";
    array_push($ci->vocabulary, 'blue', 'red', 'orange', 'green');
    echo "<li>Setting some values.</li>";
    $ci->blue = 'left';
    $ci->red = 'up';
    $ci->orange = 'straight';
    $ci->green = 'back';
    echo "<li>Testing... should return 'left up straight back'";
    $val = "{$ci->blue} {$ci->red} {$ci->orange} {$ci->green}";
    echo $val;
    if ($val == "left up straight back") {
        echo "... success.</li>" ;
    } else {
        echo "... failed.</li>" ;
    }
    echo "<li>Attempting to set something not in the vocabulary...";
    try {
        $ci->blah = "should fail";
        echo "Failure (it succeeded)</li>";
    } catch (\TemplateException $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
        echo "Success, it failed.</li>";
    }
    echo "<li>Attempting to get something not in the vocabulary...";
    try {
        $shouldfail = $ci->blah;
        echo "Failure (it succeeded)</li>";
    } catch (\TemplateException $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
        echo "Success, it failed.</li>";
    }
    echo "</ol>";
}

// vim: set tags+=../../../**/tags foldmethod=indent :
?>
