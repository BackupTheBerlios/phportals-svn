<?php

ob_start("ob_gzhandler");

/**********************************************************************
 * PHPortalS - PHP Portal System
 *
 * Copyright (c) 2004-2005 by Hinrich Donner, hdonner@users.sf.net
 * http://phportals.de
 *
 * This program is free software.  You can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License.
 *********************************************************************/

require_once 'includes/defines.php';
require_once 'includes/functions.php';
require_once 'includes/exceptions.php';
require_once 'includes/class.cookies.php';
require_once 'includes/class.session.php';
require_once 'includes/class.sessionmessages.php';
require_once 'includes/class.admin.php';
require_once 'includes/class.abstractmodule.php';           // Abstrakte Basis f�r Module

/**
 * @brief System-Container
 * @author Hinrich Donner
 * @version 2
 * @ingroup api
 */
class TSystem
{
    /**
     * @brief Instanz
     * @var TSystem
     * @sa TSystem::instance()
     */
    private static $_instance;

    /**
     * @brief Administrator
     * @var TAdmin
     */
    public $admin;

    /**
     * @brief Konfigurationsvariablen
     * @var TConfig
     */
    public $config;

    /**
     * @brief Cookies
     * @var TCookies
     */
    public $cookies;

    /**
     * @brief Datenbank-Layer
     * @var TDatabase
     */
    public $db;

    /**
     * @brief Session
     * @var TSession
     */
    public $session;

    /**
     * @brief Konstruktor
     * @sa TSystem::instance()
     */
    private function __construct()
    {
        // Dateien einbinden
        //
        require_once 'includes/class.config.php';

        // Statische Konfiguration lesen
        //
        $this->config = new TConfig();
        $this->config->load(PS_CONFIG_FILE);

        // Session starten
        //
        session_start();
        $this->session = new TSession();
    }

    /**
     * @brief Initialisierung
     */
    private function initialize()
    {
        // Datenbank binden
        //
        $this->initializeDatabase();

        $this->cookies = new TCookies();

        $this->admin = new TAdmin($this);
    }

    /**
     * @brief Datenbank initialisieren
     */
    private function initializeDatabase()
    {
        $filename = sprintf('includes/db.class.%s.php',
                            strtolower(toFilename($this->config->dbtype)));
        if (!file_exists($filename))
            throw new EFileNotFound($filename);

        // Load Driver
        //
        require_once $filename;

        // Make Connection
        //
        $classname = sprintf('T%s', $this->config->dbtype);
        $this->db = new $classname();
        $this->db->connect($this->config->dbhost,
                           $this->config->dbuname,
                           $this->config->dbpass);
        $this->db->select($this->config->dbname);
    }

    /**
     * @brief Instanz ermitteln
     * @return TSystem
     */
    public function instance()
    {
        if (isset(self::$_instance))
            return self::$_instance;

        // Instanz erzeugen
        //
        self::$_instance = new TSystem();

        // Initialisieren
        //
        self::$_instance->initialize();

        // Fertig
        //
        return self::$_instance;
    }
}

/**
 * @brief System-Container
 * @var TSystem
 * @ingroup api
 */
$system = TSystem::instance();

/************************************************************************/
/* PHP-NUKE: Advanced Content Management System                         */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi (fbc@mandrakesoft.com)         */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

foreach ($HTTP_GET_VARS as $secvalue) {
    if (eregi("<[^>]*script*\"?[^>]*>", $secvalue)) {
    die ("I don't like you...");
    }
}

if (eregi("mainfile.php",$PHP_SELF)) {
    Header("Location: index.php");
    die();
}

require PS_CONFIG_FILE;
require_once 'includes/sql_layer.php';

$dbi = sql_connect($dbhost, $dbuname, $dbpass, $dbname);
$mainfile = 1;

