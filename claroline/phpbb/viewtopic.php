<?php
/***************************************************************************
viewtopic.php  -  description
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

$pagetitle = $l_topictitle;
$pagetype  = 'viewtopic';

$topicSettingList = get_topic_settings($topic);

$forum            = $topicSettingList['forum_id'];
$topic_subject    = own_stripslashes($topicSettingList['topic_title']);
$lock_state       = $topicSettingList['topic_status'];

$forumSettingList = get_forum_settings($forum, $topic);

$forum_name       = own_stripslashes($forumSettingList['forum_name']);


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


include('page_header.php');

if ( ! $start) $start = 0;

$postLister = new postLister($topic, $start, $posts_per_page);

$postList   = $postLister->get_post_list();

$pagerUrl = $_SERVER['PHP_SELF'] . "?topic=" . $topic;

$postLister->disp_pager_tool_bar($pagerUrl);

echo '<table class="claroTable" width="100%">'
.    '<tr align="left">'
.    '<th class="superHeader">'
;

/*
* EMAIL NOTIFICATION COMMANDS
*/

// For (Added for claro 1.5) execute notification preference change
// if the command was called

if ($cmd && $_uid)
{
    switch ($cmd)
    {
        case 'exNotify' :
            request_topic_notification($_uid, $topic);
        break;

        case 'exdoNotNotify' :
            cancel_topic_notification($_uid, $topic);
        break;
    }

    $increaseTopicView = false; // the notification change command doesn't
    // have to be considered as a new topic
    // consult
}
else
{
    $increaseTopicView = true;
}


// For (Added for claro 1.5) allow user to be have notification for this
// topic or disable it

if ( isset($_uid) )  //anonymous user do not have this function
{
    echo '<div style="float: right;">' . "\n"
    .    '<small>'
    ;

    if (is_topic_notification_requested($_uid, $topic))   // display link NOT to be notified
    {
        echo '<img src="' . $imgRepositoryWeb . 'email.gif">'
        .    $l_notify
        .    ' [<a href="' . $_SERVER['PHP_SELF'] . '?topic=' . $topic . '&amp;cmd=exdoNotNotify">'
        .    $langDisable
        .    '</a>]'
        ;
    }
    else   //display link to be notified for this topic
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?topic=' . $topic . '&amp;cmd=exNotify">'
        .    '<img src="' . $imgRepositoryWeb . 'email.gif"> '
        .    $l_notify
        .    '</a>'
        ;
    }

    echo '</small>' . "\n"
    .    '</div>' . "\n"
    ;

} //end not anonymous user

echo $topic_subject
.    '</th>' . "\n"
.    '</tr>' . "\n"
;

foreach($postList as $thisPost )
{
    // Check if the forum post is after the last login
    // and choose the image according this state

    $post_time = datetime_to_timestamp($thisPost['post_time']);

    if($post_time < $last_visit) $postImg = 'post.gif';
    else                         $postImg = 'post_hot.gif';

    echo '<tr>' . "\n"
    .    '<th class="headerX">' . "\n"
    .    '<img src="' . $imgRepositoryWeb . $postImg . '" alt="">'
    .    $l_author . ' : <b>' . $thisPost['firstname'] . ' ' . $thisPost['lastname'] . '</b> '
    .    '<small>' . $l_posted . ' : '
    .    claro_disp_localised_date($dateTimeFormatLong, datetime_to_timestamp($thisPost['post_time']))
    .    '</small>' . "\n"
    .    '</th>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td>' . "\n"
    .    claro_parse_user_text(own_stripslashes($thisPost['post_text'])) . "\n"
    ;

    if($is_allowedToEdit)
    {
        echo '<p>' . "\n"
        .    '<a href="editpost.php'
        .    '?post_id=' . $thisPost['post_id'] . '&amp;topic=' . $topic . '&amp;forum=' . $forum . '">'
        .    '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . $langEditDel . '">'
        .    '</a>' . "\n"
        .    '<a href="editpost.php'
        .    '?post_id=' . $thisPost['post_id'] . '&amp;topic=' . $topic . '&amp;forum=' . $forum
        .    '&amp;delete=delete&amp;submit=submit">'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" '
        .    'border="0" alt="' . $langEditDel . '">'
        .    '</a>' . "\n"
        .    '</p>' . "\n"
        ;
    }

    echo '</td>' . "\n"
    .    '</tr>' . "\n"
    ;
} // end for each

if ($increaseTopicView)
{
    $sql = "UPDATE `".$tbl_topics."`
                 SET   topic_views = topic_views + 1
                 WHERE topic_id    = '" . $topic . "'";

    claro_sql_query($sql);
}

echo '</table>' . "\n";

$postLister->disp_pager_tool_bar($pagerUrl);

require 'page_tail.php';
?>