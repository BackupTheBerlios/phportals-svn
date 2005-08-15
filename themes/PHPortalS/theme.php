<?php

$bgcolor1 = "red";
$bgcolor2 = "blue";
$bgcolor3 = "green";
$bgcolor4 = "yellow";
$textcolor1 = "navy";
$textcolor2 = "darkgreen";

function OpenTable($layer = 1)
{
    echo "<div class=\"layer-$layer\">\n";
}

function OpenTable2()
{
    OpenTable(2);
}

function CloseTable()
{
    echo "</div>\n";
}

function CloseTable2()
{
    CloseTable();
}

function FormatStory($thetext, $notes, $aid, $informant) {
    global $anonymous;
    if ($notes != "") {
    $notes = "<b>"._NOTE."</b> <i>$notes</i>\n";
    } else {
    $notes = "";
    }
    if ("$aid" == "$informant") {
    echo "<font class=\"content\">$thetext<br>$notes</font>\n";
    } else {
    if($informant != "") {
        $boxstuff = "<a href=\"modules.php?name=Your_Account&amp;op=userinfo&amp;uname=$informant\">$informant</a> ";
    } else {
        $boxstuff = "$anonymous ";
    }
    $boxstuff .= ""._WRITES." <i>\"$thetext\"</i> $notes\n";
    echo "<font class=\"content\">$boxstuff</font>\n";
    }
}

function themeheader()
{
    global $index, $system;

    if ($index == 1)
        $colspan = 3;
    else
        $colspan = 2;

    echo "\n"
        . "<body>"
        . "<a id=\"TOP\" name=\"TOP\"></a>\n"
        . "<table id=\"frame\">\n"
        . "<tr class=\"frame-top\">\n"
        . "<td colspan=\"$colspan\" class=\"frame-top\">\n"
        . "<div class=\"sitelinks\">\n"
        . " | <a class=\"sitelinks\" target=\"_blank\" href=\"https://sourceforge.net/projects/phportals/\">SourceForge</a>\n"
        . " | <a class=\"sitelinks\" target=\"_blank\" href=\"https://sourceforge.net/tracker/?atid=394841&group_id=28947&func=browse\">Bugs</a>\n"
        . " | <a class=\"sitelinks\" target=\"_blank\" href=\"https://sourceforge.net/project/showfiles.php?group_id=28947\">Downloads</a>\n"
        . " | <a class=\"sitelinks\" target=\"_blank\" href=\"http://www.nukeboards.de/forum-PHPortalS_2668.html\">Forum bei Nukeboards</a>\n"
        . " |</div>\n"
        . "<div class=\"sitelogo\">\n"
        . "<img src=\"themes/PHPortalS/images/logo.png\" alt=\"PHPortalS\" width=\"156\" height=\"60\" title=\"PHPortalS\" class=\"sitelogo\" />\n";

    if ($system->config->banners == 1)
    {
        echo "<div class=\"banner\" title=\"Werbung\">";
        include 'banners.php';
        echo "</div>\n";
    }

    echo "</div>\n"
        . "</td>\n"
        . "</tr>\n"
        . "<tr class=\"frame-body\">\n"
        . "<td class=\"left-row\">\n";
    blocks('left');
    echo "</td>\n"
        . "<td class=\"center-row\">\n";
}

function themefooter()
{
    global $index, $system;

    echo "</td>\n";

    if ($index == 1)
    {
        $colspan = 3;
        echo "<td class=\"right-row\">\n";
        blocks('right');
        echo "</td>\n";
    }
    else
        $colspan = 2;

    echo "</tr>\n"
        . "<tr class=\"frame-footer\">\n"
        . "<td colspan=\"$colspan\" class=\"frame-footer\">\n";
    for ($i = 1; $i < 5; $i++)
        echo "<div class=\"footer\">" . $system->config->get("foot$i", '') . "</div>\n";
    echo "<br style=\"clear: both;\"></td>\n"
        . "</tr>\n"
        . "<table>\n";
}

function themeindex ($aid, $informant, $time, $title, $counter, $topic, $thetext, $notes, $morelink, $topicname, $topicimage, $topictext) {
    global $anonymous;
    echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" bgcolor=\"000000\" width=\"100%\"><tr><td>"
        ."<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr><td bgcolor=\"ffffff\">"
        ."<b>$title</b><br>"
        ."<font class=\"tiny\">"
        .""._POSTEDBY." <b>";
    formatAidHeader($aid);
    echo "</b> "._ON." $time $timezone ($counter "._READS.")<br>"
    ."<b>"._TOPIC."</b> <a href=\"modules.php?name=Search&amp;query=&amp;topic=$topic&amp;author=\">$topictext</a><br>"
    ."</font></td></tr><tr><td bgcolor=\"ffffff\">";
    FormatStory($thetext, $notes, $aid, $informant);
    echo "<br><br>"
        ."</td></tr><tr><td bgcolor=\"ffffff\" align=\"right\">"
        ."<font class=\"content\">$morelink</font>"
        ."</td></tr></table></td></tr></table>"
    ."<br>";
}

function themearticle ($aid, $informant, $datetime, $title, $thetext, $topic, $topicname, $topicimage, $topictext) {
    global $admin, $sid;
    if ("$aid" == "$informant") {
    echo"
    <table border=0 cellpadding=0 cellspacing=0 align=center bgcolor=000000 width=100%><tr><td>
    <table border=0 cellpadding=3 cellspacing=1 width=100%><tr><td bgcolor=FFFFFF>
    <b>$title</b><br><font class=tiny>".translate("Posted on ")." $datetime";
    if ($admin) {
        echo "&nbsp;&nbsp; $font2 [ <a href=admin.php?op=EditStory&sid=$sid>".translate("Edit")."</a> | <a href=admin.php?op=RemoveStory&sid=$sid>".translate("Delete")."</a> ]";
    }
    echo "
    <br>".translate("Topic").": <a href=modules.php?name=Search&amp;query=&topic=$topic&author=>$topictext</a>
    </td></tr><tr><td bgcolor=ffffff>
    $thetext
    </td></tr></table></td></tr></table><br>";
    } else {
    if($informant != "") $informant = "<a href=\"modules.php?name=Your_Account&amp;op=userinfo&uname=$informant\">$informant</a> ";
    else $boxstuff = "$anonymous ";
    $boxstuff .= "".translate("writes")." <i>\"$thetext\"</i> $notes";
    echo "
    <table border=0 cellpadding=0 cellspacing=0 align=center bgcolor=000000 width=100%><tr><td>
    <table border=0 cellpadding=3 cellspacing=1 width=100%><tr><td bgcolor=FFFFFF>
    <b>$title</b><br><font class=content>".translate("Contributed by ")." $informant ".translate("on")." $datetime</font>";
    if ($admin) {
        echo "&nbsp;&nbsp; $font2 [ <a href=admin.php?op=EditStory&sid=$sid>".translate("Edit")."</a> | <a href=admin.php?op=RemoveStory&sid=$sid>".translate("Delete")."</a> ]";
    }
    echo "
    <br>".translate("Topic").": <a href=modules.php?name=Search&amp;query=&topic=$topic&author=>$topictext</a>
    </td></tr><tr><td bgcolor=ffffff>
    $thetext
    </td></tr></table></td></tr></table><br>";
    }
}

function themesidebox($title, $content)
{
    echo "<h1 class=\"sidebox\">$title</h1>\n"
        . "<div class=\"sidebox\">$content</div>\n";
}

?>
