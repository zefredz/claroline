<?php // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.6
  +----------------------------------------------------------------------+
  | Copyright (c) 2001 - 2004 Universite catholique de Louvain (UCL)     |
  +----------------------------------------------------------------------+
 */

$tlabelReq = "CLUSR___";
define("CLARO_STUDENT_STATUS",1);
define("CLARO_COURSE_CREATOR_STATUS",5);
$descSizeToPrupose = array(3,5,10,15,20); // size in lines for desc - don't add 1

$langFile = "registration";

require '../inc/claro_init_global.inc.php';
include($includePath."/lib/admin.lib.inc.php");
include($includePath."/lib/events.lib.inc.php");
include($includePath."/lib/user.lib.inc.php");
@include($includePath."/lib/debug.lib.inc.php");
// add userInfo langfile / english one + $language one if exists !
include("../lang/english/userInfo.inc.php");
@include("../lang/".$languageInterface."/userInfo.inc.php");


$TBL_USERINFO_DEF=$_course['dbNameGlu']."userinfo_def";
$TBL_USERINFO_CONTENT=$_course['dbNameGlu']."userinfo_content";
$interbredcrump[]= array ("url"=>"user.php", "name"=> $langUsers);
$currentCourse = $currentCourseID;

$nameTools = $langUser;

/** OUTPUT **/


include($includePath."/claro_init_header.inc.php");
if ( ! $is_courseAllowed) claro_disp_auth_form();
define ("WARNING_MESSAGE", "<h4 style='color:red'> Database problem !! </h4>");

/*
 * data  found  in settings  are :
 *	$uid
 *	$isAdmin
 *	$isAdminOfCourse
 *	$mainDbName
 *	$currentCourseID
 */

$userIdViewed = $uInfo; // Id of the user we want to view coming from the user.php

/*--------------------------------------------------------
  Connection API between Claroline and the current script
  --------------------------------------------------------*/

$mainDB = $mainDbName; // main DB of Claroline
$courseCode = $currentCourseID = $_course['sysCode'];
$tbl_coursUser = "$mainDB`.`cours_user";

//mysql_select_db($_course['dbName']) or die (WARNING_MESSAGE); // select Course DB

$userIdViewer = $_uid; // id fo the user currently online
//$userIdViewed = $HTTP_GET_VARS['userIdViewed']; // Id of the user we want to view

$allowedToEditContent 	= ($userIdViewer == $userIdViewed) || $is_platformAdmin;
$allowedToEditDef     	= $is_courseAdmin;
$is_allowedToTrack 		= $is_courseAdmin && $is_trackingEnabled || ($userIdViewer == $userIdViewed );


// clean field submited by the user
if ($HTTP_POST_VARS)
{
	foreach($HTTP_POST_VARS as $key => $value)
	{
		$$key = replace_dangerous_char($value);
	}
}


/*======================================
           COMMANDS SECTION
  ======================================*/

$displayMode = "viewContentList";


if ($allowedToEditDef)
{
	if ($submitDef)
	{
		if ($id)
		{
			claro_user_info_edit_cat_def($id, $title, $comment, $nbline);
		}
		else
		{
			claro_user_info_create_cat_def($title, $comment, $nbline);
		}

		$displayMode = "viewDefList";
	}
	elseif ($removeDef)
	{
		claro_user_info_remove_cat_def($removeDef, true);
		$displayMode = "viewDefList";
	}
	elseif ($editDef)
	{
		$displayMode = "viewDefEdit";
	}
	elseif (isset($addDef))
	{
		$displayMode = "viewDefEdit";
	}
	elseif ($moveUpDef)
	{
		claro_user_info_move_cat_rank($moveUpDef, "up");
		$displayMode = "viewDefList";
	}
	elseif ($moveDownDef)
	{
		claro_user_info_move_cat_rank($moveDownDef, "down");
		$displayMode = "viewDefList";
	}
	elseif($viewDefList)
	{
		$displayMode = "viewDefList";
	}
	elseif ($editMainUserInfo)
	{
		$userIdViewed = $editMainUserInfo;
		$displayMode = "viewMainInfoEdit";
	}
	elseif ($submitMainUserInfo)
	{
		$userIdViewed = $submitMainUserInfo;

		$promoteCourseAdmin ? $userProperties['status'] = CLARO_STUDENT_STATUS : $userProperties['status'] = CLARO_COURSE_CREATOR_STATUS;
		$promoteTutor       ? $userProperties['tutor' ] = 1 : $userProperties['tutor' ] = 0;

		$userProperties['role'] =  $role;

		update_user_course_properties($userIdViewed, $courseCode, $userProperties);

		$displayMode = "viewContentList";
	}
}

