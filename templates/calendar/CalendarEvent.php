<?php
namespace flexical\calendar;
if (!isset($includeroot) &&
    basename($_SERVER['PHP_SELF']) == "CalendarEvent.php")
{
    echo "Setting includeroot.";
    $includeroot = dirname(dirname(dirname(__FILE__)));
}
$dir = dirname(__FILE__);
require_once("{$dir}/CalendarItem.php");
require_once("{$includeroot}/Exceptions.php");
require_once("{$includeroot}/functions.php");
class CalendarEvent extends CalendarItem
{
    public function __construct($evt_data) {
        global $sprefix;
        $this->registerVocabulary(array('title', 'all_day', 'related',
            'category', 'start_time', 'end_time', 'restricted',
            'usertz_start_time', 'usertz_end_time',
            'suppress_key', 'id', 'timezone', 'timefmt', 'urlbase', 'remoteid'));
        parent::__construct();
        foreach ($evt_data as $k => $v) {
            if (in_array($k, $this->vocabulary)) $this->val[$k] = $v;
            else throw new \TemplateException("Unknown event data: {$k}");
        }
        if ($_SESSION[$sprefix]["usertz"] == "on") {
            $this->val['start_time'] = $evt_data['usertz_start_time'];
            $this->val['end_time'] = $evt_data['usertz_end_time'];
        }
    }

    protected function userTZStartTime() {
        return $this->val['start_time']->format(\formattime('php'));
    }

    protected function userTZEndTime() {
        return $this->val['end_time']->format(\formattime('php'));
    }

    public function write($auth=0, $compactfmt=false) {
        global $sprefix;
        if ($this->restricted && (! $auth)) return "";
        $out = array();
        if ($compactfmt) $compact_text = " compact";
        else $compact_text = "";
        $out[] = "<p class=\"title-txt{$compact_text}\"><span class=\"".
            $this->categoryClass();
        if (0 == $this->id) {
            $out[] = " external-category\">";
        } else {
            $out[] = "\">";
        }
        if ($auth > 1 && $this->id != 0) {
            $onclick = "class=\"menuanchor\" data-event-id=\"{$this->id}\" "
                ."data-event-related=\"{$this->related}\"";
        }
        if (! $this->all_day) {
            if ($auth > 1) {
                if ($this->config['include_end_times']) {
                    // Format with begin and end times
                    if (0 == $this->id) {
                        $time =
                            "<div class=\"end-time-str\">&#8203;"
                            ."<a href=\"{$this->urlbase}?action=eventdisplay&id={$this->remoteid}\">"
                            .$this->userTZStartTime()."-".$this->userTZEndTime()
                            ."</a>"
                            ."</div>";
                    } else {
                        $time =
                            "<div class=\"end-time-str\">"
                            ."<a href=\"copyform.php?id={$this->id}\" "
                            ."{$onclick} title=\"Copy\">&nbsp;</a>&#8203;"
                            ."<a href=\"eventform.php?id={$this->id}\" "
                            ."{$onclick} title=\"Edit\">"
                            .$this->userTZStartTime()."-".$this->userTZEndTime()
                            ."</a></div>";
                    }
                } else {
                    // Format with start time only
                    if (0 == $this->id) {
                        $out[] = "<span class=\"start-time-str\">"
                            ."<a href=\"{$this->urlbase}?action=eventdisplay&id={$this->remoteid}\">";
                    } else {
                        $out[] = "<span class=\"start-time-str\">"
                            ."<a href=\"eventform.php?id={$this->id}\" "
                            ."{$onclick} title=\"Edit\">";
                    }
                    $out[] = $this->userTZStartTime();
                    if (0 == $this->id) {
                        $out[] = "</a>&#8203; </span>";
                    } else {
                        $out[] = "</a>"
                            ."<a href=\"copyform.php?id={$this->id}\""
                            ."{$onclick} title=\"Copy\"> </a>&#8203;</span>";
                    }
                }
            } else {
                $onclick="";
                if ($this->config['include_end_times']) {
                    $time = "<div class=\"end-time-str\">"
                        ."<a href=\"{$this->urlbase}?action=eventdisplay&id={$this->remoteid}\">"
                        .$this->userTZStartTime()."-".$this->userTZEndTime()
                        ."</a>"
                        ."</div>";
                } else {
                    $out[] = $this->userTZStartTime()." ";
                }
            }
        }
        if (0 != $this->id) {
            $out[] = " <a href=\"index.php?action=eventdisplay&id={$this->id}\" "
                ."title=\"{$this->title}/{$this->category}\" {$onclick}>";
        } else {
            $out[] = " <a href=\"{$this->urlbase}?action=eventdisplay&id={$this->remoteid}\">";
        }
        $out[] = $this->title;
        $out[] = "</a></span></p>";
        if ((! $this->all_day) && $this->config['include_end_times']) {
            $out[] = $time;
        }
        return implode("", $out);
    }

    public function keyitem() {
        if (! $this->suppresskey) {
            return " <li><span class=\"".$this->categoryClass()."\">{$cat}</span></li>\n";
        } else return "";
    }
    protected function categoryClass() {
        return \toCSSID($this->category);
    }
}

if (getGET('test') == 'flexical') {
    $sessiondata = $_SESSION[$sprefix];
    require_once("{$includeroot}/templates/calendar/testfunctions.php");
    try {
        echo "<h1>".__FILE__."</h1>";
        echo "<ol>";
        $time = time();
        echo "<li>Making an object with time ".strftime(formattime('php'), $time)."</li>";
        // FIXME: Fix time/date arithmetic to use DateTime Objects
        $eventdata = array('title'=>'Test Event', 'all_day'=>false,
            'related'=>null,
            'category'=>'Work', 'start_time'=>$time,
            'end_time'=>\time_add(time(), 4,30,0,0,0,0), 'restricted'=>false,
            'suppress_key'=>false, 'id'=>1, 'tz'=>-8+1);
        $configdata = array('include_end_times'=>true,
            'timezone'=>-8+1, 'timefmt' => formattime('php'));
        $_SESSION[$sprefix]['timeformat'] = 12;
        $ce = new CalendarEvent($configdata, $eventdata);
        writeAuth($ce);
        $configdata['include_end_times'] = false;
        $_SESSION[$sprefix]['timeformat'] = 24;
        $ce = new CalendarEvent($configdata, $eventdata);
        writeAuth($ce);
        $configdata['all_day'] = true;
        writeAuth($ce);
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }
    echo "</ol>";
    $_SESSION[$sprefix] = $sessiondata;
}
?>
