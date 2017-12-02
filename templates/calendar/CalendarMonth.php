<?php
namespace flexical\calendar;
if (!isset($includeroot) &&
    basename($_SERVER['PHP_SELF']) == "CalendarMonth.php")
{
    echo "Setting includeroot.";
    $includeroot = dirname(dirname(dirname(__FILE__)));
}
$dir = dirname(__FILE__);
require_once("{$dir}/CalendarItem.php");
require_once("{$dir}/CalendarWeek.php");
require_once("{$includeroot}/lang/Translate.php");
class CalendarMonth extends CalendarItem
{
    function __construct($events,
                        $month, // number 1-12
                        $year // Anno Domini number
                        )
    {
        $this->registerVocabulary(array('month', 'year', 'events',
            'month_length', 'weekday_start', 'weeks_included',
            'endnumber', 'startpos', 'displayweeks', 'vsize', 'shortcells'));
        parent::__construct();
        $this->month = $month;
        $this->year = $year;
        $this->events = $events;
        $this->month_length = date('j', mktime(0,0,0,-1,$month+1,$year));
        $this->weekday_start = date('w', mktime(0,0,0,1,$month,$year));
        if ($this->weekday_start == 0) $weeks = 4;
        else $weeks = 5;
        $wkday28th = date('w', mktime(0,0,0,28,$month,$year));
        $extradays = $this->month_length - 28;
        if ($wkday28th > (6-$extradays)) $weeks += 1;
        $this->weeks_included = $weeks;
        /* Make a few measurements available as object properties before writing out */
        // Get number of days in month
        $this->endnumber = 31-((($this->month-(($this->month<8)?1:0))%2)+(($this->month==2)?((!($this->year%((!($this->year%100))?400:4)))?1:2):0));
        // Get position of first day of month, 0-6
        $this->startpos = date("w",mktime(0,0,0,$this->month,1,$this->year));
        // Get number of display weeks in month
        $this->displayweeks = ceil(($this->endnumber + $this->startpos) / 7);
        // Get the needed vertical cell size
        $this->vsize = round(97/$this->displayweeks, 2);
        if ($this->displayweeks > 5) $this->shortcells = true; else $this->shortcells = false;
    }

    function write($auth=0, $special=array()) {
        $out = array();
        $out[] = "<table class=\"calendarbox\"><tbody>";
        $out[] = "<tr id=\"heading\" class=\"calendarweek\">";
        foreach(\__('abrvdays') as $day) {
            $out[] = "<td class=\"columnheader\">&nbsp;{$day}&nbsp;</td>\n";
        }
        $out[] = "</tr>";

        // Write the first week
        $startdate = mktime(0,0,0,$this->month,1,$this->year);
        $cw = new CalendarWeek($this->events,
            strftime('%Y-%m-%d', $startdate), $this->startpos+1,
            $this->endnumber, $this->vsize, $this->shortcells);
        $out[] = $cw->write($auth, $special);
        // Write each subsequent week
        //  Number of days in first week of month
        $week1length = 7-$this->startpos;
        //  Date of first Sunday
        $firstsunday = $week1length+1;
        for ($day = $firstsunday; $day <= $this->endnumber; $day+=7) {
            $startdate = mktime(0,0,0,$this->month,$day,$this->year);
            $out[] = $cw->write($auth, $special,
                strftime('%Y-%m-%d', $startdate), 1, $this->endnumber, $this->vsize,
                $this->shortcells);
        }
        $out[] = "</tbody></table>\n";
        return implode("\n", $out);
    }
}


if (getGET('test') == 'flexical') {
    unset($_GET['test']);
    require_once("{$includeroot}/functions.php");
    require_once("{$dir}/testfunctions.php");
    echo "<h1>".__FILE__."</h1>";
    echo "<ol>";
    $month = date("n");
    $year = date("Y");
    echo "<li>Creating an object... for $month/$year";
    $config = array('timezone' => -8+1, 'title_limit' => 8,
        'compact_title_limit' => 6, 'include_end_times' => true,
        'timefmt' => formattime('php'));
    $tz = date('Z')/60/60;
    $events = array(1 => array( //FIXME: Adjust time arithmetic to DateTime
        array('title'=>'Quart of milk', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>time(), 'tz'=>$tz,
            'end_time'=>\time_add(time(), 0,30,1,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1),
        array('title'=>'Loaf of bread', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),2,0,0,0,0,0),
            'end_time'=>\time_add(time(),0,4,0,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        array('title'=>'Stick of butter', 'all_day'=>true, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),0,0,2,0,0,0),
            'end_time'=>null, 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        ), 2=> array(
        array('title'=>'Test Event day 2', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>time(),
            'end_time'=>\time_add(time(), 0,10,0,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        array('title'=>'Test Event 2 day 2', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),0,0,2,0,0,0),
            'end_time'=>\time_add(time(),0,30,0,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        array('title'=>'Test Event', 'all_day'=>true, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),0,0,1,0,0,0),
            'end_time'=>null, 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        ), 6 => array(
        array('title'=>'Test Event day 6', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>time(),
            'end_time'=>\time_add(time(), 4,30,1,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        array('title'=>'Test Event 2', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),0,15,0,0,0,0),
            'end_time'=>\time_add(time(),0,0,2,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz)
        ));
    $cm = new CalendarMonth($config, $events, $month, $year);
    echo "</li>";
    echo "<li>Writing it out...</li>";
    echo $cm->write();
    echo "</li>";
    echo "<li>Writing it with special text...</li>";
    $sp = array("With extra butter!", "Don't forget the cheese.", "a", "b",
        "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n");
    echo $cm->write(0, $sp);
    echo "</li>";
    echo "</ol>";
}

// vim: set tags+=../../../**/tags foldmethod=indent :
?>

