<?php #	$Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de	Louvain	(UCL)
//----------------------------------------------------------------------
// This	program	is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published	by the FREE	SOFTWARE FOUNDATION. The GPL is	available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors:	see	'credits' file
//----------------------------------------------------------------------

//////////////////////////////////////////////////////////////////////////////
//				   CLAROLINE DB	QUERY WRAPPRER MODULE
//////////////////////////////////////////////////////////////////////////////

/**
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @return array list of the central claroline database	tables
 */

function claro_sql_get_main_tbl()
{
	global $mainDbName;

	$mainDb	= $mainDbName;

	static $mainTblList	= array();

	if ( count($mainTblList) ==	0 )
	{
		$mainTblList['admin'		  		]	= $mainDb.'`.`admin';
		$mainTblList['cours'		  		]	= $mainDb.'`.`cours';
		$mainTblList['rel_course_user']	= $mainDb.'`.`cours_user';
		$mainTblList['category'		  	]	= $mainDb.'`.`faculte';
		$mainTblList['todo'			  	]	= $mainDb.'`.`todo';
		$mainTblList['user'			  	]	= $mainDb.'`.`user';
	}

	return $mainTblList;
}




/**
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @return array list of the current course	database tables
 */

function claro_sql_get_course_tbl()
{
	static $courseTblList =	array();

	if ( count($course_tbl)	== 0 )
	{
        $courseTblList['announcement'          ] = $courseDb.'announcement';
        $courseTblList['assignment_doc'        ] = $courseDb.'assignment_doc';
        $courseTblList['bb_access'             ] = $courseDb.'bb_access';
        $courseTblList['bb_banlist'            ] = $courseDb.'bb_banlist';
        $courseTblList['bb_categories'         ] = $courseDb.'bb_categories';
        $courseTblList['bb_config'             ] = $courseDb.'bb_config';
        $courseTblList['bb_disallow'           ] = $courseDb.'bb_disallow';
        $courseTblList['bb_forum_access'       ] = $courseDb.'bb_forum_access';
        $courseTblList['bb_forum_mods'         ] = $courseDb.'bb_forum_mods';
        $courseTblList['bb_forums'             ] = $courseDb.'bb_forums';
        $courseTblList['bb_headermetafooter'   ] = $courseDb.'bb_headermetafooter';
        $courseTblList['bb_posts'              ] = $courseDb.'bb_posts';
        $courseTblList['bb_posts_text'         ] = $courseDb.'bb_posts_text';
        $courseTblList['bb_priv_msgs'          ] = $courseDb.'bb_priv_msgs';
        $courseTblList['bb_ranks'              ] = $courseDb.'bb_ranks';
        $courseTblList['bb_sessions'           ] = $courseDb.'bb_sessions';
        $courseTblList['bb_themes'             ] = $courseDb.'bb_themes';
        $courseTblList['bb_topics'             ] = $courseDb.'bb_topics';
        $courseTblList['bb_users'              ] = $courseDb.'bb_users';
        $courseTblList['bb_whosonline'         ] = $courseDb.'bb_whosonline';
        $courseTblList['bb_words'              ] = $courseDb.'bb_words';
        $courseTblList['calendar_event'        ] = $courseDb.'calendar_event';
        $courseTblList['course_description'    ] = $courseDb.'course_description';
        $courseTblList['document'              ] = $courseDb.'document';
        $courseTblList['group_property'        ] = $courseDb.'group_property';
        $courseTblList['group_rel_team_user'   ] = $courseDb.'group_rel_team_user';
        $courseTblList['group_team'            ] = $courseDb.'group_team';
        $courseTblList['link'                  ] = $courseDb.'link';
        $courseTblList['quiz_answer'           ] = $courseDb.'quiz_answer';
        $courseTblList['quiz_question'         ] = $courseDb.'quiz_question';
        $courseTblList['quiz_rel_test_question'] = $courseDb.'quiz_rel_test_question';
        $courseTblList['quiz_test'             ] = $courseDb.'quiz_test' ;
        $courseTblList['tool_intro'            ] = $courseDb.'tool_intro';
        $courseTblList['tool_list'             ] = $courseDb.'tool_list';
        $courseTblList['userinfo_content'      ] = $courseDb.'userinfo_content';
        $courseTblList['userinfo_def'          ] = $courseDb.'userinfo_def';
        $courseTblList['work_student'          ] = $courseDb.'work_student';

	} // end if	( count($course_tbl) ==	0 )

	return $courseTbl;
}




