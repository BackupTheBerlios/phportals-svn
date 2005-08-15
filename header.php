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

if (eregi("header.php",$PHP_SELF)) {
    Header("Location: index.php");
    die();
}

require_once("mainfile.php");

##################################################
# Include some common header for HTML generation #
##################################################

$header = 1;

function head()
{
    $system = TSystem::instance();

    global $slogan, $sitename, $banners, $Default_Theme, $nukeurl, $Version_Num, $artpage, $topic, $hlpfile, $user, $hr, $theme, $cookie, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $textcolor1, $textcolor2, $forumpage, $adminpage, $userpage, $pagetitle;

    if (is_user())
    {
        $userinfo = getusrinfo();
        $theme_name = trim(toFilename($userinfo['theme']));
        if (!empty($theme_name))
        {
            if (!file_exists("themes/$theme_name") ||
                    !is_dir("themes/$theme_name") ||
                    !file_exists("themes/$theme_name/theme.php"))
            {
                // Theme auf den Defaultwert setzen
                //
                $theme_name = $system->config->Default_Theme;

                // Benutzereintrag berichtigen
                //
                $sql = sprintf("UPDATE `%s_user` SET `theme`='' WHERE `uid`='%u'",
                               $system->config->user_prefix,
                               $userinfo['uid']);
                $system->db->execute($sql);
            }
        }
        else
            $theme_name = $system->config->Default_Theme;

        // Nuke-Variable setzen
        //
        $ThemeSel = $system->config->Default_Theme;
    }
    else
        $ThemeSel = $theme_name = $system->config->Default_Theme;

    // Seitentitel setzen
    //
    $sitetitle = trim($pagetitle);
    if (!empty($sitetitle))
        $sitetitle = $system->config->sitename . ' - ' . $sitetitle;
    else
        $sitetitle = $system->config->sitename . ' - ' . $system->config->slogan;

    // Theme laden
    //
    include "themes/$ThemeSel/theme.php";

    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n"
        ."        \"http://www.w3.org/TR/html4/loose.dtd\">\n"
        . "<html>\n"
        . "<head>\n"
        . "<title>$sitetitle</title>\n";

    include 'includes/meta.php';
    include 'includes/javascript.php';

    echo "\n<link rel=\"StyleSheet\" href=\"themes/$ThemeSel/style/style.css\" type=\"text/css\">\n";
    include 'includes/my_header.php';
    echo "\n</head>\n\n";
    themeheader();

    // Session-Meldungen ausgeben
    //
    $sm = new TSessionMessages();
    foreach ($sm as $message)
        echo "<div class=\"message-$message[type]\">$message[message]</div>\n";
    $sm->delete();
}

$system = TSystem::instance();
setlocale(LC_ALL, $system->config->locale);

head();
include 'includes/counter.php';
global $home;
if ($home == 1)
{
    message_box();
    blocks('center');
}
online();

?>
