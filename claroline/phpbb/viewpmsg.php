<?php  session_start();
/***************************************************************************
                          viewpmsg.php  -  description
                             -------------------
    begin                : Wed June 19 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

require 'functions.php';
require 'config.php';

$pagetitle = 'Private Messages';
$pagetype =  'privmsgs';
require 'page_header.php';

if ( ! $submit && !$user_logged_in)
{
//...
} else {

   if ( ! $user_logged_in ) error_die("You're not logged");



    $sql = "SELECT * FROM `".$tbl_priv_msgs."`
            WHERE to_userid = ".$userdata['user_id']."
            ORDER BY msg_time DESC";

    $msgList = claro_sql_query_fetch_all($sql);



echo "<table>"
     ."<tr>\n"
     ."<td colspan=\"2\">".$l_from."</td>\n"
     ."</tr>\n";

    if ( count($msgList) == 0 )
    {
        echo "<td colspan = 2 align=center>".$l_nopmsgs."</td></tr>\n";
    }

    foreach($msgList as $thisMsg )
    {
        $posterdata = get_userdata_from_id($thisMsg['from_userid'], $db);
        $posts = $posterdata['user_posts'];

        echo "<tr align=\"left\">\n"

            ."<td valign=top>"
            ."<b>".$posterdata['username']."</b><br>\n"
            .$posts < 15 ? "<font size=-2>".$rank1."<br>\n" : $rank2."<br>\n"
            ."<br>".$l_posts." : ".$posts."<br>\n"
            .$l_location." : ".$posterdata['user_from']."<br>"
            ."</td>\n"

            ."<td>\n"
            ."<img src=\"".$posticon."\">".$l_posted." : ".$thisMsg['msg_time']
            ."<hr>\n"
            .stripslashes($thisMsg['msg_text'])
            ."<hr>\n"
            ."<a href=\"bb_profile.php?mode=view&user=".$posterdata['user_id']."\">"
            .$l_profileof." ".$thisMsg['poster_name']
            ."</a> \n";

		if($posterdata['user_viewemail'] != 0)
        {
			echo "<a href=\"mailto:".$posterdata['user_email']."\">"
                 .$l_email." ".$posterdata['username']
                 ."</a> \n";
        }

        echo "<img src=\"images/div.gif\">\n"
            ."<a href=\"replypmsg.php?msgid=".$thisMsg['msg_id']."&quote=1\">"
            ."<img src=\"".$reply_wquote_image."\" border=\"0\" alt=\"".$l_replyquote."\">"
            ."</a>\n"
            ."<img src=\"images/div.gif\">\n"
            ."<a href=\"replypmsg.php?msgid=".$thisMsg['msg_id']."\">".$l_reply."</a>\n"
            ."<IMG SRC=\"images/div.gif\">\n"
            ."<a href=\"".$url_phpbb."/delpmsg.php?msgid=".$thisMsg['msg_id']."\">"
            .$l_delete
            ."</a>\n"
            ."</td>\n"
            ."</tr>";

	} // end foreach

	$sql = "UPDATE `".$tbl_priv_msgs."` 
            SET msg_status='1' 
            WHERE to_userid = '".$userdata['user_id']."'";

	$result = claro_sql_query($sql);

    echo "</table>"
        ."</td>";

    echo "<div align=\"right\">\n";

    make_jumpbox();

	echo "</div>\n";

} // if/else

require('page_tail.php');
?>