/**
 * Claroline mySQL query wrapper. It also provides a debug display which works 
 * when	the	CLARO_DEBUG_MODE constant flag is set to on	(true)
 *
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
 * @author Hugues Peeters	 <peeters@ipm.ucl.ac.be>, 
 * @param  string  $sqlQuery   - the sql query
 * @param  handler $dbHandler  - optional
 * @return handler			   - the result	handler
 */

function claro_sql_query($sqlQuery,	$dbHandler = '#' )
{

	if ( $dbHandler	== '#')
	{
		$resultHandler =  @mysql_query($sqlQuery);
	}
	else
	{
		$resultHandler =  @mysql_query($sqlQuery, $dbHandler);
	}

	if ( CLARO_DEBUG_MODE && mysql_errno() )
	{
		echo	'<hr size="1" noshade>',

				mysql_errno(), " : ", mysql_error(), '<br>',

				'<pre style="color:red">',

				$sqlQuery,

				'</pre>',

				'<hr size="1" noshade>';
	}

	return $resultHandler;
}


/**
 * Claroline mySQL fetch array returning all the result	rows
 * in an associative array.	Compared to	the	PHP	mysql_fetch_array(),
 * it proceeds in a	single pass.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  handler	$sql - $sqlResultHandler
 * @param  int $resultType (optional) -	MYSQL_ASSOC	constant by	default
 * @return array		 - associative array containing	all	the	result rows
 */


function claro_sql_fetch_all($sqlResultHandler, $resultType	= MYSQL_ASSOC)
{
	$rowList = array();

	while( $row = mysql_fetch_array($sqlResultHandler, $resultType) )
	{
		$rowList [] = $row;
	}

	mysql_free_result($sqlResultHandler);

	return $rowList;
}



/**
 * Claroline mySQL query and fetch array wrapper. It returns all the result rows
 * in an associative array. It also provides debug display wich works when the
 * global debug flag is set to on (true)
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sql - the sql query
 * @param  handler $db  - optional
 * @return array        - associative array containing all the result rows
 *
 * @see	claro_sql_query(), claro_sql_fetch_all
 *
 */

function claro_sql_query_fetch_all($sqlQuery, $dbHandler = '#')
{
	$result = claro_sql_query($sqlQuery, $dbHandler);

	if ($result)
	{
		return claro_sql_fetch_all($result);
	}
	else
	{
		return false;
	}
}


/**
 * Claroline mySQL query wrapper returning the number of rows affected by the 
 * query
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery  - the sql query
 * @param  handler $dbHandler - optional
 * @return int                - the number of rows affected by the query
 *
 * @see	claro_sql_query()
 *
 */


function claro_sql_query_affected_rows($sqlQuery, $dbHandler = '#')
{
	$result = claro_sql_query($sqlQuery, $dbHandler);

	if ($result)
	{
		if ($dbHandler == '#')
		{
			return mysql_affected_rows();
		}
		else
		{
			return mysql_affected_rows($dbHandler);
		}
	}
	else
	{
		return false;
	}
}


/**
 * Claroline mySQL query wrapper returning the last id generated by the last 
 * inserted row
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery  - the sql query
 * @param  handler $dbHandler - optional
 * @return long         -  the id generated by the previous insert query
 *
 * @see	claro_sql_query()
 *
 */

function claro_sql_query_insert_id($sqlQuery, $dbHandler = '#')
{
	$result = claro_sql_query($sqlQuery, $dbHandler);

	if ($result)
	{
		return mysql_insert_id();
	}
	else
	{
		return false;
	}
}


//////////////////////////////////////////////////////////////////////////////
//					CLAROLINE FAILURE MANGEMENT
//////////////////////////////////////////////////////////////////////////////

/*
 * The Failure Management module is here to compensate
 * the absence of 'exception' device in PHP 4.
 */


/**
 * $claro_failureList -	array containing all the failure recorded
 * in order	of arrival.
 */

$claro_failureList = array();

/**
 * Fills a global array	called $claro_failureList
 * This	array collects all the failure occuring	during the script runs
 * The main	purpose	is allowing	to manage the display messages externaly
 * from	the	functions or objects. This strengthens encupsalation principle
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  string $failureType - the type of failure
 * @global array $claro_failureList
 * @return bolean false	to stay	consistent with	the	main script
 */