if (isset($newlang)) {
    if (file_exists("language/lang-$newlang.php")) {
    setcookie("lang",$newlang,time()+31536000);
    include("language/lang-$newlang.php");
    $currentlang = $newlang;
    } else {
    setcookie("lang",$language,time()+31536000);
    include("language/lang-$language.php");
    $currentlang = $language;
    }
} elseif (isset($lang)) {
    include("language/lang-$lang.php");
    $currentlang = $lang;
} else {
    setcookie("lang",$language,time()+31536000);
    include("language/lang-$language.php");
    $currentlang = $language;
}

function get_lang($module) {
    global $currentlang;
    if ($module == admin) {
    if (file_exists("admin/language/lang-$currentlang.php")) {
        include_once("admin/language/lang-$currentlang.php");
    }
    } else {
    if (file_exists("modules/$module/language/lang-$currentlang.php")) {
        include_once("modules/$module/language/lang-$currentlang.php");
    }
    }
}

function is_admin()
{
    static $result;

    if (isset($result))
        return $result;

    $system = TSystem::instance();
    $result = false;
    $admin = trim($system->cookies->admin);

    if (empty($admin))
        return $result;

    list ($aid,
          $passwd,
          $rest) = explode(':', base64_decode($admin), 3);

    $sql = sprintf("SELECT `pwd` FROM `%s_authors` WHERE `aid`='%s'",
                   $system->config->prefix,
                   $system->db->qStr(substr($aid, 0, 25)));
    if (false === ($db_passwd = $system->db->queryOne($sql)))
        return $result;

    if (strlen(trim($passwd)) && !strcmp($db_passwd, $passwd))
        $result = true;

    return $result;
}

function is_user()
{
    static $result;

    if (isset($result))
        return $result;

    $system = TSystem::instance();
    $result = false;
    $user = trim($system->cookies->user);

    if (empty($user))
        return $result;

    list ($uid,
          $username,
          $passwd,
          $rest) = explode(':', base64_decode($user), 4);

    $sql = sprintf("SELECT `pass` FROM `%s_users` WHERE `uid`='%u'",
                   $system->config->user_prefix,
                   $uid);
    if (false === ($db_passwd = $system->db->queryOne($sql)))
        return $result;

    if (strlen(trim($passwd)) && !strcmp($db_passwd, $passwd))
        $result = true;

    return $result;
}

function title($text)
{
    OpenTable(2);
    echo "<center><span class=\"title\"><b>$text</b></span></center>";
    CloseTable();
}

function is_active($module) {
    global $prefix, $dbi;
    $result = sql_query("select active from ".$prefix."_modules where title='$module'", $dbi);
    list ($act) = sql_fetch_row($result, $dbi);
    if (!$result OR $act == 0) {
    return 0;
    } else {
    return 1;
    }
}

function render_blocks($side, $blockfile, $title, $content, $bid, $url) {
    if ($url == "") {
    if ($blockfile == "") {
        if ($side == "c") {
        themecenterbox($title, $content);
        } else {
        themesidebox($title, $content);
        }
    } else {
        if ($side == "c") {
        blockfileinc($title, $blockfile, 1);
        } else {
        blockfileinc($title, $blockfile);
        }
    }
    } else {
    if ($side == "c") {
        headlines($bid,1);
    } else {
            headlines($bid);
    }
    }
}

function blocks($side) {
    global $storynum, $prefix, $multilingual, $currentlang, $dbi, $admin, $user;
    if ($multilingual == 1) {
        $querylang = "AND (blanguage='$currentlang' OR blanguage='')";
    } else {
        $querylang = "";
    }
    if (strtolower($side[0]) == "l") {
    $pos = "l";
    } elseif (strtolower($side[0]) == "r") {
    $pos = "r";
    }  elseif (strtolower($side[0]) == "c") {
    $pos = "c";
    }
    $side = $pos;
    $result = sql_query("select bid, bkey, title, content, url, blockfile, view from ".$prefix."_blocks where position='$pos' AND active='1' $querylang ORDER BY weight ASC", $dbi);
    while(list($bid, $bkey, $title, $content, $url, $blockfile, $view) = sql_fetch_row($result, $dbi)) {
    if ($bkey == admin) {
        adminblock();
    } elseif ($bkey == userbox) {
        userblock();
    } elseif ($bkey == "") {
        if ($view == 0) {
        render_blocks($side, $blockfile, $title, $content, $bid, $url);
        } elseif ($view == 1 AND is_user($user) || is_admin($admin)) {
        render_blocks($side, $blockfile, $title, $content, $bid, $url);
        } elseif ($view == 2 AND is_admin($admin)) {
        render_blocks($side, $blockfile, $title, $content, $bid, $url);
        } elseif ($view == 3 AND !is_user($user) || is_admin($admin)) {
        render_blocks($side, $blockfile, $title, $content, $bid, $url);
        }
    }
    }
}

