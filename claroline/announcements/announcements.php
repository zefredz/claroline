<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
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
 *
 *		commands
 *			move up and down announcement
 *			delete announcement
 *			delete all announcements
 *			modify announcement
 *			submit announcement (new or modified)
 *
 *		display
 *			title
 *          button line
 *          form
 *			announcement list
 *			form to fill new or modified announcement
 */


/*==========================
   CLAROLINE MAIN SETTINGS
  ==========================*/

$langFile = "announcements";
$tlabelReq = "CLANN___";
require '../inc/claro_init_global.inc.php';
include($includePath.'/conf/announcement.conf.inc.php');
include($includePath.'/lib/text.lib.php');
include($includePath.'/lib/events.lib.inc.php');

$tbl_announcement = $_course['dbNameGlu'].'announcement';
$is_allowedToEdit = $is_courseAdmin;
$courseId         = $_course['sysCode'];
$userLastLogin    = $_user ['lastLogin'];

// DEFAULT DISPLAY

$displayForm           = false;
$displayList           = true;
$displayButtonLine     = $is_allowedToEdit && $cmd != 'rqEdit' && $cmd != 'rqCreate';

/*============================================================================
                     COMMANDS SECTION (COURSE MANAGER ONLY)
  ============================================================================*/

if($is_allowedToEdit) // check teacher status
{

    /*------------------------------------------------------------------------
                             MOVE UP AND MOVE DOWN COMMANDS
     -------------------------------------------------------------------------*/

    if ( $cmd == 'exMvDown' )
	{
		moveEntry($_REQUEST['id'],'DOWN');
	}

	if ( $cmd == 'exMvUp' )
	{
		moveEntry($_REQUEST['id'],'UP');
	}

    /*------------------------------------------------------------------------
                          DELETE ANNOUNCEMENT COMMAND
    --------------------------------------------------------------------------*/

    if ($cmd == 'exDelete')
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

    if ($cmd == 'exDeleteAll')
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


    if ( $cmd == 'rqEdit' )
    {
        // RETRIEVE THE CONTENT OF THE ANNOUNCEMENT TO MODIFY

        $sql = "SELECT id, title, contenu content
                FROM  `".$tbl_announcement."`
                WHERE id=\"".$_REQUEST['id']."\"";

        list( $announcementToEdit ) =  claro_sql_query_fetch_all($sql);

        $displayForm = true;
        $nextCommand = 'exEdit';

    }

    /*------------------------------------------------------------------------
                            CREATE NEW ANNOUNCEMENT COMMAND
      ------------------------------------------------------------------------*/


    if ($cmd == 'rqCreate')
    {
    	$displayForm = true;
        $nextCommand = 'exCreate';
    }


	/*------------------------------------------------------------------------
                          SUBMIT ANNOUNCEMENT COMMAND
	 -------------------------------------------------------------------------*/

	if ($cmd == 'exCreate' || $cmd == 'exEdit')
	{
		/* MODIFY ANNOUNCEMENT */

		if($cmd == 'exEdit') // there is an Id => the announcement already exists => udpate mode
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

		elseif ($cmd == 'exCreate')
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
            $emailContent= stripslashes($newContent);

            $emailSubject = "[".$siteName."-".$courseIdCode."] ".$emailTitle;

            if (trim($_REQUEST['title'])) $emailSubject .= $_REQUEST['title'];
            else                         $emailSubject = $professorMessage;

            $courseIdTitular =  addslashes($_user ['firstName'].' '.$_user ['lastName'])
                               ." <".$_user ['mail'].">";

            $errormanager = $administrator['email'];

            // Here we are forming one large header line
            // Every header must be followed by a \n except the last

            $emailHeaders  = 'From: '.$writer."\n";
            $emailHeaders .= 'Reply-To: "['.$courseIdCode.']'.addslashes($courseIdTitular).'" <'.$HTTP_SESSION_VARS['email'].">\n";
            $emailHeaders .= 'Return-path: '.$errormanager."\n";
            $emailHeaders .= 'Errors-To: '.$errormanager."\n";
            $emailHeaders .= "MIME-Version: 1.0\n";
            //$emailHeaders .= "Content-Type: text/html; charset=".$charset."\n";
            $emailHeaders .= "X-Priority: 2\n";
            $emailHeaders .= "X-Mailer: PHP / ".phpversion()."\n";
            $emailHeaders .= "Comments: Announcement email ";

            // Select students email list

            $sql = "SELECT user.email, user.prenom, user.nom
                    FROM ".$mainDbName.".cours_user, ".$mainDbName.".user
                    WHERE code_cours=\"".$courseId."\"
                    AND cours_user.user_id = user.user_id";

            $result = claro_sql_query($sql);

            $countEmail = mysql_num_rows($result);

            // Email syntax test
            $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";

            $unvalid=0;
            // send email one by one to avoid antispam
            while ( $myrow = mysql_fetch_array($result) )
            {
                $emailTo=$myrow["email"];
                // echo "emailTo : $emailTo<br>";	// testing
                // check email syntax validity
                if(!eregi( $regexp, $emailTo ))
                {
                    $unvalid++;
                }

                $emailBody = $myrow['prenom'].' '.$myrow['nom']."\n\n".$emailContent." \n\n-- \n".$writer."\n".$courseIdCode."\n".$siteName."\n(".$emailTitle.')';
                @mail($emailTo, $emailSubject, $emailBody, $emailHeaders);
            }

            $messageUnvalid= $langOn.' '.$countEmail.' '.$langRegUser.', '.$unvalid.' '.$langUnvalid;
            $message .= ' '.$langEmailSent.'<br><b>'.$messageUnvalid.'</b>';

        }   // end if $emailOption==1

    }   // end if $submit Announcement
} // end if is_allowedToEdit