function claro_set_failure($failureType)
{
	global $claro_failureList;

	$claro_failureList[] = $failureType;

	return false;
}



/**
 * get the last	failure	stored in $claro_failureList;
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param void
 * @return string -	the	last failure stored
 */

function claro_get_last_failure()
{
	global $claro_failureList;

	return $claro_failureList[ count($claro_failureList) - 1 ];
}



/**
 * collects and manage failures occuring during script execution
 * The main purpose is allowing to manage the display messages externaly 
 * from functions or objects. This strengthens encupsalation principle
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

class claro_failure
{
    /* 
     * IMPLEMENTATION NOTE : For now the $claro_failureList list is set to the 
     * global scope, as PHP 4 is unable to manage static variable in class. But 
     * this feature is awaited in PHP 5. The class is already written to minize 
     * the change when static class variable will be possible. And the API won't 
     * change.
     */

	var	$claro_failureList = array();

	/**
	 * Pile	the	last failure in	the	failure	list
	 *
	 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
	 * @param  string $failureType - the type of failure
	 * @global array  $claro_failureList
	 * @return bolean false	to stay	consistent with	the	main script
	 */

	function set_failure($failureType)
	{
		global $claro_failureList;

		$claro_failureList[] = $failureType;

		return false;
	}


	/**
	 * get the last	failure	stored
	 *
	 * @author Hugues Peeters <hugues.peeters@claroline.net>
	 * @param void
	 * @return string -	the	last failure stored
	 */

	function get_last_failure()
	{
		global $claro_failureList;

		return $claro_failureList[ count($claro_failureList) - 1 ];
	}
}

//////////////////////////////////////////////////////////////////////////////
//							  DISPLAY OPTIONS
//							student	view, title, ...
//////////////////////////////////////////////////////////////////////////////


/**
 * Displays the title of a tool. Optionally, there can be a subtitle below
 * the normal title, and / or a supra title above the normal title.
 *
 * e.g. supra title:
 * group
 * GROUP PROPERTIES
 *
 * e.g. subtitle:
 * AGENDA
 * calender & events tool
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  mixed $titleElement - it could either be a string or an array
 *                               containing 'supraTitle', 'mainTitle',
 *                               'subTitle'
 * @return void
 */

function claro_disp_tool_title($titleElement, $helpUrl = false)
{
	// if titleElement is simply a string transform it into an array

    if (is_string($titleElement)) 
	{
		$tit = $titleElement;
		unset($titleElement);
		$titleElement['mainTitle'] = $tit;
	}

	echo '<h3>';

    if ($helpUrl)
    {
    	global $clarolineRepositoryWeb, $langHelp;

?><a href="#" onClick="MyWindow=window.open('<?php echo $clarolineRepositoryWeb ?>help/<?php echo $helpUrl ?>','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;"><?php


        echo '<img src="'.$clarolineRepositoryWeb.'/img/help.gif" '
                .' alt ="'.$langHelp.'"'
                .' align="right"'
                .' hspace="30">'
            .'</a>';
    }


    if ($titleElement['supraTitle'])
	{
		echo '<small>'.$titleElement['supraTitle'].'</small><br>';
	}

	if ($titleElement['mainTitle'])
	{
		echo $titleElement['mainTitle'];
	}

	if ($titleElement['subTitle'])
	{
		echo '<br><small>'.$titleElement['subTitle'].'</small>';
	}

	echo '</h3>';
}


/**
 * Display options to switch between student view and course manager view
 *
 * @param $isStudentView the current state (true|false)
 * @author Roan	Embrechts
 * @version	1.1
 *
 * Example code	for	using this in your tools:
 * include('../inc/lib/claro_main.lib.php'); //this	is perhaps necessary
 * $is_AllowedToEdit = $is_courseAdmin;
 * if ($is_AllowedToEdit) claro_disp_tool_view_option($isStudentView);
 * $is_AllowedToEdit = $is_AllowedToEdit &&	! $isStudentView;
 * ...
 */