function message_box()
{
    // Initialisierung
    //
    $system             = TSystem::instance();
    $expired_rows       = array();

    global $bgcolor1, $bgcolor2, $user, $admin, $cookie, $textcolor2, $prefix, $multilingual, $currentlang, $dbi;

    $view = array(1);
    if (is_user())
        $view[] = 3;
    else
        $view[] = 2;
    if (is_admin())
        $view[] = 4;

    $sql = sprintf("SELECT `mid`, `title`, `content`, `date`, `expire`, `view` FROM `%s_message` WHERE `active`='1' AND (%s) ORDER BY `date` DESC",
                   $system->config->prefix,
                   "`view`='" . join("' OR `view`='", $view) . "'");
    $rows = $system->db->query($sql);
    foreach ($rows as $row)
    {
        extract($row);

        // Ablaufdatum pr�fen
        //
        if (($expire != 0) && (($date + $expire) < time()))
        {
            // Dieser Datensatz ist abgelaufen, Flag setzen und n�chster Datensatz
            //
            $expired_rows[] = sprintf("UPDATE `%s_message` SET `active`='0' WHERE `mid`='%u'",
                                      $system->config->prefix,
                                      $mid);
            continue;
        }

        echo "<div class=\"message-box\">\n"
            . "<h1 class=\"title\">$title</h1>\n"
            . "<div class=\"content\">$content</div>\n";

        if (is_admin())
            echo "<div class=\"adminlink\">"
                . "[ "
                . "<a href=\"admin.php?op=editmsg&amp;mid=$mid\" title=\"" . _EDIT . "\">" . _EDIT . "</a> | "
                . "<a href=\"admin.php?op=deletemsg&amp;mid=$mid\" title=\"" . _DELETE . "\">" . _DELETE . "</a>"
                . " ]"
                . "</div>\n";

        echo "</div>\n";
    }

    // Deaktivieren abgelaufener Meldungen
    //
    foreach ($expired_rows as $sql)
        $system->db->execute($sql);
}

function online() {
    global $user, $cookie, $prefix, $dbi;
    cookiedecode($user);
    $ip = getenv("REMOTE_ADDR");
    $username = $cookie[1];
    if (!isset($username)) {
        $username = "$ip";
        $guest = 1;
    }
    $past = time()-1800;
    sql_query("DELETE FROM ".$prefix."_session WHERE time < $past", $dbi);
    $result = sql_query("SELECT time FROM ".$prefix."_session WHERE username='$username'", $dbi);
    $ctime = time();
    if ($row = sql_fetch_array($result, $dbi)) {
    sql_query("UPDATE ".$prefix."_session SET username='$username', time='$ctime', host_addr='$ip', guest='$guest' WHERE username='$username'", $dbi);
    } else {
    sql_query("INSERT INTO ".$prefix."_session (username, time, host_addr, guest) VALUES ('$username', '$ctime', '$ip', '$guest')", $dbi);
    }
}

function blockfileinc($title, $blockfile, $side=0) {
    $blockfiletitle = $title;
    $file = @file("blocks/$blockfile");
    if (!$file) {
    $content = _BLOCKPROBLEM;
    } else {
    include("blocks/$blockfile");
    }
    if ($content == "") {
    $content = _BLOCKPROBLEM2;
    }
    if ($side == 1) {
    themecenterbox($blockfiletitle, $content);
    } else {
    themesidebox($blockfiletitle, $content);
    }
}

