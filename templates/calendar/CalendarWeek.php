<?php
namespace flexical\calendar;
if (!isset($includeroot) &&
    basename($_SERVER['PHP_SELF']) == "CalendarWeek.php")
{
    echo "Setting includeroot.";
    $includeroot = dirname(dirname(dirname(__FILE__)));
}
$dir = dirname(__FILE__);
require_once("{$dir}/CalendarItem.php");
require_once("{$dir}/CalendarMonthbox.php");
require_once("{$dir}/CalendarDay.php");
class CalendarWeek extends CalendarItem
{
    function __construct($events,
                        $startdate, // date of first included day
                        $startpos=1, // start position of first numbered day
                        $endnumber=31, // last possible day to number
                        $vsize=0,
                        $short=false // Compress vertically?
                        )
    {
        $this->registerVocabulary(array('startpos', 'startnumber', 'endnumber',
        'year', 'month', 'vsize', 'short', 'events'));
        parent::__construct();
        $this->setStartPos($startpos);
        $this->setStartDate($startdate);
        $this->setEndNumber($endnumber);
        $this->setVSize($vsize);
        $this->setShort($short);
        $this->events = $events;
    }

    public function setStartDate($startdate) {
        $startdate = strtotime($startdate);
        $this->startnumber = \time_getDay($startdate);
        $this->year = \time_getYear($startdate);
        $this->month = \time_getMonth($startdate);
    }

    public function setStartPos($startpos) {
        $this->startpos = $startpos;
    }
    private function setEndNumber($endnumber) {
        $this->endnumber = $endnumber;
    }
    private function setVSize($vsize) {
        $this->vsize = $vsize;
    }
    private function setShort($short) {
        $this->short = $short;
    }
    public function write($auth=0, &$special=array(),
                    $startdate=false,
                    $startpos=false,
                    $endnumber=false,
                    $vsize=false,
                    $short="novalue")
    {
        if ($startdate !== false) $this->setStartDate($startdate);
        if ($startpos !== false) $this->setStartPos($startpos);
        if ($endnumber !== false) $this->setEndNumber($endnumber);
        if ($vsize !== false) $this->setVSize($vsize);
        if ($short != "novalue") $this->setShort($short);

        $out = array();
        $out[] = "<tr class=\"calendarweek\" style=\"height:{$this->vsize}%;\">\n";
        $position = 1;
        if ($position == $this->startpos) $day = $this->startnumber;
        else $day = 0;
		for($i=1;$i < 8; $i++) {
            if ($day == 0 || $day > $this->endnumber) {
                $requested = array_shift($special);
                if (!$requested) $requested = "";
                if ($requested) $span = 1;
                elseif ($day == 0) $span = $this->startpos - $position;
                elseif ($day > $this->endnumber) $span = 7 - $position;
                $classes = array();
                $CalBox = new CalendarMonthbox($classes);
                $out[] = $CalBox->writeExtra($requested, $span);
                $position = $position + $span;
                if ($day == 0 && $position == $this->startpos)
                    $day = $this->startnumber;
                elseif ($position >= 7) break;
            } elseif ($i>=$this->startpos &&
                $day >= $this->startnumber &&
                $day <= $this->endnumber)
            {
                $CalDay = new CalendarDay(
                        mktime(0, 0, 0, $this->month, $day, $this->year),
                        getIndexOr($this->events, $day, array()), $short);
                $out[] = $CalDay->write($auth);
                $day++;
            }
        }
        $out[] = "</tr>\n";
        return implode("\n", $out);
    }
}


if (getGET('test') == 'flexical') {
    require_once("{$includeroot}/functions.php");
    require_once("{$dir}/testfunctions.php");
    echo "<h1>".__FILE__."</h1>";
    echo "<ol>";
    echo "<li>Creating an object...";
    $config = array('timezone' => -8+1, 'title_limit' => 8,
        'compact_title_limit' => 6, 'include_end_times' => true,
        'timefmt' => formattime('php'));
    $tz = date('Z')/60/60;
    $events = array(1 => array( // FIXME: Adjust time arithmetic to DateTime
        array('title'=>'Quart of milk', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>time(),
            'end_time'=>\time_add(time(), 0,30,1,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
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

    $cw = new CalendarWeek($config, $events, '2012-04-01', 1);
    echo "success.</li>";
    echo "<li>Writing test week from 4/1/2012 with end times...<br>";
    echo "<table>", $cw->write(1), "</table></li>";
    echo "<li>Writing test week from 5/1/2012 without end times...<br>";
    $config['include_end_times'] = false;
    $cw = new CalendarWeek($config, $events, '2012-05-01', 3);
    $special = array("Extra content", "Extra 2");
    echo "<table>", $cw->write(0, $special), "</table></li>";
    echo "<li>Writing test week from 7/29/2012...<br>";
    $config['include_end_times'] = true;
    $events[29] = $events[1];
    $events[31] = $events[2];
    $cw = new CalendarWeek($config, $events, '2012-07-29', 1);
    echo "<table>", $cw->write(0, $special), "</table></li>";
    echo "</ol>";
}

?>

