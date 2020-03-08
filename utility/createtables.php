<?php
try {
    $sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}eventstb` (
        `id` mediumint UNSIGNED NOT NULL auto_increment,
      `uid` tinyint UNSIGNED NOT NULL DEFAULT '0',
      `date` date NOT NULL,
      `all_day` tinyint NOT NULL default 0,
      `start_time` time NOT NULL default '00:00:00',
      `end_time` time NOT NULL default '00:00:00',
      `title` varchar(255) NOT NULL default '',
      `category` smallint NOT NULL default 0,
      `text` text NOT NULL,
      `related` mediumint unsigned,
      `timezone` varchar(255) NOT NULL default 'UTC',
      PRIMARY KEY  (`id`),
      INDEX (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8" ;
    $allsql[] = $sql;

    if (! isset($GEN_TABLEDESC)) {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec($sql);
    }

    $sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}users` (
      `uid` smallint NOT NULL auto_increment,
      `username` varchar(255) NOT NULL default '',
      `password` varchar(255) NOT NULL default '',
      `fname` varchar(255) NOT NULL default '',
      `lname` varchar(255) NOT NULL default '',
      `userlevel` tinyint NOT NULL default '0',
      `email` varchar(255) default NULL,
      `resetkey` varchar(255) default NULL,
      `resetexpiry` datetime default NULL,
      `timezone` varchar(255) NOT NULL default 'UTC',
      PRIMARY KEY  (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $allsql[] = $sql;
    if (! isset($GEN_TABLEDESC)) {
        $dbh->exec($sql);
    }

    $sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}categories` (
        `category` smallint NOT NULL auto_increment,
        `name` varchar(255) NOT NULL default '',
        `style` text,
        `restricted` tinyint default '0',
        `suppresskey` tinyint default '0',
        PRIMARY KEY (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $allsql[] = $sql;
    if (! isset($GEN_TABLEDESC)) {
        $dbh->exec($sql);
    }

    // FIXME: show_category_key is unused
    $sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}config` (
        `timestamp` datetime NOT NULL,
        `version_major` smallint NOT NULL,
        `version_minor` smallint,
        `version_tick`  smallint,
        `language` char(2),
        `site_title` varchar(255),
        `default_action` varchar(255),
        `title_limit` smallint,
        `compact_title_limit` smallint,
        `title_char_limit` smallint,
        `category_key_limit` smallint,
        `show_category_key` tinyint,
        `include_end_times` tinyint,
        `default_time` enum('twelve', 'twenty-four'),
        `default_open_time` tinyint,
        `cross_links` text,
        `email_from_address` varchar(255),
        `google_user` varchar(255),
        `google_password` varchar(255),
        `default_timezone` varchar(255) NOT NULL default 'UTC',
        `local_php_library` varchar(255),
        `authcookie_max_age` int NOT NULL default 0,
        `authcookie_path` varchar(255) default 'authcookies',
        `remotes` text,
        PRIMARY KEY (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $allsql[] = $sql;
    if (! isset($GEN_TABLEDESC)) {
        $dbh->exec($sql);
    }

    $sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}sitetabs-configa` (
        `timestamp` datetime,
        `value` varchar(255),
        `index` tinyint,
        FOREIGN KEY (`timestamp`) REFERENCES `{$tablepre}config`(`timestamp`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $allsql[] = $sql;
    if (! isset($GEN_TABLEDESC)) {
        $dbh->exec($sql);
    }

    $sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}reminders` (
        `uid` smallint NOT NULL,
        `eventid` mediumint UNSIGNED NOT NULL,
        `type` char(7),
        `days` smallint,
        PRIMARY KEY (`uid`, `eventid`),
        FOREIGN KEY (`eventid`) REFERENCES `{$tablepre}eventstb`(`id`)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $allsql[] = $sql;
    if (! isset($GEN_TABLEDESC)) {
        $dbh->exec($sql);
    }

} catch (Exception $e) {
    $dbh->rollBack();
    echo "Failed: " . $e->getMessage();
}

$sql = implode(";\n\n", $allsql);
$fh = fopen("./tabledesc.sql", "w");
fwrite($fh, $sql) or die(__('writesqlerror'));
fclose($fh) or die(__('writesqlerror'));

if (! isset($GEN_TABLEDESC)) {
    $configfile->set("dbversion", "{$version['major']}.{$version['minor']}.{$version['tick']}");
    $configfile->save();
    $dbh->commit();
}

if (isset($GEN_TABLEDESC)) {
    unset($GEN_TABLEDESC);
}
// vim: set tags+=../../**/tags :
