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


if ( ! $user_logged_in ) error_die("You're not logged");

$sql = "SELECT msg_id, from_userid, to_userid, msg_time, 
               poster_ip, msg_status, msg_text  
        FROM `".$tbl_priv_msgs."`
        WHERE to_userid = ".$userdata['user_id']."
        ORDER BY msg_time DESC";

$msgList = claro_sql_query_fetch_all($sql);


if ( count($msgList) == 0 )
{
    echo "<div align=\"center\">".$l_nopmsgs."</div>";
}

echo "<table>"; 

foreach($msgList as $thisMsg )
{
    $posterdata = get_userdata_from_id( $thisMsg['from_userid'] );

    echo "<tr align=\"left\">\n"

        ."<td valign=top>"
        .$l_from." <b>".$posterdata['first_name']." ".$posterdata['last_name']."</b>"

        ." ".$l_posted." : ".$thisMsg['msg_time']
        ."<hr>\n"
        .stripslashes($thisMsg['msg_text'])
        ."<hr>\n"
        ."<small>"
        ."<b>"
        ."<a href=\"../user/userInfo.php?uInfo=".$posterdata['user_id']."\">"
        .$l_profileof." ".$posterdata['first_name']." ".$posterdata['last_name']
        ."</a> \n"
        ." | "
        ."<a href=\"replypmsg.php?msgid=".$thisMsg['msg_id']."&quote=1\">"
        ."<img src=\"".$reply_wquote_image."\" border=\"0\" alt=\"".$l_replyquote."\">"
        ."</a>\n"
        ." | "
        ."<a href=\"replypmsg.php?msgid=".$thisMsg['msg_id']."\">".$l_reply."</a>\n"
        ." | "
        ."<a href=\"".$url_phpbb."/delpmsg.php?msgid=".$thisMsg['msg_id']."\">"
        .$l_delete
        ."</a>\n"
        ."</b>"
        ."</small>"
        ."</td>\n"
        ."</tr>";

	} // end foreach

	$sql = "UPDATE `".$tbl_priv_msgs."` 
            SET msg_status='1' 
            WHERE to_userid = '".$userdata['user_id']."'";

	$result = claro_sql_query($sql);

    echo "</table>";


require('page_tail.php');
?>