<?php

/************************************************************************/
/* PHP-NUKE: Web Portal System                                          */
/* ===========================                                          */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi (fbc@mandrakesoft.com)         */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

//if (!eregi("admin.php", $PHP_SELF)) { die ("Access Denied"); }

$result = sql_query("select radminsuper, admlanguage from ".$prefix."_authors where aid='$aid'", $dbi);
list($radminsuper,$admlanguage) = sql_fetch_row($result, $dbi);
if ($radminsuper==1) {

global $_day_list;

$_day_list = array(  1   => 86400,
                     2   => 172800,
                     3   => 259200,
                     4   => 345600,
                     5   => 432000,
                     6   => 518400,
                     7   => 604800,
                     8   => 691200,
                     9   => 777600,
                    10   => 864000,
                    11   => 950400,
                    12   => 1036800,
                    13   => 1123200,
                    14   => 1209600,
                    15   => 1296000,
                    20   => 1728000,
                    25   => 2160000,
                    30   => 2592000,
                    60   => 5184000,
                    90   => 7776000,
                   120   => 10368000,
                   180   => 15552000,
                   360   => 31104000);

/*********************************************************/
/* Messages Functions                                    */
/*********************************************************/

function MsgDeactive($mid)
{
    global $prefix, $dbi;
    sql_query("update ".$prefix."_message set active='0' WHERE mid='$mid'", $dbi);
    Header("Location: admin.php?op=messages");
}

/**
 * @brief Hauptübersicht
 */
function messages()
{
    $system = TSystem::instance();
    $view_text = array(_("Unbekannt"),
                       _("Alle"),
                       _("Gäste"),
                       _("Angemeldete Benutzer"),
                       _("Administratoren"));

    global $pagetitle;

    $pagetitle = _("Verwalten der Mitteilungen");

    include 'header.php';

    GraphicAdmin();

    title(_("Verwalten der Mitteilungen"));

    $sql = sprintf("SELECT * FROM `%s_message` ORDER BY `date` DESC",
                   $system->config->prefix);
    $rows = $system->db->query($sql);
    if ($rows->count() > 0)
    {
        OpenTable();
        title(_("Vorhandene Mitteilungen"));

        echo "<table class=\"list\">\n"
            . "<tr class=\"title\">\n"
            . "<th class=\"list\"><b>". _("Titel") . "</b></th>"
            . "<th class=\"list\"><b>". _("Sichtbar für") . "</b>&nbsp;</th>"
            . "<th class=\"list\"><b>". _("Erstellt am") . "</b></th>"
            . "<th class=\"list\"><b>". _("Verfällt am") ."</b></th>"
            . "<th class=\"list\"><b>". _("Status") ."</b>&nbsp;</th>"
            . "<th class=\"list\">&nbsp;</th></tr>"
            . "</tr>\n";

        $even_state = false;
        foreach ($rows as $row)
        {
            extract($row);
            $even = (($even_state) ? 'even' : 'odd');
            $even_state = !$even_state;

            $img = (($active == 1)
                ? "<img src=\"images/active.gif\" title=\"" . _("Aktiv") . "\" alt=\"" . _("Aktiv") . "\" />"
                : "<img src=\"images/inactive.gif\" title=\"" . _("Inaktiv") . "\" alt=\"" . _("Inaktiv") . "\" />");


            echo "<tr class=\"content\">\n"
                . "<td class=\"list-$even\">$title</td>\n"
                . "<td class=\"list-$even\">$view_text[$view]</td>\n"
                . "<td class=\"list-$even\">" . strftime(_("%d.%m.%y %H:%M"), $date) . "</td>\n"
                . "<td class=\"list-$even\">" . (($expire != 0)
                        ? strftime(_("%d.%m.%y %H:%M"), ($date + $expire))
                        : _("Unbegrenzt")) . "</td>\n"
                . "<td class=\"list-$even\">$img</td>\n"
                . "<td class=\"list-$even\">"
                . "<a href=\"admin.php?op=editmsg&amp;mid=$mid\" title=\"" . _("Ändern") . "\">"
                . "<img src=\"images/edit.gif\" title=\"" . _("Ändern") . "\" alt=\"\"></a>\n"
                . "<a href=\"admin.php?op=deletemsg&amp;mid=$mid\" title=\"" . _("Löschen") . "\">"
                . "<img src=\"images/delete.gif\" title=\"" . _("Löschen") . "\" alt=\"\"></a>\n"
                . "</td>\n"
                . "</tr>\n";
        }
        echo "</table>\n";
        CloseTable();
    }

    OpenTable();
    title(_("Neue Mitteilung hinzufügen"));
    echo "<form action=\"admin.php\" id=\"message\" name=\"message\" method=\"post\">\n"
        . "<label for=\"add_title\">" . _("Titel der Mitteilung") . ":</label><br />\n"
        . "<input type=\"text\" name=\"add_title\" value=\"\" size=\"50\" maxlength=\"100\" /><br /><br />\n"
        . "<label for=\"add_content\">" . _("Inhalt der Mitteilung") . ":</label><br />"
        . "<textarea name=\"add_content\" rows=\"15\" wrap=\"virtual\" cols=\"60\"></textarea><br /><br />\n"
        . "<lable for=\"add_expire\">" . _("Lebensdauer") . ":</label>&nbsp;"
        . "<select name=\"add_expire\">\n";
    foreach ($GLOBALS['_day_list'] as $days => $secs)
        echo "<option value=\"$secs\">$days " . (($secs == 86400) ? _("Tag") : _("Tage")) . "</option>\n";
    echo "<option value=\"0\" selected\n>"._("Unbegrenzt")."</option>\n"
        . "</select>&nbsp;\n"
        . "<select name=\"add_active\">\n"
        . "<option value=\"1\" checked>" . _("Aktiv") . "</option>\n"
        . "<option value=\"0\">" . _("Inaktiv") . "</option>\n"
        . "</select>\n"
        . "<br /><br />"
        . "<label for=\"add_view\">" . _("Sichtbar für") . "</label>&nbsp;"
        . "<select name=\"add_view\">"
        . "<option value=\"1\" >" . _("alle Besucher") ."</option>"
        . "<option value=\"2\" >" . _("angemeldete Benutzer") . "</option>"
        . "<option value=\"3\" >" . _("Gäste") . "</option>"
        . "<option value=\"4\" >" . _("Administratoren") . "</option>"
        . "</select><br /><br />\n"
        . "<input type=\"hidden\" name=\"op\" value=\"addmsg\" />"
        . "<input type=\"submit\" value=\"" . _("Mitteilung hinzufügen") . "\" />"
        . "</form>\n"
        . "<script language=\"javascript\" type=\"text/javascript\"><!--\n"
        . "document.message.add_title.focus();\n"
        . "--></script>\n";
    CloseTable();
    include 'footer.php';
}

function editmsg($mid)
{
    $system = TSystem::instance();

    global $admin, $prefix, $dbi, $multilingual, $pagetitle;

    $pagetitle = _("Mitteilung bearbeiten");

    include 'header.php';

    GraphicAdmin();

    OpenTable();
    title(_("Mitteilung bearbeiten"));

    $result = sql_query("select title, content, date, expire, active, view, mlanguage from ".$prefix."_message WHERE mid='$mid'", $dbi);
    list($title, $content, $mdate, $expire, $active, $view, $mlanguage) = sql_fetch_row($result, $dbi);

    $sql = sprintf("SELECT * FROM `%s_message` WHERE `mid`='%u'",
                   $system->config->prefix,
                   $mid);
    if (false === ($row = $system->db->queryRow($sql)))
    {
        new TSessionMessages(sprintf(_("Eine Mitteilung mit der ID #%u existiert nicht."), $mid));
        location('admin.php?op=messages');
    }

    extract($row);

    echo "<form action=\"admin.php\" method=\"post\" name=\"editmessage\" id=\"editmessage\">\n"
        . "<label for=\"title\">" . _("Titel") . ":</label><br />\n"
        . "<input type=\"text\" name=\"title\" value=\"$title\" size=\"50\" maxlength=\"100\" /><br /><br />\n"
        . "<label for=\"content\">" . _ ("Inhalt der Mitteilung") . ":</label><br />\n"
        . "<textarea name=\"content\" rows=\"15\" wrap=\"virtual\" cols=\"60\">$content</textarea><br /><br />\n"
        . "<label for=\"expire\">" . _("Lebensdauer") . ":</label>&nbsp;\n"
        . "<select name=\"expire\">\n";
    foreach ($GLOBALS['_day_list'] as $days => $secs)
        echo "<option value=\"$secs\"" . (($secs == $expire) ? ' selected' : '') . ">$days " . (($secs == 86400) ? _("Tag") : _("Tage")) . "</option>\n";
    echo "<option value=\"0\"" . (empty($expire) ? ' selected' : '') . ">" . _("Unbegrenzt") . "</option>\n"
        . "</select>\n&nbsp;"
        . "<select name=\"active\">\n"
        . "<option value=\"0\"" . ((!$active) ? ' selected' : '') . ">" . _("nicht aktiviert") . "</option>\n"
        . "<option value=\"1\"" . (($active) ? ' selected' : '') . ">" . _("aktiviert") . "</option>\n"
        . "</select>&nbsp;\n"
        . "<input type=\"checkbox\" name=\"chng_date\" value=\"1\" />&nbsp;"
        . "<label for=\"chng_date\">" . _("Erstellungsdatum auf jetzt setzen") . "</label><br /><br />\n"
        . "<label for=\"view\">" . _("Sichtbar für") . ":</label>&nbsp;"
        . "<select name=\"view\">\n"
        . "<option value=\"1\"" . (($view == 1) ? ' selected' : '') . ">" . _("alle") . "</option>\n"
        . "<option value=\"2\"" . (($view == 2) ? ' selected' : '') . ">" . _("Gäste") . "</option>\n"
        . "<option value=\"3\"" . (($view == 3) ? ' selected' : '') . ">" . _("angemeldete Benutzer") . "</option>\n"
        . "<option value=\"4\"" . (($view == 4) ? ' selected' : '') . ">" . _("Administratoren") . "</option>\n"
        . "</select>\n"
        . "<br /><br />\n"
        . "<input type=\"hidden\" name=\"mid\" value=\"$mid\" />\n"
        . "<input type=\"hidden\" name=\"op\" value=\"savemsg\" />\n"
        . "<input type=\"button\" value=\"" . _("Abbrechen") . "\" onClick=\"self.location.href='" . url() . "/admin.php?op=messages'\" />\n"
        . "<input type=\"submit\" value=\"" . _("Änderungen übernehmen") . "\" />\n"
        . "</form>"
        . "<script language=\"javascript\" type=\"text/javascript\"><!--\n"
        . "document.editmessage.title.focus();\n"
        . "--></script>\n";

    CloseTable();
    include 'footer.php';
}

/**
 * @brief Mitteilung speichern
 */
function savemsg($mid, $title, $content, $expire, $active, $view, $chng_date)
{
    $system = TSystem::instance();

    if ($chng_date)
        $sql = sprintf("UPDATE `%s_message`
                        SET `title`='%s',
                            `content`='%s',
                            `date`='%u',
                            `expire`='%u',
                            `active`='%u',
                            `view`='%u'
                        WHERE `mid`='%u'",
                       $system->config->prefix,
                       $system->db->qStr(substr(trim($title), 0, 100)),
                       $system->db->qStr(trim($content)),
                       time(),
                       $expire,
                       $active,
                       $view,
                       $mid);
    else
        $sql = sprintf("UPDATE `%s_message`
                        SET `title`='%s',
                            `content`='%s',
                            `expire`='%u',
                            `active`='%u',
                            `view`='%u'
                        WHERE `mid`='%u'",
                       $system->config->prefix,
                       $system->db->qStr(substr(trim($title), 0, 100)),
                       $system->db->qStr(trim($content)),
                       $expire,
                       $active,
                       $view,
                       $mid);
    if (false === $system->db->execute($sql))
    {
        new TSessionMessages(sprintf(_("Der Datensatz #%u konnte nicht geändert werden."), $mid), 'error');
        if ($system->config->verbose)
            new TSessionMessages($system->db->error());
    }
    else
        new TSessionMessages(_("Die Mitteilung wurde geändert."));

    location('admin.php?op=messages');
}

/**
 * @brief Neue Mitteilung hinzufügen
 */
function addmsg($title, $content, $expire, $active, $view)
{
    $system = TSystem::instance();

    $sql = sprintf("INSERT INTO `%s_message`
                    (`title`,
                     `content`,
                     `date`,
                     `expire`,
                     `active`,
                     `view`)
                    VALUES ('%s',
                            '%s',
                            '%u',
                            '%u',
                            '%u',
                            '%u')",
             $system->config->prefix,
             $system->db->qStr(substr(trim($title), 0, 100)),
             $system->db->qStr(trim($content)),
             time(),
             $expire,
             $active,
             $view);

    if (false === $system->db->execute($sql))
    {
        new TSessionMessages(_("Die Mitteilung konnte nicht gespeichert werden."), 'error');
        if ($system->config->verbose)
            new TSessionMessages($system->db->error(), 'error');
    }
    else
        new TSessionMessages(_("Die Mitteilung wurde gespeichert."));

    location('admin.php?op=messages');
}

/**
 * @brief Mitteilung löschen
 */
function deletemsg($mid, $ok = 0)
{
    $system = TSystem::instance();

    if ($ok != 0)
    {
        $sql = sprintf("DELETE FROM `%s_message` WHERE `mid`='%u'",
                       $system->config->prefix,
                       $mid);
        if (false !== $system->db->execute($sql))
            new TSessionMessages(_("Die Mitteilung wurde gelöscht."));
        else
        {
            new TSessionMessages(_("Die Mitteilung konnte nicht gelöscht werden."), 'error');
            if ($system->config->verbose)
                new TSessionMessages($system->db->error());
        }
        location('admin.php?op=messages');
    }

    $sql = sprintf("SELECT `title`, `content` FROM `%s_message` WHERE `mid`='%u'",
                   $system->config->prefix,
                   $mid);
    list($title,
         $content) = $system->db->queryRow($sql);

    include 'header.php';
    GraphicAdmin();
    OpenTable();
    title(_("Löschen bestätigen"));

    echo "<div class=\"preview\">\n"
        . "<div class=\"message-box\">"
        . "<h1 class=\"title\">$title</h1>\n"
        . "<div class=\"content\">$content</div>\n"
        . "</div>\n";

    echo "<p class=\"content\" style=\"text-align: center;\">" . _("Möchten Sie die vorstehende Mitteilung löschen") . "?"
        . "<br /><br />\n"
        . "[ <a href=\"admin.php?op=messages\">"._("Nein")."</a> | "
        . "<a href=\"admin.php?op=deletemsg&amp;mid=$mid&amp;ok=1\">"._("Ja, die Mitteilung endgültig löschen")."</a> ]"
        . "</p>\n";

    CloseTable();
    include("footer.php");
}

// Funktion auswählen
//
switch ($op)
{
    case 'messages':        // Übersicht
        messages();
        break;

    case "editmsg":

        editmsg($mid, $title, $content, $mdate, $expire, $active, $view, $chng_date, $mlanguage);
    break;

    case 'addmsg':          // Hinzufügen
        $title      = argv('add_title', '');
        $content    = argv('add_content', '');
        $expire     = argv('add_expire', 0);
        $active     = argv('add_active', false);
        $view       = argv('add_view', 4);
        addmsg($title, $content, $expire, $active, $view);
        break;

    case "deletemsg":
        $mid        = argv('mid', 0);
        $ok         = argv('ok', false);
        deletemsg($mid, $ok);
        break;

    case "savemsg":
        $mid        = argv('mid', 0);
        $title      = argv('title', '');
        $content    = argv('content', '');
        $expire     = argv('expire', 0);
        $view       = argv('view', 4);
        $chng_date  = argv('chng_date', false);
        savemsg($mid, $title, $content, $expire, $active, $view, $chng_date);
        break;

}

} else {
    echo "Access Denied";
}

?>