function selectlanguage() {
    global $useflags, $currentlang;
    if ($useflags == 1) {
    $title = _SELECTLANGUAGE;
    $content = "<center><font class=\"content\">"._SELECTGUILANG."<br><br>";
    $langdir = dir("language");
    while($func=$langdir->read()) {
    if(substr($func, 0, 5) == "lang-") {
            $menulist .= "$func ";
    }
    }
    closedir($langdir->handle);
    $menulist = explode(" ", $menulist);
    sort($menulist);
    for ($i=0; $i < sizeof($menulist); $i++) {
        if($menulist[$i]!="") {
        $tl = ereg_replace("lang-","",$menulist[$i]);
        $tl = ereg_replace(".php","",$tl);
        $altlang = ucfirst($tl);
        $content .= "<a href=\"index.php?newlang=$tl\"><img src=\"images/language/flag-$tl.png\" border=\"0\" alt=\"$altlang\" hspace=\"3\" vspace=\"3\"></a> ";
    }
    }
    $content .= "</font></center>";
    themesidebox($title, $content);
    } else {
    $title = _SELECTLANGUAGE;
    $content = "<center><font class=\"content\">"._SELECTGUILANG."<br><br></font>";
    $content .= "<form action=\"index.php\" method=\"get\"><select name=\"newlanguage\" onChange=\"top.location.href=this.options[this.selectedIndex].value\">";
        $handle=opendir('language');
        while ($file = readdir($handle)) {
        if (preg_match("/^lang\-(.+)\.php/", $file, $matches)) {
                $langFound = $matches[1];
                $languageslist .= "$langFound ";
            }
        }
        closedir($handle);
        $languageslist = explode(" ", $languageslist);
        sort($languageslist);
        for ($i=0; $i < sizeof($languageslist); $i++) {
        if($languageslist[$i]!="") {
    $content .= "<option value=\"index.php?newlang=$languageslist[$i]\" ";
        if($languageslist[$i]==$currentlang) $content .= " selected";
    $content .= ">".ucfirst($languageslist[$i])."</option>\n";
        }
    }
    $content .= "</select></form></center>";
    themesidebox($title, $content);
    }
}

function ultramode() {
    global $prefix, $dbi;
    $ultra = "ultramode.txt";
    $file = fopen("$ultra", "w");
    fwrite($file, "General purpose self-explanatory file with news headlines\n");
    $rfile=sql_query("select sid, aid, title, time, comments, topic from ".$prefix."_stories order by time DESC limit 0,10", $dbi);
    while(list($sid, $aid, $title, $time, $comments, $topic) = sql_fetch_row($rfile, $dbi)) {
    $rfile2=sql_query("select topictext, topicimage from ".$prefix."_topics where topicid=$topic", $dbi);
    list($topictext, $topicimage) = sql_fetch_row($rfile2, $dbi);
    $content = "%%\n$title\n/modules.php?name=News&file=article&sid=$sid\n$time\n$aid\n$topictext\n$comments\n$topicimage\n";
    fwrite($file, $content);
    }
    fclose($file);
}

function cookiedecode($user) {
    global $cookie, $prefix, $dbi, $user_prefix;
    $user = base64_decode($user);
    $cookie = explode(":", $user);
    $result = sql_query("select pass from ".$user_prefix."_users where uname='$cookie[1]'", $dbi);
    list($pass) = sql_fetch_row($result, $dbi);
    if ($cookie[2] == $pass && $pass != "") {
    return $cookie;
    } else {
    unset($user);
    unset($cookie);
    }
}

function getusrinfo($user)
{
    static $result;

    if (isset($result))
        return $result;

    $result = array();
    $system = TSystem::instance();

    if (!is_user())
        return $result;

    list ($uid) = explode(':', base64_decode($system->cookies->user));
    $sql = sprintf("SELECT * FROM `%s_users` WHERE `uid`='%u'",
                   $system->config->user_prefix,
                   $uid);
    if (false === ($result = $system->db->queryRow($sql)))
        return array();

    global $userinfo;

    $userinfo = $result;

    return $result;
}