function claro_disp_tool_view_option($isStudentView	= false)
{
	global $PHP_SELF, $langCourseManagerview, $langStudentView;
	if ($isStudentView)
	{
		?>
		<p align="right" style="margin-top:	0; margin-bottom: 0"><small><b>
		<?php echo $langStudentView;?> | <a	href="<? echo $PHP_SELF; ?>"><u><?php echo $langCourseManagerview; ?></u></a>
		</small></b></p>
		<?php
	}
	else
	{
		?>
		<p align="right" style="margin-top:	0; margin-bottom: 0"><small><b>
		<a href="<?	echo $PHP_SELF;	?>?isStudentView=true"><u><?php	echo $langStudentView;?></u></a>
		| <?php	echo $langCourseManagerview; ?>
		</small></b></p>
		<?php
	}
}


/**
	Display	list of	messages

	!!! DEPRECATED !!!

    USE claro_disp_message_box($message) INSTEAD

	@param $msgArrBody array of	messages
	@author	Christophe gesché <moosh@phpFrance.com>
	@version 1.0

	Example	code for using this	in your	tools:
	$msgArrBody["nameOfCssClass"]="foo";
.	css	class can be defined in	script but try to use
	class from	generic	css	()
	error success warning
	...
*/

function claro_disp_msg_arr($msgArrBody)
{
	if (is_array($msgArrBody))
	{
		foreach ($msgArrBody as $thisMsgArr)
			foreach ($thisMsgArr as $anotherThis)
				$messageList[] = $anotherThis;

		claro_disp_message_box( '<p>'.implode('<p></p>', $messageList).'</p>' );
	}
}


/**
	Display	authencation form if needed

	@author	Christophe gesché <moosh@phpFrance.com>
	@version 0.1
*/

function claro_disp_auth_form()
{
	global  $includePath, $clarolineRepositoryWeb,
			$_uid, $is_courseAllowed, $_course,
			$siteName,

			$lang_thisCoursIsProtectedOrDontExist , $lang_password ,
			$lang_username, $lang_doLogin, $lang_IfYouDontHaveAccountOn, 
			$lang_YouNeedToSubscribeThisCourse, $langReg;

			
	if ( ! $is_courseAllowed)
	{
		/*
                Ce cours est protégé

                1° Entrez votre nom d'utilisteur et votre mot de passe

                   <small>(Si vous n'avez pas encore de compte sur 'site name'
                
                   cliquez ici)</small>.

                2° Votre profil utilisateur n'est pas inscrit à ce cours

                    Si vous souhaitez vous inscrire à ce cours cliquez ici.
        */

        echo "<p align=\"center>\">"
            .$lang_this_course_is_protected."<br>"
            .$lang_enter_your_user_name_and_password
            ."</p>";
		
		if( ! $_uid && ! $_course['visibility'])
		{
			echo "<table align=\"center\">\n"
                ."<tr>"
                ."<td>"
				."<form action=\"".$PHP_SELF."\" method=\"POST\">\n"
				
				."<fieldset>\n"
				
				."<legend>".$lang_doLogin."</legend>\n"
				
				."<label for=\"username\">".$lang_username." : </label><br>\n"
				."<input type=\"text\" name=\"login\" id=\"username\"><br>\n"
				
				."<label for=\"password\">".$lang_password." : </labeL><br>\n"
				."<input type=\"password\" name=\"password\" id=\"password\"><br>\n"
				."<input type=\"submit\" >\n"
				
				."</fieldset>\n"
				
				."</form>\n"
                ."</td>"
                ."</tr>"
				."</table>";
				
			/*
			 * If users are allowed to register themselves to the platform
			 * redirect this user to the platform registration page
			 */
			 
			if ( $allowSelfReg || !isset($allowSelfReg) ) 
			{
				echo "<p>\n"
					.$lang_if_you_dont_have_a_user_account_profile_on." ".$siteName
					."<a href=\"".$clarolineRepositoryWeb."auth/inscription.php\">"
					.$lang_click_here
					."</a>\n"
					."</p>\n";
			}
		} // end if ! $uid && ! $course['visibility']
		
		/*
		 * If the user is logged (authenticated) on the platform
		 * and the course settings still allows user self enrollment,
		 * redirect him to the course enrollment pages
		 */
		
		elseif( $_uid && $_course['registrationAllowed'] )
		{
			// if  I'm logged but have no access
			// this course is close, right, but the subscribe to this course ?
				echo "<p>\n"
					.$lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course.'<br>'
                    .$lang_if_you_wish_to_enroll_to_this_course
					."<a href=\"".$clarolineRepositoryWeb."auth/courses.php?cmd=rqReg&keyword=".$_course['officialCode']."\" >"
					.$langReg
					."</a>\n"
					."</p>\n";
					
		} // elseif$_uid && $_course['registrationAllowed']
		
		include($includePath."/claro_init_footer.inc.php");
		
		die('');
	}
}


