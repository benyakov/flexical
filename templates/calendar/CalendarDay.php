<?php
namespace flexical\calendar;
if (!isset($includeroot) &&
    basename($_SERVER['PHP_SELF']) == "CalendarDay.php")
{
    echo "Setting includeroot.";
    $includeroot = dirname(dirname(dirname(__FILE__)));
}
$dir = dirname(__FILE__);
require_once("{$dir}/CalendarMonthbox.php");
require_once("{$dir}/CalendarEvent.php");
class CalendarDay extends CalendarMonthbox
{

    function __construct($date, $events, $short=false) {
        global $dir;
        $this->registerVocabulary(array('date', 'year', 'month', 'day',
            'events', 'classes', 'h5data'));
        parent::__construct();
        $this->date = $date;
        $this->year = time_getYear($date);
        $this->month = time_getMonth($date);
        $this->day = time_getDay($date);
        $this->classes = array("day-cell");
        $this->h5data = array("date"=>strftime('%F', $date));
        $eventlist = array();
        if (! count($events)) $events = array();
        foreach ($events as $event) {
            if (is_object($event)) $eventlist[] = $event;
            else {
                $v = new CalendarEvent($event);
                $eventlist[] = $v;
            }
        }
        $this->events = $eventlist;
        if ($short) $self->classes[] = "shortcell";
        $tzdiff = (getIndexOr($this->config,'timezone',0)*60*60)+date('Z');
        if ($this->date == date('jnY', time()+$tzdiff))
            $this->classes[] = "today-cell";
    }

    public function write($auth=0) {
        $rv = array();
        // Write the day number as a heading
        $rv[] = "<h1 class=\"day-number\">";
        if ($auth>1)
            $rv[] = "<a class=\"eventform\" href=\"eventform.php?d={$this->day}&amp;m={$this->month}&amp;y={$this->year}\" title=\"{$this->year}-{$this->month}-{$this->day}\">{$this->day}</a>";
        else $rv[] = $this->day;
        $rv[] = "</h1>";
        // Figure out which events to display
        if ($this->config['title_limit'] < count($this->events))
            $titles = $this->config['title_limit'];
        else
            $titles = count($this->events);
        if ($this->config['compact_title_limit'] < count($this->events)) {
            $titles = $this->config['title_limit'];
            $compact_fmt = true;
        } else $compact_fmt = false;
        $eventswritten = array();
        $eventsdisplayed = array_slice($this->events, 0, $titles);
        // Write the chosen events into an array and output it
        foreach ($eventsdisplayed as $event)
            $eventswritten[] = $event->write($auth, $compact_fmt);
        if ($this->eventslength($eventswritten))
            $rv[] = implode("\n", $eventswritten);
        else
            $rv[] = "<p>&nbsp;</p>";
        //$rv[] = "</td>";
        return parent::writeExtra(implode("", $rv));
    }

    private function eventslength($strarray) {
        // Return the total length of all strings in $strarray
        for ($length=0; $strarray; $length+=strlen(array_pop($strarray)));
        return $length;
    }
}


if (getGET('test') == 'flexical') {
    require_once("{$includeroot}/functions.php");
    require_once("{$dir}/testfunctions.php");
    echo "<h1>".__FILE__."</h1>";
    echo "<ol>";
    echo "<li>Making an object with raw event data.</li>";
    $config = array('title_limit' => 8,
        'compact_title_limit' => 6, 'timefmt' => formattime('php'),
        'timezone' => -8+1);
    $date = time();
    $tz = date('Z')/60/60;
    $events = array( // Fix date arithmetic here to use DateTime objects
        array('title'=>'Test Event', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>new \DateTime(),
            'end_time'=>\time_add(time(), 4,30,0,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        array('title'=>'Test Event 2', 'all_day'=>false, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),2,0,0,0,0,0),
            'end_time'=>\time_add(time(),4,0,0,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz),
        array('title'=>'Test Event', 'all_day'=>true, 'related'=>null,
            'category'=>'Work', 'start_time'=>\time_add(time(),2,0,0,0,0,0),
            'end_time'=>null, 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>$tz)
        );
    $short = false;
    $cd = new CalendarDay($config, $date, $events, $short);
    writeAuth($cd, "<table><tr>", "</tr></table>");
    echo "</li></ol>";
}

// vim: set tags+=../../../**/tags foldmethod=indent :
?>
