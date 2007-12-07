<?php
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de	Louvain	(UCL)
//----------------------------------------------------------------------
// This	program	is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published	by the FREE	SOFTWARE FOUNDATION. The GPL is	available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors:	see	'credits' file
//----------------------------------------------------------------------

/*> > > > > > > > > > > > MESSAGES MODULE < < < < < < < < < < < <
 *
 * This modules allows to send messages to some chosen users groups from a course *
 *
 * Code	borrowed from parts
 *
 * originally written by Thomas	Depraetere (depraetere@ipm.ucl.ac.be) - January 15 2002
 * partially rewritten by Hugues Peeters (peeters@ipm.ucl.ac.be)      - April 19 2002,
 * improved by Pablo Rey & Miguel Rubio (http://aula.cesga.es)        - February 2003
 * partially rewritten by Roan Embrechts (roan_embrechts@yahoo.com)	  - September 2003
 * changes by Miguel Rubio	(teleensino@cesga.es)                     - october 2003
 * Refactored by Hugues Peeters (peeters@ipm.ucl.ac.be)               -  october 30 2003
 *
 *

 *
 */

/**************************************
	   CLAROLINE MAIN SETTINGS
**************************************/

$langFile =	'announcements'; 

require '../inc/claro_init_global.inc.php'; //	settings initialisation	
include('../inc/lib/text.lib.php');

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