function searchblock() {
    OpenTable();
    echo "<form action=\"modules.php?name=Forum&file=search\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"addterm\" value=\"any\">";
    echo "<input type=\"hidden\" name=\"sortby\" value=\"p.post_time\">";
    echo "&nbsp;&nbsp;<b>"._SEARCH."</b>&nbsp;<input type=\"text\" name=\"term\" size=\"15\">";
    echo "<input type=\"hidden\" name=\"submit\" value=\"submit\"></form>";
    echo "<div align=\"left\"><font class=\"content\">&nbsp;&nbsp;[ <a href=\"modules.php?name=Forum&file=search&addterm=any&amp;sortby=p.post_time&amp;adv=1\">Advanced Search</a> ]</font></div>";
    CloseTable();
}

function FixQuotes ($what = "") {
    $what = ereg_replace("'","''",$what);
    while (eregi("\\\\'", $what)) {
        $what = ereg_replace("\\\\'","'",$what);
    }
    return $what;
}

/*********************************************************/
/* text filter                                           */
/*********************************************************/

function check_words($Message) {
    global $EditedMessage;
    include("config.php");
    $EditedMessage = $Message;
    if ($CensorMode != 0) {

    if (is_array($CensorList)) {
        $Replace = $CensorReplace;
        if ($CensorMode == 1) {
        for ($i = 0; $i < count($CensorList); $i++) {
            $EditedMessage = eregi_replace("$CensorList[$i]([^a-zA-Z0-9])","$Replace\\1",$EditedMessage);
        }
        } elseif ($CensorMode == 2) {
        for ($i = 0; $i < count($CensorList); $i++) {
            $EditedMessage = eregi_replace("(^|[^[:alnum:]])$CensorList[$i]","\\1$Replace",$EditedMessage);
        }
        } elseif ($CensorMode == 3) {
        for ($i = 0; $i < count($CensorList); $i++) {
            $EditedMessage = eregi_replace("$CensorList[$i]","$Replace",$EditedMessage);
        }
        }
    }
    }
    return ($EditedMessage);
}

function delQuotes($string){
    /* no recursive function to add quote to an HTML tag if needed */
    /* and delete duplicate spaces between attribs. */
    $tmp="";    # string buffer
    $result=""; # result string
    $i=0;
    $attrib=-1; # Are us in an HTML attrib ?   -1: no attrib   0: name of the attrib   1: value of the atrib
    $quote=0;   # Is a string quote delimited opened ? 0=no, 1=yes
    $len = strlen($string);
    while ($i<$len) {
    switch($string[$i]) { # What car is it in the buffer ?
        case "\"": #"       # a quote.
        if ($quote==0) {
            $quote=1;
        } else {
            $quote=0;
            if (($attrib>0) && ($tmp != "")) { $result .= "=\"$tmp\""; }
            $tmp="";
            $attrib=-1;
        }
        break;
        case "=":           # an equal - attrib delimiter
        if ($quote==0) {  # Is it found in a string ?
            $attrib=1;
            if ($tmp!="") $result.=" $tmp";
            $tmp="";
        } else $tmp .= '=';
        break;
        case " ":           # a blank ?
        if ($attrib>0) {  # add it to the string, if one opened.
            $tmp .= $string[$i];
        }
        break;
        default:            # Other
        if ($attrib<0)    # If we weren't in an attrib, set attrib to 0
        $attrib=0;
        $tmp .= $string[$i];
        break;
    }
    $i++;
    }
    if (($quote!=0) && ($tmp != "")) {
    if ($attrib==1) $result .= "=";
    /* If it is the value of an atrib, add the '=' */
    $result .= "\"$tmp\"";  /* Add quote if needed (the reason of the function ;-) */
    }
    return $result;
}

