<?php
namespace flexical\calendar;
if (!isset($includeroot) &&
    basename($_SERVER['PHP_SELF']) == "CalendarMonthbox.php")
{
    echo "Setting includeroot.";
    $includeroot = dirname(dirname(dirname(__FILE__)));
}
$dir = dirname(__FILE__);
require_once("{$dir}/CalendarItem.php");
class CalendarMonthbox extends CalendarItem
{
    public function __construct($classes=array()) {
        $this->registerVocabulary(array('classes', 'h5data'));
        parent::__construct();
        $this->classes = $classes;
    }

    public function writeExtra($special=array(), $span=1) {
        if (! is_array($special)) $special = array($special);
        $contents = array_shift($special);
        if (! $contents) {
            $this->classes = array_merge(array("empty-day-cell"), $this->classes);
            $contents = "<div class=\"extramessage\"><form><textarea placeholder=\""
                .__('extra-message-placeholder')
                ."\"></textarea></form></div>";
        }
        $this->classes = array_merge($this->classes, $special);
        if ($span > 1) {
            $span = " colspan=\"$span\" ";
        } else {
            $span = "";
        }
        $h5data = array();
        if ($this->h5data) {
            foreach ($this->h5data as $k, $v) {
                $h5data[] = "data-{$k}=\"$v\"";
            }
        }
        return "<td {$span} ".(count($this->classes)?
            (" class=\"".implode(" ",$this->classes).'"'):
            ("")).($h5data?(" ".implode(" ",$h5data)):("")
            .">\n".$contents."\n</td>";
    }
}

if (getGET('test') == 'flexical') {
    echo "<h1>".__FILE__."</h1>";
    echo "<ol>";
    echo "<li>Making an object</li>";
    $config=array();
    $classes=array('class1', 'class2');
    $testcontent = "<h4>Test Content</h4>";
    $mvb = new CalendarMonthbox($config, $classes);
    $rv = $mvb->writeExtra($testcontent, 1);
    echo $rv;
    $expecting = '<td   class="class1 class2">'."\n<h4>Test Content</h4>\n</td>";
    if ($rv == $expecting)
        echo "Success.";
    else echo "Failure.";
    echo "</ol>";
}

if (getGET('display')) {
    $mvb = new CalendarMonthbox(array(),
        $_POST['span'], explode(',',$_POST['classes']));
    echo $mvb->writeExtra($_POST['content']);
}
// vim: set tags+=../../../**/tags foldmethod=indent :
?>
