<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*>>>>>>>>>>>>  ANNOUNCEMENTS module <<<<<<<<<<<<*/
/*
 * Originally written  by Thomas Depraetere <depraetere@ipm.ucl.ac.be> 15 January 2002.
 * Partially rewritten by Hugues Peeters <peeters@ipm.ucl.ac.be> 19 April 2002.
 * Rewritten again     by Hugues Peeters <peeters@ipm.ucl.ac.be> 5 April 2004
 *
 * The script works with the 'annoucement' tables in the main claroline table
 *
 * DB Table structure:
 * ---
 *
 * id         : announcement id
 * contenu    : announcement content
 * temps      : date of the announcement introduction / modification
 * title      : optionnal title for an announcement
 * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * Script Structure:
 * ---
 *
 *        commands
 *            move up and down announcement
 *            delete announcement
 *            delete all announcements
 *            modify announcement
 *            submit announcement (new or modified)
 *
 *        display
 *            title
 *          button line
 *          form
 *            announcement list
 *            form to fill new or modified announcement
 *
 */


/*==========================
   CLAROLINE MAIN SETTINGS
  ==========================*/

$tlabelReq = "CLANN___";

require '../inc/claro_init_global.inc.php';
define("CONFVAL_LOG_ANNOUNCEMENT_INSERT",FALSE);
define("CONFVAL_LOG_ANNOUNCEMENT_DELETE",FALSE);
define("CONFVAL_LOG_ANNOUNCEMENT_UPDATE",FALSE);

if ( !$_cid ) claro_disp_select_course();

include($includePath.'/lib/events.lib.inc.php');
include($includePath.'/lib/claro_mail.lib.inc.php');

claro_set_display_mode_available(TRUE);

//set flag following init settings
$is_allowedToEdit = claro_is_allowed_to_edit();

$courseId         = $_course['sysCode'];
$userLastLogin    = $_user['lastLogin'];

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_announcement = $tbl_cdb_names['announcement'];
$tbl_cdb_names = claro_sql_get_main_tbl();
$tbl_course_user = $tbl_cdb_names['rel_course_user'];
$tbl_user        = $tbl_cdb_names['user'];

// DEFAULT DISPLAY

$displayForm           = FALSE;
$displayList           = TRUE;
$displayButtonLine     = $is_allowedToEdit && $cmd != 'rqEdit' && $cmd != 'rqCreate';

/*============================================================================
                     COMMANDS SECTION (COURSE MANAGER ONLY)
  ============================================================================*/