/**
	Display selectbox for select a course

	@author	Christophe gesché <moosh@claroline.net>
	@version 0.1
*/

function claro_disp_select_course()
{
	global  $_uid, $_cid,

			$siteName,$includePath, 
			$langManager, $administrator
			;

	$mainTbl = claro_sql_get_main_tbl();
	$tbl_courses 				= $mainTbl['cours'];
	$tbl_rel_user_courses	= $mainTbl['rel_course_user'];
	if ( ! $_cid)
	{
		/*
			This function is called when a $_cid is request
		*/
		
		if($_uid)
		{
			$sql_get_course_list = 
			"select c.code `value`, concat(c.intitule,' (',c.fake_code,')') `name` 
			 from `".$tbl_courses."` c ,  `".$tbl_rel_user_courses."` cu
			 WHERE c.code= cu.code_cours and cu.user_id = '".$_uid."'" ;
		} // end if $uid 
		else
		{
			$sql_get_course_list = 
			"select c.code `value`, concat(c.intitule,' (',c.fake_code,')') `name` 
			from `".$tbl_courses."` c";
		}

		$resCourses = claro_sql_query($sql_get_course_list);
		while($course = mysql_fetch_array($resCourses))
		{
				$courses[]=$course;
		}
		if (is_array($courses))
		{
			claro_disp_tool_title("This tools need a course");
		?>
<form action="<?php echo $PHP_SELF ?>" method="post">
	<label for="selectCourse">Course</label> : 
		<select name="cidReq" id="selectCourse">
	<?php 
		echo implode("\t\t\t", prepare_option_tags($courses) );
	?>
		</select>
	
	<input type="submit">
</form>
		<?php
		}
		include($includePath."/claro_init_footer.inc.php");

		die('');
	}
}



/**
	Display	intro of tool
	This   use introductionSection.inc.php in a	function.

	!!!!! BETA !!!!! this  a test.	That's	work but is	that pertinent ????

	@param $idTools
	@author	Christophe Gesché <moosh@phpFrance.com>
	@version 0.1
*/

function claro_disp_intro($idTools)
{
	global $includePath, $_course,$urlAppend, $is_courseAdmin,
	       $langOk, $langAddIntro, $langModify, 
	       $langConfirmYourChoice,$langDelete;

	$moduleId =	$idTools; // Id	of the Student Paper introduction Area
	include($includePath.'/introductionSection.inc.php');
	return true;
}

/**
 * diplays the message box appearing on	the	top	of the window,
 * just	below the tool title. It is	recommended	to use this	function
 * to display any confirmation or error	messages, or to	ask	to the user
 * to enter	simple parameters.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message -	include	your self any additionnal html
 *							tag	if you need	them
 * @return void
 */

function claro_disp_message_box($message)
{
?>
<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">
<tr>
<td>
<?php echo $message; ?>
</td>
</tr>
</table>
<?php
}

/**
 * displays an anchor tag (<a ...>) which, thanks to style sheet (css),
 * looks like a button.
 *
 * This function is needed, because Netscap 4 family browsers renders CSS 
 * so badly that it makes the button unusable. The function prevents the problem 
 * to occur by removing class style  if the browser is from the Netscape 4 
 * familiy.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $url url inserted into the 'href' part of the tag
 * @param string $text text inserted between the two <a>...</a> tags (note : it 
 *        could also be an image ...) 
 * @param string $confirmMessage (optionnal) introduce a javascript confirmation popup
 * @return void
 */


function claro_disp_button($url, $text, $confirmMessage = '')
{
    global $HTTP_USER_AGENT;

    if (   claro_is_javascript_enabled()
        && ! preg_match('~^Mozilla/4\.[1234567]~', $HTTP_USER_AGENT))
    {
        if ($confirmMessage != '')
        {
            $onClickCommand =" if(confirm('".$confirmMessage."')){document.location='".$url."';return false}";
        }
        else
        {
            $onClickCommand = "document.location='".$url."';";
        }
        
        echo "<button class=\"claroButton\" onclick=\"".$onClickCommand."\">"
            .$text
            ."</button>&nbsp;\n";
    }
    else
    {
        echo '<nobr>[ <a  href="'.$url.'" '.$additionnalParam.'>'.$text.'</a> ] </nobr>';
    }
}