// COMMON COMMANDS


if ($allowedToEditContent)
{
	if ($submitContent)
	{
		if ($cntId)	// submit a content change
		{
			claro_user_info_edit_cat_content($catId, $userIdViewer, $content, $REMOTE_ADDR);
		}
		else		// submit a totally new content
		{
			claro_user_info_fill_new_cat_content($catId, $userIdViewer, $content, $REMOTE_ADDR);
		}

		$displayMode = "viewContentList";
	}
	elseif ($editContent)
	{
		$displayMode = "viewContentEdit";

		$userIdViewed = $userIdViewer;
	}
}

//////////////////////////////////////////////////////////////////////////////
// OUTPUT
//////////////////////////////

event_access_tool($_tid, $_SESSION['_courseTool']['label']);

claro_disp_tool_title($nameTools);

/*======================================
             DISPLAY MODES
  ======================================*/

// Back button for each display mode (Top)
echo '<p align="right"><a href="user.php">'.$langBackToUsersList."</a></p>\n";

if ($displayMode == "viewDefEdit")
{
	/*>>>>>>>>>>>> CATEGORIES DEFINITIONS : EDIT <<<<<<<<<<<<*/

	$catToEdit = claro_user_info_get_cat_def($editDef);
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<input type="hidden" name="id" value="<?php echo $catToEdit['id']?>">
<table>
<tr>
<td>
<label for="title" ><?php echo $langTitle?></label> :
</td>
<td>
<input type="text" name="title" id="title" size="80" maxlength="80" value ="<?php echo $catToEdit['title']?>" >
</td>
</tr>

<tr>
<td>
<label for="comment" ><?php echo $langComment?></label> :
</td>
<td>
<textarea name="comment" id="comment" cols="60" rows="3" wrap="virtual"><?php echo $catToEdit['comment']?></textarea>
</td>
</tr>

<tr>
<td nowrap>
<label for="nbline" ><?php echo $langLineNumber?></label> :
</td>
<td>
<select name="nbline" id="nbline">
<?php
if ($catToEdit['nbline'])
{ ?>
	<option value="<?php echo $catToEdit['nbline']?>" selected><?php echo $catToEdit['nbline']?> <?php echo $langLineOrLines?></option>
	<option>---</option>
<?php
}
sort($descSizeToPrupose);
?>
<option value="1">1 <?php echo $langLine; ?></option>
<?php
foreach($descSizeToPrupose as $nblines)
{
	echo '<option value="'.$nblines.'">'.$nblines.' '.$langLines.'</option>';
}

?>
</select>
</td>
<tr>
<td>&nbsp;</td>
<td align="center"><input type="submit" name="submitDef" value="<?php echo $langOk?>"></td>
</tr>
</table>
</form>

<?
}
elseif ($displayMode == "viewDefList")
{
	/*>>>>>>>>>>>> CATEGORIES DEFINITIONS : LIST <<<<<<<<<<<<*/

	$catList = claro_user_info_claro_user_info_get_cat_def_list();

	if ($catList)
	{

		foreach ($catList as $thisCat)
		{
			// displays Title and comments

			echo	"<p>",
					"<b>".htmlize($thisCat['title'])."</b><br>\n",
					"<i>".htmlize($thisCat['comment'])."</i>\n",
					"</p>";

			// displays lines

			echo	"<blockquote>\n",
					"<font color=\"gray\">\n";

			for ($i=1;$i<=$thisCat['nbline'];$i++ )
			{
				echo "<br>__________________________________________\n";
			}

			echo	"</font>\n",
					"</blockquote>\n";

			// displays commands

			echo	"<a href=\"".$_SERVER['PHP_SELF']."?removeDef=".$thisCat['catId']."\">".
					"<img src=\"".$clarolineRepositoryWeb."/img/delete.gif\" border=\"0\" alt=\"".$langRemove."\">".
					"</a>".
					"<a href=\"".$_SERVER['PHP_SELF']."?editDef=".$thisCat['catId']."\">".
					"<img src=\"".$clarolineRepositoryWeb."/img/edit.gif\" border=\"0\" alt=\"".$langEdit."\">".
					"</a>".
					"<a href=\"".$_SERVER['PHP_SELF']."?moveUpDef=".$thisCat['catId']."\">".
					"<img src=\"".$clarolineRepositoryWeb."/img/up.gif\" border=\"0\" alt=\"".$langMoveUp."\">".
					"</a>".
					"<a href=\"".$_SERVER['PHP_SELF']."?moveDownDef=".$thisCat['catId']."\">".
					"<img src=\"".$clarolineRepositoryWeb."/img/down.gif\" border=\"0\" alt=\"".$langMoveDown."\">".
					"</a>\n";
		} // end for each

	} // end if ($catList)


	echo	 "<center>\n"
			.'<form method="post" action="'.$_SERVER['PHP_SELF'].'">'
			.'<input type="submit" name="addDef" value="'.$langAddNewHeading.'">'
			."</form>\n"
			."<center>\n";
}
elseif ($displayMode == "viewContentEdit")
{
	/*>>>>>>>>>>>> CATEGORIES CONTENTS : EDIT <<<<<<<<<<<<*/

	$catToEdit = claro_user_info_get_cat_content($userIdViewed,$editContent);
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="cntId" value="<?php echo $catToEdit['contentId']; ?>">
<input type="hidden" name="catId" value="<?php echo $catToEdit['catId'    ]; ?>">
<input type="hidden" name="uInfo"  value="<?php echo $userIdViewed; ?>">
<p><label for="content" ><b><?php echo $catToEdit['title']?></b></label></p>
<p><i><?php echo htmlize($catToEdit['comment'])?></i></p>
<?php if ($catToEdit['nbline']==1)
	{
?><input  type="text" name="content" id="content" size="80" value="<?php echo $catToEdit['content']?>" >
<?php
	}
	else
	{
?><textarea  cols="80" rows="<?php echo $catToEdit['nbline']?>" name="content" id="content" wrap="VIRTUAL"><?php echo $catToEdit['content']?></textarea>
<?php }
?><input type="submit" name="submitContent" value="<?php echo $langOk?>">
</form>

<?php
}
elseif ($displayMode =="viewMainInfoEdit")
{
	/*>>>>>>>>>>>> CATEGORIES MAIN INFO : EDIT <<<<<<<<<<<<*/

	$mainUserInfo = claro_user_info_get_main_user_info($userIdViewed, $courseCode);

	if ($mainUserInfo)
	{
		($mainUserInfo['status'] == 1) ? $courseAdminChecked = "checked" : $courseAdminChecked = "";
		($mainUserInfo['tutor' ] == 1) ? $tutorChecked       = "checked" : $tutorChecked       = "";

		echo	'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'

				."<input type=\"hidden\" name=\"submitMainUserInfo\" value=\"".$userIdViewed."\">\n"

				."<table class=\"claroTable\" width=\"80%\" border=\"0\">"

				."<tr class=\"headerX\">\n"
				."<th align=\"left\">".$langName."</th>\n"
				."<th align=\"left\"><label for=\"role\">".$langRole."</label></th>\n"
				."<th><label for=\"promoteTutor\">".$langTutor."</label></th>\n"
				."<th><label for=\"promoteCourseAdmin\">".$langCourseManager."</label></th>\n"
				."</tr>\n"

				."<tfoot><tr align=\"center\">"
				."<td align=\"left\"><b>".htmlize($mainUserInfo['firstName']).' '.htmlize($mainUserInfo['lastName'])."</b></td>\n"

				.'<td align="left"><input type="text" name="role" id="role" value="'.$mainUserInfo['role'].'" maxlength="40"></td>'

				.'<td><input type="checkbox" name="promoteTutor" id="promoteTutor" value="1" '.$tutorChecked.'></td>';


		if ( ! ($is_courseAdmin && $_uid == $userIdViewed) )
		{
			echo	"<td><input type=\"checkbox\" name=\"promoteCourseAdmin\"  id=\"promoteCourseAdmin\" value=\"1\"",$courseAdminChecked,"></td>\n";
		}
		else
		{
			echo	"<td>",$langCourseManager,"</td>\n";

		}


		echo	"<td style=\"\"><input type=\"submit\" name=\"submit\" value=\"Ok\"></td>\n",
				"</tr>",
				"</tfoot></table>",
				"</form>\n";

		echo	"<p><a href=\"mailto:",$mainUserInfo['email'],"\">",$mainUserInfo['email'],"</a></p>";

	}
}
elseif ($displayMode == "viewContentList") // default display
{
	/*>>>>>>>>>>>> CATEGORIES CONTENTS : LIST <<<<<<<<<<<<*/

	$mainUserInfo = claro_user_info_get_main_user_info($userIdViewed, $courseCode);

	if ($mainUserInfo)
	{
		if ($mainUserInfo['picture'] != '')
		{
			echo "<img src=\"".$clarolineRepositoryWeb."/img/users/".$mainUserInfo['picture']."\" border=\"1\">";
		}

		echo	"<table class=\"claroTable\" width=\"80%\" border=\"0\">",
				"<tr class=\"headerX\">\n",
				"<th align=\"left\">",$langName,"</th>\n",
				"<th align=\"left\">",$langRole,"</th>\n",
				"<th>",$langTutor,"</th>\n",
				"<th>",$langCourseManager,"</th>\n",
				($allowedToEditDef?"<th>".$langEdit."</th>\n":""),
        ($is_allowedToTrack?"<th>".$langTracking."</th>\n":""),
				"</tr>\n",

				"<tr align=\"center\">\n",

				"<td  align=\"left\"><b>",htmlize($mainUserInfo['firstName'])," ",htmlize($mainUserInfo['lastName']),"</b></td>\n",
				"<td  align=\"left\">",htmlize($mainUserInfo['role']),"</td>";

				if ($mainUserInfo['tutor'] == 1)
				{
					echo "<td>".$langTutor."</td>\n";
				}
				else
				{
					echo "<td> - </td>\n";
				}

				if ($mainUserInfo['status'] == 1)
				{
					echo "<td>".$langCourseManager."</td>";
				}
				else
				{
					echo "<td> - </td>\n";
				}

				if($allowedToEditDef)
				{
					echo	"<td>".
							"<a href=\"".$_SERVER['PHP_SELF']."?editMainUserInfo=".$userIdViewed."\">".
							"<img border=\"0\" alt=\"".$langEdit."\" src=\"".$clarolineRepositoryWeb."/img/edit.gif\">".
							"</a>".
							"</td>";
				}
				if($is_allowedToTrack)
				{
						echo	"<td>".
							"<a href=\"../tracking/userLog.php?uInfo=".$userIdViewed."\">".
							"<img border=\"0\" alt=\"".$langTracking."\" src=\"".$clarolineRepositoryWeb."/img/statistiques.gif\">".
							"</a>",
							"</td>";
				}
				echo "</tr>",
				"</table>",
				"<p><a href=\"mailto:",$mainUserInfo['email'],"\">",$mainUserInfo['email'],"</a>",

				"<p>\n",
				"<hr noshade size=\"1\" style=\"color:#99CCFF\">\n";
	}

	if ($allowedToEditDef) // only course administrators see this line
	{
		echo	"<div align=right>"
				.'<form method="post" action="'.$_SERVER['PHP_SELF'].'">'
				.$langCourseAdministratorOnly.' : '
				.'<input type="submit" name="viewDefList" value="'.$langDefineHeadings.'">'
				.'</form>'
				.'<hr noshade size="1" style="color:#99CCFF">'
				."</div>\n";
	}

	$catList = claro_user_info_get_course_user_info($userIdViewed);

	if ($catList)
	{
		foreach ($catList as $thisCat)
		{
			// Category title

			echo	'<p><b>'.$thisCat['title']."</b></p>\n";

			// Category content

			echo	"<blockquote>\n";

			if ($thisCat['content'])
			{
				echo htmlize($thisCat['content'])."\n";
			}
			else
			{
				echo '....';
			}

			// Edit command

			if ($allowedToEditContent)
			{
				echo	"<br><br>\n",
						"<a href=\"".$_SERVER['PHP_SELF']."?editContent=",$thisCat['catId'],"&uInfo=",$userIdViewed,"\">",
						"<img src=\"".$clarolineRepositoryWeb."/img/edit.gif\" border=\"0\" alt=\"".$langEdit."\">",
						"</a>\n";
			}

			echo	"</blockquote>\n";
		}
	}
}

// Back button for each display mode (bottom)
echo "<p align=\"right\"><a href=\"user.php\">".$langBackToUsersList."</a></p>\n";

include($includePath."/claro_init_footer.inc.php");
?>