function check_html ($str, $strip="") {
    /* The core of this code has been lifted from phpslash */
    /* which is licenced under the GPL. */
    include("config.php");
    if ($strip == "nohtml")
        $AllowableHTML=array('');
    $str = stripslashes($str);
    $str = eregi_replace("<[[:space:]]*([^>]*)[[:space:]]*>",
                         '<\\1>', $str);
               // Delete all spaces from html tags .
    $str = eregi_replace("<a[^>]*href[[:space:]]*=[[:space:]]*\"?[[:space:]]*([^\" >]*)[[:space:]]*\"?[^>]*>",
                         '<a href="\\1">', $str); # "
               // Delete all attribs from Anchor, except an href, double quoted.
    $str = eregi_replace("<img?",
                         '', $str); # "
    $tmp = "";
    while (ereg("<(/?[[:alpha:]]*)[[:space:]]*([^>]*)>",$str,$reg)) {
        $i = strpos($str,$reg[0]);
        $l = strlen($reg[0]);
        if ($reg[1][0] == "/") $tag = strtolower(substr($reg[1],1));
        else $tag = strtolower($reg[1]);
        if ($a = $AllowableHTML[$tag])
            if ($reg[1][0] == "/") $tag = "</$tag>";
            elseif (($a == 1) || ($reg[2] == "")) $tag = "<$tag>";
            else {
              # Place here the double quote fix function.
              $attrb_list=delQuotes($reg[2]);
              // A VER
              $attrb_list = ereg_replace("&","&amp;",$attrb_list);
              $tag = "<$tag" . $attrb_list . ">";
            } # Attribs in tag allowed
        else $tag = "";
        $tmp .= substr($str,0,$i) . $tag;
        $str = substr($str,$i+$l);
    }
    $str = $tmp . $str;
    return $str;
    exit;
    /* Squash PHP tags unconditionally */
    $str = ereg_replace("<\?","",$str);
    return $str;
}

function filter_text($Message, $strip="") {
    global $EditedMessage;
    check_words($Message);
    $EditedMessage=check_html($EditedMessage, $strip);
    return ($EditedMessage);
}

/*********************************************************/
/* formatting stories                                    */
/*********************************************************/