/**
 * Function used to draw a progression bar
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 * @param $progress progression in pourcent
 * @param $factor will be multiply by 100 to have the full size of the bar (i.e. 1 will give a 100 pixel wide bar)
 */
function claro_disp_progress_bar ($progress, $factor)
{
	global $clarolineRepositoryWeb;
        $maxSize = $factor * 100; //pixels
        $barwidth = $factor * $progress ;

	// display progress bar
	// origin of the bar
	$progressBar = "<img src=\"".$clarolineRepositoryWeb."img/bar_1.gif\" width=\"1\" height=\"12\" alt=\"\">";

	if($progress != 0)
        	$progressBar .= "<img src=\"".$clarolineRepositoryWeb."img/bar_1u.gif\" width=\"$barwidth\" height=\"12\" alt=\"\">";
	// display 100% bar

	if($progress!= 100 && $progress != 0)
        	$progressBar .= "<img src=\"".$clarolineRepositoryWeb."img/bar_1m.gif\" width=\"1\" height=\"12\" alt=\"\">";

	if($progress != 100)
        	$progressBar .= "<img src=\"".$clarolineRepositoryWeb."img/bar_1r.gif\" width=\"".($maxSize-$barwidth)."\" height=\"12\" alt=\"\">";
	// end of the bar
	$progressBar .=  "<img src=\"".$clarolineRepositoryWeb."img/bar_1.gif\" width=\"1\" height=\"12\" alt=\"\">";

	return $progressBar;
}


/**
 * Insert a	sort of	HTML Wysiwyg textarea inside a FORM
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $name 
 * @param string $content optional content previously inserted into	the	area
 * @param int	 $rows optional	textarea rows
 * @param int	 $cols optional	textarea columns
 * @param string $optAttrib	optional - additionnal tag attributes 
 *									   (wrap, class, ...)
 * @return void
 *
 * @globals	$urlAppend from	claro_main.conf.php
 *
 * @desc the html area currently implemented is	HTMLArea 3.0. To work correctly,
 * the area	needs a	specific stylesheet	previously loaded in the html header.
 * For that, use the claroline $htmlHeadXtra[] array at	the	top	of the script 
 * just	before including claro_init_header.inc.php
 *
 * Example : $htmlHeadXtra[] = '<style type="text/css">
 * @import url('.$urlAppend.'/claroline/inc/htmlarea'.'/htmlarea.css);
 *								</style>';
 */

function claro_disp_html_area($name, $content =	'',	
							  $rows=20,	$cols=80, $optAttrib='')
{
	global $urlAppend;

	$incPath = $urlAppend.'/claroline/inc/htmlarea';
?>

<textarea id    = "<?php echo $name; ?>" 
          name  = "<?php echo $name; ?>" 
          style = "width:100%" 
          rows  = "<?php echo $rows; ?>" 
          cols  = "<?php echo $cols ?>"
          <?php echo $optAttrib; ?> ><?php echo $content; ?></textarea>

<script>_editor_url	= "<?php echo  $incPath?>";</script>
<script	type="text/javascript" src="<?php echo $incPath; ?>/htmlarea.js"></script>
<script	type="text/javascript" src="<?php echo $incPath; ?>/lang/en.js"></script>
<script	type="text/javascript" src="<?php echo $incPath; ?>/dialog.js"></script>

<script	type="text/javascript">
var	editor = null;
function initEditor() {
  editor = new HTMLArea("<?php echo	$name ?>");

  // comment the following two lines to	see	how	customization works
  editor.generate();
  return false;
}

function insertHTML() {
 var html =	prompt("Enter some HTML	code here");
 if	(html) {editor.insertHTML(html);}
}
function highlight() {
  editor.surroundHTML('<span style="background-color: yellow">', '</span>');
}
</script>

<script>
initEditor();
</script>

<!-- <input type="button" name="ins"	value="	 insert	html  "	onclick="return	insertHTML();" /> -->
<!-- <input type="button" name="hil"	value="	 highlight text	 " onclick="return highlight();" /> -->
<?php
}

