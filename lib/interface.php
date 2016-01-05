<?php

function dayBox($day, $formname="") {
    echo "<div class=\"daybox\"><input type=\"number\" min=\"1\" max=\"31\" name=\"day\" class=\"dayinput\" value=\"$day\"></div>\n";
}

function monthBox($month, $formname="") {
    echo "<div class=\"daybox\"><input type=\"number\" min=\"1\" max=\"12\" name=\"month\" class=\"monthinput\" value=\"$month\"></div>\n";
}

function yearBox($year, $formname="") {
    echo "<div class=\"yearbox\"><input type=\"number\" name=\"year\" class=\"yearinput\" value=\"{$year}\" min=\"1900\" max=\"2100\"></div>\n";
}

function hourBox($hour, $formname, $name, $disabled=0, $classes="") {
    if ($disabled) {
        $disabled = " disabled ";
    } else {
        $disabled = "";
    }
    echo "<div class=\"hourbox {$classes}\"><input type=\"number\" name=\"{$name}\" class=\"hourinput\" placeholder=\"-\" min=\"0\" max=\"23\" value=\"$hour\" $disabled></div>\n";
}
function minuteBox($minute, $formname, $name, $disabled=0, $classes="") {
    if ($disabled) {
        $disabled = " disabled ";
    } else {
        $disabled = "";
    }
    echo "<div class=\"minutebox {$classes}\"><input type=\"number\" name=\"{$name}\" class=\"minuteinput\" min=\"0\" max=\"59\" placeholder=\"-\" value=\"{$minute}\" $disabled></div>\n";
}

function checkBox($name, $val) {
    if ($val=="1" || $val==1 || $val==true) {
        $checked = " checked ";
    } else {
        $checked = "";
    }
    echo "<input type=\"checkbox\" name=\"{$name}\" value=\"1\" {$checked}>";
}

function lengthBox($length, $formname="") {
    echo "<div class=\"lengthbox\"><input type=\"number\" name=\"length\" value=\"$length\" min=\"1\" class=\"lengthinput\"></div>\n";
}

function pullDown($unit, $unitarray, $name, $values=array()) {
    echo "\n<select name=\"{$name}\" id=\"{$name}\" style=\"padding-right: 8px;\">\n";
    for($i=0;$i < count($unitarray); $i++) {
        $thisvalue = array_key_exists($i, $values)?$values[$i]:$i+1;
        if (($unit === $thisvalue) or
            (is_numeric($unit) && $unit == $thisvalue)) {
            // echo "Unit is $unit.";
            echo "  <option value=\"" . $thisvalue . "\" selected>$unitarray[$i]</option>\n";
        } else {
            echo "  <option value=\"" . $thisvalue . "\">$unitarray[$i]</option>\n";
        }
    }
    echo "</select>\n\n";
}

function monthPullDown($month, $montharray) {
    pullDown($month, $montharray, "month");
}

function yearPullDown($year) {
    $min = $year - 100;
    $max = $year + 100;
    echo "\n<input type=\"number\" name=\"year\" id=\"year\" min=\"{$min}\" max=\"{$max}\" value=\"{$year}\" class=\"yearinput\">";
}

function dayPullDown($day) {
    $dayrange = range(1, 31);
    pullDown($day, $dayrange, "day", $dayrange);
}

function amPmPullDown($pm, $namepre, $blank=false, $disabled, $classes="") {
    if ($disabled) {
        $disabled = " disabled ";
    } else {
        $disabled = "";
    }
    if (! $blank) {
        if ($pm) { $pm = " selected"; } else { $am = " selected"; }
    } else { $nothing = " selected"; }
    echo "\n<select class=\"am-pm-pulldown {$classes}\" name=\"" . $namepre . "_am_pm\" $disabled>\n";
    echo "  <option value=\"-\"$nothing>-</option>\n";
    echo "  <option value=\"0\"$am>am</option>\n";
    echo "  <option value=\"1\"$pm>pm</option>\n";
    echo "</select>\n\n";
}

