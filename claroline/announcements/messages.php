<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de	Louvain	(UCL)
//----------------------------------------------------------------------
// This	program	is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published	by the FREE	SOFTWARE FOUNDATION. The GPL is	available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors:	see	'credits' file
//----------------------------------------------------------------------

/*
 * > > > > > > > > > > > > MESSAGES MODULE < < < < < < < < < < < <
 *
 * This modules allows to send messages to some chosen users groups from a course *
 *
 * Code	borrowed from parts
 *
 * originally written by Thomas	Depraetere (depraetere@ipm.ucl.ac.be) - January 15 2002
 * partially rewritten by Hugues Peeters (peeters@ipm.ucl.ac.be)      - April 19 2002,
 * improved by Pablo Rey & Miguel Rubio (http://aula.cesga.es)        - February 2003
 * partially rewritten by Roan Embrechts (roan_embrechts@yahoo.com)	  - September 2003
 * changes by Miguel Rubio	(teleensino@cesga.es)                     - October 2003
 * Refactored by Hugues Peeters (peeters@ipm.ucl.ac.be)               - October 30 2003
 *
 */

/**************************************
	   CLAROLINE MAIN SETTINGS
**************************************/

require '../inc/claro_init_global.inc.php'; //	settings initialisation	

if ( ! $_cid ) claro_disp_select_course();

include($includePath.'/lib/claro_mail.lib.inc.php');

$htmlHeadXtra[]="<script type=\"text/javascript\" language=\"JavaScript\">

<!-- Begin javascript menu swapper