if($is_allowedToEdit) // check teacher status
{

    /*------------------------------------------------------------------------
                             MOVE UP AND MOVE DOWN COMMANDS
     -------------------------------------------------------------------------*/

    if ( $_REQUEST['cmd'] == 'exMvDown' )
    {
        moveEntry($_REQUEST['id'],'DOWN');
    }

    if ( $_REQUEST['cmd'] == 'exMvUp' )
    {
        moveEntry($_REQUEST['id'],'UP');
    }

    /*------------------------------------------------------------------------
                          DELETE ANNOUNCEMENT COMMAND
    --------------------------------------------------------------------------*/

    if ($_REQUEST['cmd'] == 'exDelete')
    {
        $sql = "DELETE FROM  `".$tbl_announcement."`
                WHERE id=\"".$_REQUEST['id']."\"";

        if ( claro_sql_query($sql) )
        {
            $message = $langAnnDel;
            if (CONFVAL_LOG_ANNOUNCEMENT_DELETE)
            {
                event_default("ANNOUNCEMENT",array ("DELETE_ENTRY"=>$_REQUEST['id']));
            }
        }
        else
        {
            //error on delete
        }
    }


/*----------------------------------------------------------------------------
                        DELETE ALL ANNOUNCEMENTS COMMAND
  ----------------------------------------------------------------------------*/

    if ($_REQUEST['cmd'] == 'exDeleteAll')
    {
        $sql = "DELETE FROM  `".$tbl_announcement."`";

        if ( claro_sql_query($sql) )
        {
            $message = $langAnnEmpty;
        }
        if (mysql_error()==0)
        {
            if (CONFVAL_LOG_ANNOUNCEMENT_DELETE)
            {
                event_default("ANNOUNCEMENT",array ("DELETE_ENTRY"=>"ALL"));
            }
        }
        else
        {
            //error on delete
        }
    }


    /*------------------------------------------------------------------------
                               EDIT ANNOUNCEMENT COMMAND
    --------------------------------------------------------------------------*/


    if ( $_REQUEST['cmd'] == 'rqEdit' )
    {
        // RETRIEVE THE CONTENT OF THE ANNOUNCEMENT TO MODIFY

        $sql = "SELECT id, title, contenu content
                FROM  `".$tbl_announcement."`
                WHERE id=\"".$_REQUEST['id']."\"";

        list( $announcementToEdit ) =  claro_sql_query_fetch_all($sql);

        $displayForm = TRUE;
        $nextCommand = 'exEdit';

    }

    /*------------------------------------------------------------------------
                            CREATE NEW ANNOUNCEMENT COMMAND
      ------------------------------------------------------------------------*/


    if ($_REQUEST['cmd'] == 'rqCreate')
    {
        $displayForm = TRUE;
        $nextCommand = 'exCreate';
    }


    /*------------------------------------------------------------------------
                          SUBMIT ANNOUNCEMENT COMMAND
     -------------------------------------------------------------------------*/

    if ($_REQUEST['cmd'] == 'exCreate' || $_REQUEST['cmd'] == 'exEdit')
    {
        /* MODIFY ANNOUNCEMENT */

        if($_REQUEST['cmd'] == 'exEdit') // there is an Id => the announcement already exists => udpate mode
        {
            $sql = "UPDATE  `".$tbl_announcement."`
                    SET contenu= \"".trim($_REQUEST['newContent'])."\",
                        temps  = NOW(),
                        `title`  = \"".trim($_REQUEST['title'])."\"
                    WHERE id=\"".$_REQUEST['id']."\"";

            if (claro_sql_query($sql))
            {
                $message = $langAnnModify;
                if (CONFVAL_LOG_ANNOUNCEMENT_UPDATE)
                {
                    event_default("ANNOUNCEMENT",array ("UPDATE_ENTRY"=>$_REQUEST['id']));
                }
            }
            else
            {
                //error on UPDATE
            }
        }

        /* CREATE NEW ANNOUNCEMENT */

        elseif ($_REQUEST['cmd'] == 'exCreate')
        {
            // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT

            $sql = "SELECT MAX(ordre)
                    FROM  `".$tbl_announcement."`";

            $result = claro_sql_query($sql);

            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;

            // INSERT ANNOUNCEMENT

            $sql = "INSERT INTO  `".$tbl_announcement."`
                    SET title =\"".trim($_REQUEST['title'])."\",
                        contenu = \"".trim($_REQUEST['newContent'])."\",
                    temps = NOW(),
                    ordre =\"".$order."\"";

            $insert_id = claro_sql_query_insert_id($sql);

            if ( $insert_id )
            {
                $message = $langAnnAdd;
                if (CONFVAL_LOG_ANNOUNCEMENT_INSERT)
                {
                    event_default("ANNOUNCEMENT",array ("INSERT_ENTRY"=>$insert_id));
                }
            }
            else
            {
                //error on insert
            }
        } // end elseif cmd == exCreate

        /* SEND EMAIL (OPTIONAL) */

        if($emailOption==1)
        {
            // sender name and email
            $courseSender =  $_user['firstName'] . ' ' . $_user['lastName'];
        
            // email subject
            $emailSubject = "[" . $siteName. " - " . $_course['officialCode'] . "] ";
            if (trim($_REQUEST['title'])) $emailSubject .= stripslashes(trim($_REQUEST['title']));
            else                          $emailSubject .= $professorMessage;

            // email message
            $msgContent = stripslashes($newContent);
            $msgContent = preg_replace('/<br( \/)?>/',"\n",$msgContent);
            $msgContent = preg_replace('/<p>/',"\n\n",$msgContent);
            $msgContent = preg_replace('/  /',' ',$msgContent);
            $msgContent = unhtmlentities($msgContent);
            $msgContent = strip_tags($msgContent);
        
            $emailBody = $msgContent . "\n" .
                         "\n" .
                         '--' . "\n" . 
                         $courseSender . "\n" . 
                         $_course['name'] . " (" . $_course['categoryName'] . ")" . "\n" . 
                         $siteName . "\n";

            // Select students email list
            $sql = "SELECT u.user_id
                    FROM `".$tbl_course_user."` cu , `".$tbl_user."` u
                    WHERE code_cours=\"".$courseId."\"
                    AND cu.user_id = u.user_id";
            $result = claro_sql_query($sql);

            // count
            $countEmail = mysql_num_rows($result);
            $countUnvalid = 0;
            $messageFailed = "";

            // send email one by one to avoid antispam
            while ( $myrow = mysql_fetch_array($result) )
            {
                if (!claro_mail_user($myrow['user_id'], $emailBody, $emailSubject, $_user['mail'], $courseSender))
                {
                    $messageFailed.= claro_failure::get_last_failure() ;
                    $countUnvalid++;
                }
            }
            $messageUnvalid= $langOn.' '.$countEmail.' '.$langRegUser.', '.$countUnvalid.' '.$langUnvalid;
            $message .= ' '.$langEmailSent.'<br><b>'.$messageUnvalid.'</b><br />';
            $message .= $messageFailed;

        }   // end if $emailOption==1
    }   // end if $submit Announcement
} // end if is_allowedToEdit