function formatTimestamp($time) {
    global $datetime, $locale;
    setlocale (LC_ALL, $locale);
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = strftime(""._DATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    return($datetime);
}

function formatAidHeader($aid) {
    global $prefix, $dbi;
    $holder = sql_query("SELECT url, email FROM ".$prefix."_authors where aid='$aid'", $dbi);
    if (!$holder) {
        echo _ERROR;
    exit();
    }
    list($url, $email) = sql_fetch_row($holder, $dbi);
    if (isset($url)) {
    $aid = "<a href=\"$url\">$aid</a>";
    } elseif (isset($email)) {
    $aid = "<a href=\"mailto:$email\">$aid</a>";
    } else {
    $aid = $aid;
    }
    echo "$aid";
}

function get_author($aid) {
    global $prefix, $dbi;
    $holder = sql_query("SELECT url, email FROM ".$prefix."_authors where aid='$aid'", $dbi);
    if (!$holder) {
        echo _ERROR;
    exit();
    }
    list($url, $email) = sql_fetch_row($holder, $dbi);
    if (isset($url)) {
    $aid = "<a href=\"$url\">$aid</a>";
    } elseif (isset($email)) {
    $aid = "<a href=\"mailto:$email\">$aid</a>";
    } else {
    $aid = $aid;
    }
    return($aid);
}

function themepreview($title, $hometext, $bodytext="", $notes="") {
    echo "<b>$title</b><br><br>$hometext";
    if ($bodytext != "") {
    echo "<br><br>$bodytext";
    }
    if ($notes != "") {
    echo "<br><br><b>"._NOTE."</b> <i>$notes</i>";
    }
}

function adminblock() {
    global $admin, $prefix, $dbi;
    if (is_admin($admin)) {
    $result = sql_query("select title, content from ".$prefix."_blocks where bkey='admin'", $dbi);
    while(list($title, $content) = sql_fetch_array($result, $dbi)) {
        $content = "<font class=\"content\">$content</font>";
        themesidebox($title, $content);
    }
    $title = ""._WAITINGCONT."";
    $result = sql_query("select * from ".$prefix."_queue", $dbi);
    $num = sql_num_rows($result, $dbi);
    $content = "<font class=\"content\">";
    $content .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"admin.php?op=submissions\">"._SUBMISSIONS."</a>: $num<br>";
    $result = sql_query("select * from ".$prefix."_reviews_add", $dbi);
    $num = sql_num_rows($result, $dbi);
    $content .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"admin.php?op=reviews\">"._WREVIEWS."</a>: $num<br>";
    $result = sql_query("select * from ".$prefix."_links_newlink", $dbi);
    $num = sql_num_rows($result, $dbi);
    $content .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"admin.php?op=Links\">"._WLINKS."</a>: $num<br>";
    $result = sql_query("select * from ".$prefix."_downloads_newdownload", $dbi);
    $num = sql_num_rows($result, $dbi);
    $content .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"admin.php?op=downloads\">"._UDOWNLOADS."</a>: $num<br></font>";
    themesidebox($title, $content);
    }
}

function loginbox() {
    global $user;
    if (!is_user($user)) {
    $title = _LOGIN;
    $boxstuff = "<form action=\"modules.php?name=Your_Account\" method=\"post\">";
    $boxstuff .= "<center><font class=\"content\">"._NICKNAME."<br>";
    $boxstuff .= "<input type=\"text\" name=\"uname\" size=\"8\" maxlength=\"25\"><br>";
    $boxstuff .= ""._PASSWORD."<br>";
    $boxstuff .= "<input type=\"password\" name=\"pass\" size=\"8\" maxlength=\"20\"><br>";
    $boxstuff .= "<input type=\"hidden\" name=\"op\" value=\"login\">";
    $boxstuff .= "<input type=\"submit\" value=\""._LOGIN."\"></font></center></form>";
    $boxstuff .= "<center><font class=\"content\">"._ASREGISTERED."</font></center>";
    themesidebox($title, $boxstuff);
    }
}

function userblock() {
    global $user, $cookie, $prefix, $dbi, $user_prefix;
    if((is_user($user)) AND ($cookie[8])) {
    $getblock = sql_query("select ublock from ".$user_prefix."_users where uid='$cookie[0]'", $dbi);
    $title = ""._MENUFOR." $cookie[1]";
    list($ublock) = sql_fetch_row($getblock, $dbi);
    themesidebox($title, $ublock);
    }
}

function getTopics($s_sid) {
    global $topicname, $topicimage, $topictext, $prefix, $dbi;
    $sid = $s_sid;
    $result = sql_query("SELECT topic FROM ".$prefix."_stories where sid=$sid", $dbi);
    list($topic) = sql_fetch_row($result, $dbi);
    $result = sql_query("SELECT topicid, topicname, topicimage, topictext FROM ".$prefix."_topics where topicid=$topic", $dbi);
    list($topicid, $topicname, $topicimage, $topictext) = sql_fetch_row($result, $dbi);
}

function headlines($bid, $cenbox=0) {
    global $prefix, $dbi;
    $result = sql_query("select title, content, url, refresh, time from ".$prefix."_blocks where bid='$bid'", $dbi);
    list($title, $content, $url, $refresh, $otime) = sql_fetch_row($result, $dbi);
    $past = time()-$refresh;
    if ($otime < $past) {
    $btime = time();
    $rdf = parse_url($url);
    $fp = fsockopen($rdf['host'], 80, $errno, $errstr, 15);
    if (!$fp) {
        $content = "";
        //$content = "<font class=\"content\">"._RSSPROBLEM."</font>";
        $result = sql_query("update ".$prefix."_blocks set content='$content', time='$btime' where bid='$bid'", $dbi);
        $cont = 0;
        if ($cenbox == 0) {
        themesidebox($title, $content);
        } else {
        themecenterbox($title, $content);
        }
        return;
    }
    if ($fp) {
        fputs($fp, "GET " . $rdf['path'] . "?" . $rdf['query'] . " HTTP/1.0\r\n");
        fputs($fp, "HOST: " . $rdf['host'] . "\r\n\r\n");
        $string = "";
        while(!feof($fp)) {
            $pagetext = fgets($fp,300);
            $string .= chop($pagetext);
        }
        fputs($fp,"Connection: close\r\n\r\n");
        fclose($fp);
        $items = explode("</item>",$string);
        $content = "<font class=\"content\">";
        for ($i=0;$i<10;$i++) {
        $link = ereg_replace(".*<link>","",$items[$i]);
        $link = ereg_replace("</link>.*","",$link);
        $title2 = ereg_replace(".*<title>","",$items[$i]);
        $title2 = ereg_replace("</title>.*","",$title2);
        if ($items[$i] == "") {
            $content = "";
            sql_query("update ".$prefix."_blocks set content='$content', time='$btime' where bid='$bid'", $dbi);
            $cont = 0;
            if ($cenbox == 0) {
            themesidebox($title, $content);
            } else {
            themecenterbox($title, $content);
            }
            return;
        } else {
            if (strcmp($link,$title)) {
            $cont = 1;
            $content .= "<strong><big>&middot;</big></strong><a href=\"$link\" target=\"new\">$title2</a><br>\n";
            }
        }
        }

    }
    sql_query("update ".$prefix."_blocks set content='$content', time='$btime' where bid='$bid'", $dbi);
    }
    $siteurl = ereg_replace("http://","",$url);
    $siteurl = explode("/",$siteurl);
    if (($cont == 1) OR ($content != "")) {
    $content .= "<br><a href=\"http://$siteurl[0]\" target=\"blank\"><b>"._HREADMORE."</b></a></font>";
    } elseif (($cont == 0) OR ($content == "")) {
    $content = "<font class=\"content\">"._RSSPROBLEM."</font>";
    }
    if ($cenbox == 0) {
    themesidebox($title, $content);
    } else {
    themecenterbox($title, $content);
    }
}

function automated_news() {
    global $prefix, $multilingual, $currentlang, $dbi;
    if ($multilingual == 1) {
        $querylang = "WHERE (alanguage='$currentlang' OR alanguage='')"; /* the OR is needed to display stories who are posted to ALL languages */
    } else {
        $querylang = "";
    }
    $today = getdate();
    $day = $today[mday];
    if ($day < 10) {
    $day = "0$day";
    }
    $month = $today[mon];
    if ($month < 10) {
    $month = "0$month";
    }
    $year = $today[year];
    $hour = $today[hours];
    $min = $today[minutes];
    $sec = "00";
    $result = sql_query("select anid, time from ".$prefix."_autonews $querylang", $dbi);
    while(list($anid, $time) = sql_fetch_row($result, $dbi)) {
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $date);
    if (($date[1] <= $year) AND ($date[2] <= $month) AND ($date[3] <= $day)) {
        if (($date[4] < $hour) AND ($date[5] >= $min) OR ($date[4] <= $hour) AND ($date[5] <= $min)) {
        $result2 = sql_query("select catid, aid, title, time, hometext, bodytext, topic, informant, notes, ihome, alanguage, acomm from ".$prefix."_autonews where anid='$anid'", $dbi);
        while(list($catid, $aid, $title, $a_time, $hometext, $bodytext, $topic, $author, $notes, $ihome, $alanguage, $acomm) = sql_fetch_row($result2, $dbi)) {
            $title = stripslashes(FixQuotes($title));
            $hometext = stripslashes(FixQuotes($hometext));
            $bodytext = stripslashes(FixQuotes($bodytext));
            $notes = stripslashes(FixQuotes($notes));
            sql_query("insert into ".$prefix."_stories values (NULL, '$catid', '$aid', '$title', '$a_time', '$hometext', '$bodytext', '0', '0', '$topic', '$author', '$notes', '$ihome', '$alanguage', '$acomm', '0', '0', '0', '0')", $dbi);
            sql_query("delete from ".$prefix."_autonews where anid='$anid'", $dbi);
        }
        }
    }
    }
}

function themecenterbox($title, $content) {
    OpenTable();
    echo "<center><fon class=\"option\"><b>$title</b></font></center><br>"
    ."$content";
    CloseTable();
    echo "<br>";
}

?>
