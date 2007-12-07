<?php
/***************************************************************************
                            replypmsg.php  -  description
                             -------------------
    begin                : Sat June 17 2000
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

$pagetitle = 'Post PM Reply';
$pagetype  = "pmreply";

require 'page_header.php';

if($submit) {
	if($message == '') error_die($l_emptymsg.' '.$l_tryagain);

	$sql = "SELECT u.* FROM `".$tbl_users."` u, `".$tbl_priv_msgs."` p 
	        WHERE  u.user_id = p.to_userid
              AND  p.msg_id  = '".$msgid."'";

	$result       = claro_sql_query($sql);
	$fromuserdata = mysql_fetch_array($result, MYSQL_ASSOC);

	$message = addslashes($message);
	$time    = date('Y-m-d H:i');

	$sql = "SELECT from_userid 
            FROM `".$tbl_priv_msgs."` 
            WHERE msg_id = '".$msgid."'";

    $touserid = claro_sql_query_get_single_value($sql);

	$sql = "INSERT INTO `".$tbl_priv_msgs."` 
            SET from_userid = ".$fromuserdata['user_id']."', 
                to_userid   = '".$touserid."', 
                msg_time    = '".$time."', 
                msg_text    = '".$message."', 
                poster_ip   = '".$poster_ip."'";

    $result = claro_sql_query($sql);
	
   echo "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" align=\"center\" width=\"".$tablewidth."\">"
       ."<tr>"
       ."<td><center>".$l_pmposted."</center></td>"
        ."</tr>"
        ."</table>";
		
} // end if submit
else
{

    $sql = "SELECT from_userid, to_userid 
            FROM `".$tbl_priv_msgs."` 
            WHERE msg_id = '".$msgid."'";

	$result = claro_sql_query_fetch_all($sql);
    if ( count($result) == 0 ) error_die('Message not found');
    else                       $row = $result[0];
	$fromuserdata = get_userdata_from_id($row['from_userid'], $db);
	$touserdata   = get_userdata_from_id($row['to_userid'], $db);


	if ( $user_logged_in && ($userdata['user_id'] != $touserdata['user_id']) ) {
		error_die('You can\'t reply to that message. It wasn\'t sent to you.');
	}

?>
<form action="<?php echo $php_self?>" method="post">
<table border="0" cellpadding="1" align="center" width="95%">

<tr>
<td><b><?php echo $l_aboutpost?>:</b></td>
<td><?php echo $l_regusers." ".$l_cansend ?></td> 
</tr>

<tr>
<td><b><?php echo $l_yourname?>:<b></td>
<td>
<?php
    if ($user_logged_in) echo $userdata['username']."\n";
    else                 echo $touserdata['username']."\n";
?>
</td>
</tr>

<tr align="left">
<td><b><?php echo $l_recptname?>:<b></td>
<td><?php echo $fromuserdata['username']?></td>
</tr>

<tr align="left">
<td>
<label for="message"><b><?php echo $l_body?>:</b></label><br><br>
		<?php
		if($quote) {

            $sql = "SELECT p.msg_text, p.msg_time, u.username 
                    FROM `".$tbl_priv_msgs."` p, 
                         `".$tbl_users."` u  
                    WHERE p.msg_id      = '".$msgid."' 
                      AND p.from_userid = u.user_id";

            if($result = mysql_query($sql, $db)) {
                $m                  = mysql_fetch_array($result,MYSQL_ASSOC);
                $m['post_time']     = $m['msg_time'];
                $text               = stripslashes($text);
                $syslang_quotemsg   = get_syslang_string($sys_lang, 'l_quotemsg');
                eval("\$reply = \"$syslang_quotemsg\";");
            }
            else {
                error_die('Problem with getting the quoted message.');
            }
		}
		?>
</td>

<td>
<textarea name="message" id="message" rows=10 cols=45 wrap="virtual"><?php echo $reply?></textarea>
</td>

</tr>

<tr>
<td  colspan=2 align="center">
<input type="hidden" name="msgid" value="<?php echo $msgid?>">
<input type="hidden" name="quote" value="<?php echo $quote?>">
<input type="submit" name="submit" value="<?php echo $l_submit?>">
</td>
</tr>

</table>

</form>
<?php
} // end else if submit

require 'page_tail.php';
?>