function selectAll(cbList,bSelect)
{
	if (cbList.length <	1) {
		//old: Debe	seleccionar	algún Alumno
		alert(\"$langPleaseSelectUsers\");
		return;
	}
	for	(var i=0; i<cbList.length; i++)	
		cbList[i].selected = cbList[i].checked = bSelect
}

function reverseAll(cbList)
{
	for	(var i=0; i<cbList.length; i++)
	{
		cbList[i].checked  = !(cbList[i].checked) 
		cbList[i].selected = !(cbList[i].selected)
	}
}
//	End	-->
</script>";

$interbredcrump[]= array ("url"=>"../announcements/announcements.php", "name"=> $langAn);

$nameTools = $langMessages;

include('../inc/claro_init_header.inc.php'); 


/*
 * DB tables definition
 */

$tbl_group      = $_course['dbNameGlu']."group_team";
$tbl_groupUser  = $_course['dbNameGlu']."group_rel_team_user";

$tbl_user       = $mainDbName."`.`user";
$tbl_courseUser = $mainDbName."`.`cours_user";

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
	echo	"<h3>",$langMessages,"</h3>";

	/*----------------------------------------
		   DEFAULT DISPLAY SETTINGS
	 --------------------------------------*/

	$displayForm = true;

	// The commands	below will change these display settings if	they need it





	/*----------------------------------------
			SUBMIT ANNOUNCEMENT	COMMAND
	 --------------------------------------*/

	if ($submitAnnouncement) 
	{
		// SEND	EMAIL (OPTIONAL)
		// THIS	FUNCTION ADDED BY THOMAS MAY 2002
		// MODIFIED	CODE BY	MIGUEL ON 13/10/2003

		/******************************************************
		 * explode the values of	incorreo in	groups and users  *
		 *******************************************************/

		foreach($incorreo as $thisIncorreo)
		{
			list($type, $elmtId) = explode(':', $thisIncorreo);

			switch($type)
			{
				case 'GROUP':
					$groupIdList [] =$elmtId;
					break;

				case 'USER':
					$userIdList  [] =$elmtId;
					break;
			}
		}				// end while
		
		// SELECCIONAMOS	LOS	ALUMNOS	DE LOS DISTINTOS GRUPOS
		
		if ($groupIdList)
		{
			$groupIdList = implode(', ',$groupIdList);

			$sql = "SELECT user_id
					FROM ".$tbl_groupUser."` user_group
					WHERE user_group.team IN (".$groupIdList.")";

			$groupMemberResult = mysql_query($sql);
			
			if ($groupMemberResult)
			{
				while ($u = mysql_fetch_array($groupMemberResult))
				{
					$userIdList [] = $u[user_id]; // complete the user id list ...
				}
			}
		}


		if ($userIdList)
		{
			$userIdList = implode(', ', array_unique($userIdList) );

			$sql = "SELECT prenom firstName, nom lastName, email
			        FROM `".$tbl_user."` WHERE user_id IN (".$userIdList.")";

			$emailResult = mysql_query($sql);

			if ($emailResult)
			{
				while ($e = mysql_fetch_array($emailResult))
				{
					if(eregi('^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$', $e[email] ))
					{
						$emailList [] = $e[firstName].' '.$e[lastName].' <'.$e[email].'>';
					}
					else
					{
						$invalidMailUserList [] = $e[firstName].' '.$e[lastName];
					}
				}
			}
		} // end if userIdList
		

		//we´ll	send the differents mails
		
		 if( count($emailList) > 0)
		 {
			/* 
			 * Prepare	email
			 *
			 * Here	we are forming one large header	line
			 * Every header	must be	followed by	a \n except the	last
			 */

			$emailSubject = $courseCode." - ".$professorMessage;
		
			$emailHeaders = 'From:	'.$senderFirstName.' '.$senderLastName.' <'.$senderMail.'>\n'
			               .'Reply-To:	'.$senderMail;

			$emailContent = stripslashes($emailContent);

			/*
			 * Send	email one by one to	avoid antispam
			 */

			$students='';  //MIGUEL: STUDENTS LIST FOR TEACHER MESSAGE
		
			foreach($emailList as $emailTo)
			{
				//AVOID ANTISPAM BY	VARYING STRING

				$emailBody = $courseName.' \n'
							.$emailTo.'\n\n'
							.$emailContent; 

				@mail($emailTo,	$emailSubject, $emailBody, $emailHeaders);		
			}
		 }

		$message = '<p>'.$langMsgSent.'<p>';

		if ($invalidMailUserList && count($invalidMailUserList) > 0)
		{
			$messageUnvalid	= '<p>'
			                 .$langOn.'	'
			                 .count($emailList) + count($invalidMailUserList) .' '
			                 .$langSelUser.',	'.$unvalid.' '.$langUnvalid
			                 .'<br><small>('
			                 .implode(', ', $invalidMailUserList)
			                 .')</small>'
			                 .'</p>';

			$message .= $messageUnvalid;
		}

  }	// if $submit Announcement

//////////////////////////////////////////////////////////////////////////////





	/*----------------------------------------
				DISPLAY	ACTION MESSAGE
	 --------------------------------------*/

	if ($message)
	{
		echo	$message,
				"<br>",
				"<br>",
				"<a	href=\"",$PHP_SELF,"\">",$langBackList,"&nbsp;&gt;</a>",
				"<br>";

		$displayForm = false;
	}

//////////////////////////////////////////////////////////////////////////////




	/*----------------------------------------
	   DISPLAY FORM	TO FILL	AN ANNOUNCEMENT
	       (USED FOR ADD AND MODIFY)
	 --------------------------------------*/

	if ($displayForm ==	 true)
	{
		/*
		 * Get user	list of	this course
		 */

		$sql =	"SELECT u.nom lastName, u.prenom firstName, u.user_id uid
		         FROM `".$tbl_user."` u, `".$tbl_courseUser."` cu
		         WHERE cu.code_cours = \"".$_cid."\" 
		         AND cu.user_id = u.user_id
		         ORDER BY u.prenom, u.nom";

		$result	= mySqlQueryShowError($sql);

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

		$groupSelect = mySqlQueryShowError($sql);

		while ($groupData = mysql_fetch_array($groupSelect))
		{
			$groupList [] = $groupData;
		}

		/*
		 * Create Form
		 */

		echo	$langIntroText;

		echo	"<form method=\"post\" ",
				"action=\"",$PHP_SELF,"\" ",
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
				echo	"<option value=\"GROUP:".$thisGroup[id]."\">",
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
			echo	"<option value=\"USER:",$thisUser[uid],"\">",
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

				"<b>",$langMsgText,"</b><br>",
				"<center>",
				"<textarea wrap=\"physical\" rows=\"7\"	cols=\"60\"	name=\"emailContent\"></textarea>",
				"</center>",

				"</td>",
				"</tr>",

				"<tr>",

				"<td colspan=3 align=center>",

				"<input	type=\"Submit\"	
				name=\"submitAnnouncement\"	",
				"value=\"",$langSubmit,"\" ",
				"onClick=\"selectAll(this.form.elements[3],true)\">",

				"</td>",

				"</tr>";
	}

echo	"</table>",
		"</center>",
		"</form>";

} // end: teacher only

include($includePath."/claro_init_footer.inc.php");	







//////////////////////////////////////////////////////////////////////////////

/*> > > > > > > > > > > > FUNCTIONS DEFINITION < < < < < < < < < < < <*/


// Function	developped by Christophe Gesché	at Claroline 
// to detect errors	in Mysql Queries

function mySqlQueryShowError($sql,$handledb="###")
{
	if ($handledb=="###")
	{
		$val =	@mysql_query($sql);
	}
	else
	{
		$val =	@mysql_query($sql,$handledb);
	}
	if (mysql_errno())
	{
		echo "<hr>".mysql_errno().": ".mysql_error()."<br><PRE>$sql</PRE><HR>";
	}
	else
	{
		echo "<!-- \n$sql\n-->";
	}
	return $val;
}

?>