/**
 * function claro_build_nested_select_menu($name, $elementList)
 * Build in a relevant way 'select' menu for an HTML form containing nested data
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param string $name, name of the select tag
 *
 * @param array nested data in a composite way
 *
 *  Exemple :
 *
 *  $elementList[1]['name'    ] = 'level1';
 *  $elementList[1]['value'   ] = 'level1';
 *
 *  $elementList[1]['children'][1]['name' ] = 'level2';
 *  $elementList[1]['children'][1]['value'] = 'level2';
 *
 *  $elementList[1]['children'][2]['name' ] = 'level2';
 *  $elementList[1]['children'][2]['value'] = 'level2';
 *
 *  $elementList[2]['name' ]  = 'level1';
 *  $elementList[2]['value']  = 'level1';
 *
 * @return string the HTML flow
 * @desc depends on prepare option tags
 *
 */

function claro_build_nested_select_menu($name, $elementList)
{
    return "<select name=\"".$name."\">\n"
          .implode("\n", prepare_option_tags($elementList) )
          ."</select>\n";
}

/**
 * prepare the 'option' html tag for the claro_disp_nested_select_menu()
 * fucntion
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param array $elementList
 * @param int  $deepness (optionnal, default is 0)
 * @return array of option tag list
 */


function prepare_option_tags($elementList, $deepness = 0)
{
    foreach($elementList as $thisElement)
    {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[] = '<option value="'.$thisElement['value'].'">'
                          .$tab.$thisElement['name']
                          .'</option>';

        if (   isset( $thisElement['children'] )
            && sizeof($thisElement['children'] ) > 0)
        {
            $optionTagList = array_merge( $optionTagList,
                                          prepare_option_tags($thisElement['children'],
                                                              $deepness + 1 ) );
        }
    }

    return  $optionTagList;
} 
//////////////////////////////////////////////////////////////////////////////
//							  INPUT HANDLING
//							addslashes,...
//////////////////////////////////////////////////////////////////////////////

/**
 * Add slashes to $text if it has not be automatically done by magic_quotes
 * Use this function _ONLY_ for vars that are affected by magic_quote_gpc 
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @param string text to add slashes in
 * @return string $text without change if magi_quote_gpc is on, addslahed $text else
 * @desc Use this only for get/post/cookies vars, not for lang vars,...
 */
function claro_addslashes($text)
{
  if( get_magic_quotes_gpc() )
  {
    // magic_quote_gpc is on : do not addslashes
    return $text;
  }
  else
  {
    // magic_quote_gpc is off : addslashes
    return addslashes($text);
  }
}

/**
 * checks if the javascript is enabled on the client browser
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param void
 * @return boolean
 * @desc Actually a cookies is set on the header by a javascript code. 
 *       If this cookie isn't set, it means javascript isn't enabled.
 */

function claro_is_javascript_enabled()
{
    global $_COOKIE;

    if ($_COOKIE['javascriptEnabled'] == true)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * parse the user text (e.g. stored in database) 
 * before displaying it to the screen
 * For example it change new line charater to <br> tag etc.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $userText original user tex
 * @return string parsed user text
 */

function claro_parse_user_text($userText)
{
   global $claro_texRendererUrl; // see 'inc/conf/claro_main.conf.php'

   if ($claro_texRendererUrl) 
   {
       $userText = str_replace('[tex]',
                          '<img src="'.$claro_texRendererUrl.'?',
                          $userText);

       $userText = str_replace('[/tex]',
                           '">',
                           $userText);   	
   }
   else
   {
       $userText = str_replace('[tex]',
                              '<embed TYPE="application/x-techexplorer" texdata="',
                              $userText);

       $userText = str_replace('[/tex]',
                               '" width="100%" pluginspace="http://www.integretechpub.com/">',
                               $userText);
   }

   if ( strpos($usertext, '<!-- content: html -->') === false )
   {
        // only if the content isn't HTML change new line to <br>
        // Note the '<!-- content: html -->' is introduced by HTML Area
        $userTex = nl2br($userText); 
   }

   return $userText;
}


/**
 * strips the tags from str 
 * Why such a function ? for the future, to allow some tags some times.
 *
 * @param string $str text to clean from html tags
 * 
 */

function claro_strip_tags($str)
{
	return strip_tags($str);
}


?>
