<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------


$langFile = "course_info";

$gidReset = true; // If user is here. It means he isn't in any group space now.
                  // So it's careful to to reset the group setting

require '../inc/claro_init_global.inc.php';

$htmlHeadXtra[] =
"<style type=text/css>
<!--
.comment { margin-left: 30px}
.invisible {color: #999999}
.invisible a {color: #999999}
-->
</style>";

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";

$toolRepository = '../';
$imgRepository = $clarolineRepositoryWeb."/img/";
$currentCourseRepository = $_course['path'];

include($includePath.'/claro_init_header.inc.php');

include('course_home.lib.php');

if ( ! $is_courseAdmin) die("<br><center>not allowed ...</center>");

if     ($is_courseAdmin)     $is_allowedToEdit = true;

/*
 * set access level of the user
 */

if     ($is_platformAdmin)   $reqAccessLevel = 'PLATFORM_ADMIN';
elseif ($is_courseAdmin  )   $reqAccessLevel = 'COURSE_ADMIN';

/*
 * Language initialisation of the tool names
 */

$toolNameList = array('CLANN___' => $langAnnouncement,
                      'CLFRM___' => $langForum,
                      'CLCAL___' => $langAgenda,
                      'CLCHT___' => $langChat,
                      'CLDOC___' => $langDocument,
                      'CLDSC___' => $langDescriptionCours,
                      'CLGRP___' => $langGroups,
                      'CLLNP___' => $langLearnPath,
                      'CLQWZ___' => $langExercise,
                      'CLWRK___' => $langWork,
                      'CLUSR___' => $langUser);

/*
 * Initialisation for the access level types
 */

$accessLevelList = array('ALL'            => 0, 
                         'COURSE_MEMBER'  => 1, 
                         'GROUP_TUTOR'    => 2, 
                         'COURSE_ADMIN'   => 3, 
                         'PLATFORM_ADMIN' => 4);

/*============================================================================
                                COMMAND SECTION
  ============================================================================*/


if ($_REQUEST['cmd']) $cmd = $_REQUEST['cmd'];

$msg = '';

/*----------------------------------------------------------------------------
                              SET THE TOOL ACCESSES
  ----------------------------------------------------------------------------*/

if ($cmd == 'exSetToolAccess')
{
    $enablableToolList = array();
    $disablableToolList = array();

    $currentToolStateList=get_course_tool_list($reqAccessLevel);

    foreach($currentToolStateList as $thisCurrentToolState)
    {

        if (in_array($thisCurrentToolState['id'],$_REQUEST['toolAccessList']))
        {
             $enablableToolList[] = $thisCurrentToolState['id'];
        }
        else
        {
            $disablableToolList[] = $thisCurrentToolState['id'];
        }
    }

    $enableToolQuerySucceed = enable_course_tool($enablableToolList);
    $disableToolQuerySucceed = disable_course_tool($disablableToolList);

    if ($enableToolQuerySucceed !== FALSE && $disableToolQuerySucceed !== FALSE)
    {
        $msg .= $langChangedTool;
    }
    else
    {
        $msg .= $langUnableChangedTool;
    }

}

/*----------------------------------------------------------------------------
                              ADD AN EXTERNAL TOOL
  ----------------------------------------------------------------------------*/


if ($cmd == 'exAdd')
{
    if ( ! empty ($_REQUEST['toolName']) && ! empty ($_REQUEST['toolUrl']))
    {
        if (insert_local_course_tool($_REQUEST['toolName'],$_REQUEST['toolUrl']) !== false )
        {
         $msg .= $langAddedExternalTool;
        }
        else
        {
         $msg .= $langUnableAddExternalTool;
        }
    }
    else
    {
        $msg .= $langMissingValue;
        $cmd = 'rqAdd';
    }
}

/*----------------------------------------------------------------------------
                         UPDATE EXTERNAL TOOL SETTINGS
  ----------------------------------------------------------------------------*/


if ($cmd == 'exEdit')
{
    if ( ! empty ($_REQUEST['toolName']) && ! empty ($_REQUEST['toolUrl']))
    {
        if (set_local_course_tool($_REQUEST['externalToolId'],$_REQUEST['toolName'],$_REQUEST['toolUrl']) !== false )
        {
            $msg .= $langUpdatedExternalTool;
        }
        else
        {
            $msg .= $langUnableUpdateExternalTool;
        }
    }
    else
    {
        $msg .= $langMissingValue;
        $cmd = 'rqEdit';
    }

}

/*----------------------------------------------------------------------------
                    DELETE EXTERNAL TOOL
  ----------------------------------------------------------------------------*/

if ($cmd == 'exDelete')
{
    if ($_REQUEST['externalToolId'])
    {
        if (delete_course_tool($_REQUEST['externalToolId']) !== false)
        {
            $msg .= $langDeletedExternalTool;
        }
        else
        {
            $msg .= $langUnableDeleteExternalTool;
        }
    }
    else
    {
        $msg .= $langUnableDeleteExternalTool;

    }


}

/*----------------------------------------------------------------------------
                     REQUEST AN EXTERNAL TOOL CHANGE OR ADD
  ----------------------------------------------------------------------------*/

if ($cmd == 'rqAdd' || $cmd == 'rqEdit')
{
    if ($_REQUEST['externalToolId'])
    {
        $externalToolId = $_REQUEST['externalToolId'];

        if ( isset ($_REQUEST['toolName']) && isset ($_REQUEST['toolUrl']))
        {
            $toolName = stripslashes($_REQUEST['toolName']);
            $toolUrl  = stripslashes($_REQUEST['toolUrl']);
        }
        else
        {
            $toolSettingList = get_course_tool_settings($externalToolId);
            $toolName = $toolSettingList['name'];
            $toolUrl  = $toolSettingList['url'];
        }
    }
    else
    {
        $externalToolId = null;

        $toolName = '';
        $toolUrl  = '';
    }

    $msg .= "<form action=\"".$PHP_SELF."\">"
            ."<input type=\"hidden\" name=\"cmd\" value=\"".($externalToolId ? 'exEdit' : 'exAdd')."\">";

    if ($externalToolId)
    {
        $msg .= "<input type=\"hidden\" name=\"externalToolId\" value=\"".$externalToolId."\">";
    }

    $msg .= "<label for=\"toolName\">".$langToolName."</label><br>"
            ."<input type=\"text\" name=\"toolName\" name=\"toolName\" value=\"".$toolName."\"><br>"
            ."<label for=\"toolUrl\">".$langToolUrl."</label><br>"
            ."<input type=\"text\" name=\"toolUrl\" name=\"toolUrl\" value=\"".$toolUrl."\"><br>"
            ."<input class=\"claroButton\" type=\"submit\" value=\"".$langOk."\">&nbsp;"
            ."<a class=\"claroButton\" href=\"" . $PHP_SELF ."\">".$langCancel."</a>"
            ."</form>";
}




/*============================================================================
                                    DISPLAY
  ============================================================================*/

$backLink = "<p>"
            . "<small>"
            . "<a href=\"".$coursesRepositoryWeb.$currentCourseRepository."/index.php?cidReset=true&cidReq=".$_cid."\">"
            . "&lt;&lt;&nbsp;" . $langHome. "</a>"
            . "</small>"
            . "</p>";

echo $backLink;

claro_disp_tool_title($langEditToolList);

if ($msg) claro_disp_message_box($msg);

echo "<p>".$langIntroEditToolList."</p>";

echo "<blockquote>\n"
    ."<form action=\"".$PHP_SELF."\">\n";

echo "<input type=\"hidden\" name=\"cmd\" value=\"exSetToolAccess\" />";

$toolList = get_course_tool_list($reqAccessLevel);

echo "<table class=\"claroTable\" >"
    . " <tr class=\"headerX\">"
    . " <th>".$langTools."</th>"
    . " <th>".$langActivate."</th>"
    . " </tr>";

foreach($toolList as $thisTool)
{
    // get name and url from course or main database

    if ( ! empty($thisTool['label'])) // standart claroline tool
    {
        $toolName      = $toolNameList[ $thisTool['label'] ];
        $url           = trim($toolRepository.$thisTool['url']);
        $removableTool = false;
    }
    else                            // external tool added by course manager
    {
        if ( ! is_null($thisTool['name']) ||  ! is_null($thisTool['url']) )
        {
            $removableTool = true;

            if ( ! empty($thisTool['name'])) $toolName = $thisTool['name'];
            else                             $toolName = '<i>no name</i>';

            $url = trim($thisTool['url']);
        }
    }

    if (! empty($thisTool['icon']))
    {
        $icon = $imgRepository.$thisTool['icon'];
    }
    else
    {
    	$icon = $imgRepository.'external.gif'; // default icon if none defined
    }

    if ($accessLevelList[$thisTool['access']] > $accessLevelList['ALL'])
    {
        $checkState = '';
    }
    else
    {
        $checkState = ' checked';
    }

    echo "<tr>";

    echo "<td ".$style.">"
    	."<label for=\"toolAccessList".$thisTool['id']."\">"
        ."<img src=\"".$icon."\">"
        .$toolName . "</label></td>"
       ."<td><input type=\"checkbox\" name=\"toolAccessList[]\"
               id=\"toolAccessList".$thisTool['id']."\" value=\"".$thisTool['id']."\"".$checkState.">";

    if ($removableTool)
    {
        echo "<a href=\"". $PHP_SELF ."?cmd=rqEdit&externalToolId=".$thisTool['id']."\">"
             ."<img src=\"" . $imgRepository. "edit.gif\" alt=\"".$langModify."\" />"
             ."</a>\n"
             . "<a href=\"". $PHP_SELF ."?cmd=exDelete&externalToolId=". $thisTool['id'] . "\""
             . " onClick=\"return confirmation('" . addslashes($toolName) . "');\">"
             ."<img src=\"" . $imgRepository. "delete.gif\" alt=\"".$langDelete."\" />"
             ."</a>\n";

    }

    echo "</td></tr>\n";
}

echo "</table>";

echo "<input class=\"claroButton\" type=\"submit\" value=\"".$langOk."\" />\n";
claro_disp_button($coursesRepositoryWeb.$_course['path'], $langCancel);
echo "</form>\n"
    ."</blockquote>\n";

echo "<hr size=\"1\" noshade=\"noshade\"  />";

claro_disp_button($PHP_SELF.'?cmd=rqAdd', $langAddExternalTool);

echo $backLink;

include $includePath.'/claro_init_footer.inc.php';

?>
