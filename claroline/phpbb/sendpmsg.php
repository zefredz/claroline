<?php
/***************************************************************************
                          sendpmsg.php  -  description
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

/**
 * sendpmsg.php - Nathan Codding
 * - Used for sending private messages between users of the BB.
 */
require 'functions.php';
require 'config.php';

$pagetitle = 'Send Private Message';
$pagetype  = 'sendprivmsg';

require 'page_header.php';


if($submit) {

    $message = trim($_REQUEST['message']);

    if ($message  == '')             error_die($l_emptymsg);
    if ($_REQUEST['touserid'] == '') error_die($l_norecipient);

    $touserdata         = get_userdata_from_id($_REQUEST['touserid']);
    if (! $touserdata ) error_die($l_norecipient);

    $fromuserdata = $userdata; // fromuser = current user.
    if ($userdata['user_id'] == -1) error_die('operation not allowed');
   

    $message = addslashes($message);
    $time    = date('Y-m-d H:i');

    $sql = "INSERT INTO `".$tbl_priv_msgs."`
            SET from_userid = '".$fromuserdata['user_id']."',
                to_userid   = '".$touserdata['user_id']."', 
                msg_time    = '".$time."', 
                msg_text    = '".$message."'";

    if ( claroline_sql_query($sql) !== false)
    {
        disp_confirmation_message($l_stored."<br />\n"    
                               ."<a href=\"sendpmsg.php\">"
                               .$l_sendothermsg
                               ."</a><br />\n");
    }
    else
    {
        error_die('Could not enter data into the database.');
    }

}
else
{
    $touserid = 1;
    $touserdata = get_userdata_from_id($touserid);

/* displaying the login form */

?>
<form action="<?php echo $PHP_SELF ?>" method="post">
<input type="hidden" name="touserid" value="<?php echo $touserdata['user_id'] ?>">
<table border="0" cellpadding="1">

<tr valign="top">
<td align="right"><?php echo $l_yourname ?> : </td>
<td><?php echo $userdata['first_name'].' '.$userdata['last_name']; ?></td>
</tr>

<tr valign="top">
<td align="right"><?php echo $l_recptname?> : </td>
<td><?php echo $touserdata['first_name'].' '.$touserdata['last_name']; ?></td>
</tr>

<tr valign="top">
<td>
<label for="message"><b><?php echo $l_body?> :</b></label>
</td>
<td>
<textarea id="message" name="message" rows="10" cols="45" wrap="virtual"></textarea>
</td>
</tr>

<tr valign="top">
<td>
</td>
<td>
<input type="submit" name="submit" value="<?php echo $l_submit?>">
</tr>
</table>
</form>

<?php
}
require 'page_tail.php';
?>