function categoryCheckBoxes($columnTitles, $checkCurrent=0,
    $checkHidden=0, $checkKeySuppressed=0, $renameBoxes=False,
    $styleBoxes=False) {
    // Gather and return n+1 -item rows of a table, where the first n
    // rows have columnTitles as headings, and each cell is a checkbox
    // with name "category-title".

    // - If checkCurrent > 0, all checkboxes in that column will reflect
    // the state of the currently selected categories
    // (in $_SESSION[$sprefix]['categories']).
    // - Likewise, if $checkHidden > 0, the
    // checkboxes in that column will reflect the state of the currently
    // hidden categories in the categories table.
    // - So also, if $checkKeySuppressed > 0, the checkboxes in that column
    // will reflect the state of the categories that will not appear in the key.
    // - If $renameBoxes is true, then each category will have a textbox for
    // renaming the category.
    // - If $styleBoxes is true, then each category will have another textbox
    // for showing/altering the style in the categories table.

    global $tablepre, $sprefix, $dbh;
    $_ = "__";

    $q = $dbh->query("SELECT `name`, `restricted`, `suppresskey`, `style`
            FROM `{$tablepre}categories` ORDER BY `name`");
    // Column Headings
    $rv = "<tr>";
    foreach ($columnTitles as $heading) {
        $rv .= "<th><div class=\"checkbox-heading\">{$heading}</div></th>";
    }
    $rv .= "<th></th>";
    if ($renameBoxes) {
        $rv .= "<th>{$_('newname')}</th>";
    }
    if ($styleBoxes) {
        $rv .= "<th>{$_('style')}</th>";
    }
    $rv .= "</tr>\n";
    // Check All Boxes checkbox for each column
    $rv .= "<tr>";
    foreach ($columnTitles as $heading) {
        $heading = toCSSID($heading);
        $rv .= "  <td align=\"center\"><a class=\"tinybutton\" onclick=\"checkAll(this, '{$heading}');\" href=\"javascript: void(0);\">{$_('all')}</a> <a class=\"tinybutton\" onclick=\"uncheckAll(this, '{$heading}');\" href=\"javascript: void(0);\">{$_('none')}</a></td>\n";
    }
    $rv .= "  <td colspan=\"3\">{$_('checkall')}</td></tr>\n";

    // Each row of checkboxes, the name, name changer, and style
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $catname = $row["name"];
        $formname = toCSSID($catname);
        $restricted = $row["restricted"];
        $suppressed = $row["suppresskey"];
        $rv .= "<tr>";

        for ($x=1; $x<=count($columnTitles); $x++) {
            $heading = toCSSID($columnTitles[$x-1]);
            // Set Checkbox Status
            $status = "";
            if ($checkCurrent == $x &&
                array_key_exists('categories', $_SESSION[$sprefix]) &&
                is_array($_SESSION[$sprefix]['categories']) &&
                in_array($catname, $_SESSION[$sprefix]['categories'])) {
                    $status = " CHECKED";
            } elseif ($checkHidden == $x && $restricted) {
                $status = " CHECKED";
            } elseif ($checkKeySuppressed == $x && $suppressed) {
                $status = " CHECKED";
            }
            // Write the checkbox
            $rv .= "<td align=\"center\"><input type=\"checkbox\" name=\"{$formname}-{$heading}\"$status></td>";
        }
        $rv .= "<td align=\"left\" class=\"categorydemo\"><span class=\"".toCSSID($catname) . "\">{$catname}</span></td>";
        if ($renameBoxes) {
            $rv .= "<td align=\"center\"><input type=\"text\" name=\"{$formname}-name\"/></td>";
        }
        if ($styleBoxes) {
            $rv .= "<td align=\"center\"><input type=\"text\" name=\"{$formname}-style\" class=\"styleinput\" value=\"{$row['style']}\"/></td>";
            $_SESSION[$sprefix]['categorystyles'][$catname] = $row['style'];
        }
        $rv .= "</tr>\n";
    }
    return $rv;
}

function categoryPullDown($cat="", $multiple=false) {
    global $tablepre, $dbh;
    $q = $dbh->query("SELECT `name` FROM `{$tablepre}categories`
        ORDER BY `name`");
    if ($multiple) {
        $multi = " MULTIPLE";
    } else {
        $multi = "";
    };
    echo "\n<select name=\"category\" required$multi>\n";
    if ("" == $cat)
        echo "  <option value=\"-\" disabled selected>-</option>\n";
    $_ = "__";
    if (!$multiple) {
        echo "  <option value=\"{$_('new-category')}\">{$_('new-category')}</option>\n";
    }
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $catname = $row["name"];
        if ($catname == $cat) {$sel = " selected"; } else {$sel = ""; };
        echo "  <option value=\"$catname\"$sel>$catname</option>\n";
    }
    echo "</select>\n\n";
}