/*============================================================================
                                DISPLAY SECTION
  ============================================================================*/

if ( ! $is_courseAllowed)
    claro_disp_auth_form();

$nameTools = $langAnnouncement;
include($includePath.'/claro_init_header.inc.php');

//stats
event_access_tool($_tid, $_courseTool['label']);

/*----------------------------------------------------------------------------
                                   TOOL TITLE
  ----------------------------------------------------------------------------*/

if     ($_REQUEST['cmd'] == 'rqEdit'  ) $subTitle = $langModifAnn;
elseif ($_REQUEST['cmd'] == 'rqCreate') $subTitle = $langAddAnn;
else                        $subTitle = '';
    
claro_disp_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

/*----------------------------------------------------------------------------
                                 ACTION MESSAGE
  ----------------------------------------------------------------------------*/

if ( !empty($message) )
{
    claro_disp_message_box($message);
}

/*----------------------------------------------------------------------------
                                 MAIN COMMANDS LINE
  ----------------------------------------------------------------------------*/

if ($displayButtonLine)
{
    echo '<p>'."\n"
         .'<a class="claroCmd" href="'.$_SERVER['PHP_SELF'].'?cmd=rqCreate">'
         .'<img src="'.$imgRepositoryWeb.'announcement.gif">'
         .$langAddAnn
         .'</a>'
         .' | '
         .'<a class="claroCmd" href="messages.php">'
         .'<img src="'.$imgRepositoryWeb.'email.gif">'
         .$langMessageToSelectedUsers
         .'</a>'
         .' | '
         .'<a class="claroCmd" href="'.$PHP_SELF.'?cmd=exDeleteAll" '
         .' onclick="if (confirm(\''.clean_str_for_javascript($langEmptyAnn).' ?\')){return true;}else{return false;}">'
         .'<img src="'.$imgRepositoryWeb.'delete.gif">'
         .$langEmptyAnn
         .'</a>'
         .'</p>'."\n";
}



/*----------------------------------------------------------------------------
                     FORM TO FILL OR MODIFY AN ANNOUNCEMENT
  ----------------------------------------------------------------------------*/

if ($displayForm)
{

    // DISPLAY ADD ANNOUNCEMENT COMMAND

    echo    "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n",
            "<input type=\"hidden\" name=\"cmd\" value=\"".$nextCommand."\">",

            $announcementToEdit ? "<input type=\"hidden\" name=\"id\" value=\"".$announcementToEdit['id']."\">\n"
                                  : '',
            "<table>",
            "<tr>",
            "<td valign=\"top\"><label for=\"title\">".$langTitle." : </label></td>",
            "<td><input type=\"text\" id=\"title\" name=\"title\" value = \"",
                $announcementToEdit ? $announcementToEdit['title'] : '',
                "\"size=\"80\"></td>",
            "</tr>\n",
            "<tr>",
            "<td valign=\"top\"><label for=\"newContent\">Content    : </label></td>",
            "<td>",

            claro_disp_html_area('newContent', $announcementToEdit ? $announcementToEdit['content'] : '',12,67, $optAttrib=' wrap="virtual"');

   echo    "</td>",
           "</tr>\n",
           "<tr>",
           "<td></td>",
           "<td><input    type=checkbox value=\"1\" name=\"emailOption\" id=\"emailOption\" >",
            "<label for=\"emailOption\">",$langEmailOption,"</label><br>\n",
            "<input    type=\"Submit\"    class=\"claroButton\" name=\"submitAnnouncement\"    value=\"".$langOk."\">\n";

   claro_disp_button ($_SERVER['PHP_SELF'], 'Cancel');

   echo     "</td>",
            "<tr>\n",
            "</table>",
            "</form>\n";
}


/*----------------------------------------------------------------------------
                               ANNOUNCEMENT LIST
  ----------------------------------------------------------------------------*/


