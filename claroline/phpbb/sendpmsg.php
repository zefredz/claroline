<?  session_start(); ?>
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

    if ($message    == '') error_die($l_emptymsg);
    if ($tousername == '') error_die($l_norecipient);

    $touserdata = get_userdata($tousername);
    if( ! $touserdata['username']) error_die($l_nouser);

    $fromuserdata = $userdata; // fromuser = current user.

    /* correct password or logged-in user, continuing with message send. */

	$message = addslashes($message);
	$time = date('Y-m-d H:i');

    $sql = "INSERT INTO `".$tbl_priv_msgs."`
            SET from_userid = '".$fromuserdata['user_id']."',
                to_userid   = '".$touserdata['user_id']."', 
                msg_time    = '".$time."', 
                msg_text    = '".$message."'";

	if(!mysql_query($sql, $db)) {
		echo $sql . " : " . mysql_error() . "<br>";
		error_die("Could not enter data into the database.");
	}

	echo "<table border=\"0\" cellpadding=\"1\" align=\"center\" valign=\"top\" width=\"".$tablewidth."\">"
	    ."<tr bgcolor=\"".$color1."\">"
        ."<td>"
        ."<center>"
	    .$l_stored."<br> \n";
	    ."<a href=\"sendpmsg.php\">".$l_sendothermsg."</a> <br> \n"
	    ."</center>"
	    ."</td>"
        ."</tr>"
        ."</table>";

} else {

/* displaying the login form */

?>
<form action="<?php echo $PHP_SELF ?>" method="post">
<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top">

<tr>
<td align="right"><b><?php echo $l_aboutpost?></b></td>
<td><?php echo $l_regusers." ".$l_cansend ?></td>
</tr>

<tr>
<td align="right"><b><?php echo $l_yourname?> : <b></td>
<td><?ph echo $userdata['username'] . " \n"; ?></td>
</tr>

<tr>
<td><b><?php echo $l_recptname?> : <b></td>
<td>
<input type="text" 
       name="tousername" 
       size="25" 
       maxlength="40" 
       value="<?php echo $tousername?>">
</td>
</tr>

<tr>
<td>
<label for="message"><b><?php echo $l_body?> :</b></label>
</td>
<td>
<textarea id="message" name="message" rows="10" cols="45" wrap="virtual"></textarea>
</td>
</tr>

<tr>
<td  colspan=2 align="center">
<input type="submit" name="submit" value="<?php echo $l_submit?>">
</tr>
</table>
</form>

<?php
}
require('page_tail.'.$phpEx);
?>