/*============================================================================
                                DISPLAY SECTION
  ============================================================================*/


$nameTools = $langAn;
include($includePath.'/claro_init_header.inc.php');

if ( ! $is_courseAllowed)
	claro_disp_auth_form();

//stats
event_access_tool($nameTools);

/*----------------------------------------------------------------------------
                                   TOOL TITLE
  ----------------------------------------------------------------------------*/

if     ($cmd == 'rqEdit'  ) $subTitle = $langModifAnn;
elseif ($cmd == 'rqCreate') $subTitle = $langAddAnn;
else                        $subTitle = '';
    
claro_disp_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

/*----------------------------------------------------------------------------
                                 ACTION MESSAGE
  ----------------------------------------------------------------------------*/

if ($message)
{
    claro_disp_message_box($message);
}

/*----------------------------------------------------------------------------
                                 MAIN COMMANDS LINE
  ----------------------------------------------------------------------------*/

if ($displayButtonLine)
{
    echo    "<p>\n";
    claro_disp_button($PHP_SELF.'?cmd=rqCreate',
                      '<img src="'.$clarolineRepositoryWeb.'img/valves.gif">'.$langAddAnn);
    claro_disp_button('messages.php',
                      '<img src="'.$clarolineRepositoryWeb.'img/email.gif">'.$langMessageToSelectedUsers);
    claro_disp_button($PHP_SELF.'?cmd=exDeleteAll',
                      '<img src="'.$clarolineRepositoryWeb.'img/delete.gif">'.$langEmptyAnn);
    echo "</p>\n";
}



/*----------------------------------------------------------------------------
                     FORM TO FILL OR MODIFY AN ANNOUNCEMENT
  ----------------------------------------------------------------------------*/

if ($displayForm)
{

    // DISPLAY ADD ANNOUNCEMENT COMMAND

    echo    "<form method=\"post\" action=\"".$PHP_SELF."\">\n",
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
            "<td valign=\"top\"><label for=\"newContent\">Content	: </label></td>",
            "<td>",

            claro_disp_html_area('newContent', $announcementToEdit ? $announcementToEdit['content'] : '', 12, 67, $optAttrib=' wrap="virtual"');

   echo    "</td>",
           "</tr>\n",
           "<tr>",
           "<td></td>",
           "<td><input	type=checkbox value=\"1\" name=\"emailOption\">",
            "<label for=\"emailOption\">",$langEmailOption,"</label><br>\n",
            "<input	type=\"Submit\"	class=\"claroButton\" name=\"submitAnnouncement\"	value=\"".$langOk."\">\n";

   claro_disp_button ($PHP_SELF, 'Cancel');

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
            $imageFile = 'valvesred.gif';
            $altImg    = 'new';
        }
        else
        {
            $imageFile = 'valves.gif';
            $altImg    = '';
        }

        echo	"<tr>\n",

                "<th class=\"headerX\">\n",
                "<img src=\"".$clarolineRepositoryWeb."/img/".$imageFile."\" alt=\"".$altImg."\">\n".
                $langPubl," : ", claro_format_locale_date($dateFormatLong,
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
                 "<a href=\"".$PHP_SELF."?cmd=rqEdit&id=".$thisAnnouncement['id']."\">",
                 "<img src=\"".$clarolineRepositoryWeb."/img/edit.gif\" alt=\"".$langModify,"\">".
                 "</a>\n",
                 "<a href=\"".$PHP_SELF."?cmd=exDelete&id=".$thisAnnouncement['id']."\" onclick=\"javascript:if(!confirm('".addslashes(htmlentities($langConfirmYourChoice))."')) return false;\">",
                 "<img src=\"".$clarolineRepositoryWeb."/img/delete.gif\" alt=\"".$langDelete."\" border=\"0\">".
                 "</a>\n";

                // DISPLAY MOVE UP COMMAND only if it is not the top announcement

                if($iterator != 1)
                {
                    echo	"<a href=\"".$PHP_SELF."?cmd=exMvUp&id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                            "<img src=\"".$clarolineRepositoryWeb."/img/up.gif\" alt=\"".$langUp."\">".
                            "</a>\n";
                }

                // DISPLAY MOVE DOWN COMMAND only if it is not the bottom announcement

                if($iterator < $bottomAnnouncement)
                {
                    echo	"<a href=\"".$PHP_SELF."?cmd=exMvDown&id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                            "<img src=\"".$clarolineRepositoryWeb."/img/down.gif\" alt=\"".$langDown."\">",
                            "</a>\n";
                }

            echo "</p>\n";

        } // end if is_AllowedToEdit

        echo	"</td>\n",
                "</tr>\n";

        $iterator ++;
    }	// end while ($myrow = mysql_fetch_array($result))

    echo "</table>";

} // end if displayList

/*------------------------------------*/

include($includePath."/claro_init_footer.inc.php");


/**
 * function moveEntry($entryId,$cmd)
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @param $entryId 	integer 	an valid id of announcement.
 * @param $cmd 		string 	    'UP' or 'DOWN'
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
		return false;

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

			if (isset ($thisAnnouncementRankFound) && $thisAnnouncementRankFound == true)
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
				$thisAnnouncementRankFound = true;
			}
		}
	}
	return true;
}
?>
