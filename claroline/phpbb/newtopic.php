<?php  

/***************************************************************************
                            newtopic.php  -  description
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
// Set the error reporting to a sane value. 
// It will NOT report uninitialized variables
error_reporting  (E_ERROR | E_WARNING | E_PARSE); 

if($cancel)
{
	header('Location: viewforum.php?forum='.$forum);
	exit();
}

require 'functions.php';
require 'config.php';

$pagetitle = 'New Topic';
$pagetype =  'newtopic';

$forumSettingList = get_forum_settings($forum);

$forum_name 		= $forumSettingList['forum_name'  ];
$forum_access 		= $forumSettingList['forum_access'];
$forum_type 		= $forumSettingList['forum_type'  ];
$forum_groupId 		= $forumSettingList['idGroup'     ];
$forum_groupname	= $forumSettingList['nameGroup'   ];
$forum_id 			= $forum;

// Check if the forum isn't attached to a group, 
// or -- if it is attached --, check the user 
// is allowed to see the current group forum.

/* 
 * Check if the topic isn't attached to a group,  or -- if it is attached --, 
 * check the user is allowed to see the current group forum.
 */

if (   ! is_null($forumSettingList['idGroup']) 
    && ( $forumSettingList['idGroup'] != $_gid || ! $is_groupAllowed) )
{
    // NOTE : $forumSettingList['idGroup'] != $_gid is necessary to prevent any hacking 
    // attempt like rewriting the request without $cidReq. If we are in group 
    // forum and the group of the concerned forum isn't the same as the session 
    // one, something weird is happening, indeed ...

    die ('<center>not allowed</center>');
}



if($submit)
{
    // Commented by the Claroline team
    //
    // Either valid user/pass, or valid session. continue with post.. but first:
    // Check that, if this is a private forum, the current user can post here.
    //
    // if ($forum_type == 1)
    // {
    //      if (!check_priv_forum_auth($userdata[user_id], $forum, TRUE, $db))
    //      {
    //          error_die("$l_privateforum $l_nopost");

    /*------------------------------------------------------------------------
                                PREPARE THE DATA
      ------------------------------------------------------------------------*/

    
    /*
     * SUBJECT
     */
    
    $subject = strip_tags($subject);
    $subject = trim($subject);
    $subject = addslashes($subject);

    /*
     * MESSAGE
     */

    if($allow_html == 0 || isset($html)) $message = htmlspecialchars($message);
    $message = trim($message);
    $message = addslashes($message);

    /*
     * USER (ADDED FOR CLAROLINE)
     */
    
    $userLastname  = addslashes($userdata['last_name']);
    $userFirstname = addslashes($userdata['first_name']);
    $poster_ip     = $REMOTE_ADDR;

    $time      = date('Y-m-d H:i');
    
    // prevent to go further if the fields are actually empty
    if( strip_tags($message) == '' || $subject == '' ) error_die($l_emptymsg);

    /*------------------------------------------------------------------------
                            RECORD THE DATA
      ------------------------------------------------------------------------*/


    $topic_id = create_new_topic($subject, $time, $forum_id, 
                          $userdata['user_id'], $userFirstname, $userLastname);
    if ($topic_id)
    {
        create_new_post($topic_id, $forum_id, $userdata['user_id'], $time, $poster_ip, 
                             $userLastname, $userFirstname, $message);
    }


    /*------------------------------------------------------------------------
                            DISPLAY SUCCES MESSAGE
      ------------------------------------------------------------------------*/

    include('page_header.php');

    disp_confirmation_message ($l_stored, $forum_id, $topic_id);

} // end if submit
else
{
    include('page_header.php');

    if ( ! $_uid)    // ADDED BY CLAROLINE: exclude non identified visitors
    {
        error_die($langLoginBeforePost1.'<br />'
                  .$langLoginBeforePost2
                  ."<a href=../../index.php>".$langLoginBeforePost3.".</a>");
    }               // END ADDED BY CLAROLINE exclude visitors unidentified
    
?>

<form action="<?php echo $php_self?>" method="post">

<table border="0">

<tr valign="top">

<td align="right"><label for="subject"><?php echo $l_subject?></label> :</td>
<td><input type="text" name="subject" id="subject" size="50" maxlength="100"></td>

</tr>

<tr valign="top">

<td align="right">
<?php echo $l_body?> :
<br><br>
</td>

<td>
<?php claro_disp_html_area('message'); ?>
</td>

</tr>

<tr>

<td></td>

<td>
<input type="hidden" name="forum" value="<?php echo $forum?>">
<input type="submit" name="submit" value="<?php echo $l_submit?>">
&nbsp;<input type="submit" name="cancel" value="<?php echo $l_cancelpost?>">
</td>

</tr>

</table>

</form>

<?php
}

require('page_tail.php');
?>