function monthmenu() {
    // Include a hidden monthmenu div
    echo "<div id=\"MonthMenu\" style=\"display: none\"></div>";
}

function loginPanel() {
    global $sprefix;
    ob_start();
    $d = $_SESSION[$sprefix]['day'];
    $m = $_SESSION[$sprefix]['month'];
    $y = $_SESSION[$sprefix]['year'];
    $l = $_SESSION[$sprefix]['length'];
    $u = $_SESSION[$sprefix]['unit'];
    $action = $_SESSION[$sprefix]['action'];
    if (isset($_SESSION[$sprefix]['id']))
        $idtext = "&id={$_SESSION[$sprefix]['id']}";
    else $idtext = "";
    if (authType() == "cookie")
        $authindicator = "*";
    else
        $authindicator = "";
    $auth = auth(); ?>
    <div id="login">
    <?php if ($auth !== false) { ?>
        <a href="login.php?action=logout&amp;day=<?=$d?>&amp;month=<?=$m?>&amp;year=<?=$y?>&amp;length=<?=$l?>&amp;unit=<?=$u?>&amp;view=<?=$action?><?=$idtext?>"><?=__('logout')?></a> <div class="username"><?=$_SESSION[$sprefix]['authdata']['fullname'].$authindicator?></div>
    <?php } else { ?>
        <form action="login.php?action=login&view=<?=$action?>&month=<?=$m?>&day=<?=$d?>&year=<?=$y?>&length=<?=$l?>&unit=<?=$u?><?=$idtext?>" method="post">
        <label class="login_label" for="username"><?=__('username')?></label>
        <input class="username" type="text" name="username" required>
        <label class="login_label"><?=__('password')?></label>
        <input type="password" name="password" required>
        <button type="submit"><?=__('login')?></button><br>
        <a title="<?=__('forgot password')?>" href="resetpw.php"><?=__('forgot password')?></a> |
        <a title="<?=__('setup subscriber')?>" href="useradmin.php?flag=add"><?=__('setup subscriber')?></a>
        </form>
    <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function topMatter($action, $sitetabs) {
    $configuration = getConfiguration();
    ob_start();
    if ($configuration['cross_links']) { ?>
        <div id="crosslinks"> <?php
        $crosslinks = json_decode($configuration['cross_links'], true);
        foreach ($crosslinks as $cltitle=>$linkval) { ?>
            <a href="<?=$linkval?>" title="<?=$cltitle?>"><?=$cltitle?></a>
        <?php } ?>
        </div>
    <?php }
    echo loginPanel();
    showMessage();
    sitetabs($action, $sitetabs);
    return ob_get_clean();
}

function sitetabs($action, $sitetabs) {
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
    $tabs[$action] = 1;
    /* Suppress event tab if it's not the current action.
     * unless it's in the site configuration.
     */
    $configuration = getConfiguration();
    if ("eventdisplay" != $action &&
        ! in_array('eventdisplay', $configuration['sitetabs']))
        unset($tabs['eventdisplay']);
    echo "<div id=\"sitetabs_background\">";
    echo "<ul id=\"sitetabs\">";
    foreach ($tabs as $name => $activated) {
        if ($activated) {
            $class = ' class="activated"';
        } else {
            $class = "";
        }
        $tabtext = htmlspecialchars(__("tabtext-$name"));
        echo "<li$class><a href=\"index.php?day={$day}&amp;month={$month}&amp;year={$year}&amp;length={$length}&amp;unit={$unit}&amp;action=$name\">$tabtext</a></li>\n";
    }
    echo "</ul></div>\n";
}

function footprint($auth) {
    global $sprefix;
    $configuration = getConfiguration();
    require('version.php');
    $d = $_SESSION[$sprefix]['day'];
    $m = $_SESSION[$sprefix]['month'];
    $y = $_SESSION[$sprefix]['year'];
    $l = $_SESSION[$sprefix]['length'];
    $u = $_SESSION[$sprefix]['unit'];
    if ($_SESSION[$sprefix]['id'])
        $idtext = "&id={$_SESSION[$sprefix]['id']}";
    else $idtext = "";

    $action = $_SESSION[$sprefix]['action'];
    // Always show ?>
    <ul class="hbuttons footprint">
    <li><a href="help/?n=Contents.<?=$configuration['language']?>.txt"><?=__('help')?></a> <?=__('manual')?></li>
    <li><a href="categorychooser.php"><?=__('Choose')?></a>
       <?=__('categories').": ".count($_SESSION[$sprefix]['categories']).'/'.
       count($_SESSION[$sprefix]['allcategories'])?>
    </li>
    <li><a class="half" href="filter.php"><?=__('filter')?></a><?php
    if (array_key_exists('filters', $_SESSION[$sprefix]) && $_SESSION[$sprefix]['filters']) {
        ?><a class="half" href="filter.php?unfilter=1"><?=__('unfilter')?></a> <?php echo __('Events Filtered');
    } else {
        ?><a class="half deactivated" href="javascript:void(0);"><?=__('unfilter')?></a> <?php
            echo __('Events Unfiltered');
        }
    ?></li>
    <li><a href="<?=$_SERVER['PHP_SELF']?>?toggle=time"><?=__('toggle')?></a> <?=__('timeformat').": {$_SESSION[$sprefix]['timeformat']} "?></li>
    <li><a href="<?=$_SERVER['PHP_SELF']?>?toggle=usertz"><?=__('toggle')?></a><?=__('usertz').": ".__($_SESSION[$sprefix]['usertz'])?></li>
    </ul><?php
    if ( $auth >= 2 ) { // Show for event editors ?>
        <ul class="hbuttons footprint">
        <li><a href="useradmin.php?flag=changepw"><?=$auth==3?__('useradmin'):__('changepw')?></a> </li>
        <li><a href="eventform.php"><?=__('addanevent')?></a> </li>
        </ul> <?php
    }
    if ( $auth >= 3 ) { // Show for admin ?>
        <ul class="hbuttons footprint">
        <li><a href="index.php?admin=configure"><?=__('configure')?></a></li>
        <li><a href="categoryadmin.php"><?=__('modcategories')?></a></li>
        <li><a href="index.php?admin=backup"><?=__('DBDump')?></a> </li>
        <li><a href="index.php?admin=restore"><?=__('DBRestore')?></a> </li>
        <li><a href="index.php?action=customstyles"><?=__('customstyles')?></a>
        </li>
        </ul> <?php
    } ?>
    <p class="attribution"><a href="http://www.christfor.us/flexical.html">FlexiCal</a> <?="{$version['major']}.{$version['minor']}.{$version['tick']}"?></p>
    <?php
}

function scrollArrows($d, $m, $y, $length, $unit, $action) {
    if ($action=="calendar") {
        // set variables for month scrolling
        $nextyear = ($m != 12) ? $y : $y + 1;
        $prevyear = ($m != 1) ? $y : $y - 1;
        $prevmonth = ($m == 1) ? 12 : $m - 1;
        $nextmonth = ($m == 12) ? 1 : $m + 1;

        $s = "<a href=\"index.php?month=$prevmonth&amp;year=$prevyear&amp;action=$action\">\n";
        $s .= "<img src=\"images/leftArrow.gif\" border=\"0\" alt=\"Prev\"></a> ";
        $s .= "<a href=\"index.php?month=$nextmonth&amp;year=$nextyear&amp;action=$action\">";
        $s .= "<img src=\"images/rightArrow.gif\" border=\"0\" alt=\"Next\"></a>";

        return $s;
    } elseif ($action == 'eventlist') {
        $highdate = time_add(mktime(0,0,0,$m,$d,$y),0,0,0,
            $unit==1?$length:       // "days"; see __('units')
            ($unit==2?$length*7:0),   // "weeks"
            $unit==3?$length:0,     // "months"
            $unit==4?$length:0);     // "years"
        $lowdate = time_sub(mktime(0,0,0,$m,$d,$y),0,0,0,
            $unit==1?$length:       // "days"; see __('units')
            ($unit==2?$length*7:0),   // "weeks"
            $unit==3?$length:0,     // "months"
            $unit==4?$length:0);     // "years"
        $next = getdate($highdate);
        $prev = getdate($lowdate);

        $s = "<a href=\"index.php?month={$prev['mon']}&amp;year={$prev['year']}".
            "&amp;day={$prev['mday']}&amp;action=$action\">\n";
        $s .= "<img src=\"images/leftArrow.gif\" border=\"0\" alt=\"Prev\"></a> ";
        $s .= "<a href=\"index.php?month={$next['mon']}&amp;year={$next['year']}".
            "&amp;day={$next['mday']}&amp;action=$action\">\n";
        $s .= "<img src=\"images/rightArrow.gif\" border=\"0\" alt=\"Next\"></a>";

        return $s;
    }
}


// vim: set foldmethod=indent tags+=../../tags :
