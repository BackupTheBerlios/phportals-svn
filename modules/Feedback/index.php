<?php

/************************************************************************/
/* PHP-NUKE: Web Portal System                                          */
/* ===========================                                          */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi (fbc@mandrakesoft.com)         */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* Based on php Addon Feedback 1.0                                      */
/* Copyright (c) 2001 by Jack Kozbial                                   */
/* http://www.InternetIntl.com                                          */
/* jack@internetintl.com                                                */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

if (!eregi("modules.php", $PHP_SELF)) {
    die ("You can't access this file directly...");
}

require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
get_lang($module_name);

/**********************************/
/* Configuration                  */
/*                                */
/* You can change this:           */
/* $index = 0; (right side off)   */
/**********************************/
$index = 1;
$subject = "$sitename "._FEEDBACK."";
/**********************************/

include("header.php");

$form_block = "
    <center><font class=\"title\"><b>$sitename: "._FEEDBACKTITLE."</b></font>
    <br><br><font class=\"content\">"._FEEDBACKNOTE."</font>
    <FORM METHOD=\"post\" ACTION=\"modules.php?name=Feedback\">
    <P><strong>"._YOURNAME.":</strong><br>
    <INPUT type=\"text\" NAME=\"sender_name\" VALUE=\"$sender_name\" SIZE=30></p>
    <P><strong>"._YOUREMAIL.":</strong><br>
    <INPUT type=\"text\" NAME=\"sender_email\" VALUE=\"$sender_email\" SIZE=30></p>
    <P><strong>"._MESSAGE.":</strong><br>
    <TEXTAREA NAME=\"message\" COLS=30 ROWS=5 WRAP=virtual>$message</TEXTAREA></p>
    <INPUT type=\"hidden\" name=\"opi\" value=\"ds\">
    <P><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\""._SEND."\"></p>
    </FORM></center>
";

OpenTable();
if ($opi != "ds") {
    echo "$form_block";
} elseif ($opi == "ds") {
    if ($sender_name == "") {
	$name_err = "<center><font class=\"option\"><b><i>"._FBENTERNAME."</i></b></font></center><br>";
	$send = "no";
    } 
    if ($sender_email == "") {
	$email_err = "<center><font class=\"option\"><b><i>"._FBENTEREMAIL."</i></b></font></center><br>";
	$send = "no";
    } 
    if ($message == "") {
    	$message_err = "<center><font class=\"option\"><b><i>"._FBENTERMESSAGE."</i></b></font></center><br>";
	$send = "no";
    } 
    if ($send != "no") {
	$msg = "$sitename\n\n";
	$msg .= ""._SENDERNAME.": $sender_name\n";
	$msg .= ""._SENDEREMAIL.": $sender_email\n";
	$msg .= ""._MESSAGE.": $message\n\n";
	$to = $adminmail;
	$mailheaders = "From: $nukeurl <> \n";
	$mailheaders .= "Reply-To: $sender_email\n\n";
	mail($to, $subject, $msg, $mailheaders);
	echo "<P><center>"._FBMAILSENT."</center></p>";
	echo "<P><center>"._FBTHANKSFORCONTACT."</center></p>";
    } elseif ($send == "no") {
	OpenTable2();
	echo "$name_err";
	echo "$email_err";
	echo "$message_err";
	CloseTable2();
	echo "<br><br>";
	echo "$form_block";  
    } 
}

CloseTable();   
include("footer.php");

?>