<?php
/* Singleton for holding the calendar's configuration
    Copyright (C) 2013 Jesse Jacobsen

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

    Send feedback or donations to: Jesse Jacobsen <jmatjac@gmail.com>

    Mailed donation may be sent to:
    Bethany Lutheran Church
    2323 E. 12th St.
    The Dalles, OR 97058
    USA
 */

class CalendarConfig {
    private static $instance;
    private static $configdb;
    public static $config = array();

    public function __construct($ver="") {
        if (self::$instance) {
            return self::$instance;
        } else {
            self::$instance = $this;
            if (! $ver) {
                global $version;
                $ver = $version;
            }
            self::setup($ver);
            return self::$instance;
        }
    }
    private function setup($version) {
        require_once("./utility/configdb.php");
        self::$configdb = new ConfigDB($version);
        self::$config = self::$configdb->newest();
    }
    public function getConfig() {
        return self::$config;
    }
    public function __call($name, $args) {
        if (! self::$configdb)
            $this->setup();
        return call_user_func_array(array(self::$configdb, $name), $args);
    }
}
