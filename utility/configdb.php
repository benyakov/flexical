<?php

class Configdb {

    protected $dbh = null;
    protected $version = null;

    function __construct($version) {
        $this->dbh = new DBConnection();
        $this->version = $version;
    }

    private function get_config_arrays() {
        // Return the table names of all configuration arrays
        $config_arrays = array();
        $q = $this->dbh->query("SHOW TABLES LIKE '{$this->dbh->getPrefix()}%-configa'");
        while ($row = $q->fetch()) {
            $config_arrays[] = $row[0];
        }
        return $config_arrays;
    }

    private function table_to_arrayname($tablename) {
        // Given the name of a table, return the array name within it
        $arrayname = substr($tablename, 0, strpos($tablename, '-configa'));
        $arrayname = substr($arrayname, strlen($this->dbh->getPrefix()));
        return $arrayname;
    }

    public function all() {
        // Return an array of arrays, each with all config info, newest first
        $this->dbh->beginTransaction();
        $configarrays = $this->get_config_arrays();
        $q = $this->dbh->query("SELECT * FROM `{$this->dbh->getPrefix()}config`
            ORDER BY `timestamp` DESC");
        $rv = array();
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $rv[$row['timestamp']] = $row;
        }
        foreach ($this->get_config_arrays() as $config_array) {
            $q = $this->dbh->query("SELECT `timestamp`, `value`
                FROM `{$config_array}`
                ORDER BY `timestamp`, `index`");
            $thisarray = array();
            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                $thisarray[$row['timestamp']][] = $row['value'];
            }
            foreach ($thisarray as $timestamp => $values) {
                $rv[$timestamp][$this->table_to_arrayname($config_array)] =
                    $values;
            }
        }
        $this->dbh->commit();
        return $rv;
    }

    public function newest() {
        // Return the newest config as an associative array,
        // with configuration arrays included under their names
        $this->dbh->beginTransaction();
        $q = $this->dbh->prepare("SELECT * FROM
            `{$this->dbh->getPrefix()}config`
            ORDER BY `timestamp` DESC LIMIT 1");
        if (! $q->execute()) {
            $this->dbh->rollBack();
            return false;
        }
        $rv = $q->fetch(PDO::FETCH_ASSOC);
        if (! $rv) {
            $this->dbh->rollBack();
            return false;
        }
        foreach ($this->get_config_arrays() as $config_array) {
            $q = $this->dbh->query("SELECT `value` FROM `{$config_array}`
                WHERE `timestamp` =
                (SELECT `timestamp` FROM `{$config_array}`
                ORDER BY `timestamp` DESC LIMIT 1)
                ORDER BY `index`");
            $thisarray = array();
            while ($value = $q->fetch()) {
                $thisarray[] = $value[0];
            }
            $rv[$this->table_to_arrayname($config_array)] = $thisarray;
        }
        $this->dbh->commit();
        return $rv;
    }

    function array_lookup($key, $before=null) {
        // Return an array for the config value $key, ordered by
        // the array's index.  It will be the newest as of $before,
        // which defaults to the present time.
        // Return an empty array if the array $key is not found
        if ($before == null) {
            $before = time();
        }
        $q = $this->dbh->prepare("SELECT `value`
            FROM `{$this->dbh->getPrefix()}{$key}-configa`
            WHERE `timestamp` = (
                SELECT `timestamp` FROM `{$this->dbh->getPrefix()}{$key}-configa`
                WHERE UNIX_TIMESTAMP(`timestamp`) <= :before
                ORDER BY `timestamp` DESC LIMIT 1)
            ORDER BY `index`");
        $q->bindParam(":before", $before);
        $q->execute();
        $rv = array();
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $rv[] = $row;
        }
        return $rv;
    }

    function simple_lookup ($key, $before=null) {
        // Lookup a config item by name.  It will be the newest as of $before,
        // which defaults to the present time.
        // Return empty string when not found.
        if ($before == null) {
            $before = time();
        }
        $q = $this->dbh->prepare("SELECT * FROM `{$this->dbh->getPrefix()}config`
            WHERE UNIX_TIMESTAMP(`timestamp`) <= :before
            ORDER BY `timestamp` DESC LIMIT 1");
        $q->bindParam(":before", $before);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (array_key_exists($key, $row)) {
            return $row[$key];
        } else {
            return "";
        }
    }

    public function lookup($key, $before=null) {
        // Lookup the newest config item by name.  Checks first for simple items, then an array
        if ($before == null) {
            $before = time();
        }
        if ($rv = $this->simple_lookup($key, $before)) {
            return $rv;
        } elseif ($rv = $this->array_lookup($key, $before)) {
            return $rv;
        } else {
            return false;
        }
    }

    public function newconfig ($settings) {
        // Save settings in a new configuration
        $settings['version_major'] = $this->version['major'];
        $settings['version_minor'] = $this->version['minor'];
        $settings['version_tick'] = $this->version['tick'];
        $this->dbh->beginTransaction();
        $q = $this->dbh->query("SELECT NOW()");
        $settings['timestamp'] = $q->fetchColumn();
        foreach ($settings as $k => $v) {
            if (is_array($v)) {
                $arrays[$k] = $v;
            } else {
                $simple[$k] = $v;
            }
        }
        list($tokenl, $names, $keyl) = assocToSQLInsert($simple);
        $placeholders_str = implode(', ', $tokenl);
        $q = $this->dbh->prepare("INSERT INTO `{$this->dbh->getPrefix()}config`
            ($names) VALUES ($placeholders_str)");
        foreach ($simple as $k => $v) {
            $q->bindValue(":{$k}", $v);
        }
        $q->execute();
        foreach ($arrays as $name => $items) {
            $q = $this->dbh->prepare("INSERT INTO `{$this->dbh->getPrefix()}{$name}-configa`
                (`timestamp`, `value`, `index`)
                VALUES (:timestamp, :value, :index)");
            $q->bindParam(":timestamp", $settings['timestamp']);
            foreach ($items as $index => $value) {
                $q->bindValue(":index", $index);
                $q->bindValue(":value", $value);
                $q->execute() or die(array_pop($q->errorInfo()));
            }
        }
        $this->dbh->commit();
    }

    public function restore ($timestamp) {
        // Restore the given configuration settings to the top of the list
        // Return the entire config
        $q = $this->dbh->prepare("UPDATE `{$this->dbh->getPrefix()}config`
            SET `timestamp` = NOW()
            WHERE `timestamp` = :timestamp");
        $q->bindParam(":timestamp", $timestamp);
        $q->execute();
    }
}


// vim: set tags+=../**/tags :