if ($displayList)
{
    $sql = "SELECT id, title, contenu content, temps
            FROM `".$tbl_announcement."`
            ORDER BY ordre DESC";

    $announcementList = claro_sql_query_fetch_all($sql);

    $iterator = 1;

    $bottomAnnouncement = $announcementNumber = count($announcementList);

    if ($announcementNumber < 1)
    {
        echo "<br><blockquote><p>".$langNoAnnouncement."<p></blockquote>\n";
    }
    

    echo "<table class=\"claroTable\" width=\"100%\">";

    foreach ( $announcementList as $thisAnnouncement)
    {
        $title   = $thisAnnouncement['title'];
        $content = make_clickable(claro_parse_user_text($thisAnnouncement['content']));

        $last_post_datetime = $thisAnnouncement['temps'];// post time format  datetime de mysql

        list($last_post_date, $last_post_time) = split(' ', $last_post_datetime);
        list($year, $month, $day) = explode("-", $last_post_date);
        list($hour, $min) = explode(':', $last_post_time);
        $announceDate = mktime($hour, $min, 0, $month, $day, $year);

        if ( $announceDate > $userLastLogin )
        {
            $imageFile = 'announcement_hot.gif';
            $altImg    = 'new';
        }
        else
        {
            $imageFile = 'announcement.gif';
            $altImg    = '';
        }

        echo    "<tr>\n",

                "<th class=\"headerX\">\n",
                "<img src=\"".$imgRepositoryWeb.$imageFile."\" alt=\"".$altImg."\">\n".
                $langPubl," : ", claro_disp_localised_date($dateFormatLong,
                                                          strtotime($last_post_date)),"\n",
                "</th>\n",

                "</tr>\n",
                "<tr>\n",

                "<td>
                <a href=\"#\" name=\"ann".$thisAnnouncement["id"]."\"></a>
                \n",
                $title ? "<p><strong>".$title."</strong></p>\n" : '',
                $content,"\n";

        if ($is_allowedToEdit)
        {
            echo "<p>",
                 "<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEdit&amp;id=".$thisAnnouncement['id']."\">",
                 "<img src=\"".$imgRepositoryWeb."edit.gif\" alt=\"".$langModify,"\">".
                 "</a>\n",
                 "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exDelete&amp;id=".$thisAnnouncement['id']."\" onclick=\"javascript:if(!confirm('".clean_str_for_javascript($langConfirmYourChoice)."')) return false;\">",
                 "<img src=\"".$imgRepositoryWeb."delete.gif\" alt=\"".$langDelete."\" border=\"0\">".
                 "</a>\n";

                // DISPLAY MOVE UP COMMAND only if it is not the top announcement

                if($iterator != 1)
                {
//                  echo    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvUp&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
// the anchor dont refreshpage.
                    echo    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvUp&amp;id=",$thisAnnouncement['id'],"\">",
                            "<img src=\"".$imgRepositoryWeb."up.gif\" alt=\"".$langOrderUp."\">".
                            "</a>\n";
                }

                // DISPLAY MOVE DOWN COMMAND only if it is not the bottom announcement

                if($iterator < $bottomAnnouncement)
                {
//                  echo    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvDown&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
// the anchor dont refreshpage.
                    echo    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvDown&amp;id=",$thisAnnouncement['id'],"\">",
                            "<img src=\"".$imgRepositoryWeb."down.gif\" alt=\"".$langDown."\">",
                            "</a>\n";
                }

            echo "</p>\n";

        } // end if is_AllowedToEdit

        echo    "</td>\n",
                "</tr>\n";

        $iterator ++;
    }    // end while ($myrow = mysql_fetch_array($result))

    echo "</table>";

} // end if displayList

/*------------------------------------*/

include($includePath."/claro_init_footer.inc.php");


/**
 * function moveEntry($entryId,$cmd)
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @param $entryId     integer     an valid id of announcement.
 * @param $cmd         string         'UP' or 'DOWN'
 *
 */
function moveEntry($entryId,$cmd)
{
    GLOBAL $tbl_announcement;

    if ( $cmd == 'DOWN' )
    {
        $thisAnnouncementId = $entryId;
        $sortDirection      = 'DESC';
    }
    elseif ( $cmd == 'UP' )
    {
        $thisAnnouncementId = $entryId;
        $sortDirection      = 'ASC';
    }
    else
        return FALSE;

    if ($sortDirection)
    {
        $sql = "SELECT id, ordre rank
                FROM `".$tbl_announcement."`
                ORDER BY `ordre` ".$sortDirection;

        $result = claro_sql_query($sql);

        while (list ($announcementId, $announcementRank) = mysql_fetch_row($result))
        {
            // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
            //          COMMIT ORDER SWAP ON THE DB

            if (isset ($thisAnnouncementRankFound) && $thisAnnouncementRankFound == TRUE)
            {
                $nextAnnouncementId    = $announcementId;
                $nextAnnouncementRank  = $announcementRank;

            $sql = "UPDATE `".$tbl_announcement."`
                        SET ordre = \"".$nextAnnouncementRank."\"
                        WHERE id =  \"".$thisAnnouncementId."\"";

                claro_sql_query($sql);
    
            $sql = "UPDATE `".$tbl_announcement."`
                        SET ordre = \"".$thisAnnouncementRank."\"
                        WHERE id =  \"".$nextAnnouncementId."\"";

                claro_sql_query($sql);

                break;
            }

            // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT

            if ($announcementId == $thisAnnouncementId)
            {
                $thisAnnouncementRank      = $announcementRank;
                $thisAnnouncementRankFound = TRUE;
            }
        }
    }
    return TRUE;
}
?>
