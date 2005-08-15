<?php

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

require_once("mainfile.php");

if (eregi("auth.php",$PHP_SELF)) {
    Header("Location: index.php");
    die();
}

$system = TSystem::instance();
$aid    = argv('aid', '');
$pwd    = argv('pwd', '');
$op     = argv('op', '');

if (!empty($aid) && !empty($pwd) && !strcmp($op, 'login'))
{
    $pwd = md5($pwd);
    $sql = sprintf("SELECT `pwd` FROM `%s_authors` WHERE `aid`='%s'",
                   $system->config->prefix,
                   $system->db->qStr(substr(trim($aid), 0, 25)));
    $dbpwd = $system->db->queryOne($sql);
    if (!strcmp($dbpwd, $pwd))
    {
        $admin = base64_encode("$aid:$dbpwd:");
        setcookie("admin", $admin, time()+2592000);
        location('admin.php');
    }
}

$admintest = 0;

$admin = $system->cookies->admin;
if (!empty($admin))
{
    $admin = base64_decode($admin);
    $admin = explode(":", $admin);
    $aid = $admin[0];
    $pwd = $admin[1];
    if (empty($aid) || empty($pwd))
    {
        $admintest = 0;
        echo "<html>\n";
        echo "<title>INTRUDER ALERT!!!</title>\n";
        echo "<body bgcolor=\"#FFFFFF\" text=\"#000000\">\n\n<br><br><br>\n\n";
        echo "<center><img src=\"images/eyes.gif\" border=\"0\"><br><br>\n";
        echo "<font face=\"Verdana\" size=\"+4\"><b>Get Out!</b></font></center>\n";
        echo "</body>\n";
        echo "</html>\n";
        exit;
    }
    $sql = sprintf("SELECT `pwd` FROM `%s_authors` WHERE `aid`='%s'",
                   $system->config->prefix,
                   $system->db->qStr(substr(trim($aid), 0, 25)));
    if (false === ($dbpwd = $system->db->queryOne($sql)))
    {
        new TSessionMessages(_("Datenbankfehler!"), 'error');
        if ($system->config->verbose)
            new TSessionMessages($system->db->error, 'error');
    }
    if (!strcmp($dbpwd, $pwd))
        $admintest = 1;

}

?>
