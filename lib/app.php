<?php
class FlexicalApp
{
/**** Providing encapsulation for shared data ****/

    public function __construct($installroot, $includeroot)
    {
        $this->installroot = $installroot;
        $this->includeroot = $includeroot;
    }

    public function genPageTemplate()
    {
?><!DOCTYPE html>
<html lang="<?=$language?>" ng-app>
    <head>
        <title><?=$configuration['site_title']?></title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <script type="text/javascript" src="lib/angular.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
        <script src="js/controllers.js"></script>
        <? // Add controllers from installed templates
        foreach (scandir("./templates") as $t) {
            if (! is_dir("./templates/{$t}")) continue;
            if (file_exists("./templates/{$t}/controllers.js") {
                ?><script src="templates/<?=$t?>/controllers.js"></script> 
                <?
            }
        }
        ?>
        <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
        <script type="text/javascript" language="JavaScript">
        $(document).ready(function() {
        });
        </script>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
        <link rel="stylesheet" type="text/css" href="css/categorystyles.css">
    </head>

    <body ng-controller="PageFrameCtrl">
        <div id="crosslinks"></div>
        <?=loginPanel();?>
        <div id="sitetabs_background">
        <nav>
        <ul id="sitetabs">
        <?= $this->genSiteTabs() ?>
        </ul>
        </nav>
        </div>
        <main>
        <div id="page">

        </div>
        </main>
    </body>
</html>
<?
    }

    public function genSiteTabs($action, $sitetabs) 
    {
        global $day, $month, $year, $length, $unit, $id;
        if (is_array($sitetabs)) {
            $tabs = $sitetabs;
        } else {
            $tabs = array(
                "calendar"=>0,
                "eventlist"=>0
            );
        }
        // Activate $action tab
        if (! array_key_exists($action, $tabs)) {
            $action = array_keys($tabs[0]);
        }
        $tabs[$action] = 1;
        // Suppress event tab if $id == -1 and not activated
        if (-1 == $id && array_key_exists('eventdisplay', $tabs)
            && ("eventdisplay" != $action)) {
            unset($tabs['eventdisplay']);
        }
        echo "<div id=\"sitetabs_background\">";
        echo "<ul id=\"sitetabs\">";
        foreach ($tabs as $name => $activated) {
            if ($activated) {
                $class = ' class="activated"';
            } else {
                $class = "";
            }
            $tabtext = __("tabtext-$name");
            echo "<li$class><a href=\"index.php?day={$day}&amp;month={$month}&amp;year={$year}&amp;length={$length}&amp;unit={$unit}&amp;action=$name\">$tabtext</a></li>\n";
        }
        echo "</ul></div>\n";
    }

    private function getDateSettings() {
        // TODO: set $now
        $sp = $this->sprefix;
        $day = ($_GET['day'])? 
            intval($_GET['day']) :
            (($_SESSION[$sp]['day'])? 
                $_SESSION[$sp]['day'] : 
                $now['mday']);
        $month = ($_GET['month'])? 
            intval($_GET['month']) :
            (($_SESSION[$sp]['month'])? 
                $_SESSION[$sp]['month']: 
                $now['mon']);
        $year = ($_GET['year'])? 
            intval($_GET['year']) :
            (($_SESSION[$sp]['year'])? 
                $_SESSION[$sp]['year']: 
                $now['year']);
        $id = ($_GET['id'])? 
            intval($_GET['id']) : 
            -1;
        $length = $_GET['length']? 
            intval($_GET['length']) : 
            $_SESSION[$sp]['length'];
        $unit = $_GET['unit']? 
            $_GET['unit'] : 
            $_SESSION[$sp]['unit'];
        $action = $_GET['action']? 
            $_GET['action'] : 
            $configuration['default_action'];
        $toggle = $_GET['toggle'];
        $current = $_GET['current'];

        if (! is_numeric($day)) {
            $_SESSION[$sp]['day'] = $now['mday'];
            setMessage(__('daynumeric')." ({$day})");
            header ("Location: http://".$serverdir."/index.php?action=$action&day={$now['mday']}&month=$month&year=$year&id=$id&length=$length&unit=$unit&opentime=$opentime");
        }
        if (! is_numeric($month)) {
            $_SESSION[$sp]['month'] = $now['month'];
            setMessage(__('monnumeric')." ({$month})");
            header ("Location: http://".$serverdir."/index.php?action=$action&day=$day&month={$now['mon']}&year=$year&id=$id&length=$length&unit=$unit&opentime=$opentime");
        }
        if (! is_numeric($year)) {
            $_SESSION[$sp]['year'] = $now['year'];
            setMessage(__('yearnumeric')." ({$year}-{$_GET['year']})");
            header ("Location: http://".$serverdir."/index.php?action=$action&day=$day&month=$month&year={$now['year']}&id=$id&length=$length&unit=$unit&opentime=$opentime");
        }

        // TODO: Move this into a file for the event view template
        if ($_GET['listsubmit'] && ! array_key_exists('opentime', $_GET)) {
            // coming from event view form, lack of opentime means false
            $opentime = 0;
        } elseif ($_GET['opentime']) {
            $opentime =  intval($_GET['opentime']);
        } elseif (array_key_exists('opentime', $_SESSION[$sp])) {
            $opentime = $_SESSION[$sp]['opentime'];
        } else {
            $opentime = $configuration['default_open_time'];
        }

    }

    private function setupSession() {
        $this->sprefix = realpath(dirname(__FILE__));
        session_name("FlexicalSession");
        session_set_cookie_params(604800, $this->installroot);
        session_start();
        if (! (array_key_exists($this->sprefix, $_SESSION) 
            && is_array($_SESSION))) 
        {
            $_SESSION[$this->sprefix] = array();
        }
    }
}
# vim: set tags+=../**/tags : 


