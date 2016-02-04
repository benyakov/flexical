<?php

/*** Transformations ***/

function userTimeZone() {
    global $sprefix;
    if (isset($_SESSION[$sprefix]["authdata"])
        && isset($_SESSION[$sprefix]["timezone"])) {
        return new DateTimeZone($_SESSION[$sprefix]["authdata"]["timezone"]);
    } else {
        $configuration = getConfiguration();
        return new DateTimeZone($configuration['default_timezone']);
    }
}

function toCSSID($cat) {
    // Transform the given category name to a valid CSS class name
    $cat = preg_replace("/-/", "--", $cat);
    $cat = preg_replace("/[^A-Za-z0-9]/", "-", $cat);
    return $cat;
}

function categoryMatchString() {
    global $sprefix, $dbh;
    $rv = "";
    if (array_key_exists('categories', $_SESSION[$sprefix]) and
        is_array($_SESSION[$sprefix]['categories'])) {
        foreach ($_SESSION[$sprefix]['categories'] as $sesscat) {
            $sqlsesscat = $dbh->quote($sesscat);
            $rv .= " `c`.`name` = $sqlsesscat OR";
        }
        if ($rv) {
            $rv = 'AND ('.substr($rv, 0, -3).')';
        } else {
            // An impossible match
            $rv = "AND `c`.`name` = '\0x00' ";
        }
    }
    return $rv;
}

function getfilterclause($prefix) {
    global $sprefix, $dbh;
    // Get filter clauses, if any
    if (array_key_exists('filters', $_SESSION[$sprefix]) && $_SESSION[$sprefix]['filters']) {
        $filters = array();
        foreach ($_SESSION[$sprefix]['filters'] as $filter => $value) {
           $value = $dbh->quote($value);
           if ("title" == $filter) {
               $filters[] = "`$filter` REGEXP $value";
           } elseif ("text" == $filter) {
               $filters[] = "`$filter` REGEXP $value";
           } else {
               $filters[] = "`$filter` = $value";
           }
        }
        return "{$prefix} " . implode(" AND ", $filters);
    } else {
        return "";
    }
}

function buildopentag($tag, $classes) {
    // Return the HTML open tag with the given classes
    $rv = "<{$tag}";
    if ($classes) $rv .= " class=\"" . implode(" ", $classes) . "\"";
    return $rv .= ">";
}

function formattime($type="mysql") {
    global $sprefix;

    // Return time format according to current session setting.
    if ($type == "mysql") {
        if ($_SESSION[$sprefix]['timeformat'] == 12) {
            return "'%l:%i&nbsp;%p'";
        } elseif ($_SESSION[$sprefix]['timeformat'] == 24) {
            return "'%k:%i'";
        } else {
            return "'%l:%i'";
        }
    } elseif ($type == "php") {
        if ($_SESSION[$sprefix]['timeformat'] == 12) {
            return 'g:i\&\n\b\s\p;A';
        } elseif ($_SESSION[$sprefix]['timeformat'] == 24) {
            return "G:i";
        } else {
            return "g:i";
        }
    } elseif ($type == "mysql-tex") {
        if ($_SESSION[$sprefix]['timeformat'] == 12) {
            return "'%l:%i %p'";
        } elseif ($_SESSION[$sprefix]['timeformat'] == 24) {
            return "'%k:%i'";
        } else {
            return "'%l:%i'";
        }
    }
}

function slashme($v) {
    // Callback for mapping value arrays
    return addslashes($v);
}

// These can be inlined below in PHP 5.3
function mkname($a) { return "`{$a}`"; } // For PHP < 5.3
function mkpls($a) { return ":{$a}"; } // For PHP < 5.3
function assocToSQLInsert($array) {
    // Format $array into two string lists for an Insert statement
    $keys = array_keys($array);
    $names = array_map("mkname", $keys);
    $placeholders = array_map("mkpls", $keys);
    return array($placeholders, implode(',', $names), $keys);
}

