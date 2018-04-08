<?php

class AuthKeeper {
    // v. 1.0

    private static $instance;
    private static $approved;

    public function __construct($login='', $passwd='') {
        if (! self::$instance) {
            self::$instance = $this;
            self::$approved = self::testauth($login, $passwd);
        } elseif ($login === "false") {
            self::$approved = 0;
            self::$initialized = "";
        } elseif ($login && $passwd) {
            self::$approved = self::testauth($login, $passwd);
        }
        return self::$instance;
    }

    public function auth() {
        return self::$approved;
    }

    private function testauth($login, $passwd) {
        global $sprefix;
        $dbh = new DBConnection();

        $authdata = getIndexOr($_SESSION[$sprefix],'authdata');
        if (is_array($authdata) && empty($login)
            && $authdata["authtype"] == "password")
        { // Verify that the session contains a valid login
            $authdata = $_SESSION[$sprefix]['authdata'];
            $q = $dbh->prepare("SELECT 1 FROM `{$dbh->getPrefix()}users`
                WHERE `username` = :login AND `password` = :password
                AND `uid` = :uid AND `userlevel` = :userlevel
                AND CONCAT_WS(' ', `fname`, `lname`) = :fullname");
            $q->bindValue(':login', $authdata["login"]);
            $q->bindValue(':password', $authdata["password"]);
            $q->bindValue(':uid', $authdata["uid"]);
            $q->bindValue(':userlevel', $authdata["userlevel"]);
            $q->bindValue(':fullname', $authdata["fullname"]);
            $q->execute();
            if ($q->fetch()) return $authdata["userlevel"];
            else return 0;
        } elseif (!empty($login))
        { // Check supplied credentials
            $check = $login;
            $q = $dbh->prepare("SELECT username, password,
                userlevel, uid, fname, lname, timezone
                FROM `{$dbh->getPrefix()}users`
                WHERE `username` = :check");
            $q->bindParam(':check', $check);
            if (! $q->execute()) die(array_pop($q->errorInfo()));
            $row = $q->fetch(PDO::FETCH_ASSOC);
            if ( $row["password"] == crypt($passwd, $row["password"])) {
                $_SESSION[$sprefix]["authdata"] = array(
                    "fullname"=>"{$row['fname']} {$row['lname']}",
                    "login"=>$row["username"],
                    "password"=>$row["password"],
                    "userlevel"=>$row["userlevel"],
                    "timezone"=>$row['timezone'],
                    "authtype"=>"password",
                    "uid"=>$row["uid"]);
                $this->authcookie(true);
                return $row["userlevel"];
            } else {
                unset( $_SESSION[$sprefix]['authdata'] );
                return false;
            }
        } elseif (! $this->authcookie())
        { // Not logged in
            unset($_SESSION[$sprefix]['authdata']);
            return false;
        } else
        { // Logged in with extended cookie authorization
            return $_SESSION[$sprefix]['authdata']['userlevel'];
        }
    }

    public function logout() {
        global $sprefix;
        unset($_SESSION[$sprefix]['authdata']);
        $this->authcookie(false);
    }

    private function authcookie($authorized=null) {
        // Set the authorization cookies, if $authorized or not.
        // Return whether valid auth cookie exists.
        global $sprefix;
        $dbh = new DBConnection();
        $dbp = $dbh->getPrefix();
        $max_age = getAuthCookieMaxAge();
        $this->checkAuthCookiesDir();
        if (is_null($authorized)) {
            // Check cookie
            if (! (isset($_COOKIE['auth']) &&
                file_exists("{$this->getAuthCookiePath()}/{$_COOKIE['auth']['user']}")))
            {
                return false;
            }
            $userdir = "{$this->getAuthCookiePath()}/{$_COOKIE['auth']['user']}";
            // Comb user's auth tokens and remove expired ones
            $userdirp = opendir($userdir);
            while ($seriesfile = readdir($userdirp)) {
                if (in_array($seriesfile, array('.', '..'))) continue;
                if (time() - filemtime("{$userdir}/{$seriesfile}")
                    > $max_age)
                    @unlink("{$userdir}/{$seriesfile}");
            }
            closedir($userdirp);
            // Check against saved auth tokens
            if (! (isset($_COOKIE['auth']['series']) &&
                file_exists("{$userdir}/{$_COOKIE['auth']['series']}")))
            {
                return false;
            }
            $token = file_get_contents("{$userdir}/{$_COOKIE['auth']['series']}");
            if (! $_COOKIE['auth']['token'] == $token) {
                setMessage("Someone has stolen your session. Check your security! Forgetting all of your remembered sessions.");
                return false;
            }
            $q = $dbh->prepare("SELECT fname, lname, username, uid,
                userlevel, password FROM `{$dbp}users`
                WHERE `username` = :check");
            $q->bindValue(':check', $_COOKIE['auth']['user']);
            if (! $q->execute()) die(array_pop($q->errorInfo()));
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $_SESSION[$sprefix]["authdata"] = array(
                "fullname"=>"{$row['fname']} {$row['lname']}",
                "login"=>$_COOKIE['auth']['user'],
                "password"=>$row["password"],
                "uid"=>$row["uid"],
                "userlevel"=>$row["userlevel"],
                "authtype"=>"cookie");
            $this->setAuthCookie($_COOKIE['auth']['user'], $_COOKIE['auth']['series'],
                $max_age);
            return true;
        }
        if ($authorized) {
            if (isset($_COOKIE['auth']['series'])) $series = $_COOKIE['auth']['series'];
            else $series = $this->genCookieSeriesString();
            $this->setAuthCookie($_SESSION[$sprefix]["authdata"]["login"], $series,
                $max_age);
        } else {
            $this->delAuthCookie();
        }
        return false;
    }

    private function getCookiePath() {
        global $installroot;
        if ("authcookies" != $this->getAuthCookiePath()) return "/";
        else return $installroot;
    }

    private function setAuthCookie($user, $series, $age) {
        $cookiepath = $this->getCookiePath();
        $this->checkAuthCookiesDir($user);
        $token = $this->genCookieAuthString();
        $timestamp = time()+$age;
        setcookie('auth[series]', $series, $timestamp, $cookiepath);
        setcookie('auth[token]', $token, $timestamp, $cookiepath);
        setcookie('auth[user]', $user, $timestamp, $cookiepath);
        file_put_contents("{$this->getAuthCookiePath()}/{$user}/{$series}", $token);
    }

    private function checkAuthCookiesDir($user="") {
        if ($user) $user = DIRECTORY_SEPARATOR . $user;
        if (! file_exists("{$this->getAuthCookiePath()}{$user}"))
            mkdir("{$this->getAuthCookiePath()}{$user}");
        if (! $user)
            file_put_contents("{$this->getAuthCookiePath()}".DIRECTORY_SEPARATOR.".htaccess",
                "Order Deny,Allow\nDeny from all\n");
    }

    private function genCookieAuthString() {
        return substr(str_shuffle('01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_.~'), 0, 28);
    }

    private function genCookieSeriesString() {
        return sha1($_SERVER['HTTP_USER_AGENT']."A trusty shield and weapon");
    }

    private function delAuthCookie() {
        $cookiepath = $this->getCookiePath();
        if ($_COOKIE['auth']['user']) {
            $acf = "{$this->getAuthCookiePath()}/{$_COOKIE['auth']['user']}/{$_COOKIE['auth']['series']}";
            @unlink($acf);
            $timestamp = time()-3600;
            setcookie('auth[user]', '', $timestamp, $cookiepath);
            setcookie('auth[series]', '', $timestamp, $cookiepath);
            setcookie('auth[token]', '', $timestamp, $cookiepath);
        }
    }

    private function getAuthCookiePath() {
        // Gets the current path for auth cookies.
        $config = getConfiguration();
        if ($config['authcookie_path']) return $config['authcookie_path'];
        else return "authcookies";
    }

}

function auth($login='', $passwd='') {
    $ak = new AuthKeeper($login, $passwd);
    return $ak->auth();
}

function getAuthCookieMaxAge() {
    // Gets the current maximum age of an auth cookie in seconds.
    $config = getConfiguration();
    return $config['authcookie_max_age']*60*60*24;
}

function authType() {
    // Return the contents of _SESSION -> authtype
    global $sprefix;
    if (isset($_SESSION[$sprefix]['authdata']))
        return getIndexOr($_SESSION[$sprefix]['authdata'], 'authtype', false);
    else
        return false;
}

function authLevel($authdata=false) {
    // Return the auth level from parameter or session, or 0
    global $sprefix;
    $authdata = $authdata?$authdata:
        (isset($_SESSION[$sprefix]['authdata'])?
            $_SESSION[$sprefix]['authdata']:0);
    if ($authdata) {
        return $authdata['userlevel'];
    } else {
        return 0;
    }
}

/* Unneeded?
function validateAuth($require) {
    global $serverdir, $sprefix;
    if (isset($_SESSION[$sprefix]['authdata'])) {
        if (authLevel() < 3) {
            require("../functions.php");
            setMessage("Access denied");
            header("Location: {$serverdir}/index.php");
        }
    } elseif ($require) {
        setMessage("Access denied");
        header("Location: {$serverdir}/index.php");
    }
}
 */

function hashPassword($pw) {
    $saltchars = explode(' ', '. / 0 1 2 3 4 5 6 7 8 9 A B C D E F G H I J K L M N O P Q R S T U V W X Y Z a b c d e f g h i j k l m n o p q r s t u v w x y z');
    $randexes = array_rand($saltchars, 22);
    $saltarray = array();
    foreach ($randexes as $r) $saltarray[] = $saltchars[$r];
    $salt = implode("", $saltarray);
    $algo = '$2a'; // Blowfish
    $cost = '$07';
    return crypt($pw, crypt($pw, "$algo$cost\$$salt\$"));
}
// vim: set foldmethod=indent :