function move(fbox,	tbox)
{
	var	arrFbox	= new Array();
	var	arrTbox	= new Array();
	var	arrLookup =	new	Array();

	var	i;
	for	(i = 0;	i <	tbox.options.length; i++)
	{
		arrLookup[tbox.options[i].text]	= tbox.options[i].value;
		arrTbox[i] = tbox.options[i].text;
	}

	var	fLength	= 0;
	var	tLength	= arrTbox.length;

	for(i =	0; i < fbox.options.length;	i++)
	{
		arrLookup[fbox.options[i].text]	= fbox.options[i].value;

		if (fbox.options[i].selected &&	fbox.options[i].value != \"\")
		{
			arrTbox[tLength] = fbox.options[i].text;
			tLength++;
		} 
		else
		{
			arrFbox[fLength] = fbox.options[i].text;
			fLength++;
		}
	}

	arrFbox.sort();
	arrTbox.sort();
	fbox.length	= 0;
	tbox.length	= 0;

	var	c;
	for(c =	0; c < arrFbox.length; c++)
	{
		var	no = new Option();
		no.value = arrLookup[arrFbox[c]];
		no.text	= arrFbox[c];
		fbox[c]	= no;
	}
	for(c =	0; c < arrTbox.length; c++)
	{
		var	no = new Option();
		no.value = arrLookup[arrTbox[c]];
		no.text	= arrTbox[c];
		tbox[c]	= no;
	}
}

function valida()
{
	var	f =	document.datos;
	var	dat;

	if (f.elements[3].length <	1) {
		alert(\"$langPleaseSelectUsers\");
		return false;
	}
	for	(var i=0; i<f.elements[3].length; i++)	
		f.elements[3][i].selected = f.elements[3][i].checked = true

	dat=f.emailContent.value;
	if(dat.length == 0)
	{
		//old: Debe	introducir el Texto	del	Mensaje
		alert(\"$langPleaseEnterMessage\");
		f.emailContent.focus();
		f.emailContent.select();
		return false;	
	}
	
	f.submit();
	return true;
}

//	End	-->
</script>";

$interbredcrump[]= array ("url"=>"../announcements/announcements.php", "name"=> $langAnnouncement);

$nameTools = $langMessages;

include('../inc/claro_init_header.inc.php'); 

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_group      = $tbl_cdb_names['group_team'];
$tbl_groupUser  = $tbl_cdb_names['group_rel_team_user'];

$tbl_user       = $tbl_mdb_names['user'];
$tbl_courseUser = $tbl_mdb_names['rel_course_user'];

/*
 * Various connection variables from the initialisation scripts
 */

$is_allowedToUse = $is_courseAdmin;
$courseCode      = $_course['officialCode'];
$courseName      = $_course['name'        ];
$senderFirstName = $_user  ['firstName'   ];
$senderLastName  = $_user  ['lastName'    ];
$senderMail      = $_user  ['mail'        ];

if($is_allowedToUse)	// check teacher status
{
	echo "<h3>",$langMessages,"</h3>";

    /*
     * DEFAULT DISPLAY SETTINGS
     */

	$displayForm = TRUE;

    /*
     * SUBMIT ANNOUNCEMENT COMMAND
     */

	if ( isset($_REQUEST['submitAnnouncement']) ) 
	{

        if ( isset($_REQUEST['incorreo']) ) 
        { 
		
		    /*
             * Explode the values of incorreo in groups and users 
             */

    		foreach($_REQUEST['incorreo'] as $thisIncorreo)
    		{
    			list($type, $elmtId) = explode(':', $thisIncorreo);
    
    			switch($type)
    			{
    				case 'GROUP':
    					$groupIdList[] = $elmtId;
    					break;
    
    				case 'USER':
    					$userIdList[] = $elmtId;
    					break;
    			}

    		} // end while
		
    		/*
             * Select the students of the different groups
             */
    		
    		if ( isset($groupIdList) )
    		{
    			$groupIdList = implode(', ',$groupIdList);
    
    			$sql = "SELECT user
    					FROM `".$tbl_groupUser."` user_group
    					WHERE user_group.team IN (".$groupIdList.")";
    
    			$groupMemberResult = claro_sql_query($sql);
    			
    			if ($groupMemberResult)
    			{
    				while ($u = mysql_fetch_array($groupMemberResult))
    				{
    					$userIdList[] = $u['user']; // complete the user id list ...
    				}
    			}
    		}
    
    		/*
             * Send the differents mails
             */
    		
            if( is_array($userIdList) )
            {
  			
                /* 
  			     * Prepare	email
                 */
  
                // email subject
  			    $emailSubject = "[" . $siteName . " - " . $courseCode ."] " . $professorMessage;
  
  			    // email content
  			    $emailBody = stripslashes($_REQUEST['emailContent']) . "\n" .
                             "\n" . 
                             '--' . "\n" . 
                             $senderFirstName . " " . $senderLastName . "\n" .
                             $_course['name'] . " (" . $_course['categoryName'] . ")" . "\n" .
  					         $siteName . "\n".
                             '('. $langProfessorMessage . ')';
  
                /*
                 * Send	email one by one to	avoid antispam
                 */
  
                $countUnvalid = 0;

                foreach($userIdList as $userId)
                {
                    if (!claro_mail_user($userId, $emailBody, $emailSubject, $senderMail, $senderFirstName." ".$senderLastName))
                    {
                        $messageFailed.= claro_failure::get_last_failure();
                        $countUnvalid++;
                    }
                }

  		    } // end if - is_array($userIdList)
    
            $message = '<p>'.$langMsgSent.'<p>';
    
            if ($countUnvalid > 0)
    	    {
    	        $messageUnvalid	= '<p>'
    		                     . $langOn.'	'
    		                     . count($userIdList) .' '
    		                     . $langSelUser.',	' .  $countUnvalid . ' ' .$langUnvalid
    		                     . '<br><small>'
        		                 . $messageFailed
        		                 . '</small>'
    	    	                 . '</p>';
        		$message .= $messageUnvalid;
    	    }

        } // end if - $_REQUEST['incorreo']

    } // end if - $_REQUEST['submitAnnouncement']

    /*
     * DISPLAY ACTION MESSAGE
     */

	if ( !empty($message) )
	{
        claro_disp_message_box($message);

        echo '<br>'
            .'<a href="'.$_SERVER['PHP_SELF'].'">&lt;&lt;&nbsp;'.$langBackList.'</a>'
            .'<br>';

		$displayForm = FALSE;
	}

	/*----------------------------------------
	   DISPLAY FORM	TO FILL	AN ANNOUNCEMENT
	       (USED FOR ADD AND MODIFY)
	 --------------------------------------*/

	if ($displayForm ==	 TRUE)
	{
		/*
		 * Get user	list of	this course
		 */

		$sql =	"SELECT u.nom lastName, u.prenom firstName, u.user_id uid
		         FROM `".$tbl_user."` u, `".$tbl_courseUser."` cu
		         WHERE cu.code_cours = \"".$_cid."\" 
		         AND cu.user_id = u.user_id
		         ORDER BY u.nom, u.prenom";

		$result	= claro_sql_query($sql);

		if ($result)
		{
			while ($userData = mysql_fetch_array($result))
			{
				$userList [] = $userData;
			}
		}

		/*
		 * Get group list of this course
		 */

		$sql = "SELECT g.id, g.name, COUNT(gu.id) userNb 
		        FROM `".$tbl_group."` AS g LEFT JOIN `".$tbl_groupUser."` gu 
		        ON g.id = gu.team 
		        GROUP BY g.id";

		$groupSelect = claro_sql_query($sql);

		while ($groupData = mysql_fetch_array($groupSelect))
		{
			$groupList [] = $groupData;
		}

		/*
		 * Create Form
		 */

		echo	$langIntroText;

		echo	"<form method=\"post\" ",
				"action=\"",$_SERVER['PHP_SELF'],"\" ",
				"name=\"datos\" ",
				"onSubmit=\"return valida();\">\n",

				"<center>",

				"<table	border=0 cellspacing=3 cellpadding=4>",

				"<tr valign=top	align=center>",
				"<td>",

				"<p><b>",$langUserlist,"</b></p>",

				"<select name=\"nocorreo[]\" size=15 multiple>";		

		if ($groupList)
		{
			foreach($groupList as $thisGroup)
			{
				echo	"<option value=\"GROUP:".$thisGroup['id']."\">",
						"* ",$thisGroup['name']," (",$thisGroup['userNb']," ",$langUsers,")",
						"</option>";
			}
		}

		echo	"<option value=\"\">",
				"---------------------------------------------------------",
				"</option>";

		// display user list

		foreach($userList as $thisUser)
		{
			echo	"<option value=\"USER:",$thisUser['uid'],"\">",
					$thisUser['lastName']," ",$thisUser['firstName'],
					"</option>";
		}

		// WATCH OUT ! form elements are called by numbers "form.element[3]"... 
		// because select name contains "[]" causing a javascript 
		// element name problem List of selected users
		
		echo	"</select>",

				"</td>",

				"<td valign=\"middle\">",

				"<input	type=\"button\"	",
				"onClick=\"move(this.form.elements[0],this.form.elements[3])\" ",
				"value=\"   >>   \">",

				"<p>&nbsp;</p>",

				"<input	type=\"button\"",
				"onClick=\"move(this.form.elements[3],this.form.elements[0])\" ", 
				"value=\"   <<   \">",

				"</td>",

				"<td>",

				"<p><b>",$langSelectedUsers,"</b></p>",

				"<p>",
				"<select name=\"incorreo[]\" ",
				"size=\"15\" multiple ",
				"style=\"width:200\" width=\"20\">",
				"</select>",
				"</p>",

				"</td>",
				"</tr>",
					
				"<tr>",
				"<td colspan=3>",

				"<b>",$langAnnouncement,"</b><br>",
				"<center>",
				"<textarea wrap=\"physical\" rows=\"7\"	cols=\"60\"	name=\"emailContent\"></textarea>",
				"</center>",

				"</td>",
				"</tr>",

				"<tr>",

				"<td colspan=3 align=center>",

				"<input	type=\"Submit\"	
				name=\"submitAnnouncement\"	",
				"value=\"",$langSubmit,"\" >",

				"</td>",

				"</tr>";

    } // end if - $displayForm ==  TRUE

echo	"</table>",
		"</center>",
		"</form>";

} // end: teacher only

include($includePath."/claro_init_footer.inc.php");	

?>
