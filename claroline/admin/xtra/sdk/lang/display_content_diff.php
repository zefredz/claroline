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

require '../../../../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('NotAllowed'));

/*
 * This script displays all the variables 
 * with the same content and a different name.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');
include($includePath."/lib/pager.lib.php");

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';
$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

// get start time
$starttime = get_time();

// pager params

$resultPerPage = 50;

if (isset($_REQUEST['offset'])) 
{
    $offset = $_REQUEST['offset'];
}
else
{
    $offset = 0;
}

// start content
$nameTools = 'Display different variables with the same content';

$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$urlTranslation = $urlSDK . 'translation_index.php';
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> get_lang('Administration'));
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> get_lang('SDK'));
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> get_lang('TranslationTools'));

include($includePath."/claro_init_header.inc.php");

echo claro_disp_tool_title($nameTools);

// start form

$form = "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";

if (isset($_REQUEST['language'])) 
{
    $language = $_REQUEST['language'];
}
else 
{
    $language = DEFAULT_LANGUAGE ;
}

$form .= "<p>Language: $language</p>";

// display select box with language in the table

$form .= "<p>Change Language: ";
$form .= "<select name=\"language\">";

$sql = "SELECT DISTINCT language 
        FROM ". $tbl_translation . "
        ORDER BY language ";
$results = claro_sql_query($sql);

while($result=mysql_fetch_row($results))
{
    if ($result[0] == $language) 
    {
        $form .= "<option value=$result[0] selected=\"selected\">" . $result[0] . "</option>";
    }
    else
    {
        $form .= "<option value=$result[0]>" . $result[0] . "</option>";
    }
}
$form .= "</select></p>";

$form .= "<p><input type=\"submit\" value=\"OK\" /></p>";
$form .= "</form>";

echo claro_disp_message_box($form);

// select variables with same content

$sql = " SELECT DISTINCT L1.language , L1.varContent , L1.varName , L1.sourceFile
    FROM ". $tbl_translation . " L1,
         ". $tbl_translation . " L2,
         ". $tbl_used_lang . " U
    WHERE L1.language = \"" . $language . "\" and
        L1.language = L2.language and
        L1.varContent = L2.varContent and
        L1.varName <> L2.varName and
        L1.varName = U.varName
    ORDER BY L1.varContent, L1.varName";

// build pager

$myPager = new claro_sql_pager($sql, $offset, $resultPerPage);
$results = $myPager->get_result_list();

// display nb results

echo '<p>' . get_lang('Total') . ': ' . $myPager->totalResultCount . '</p>' ;

// display pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?language='.$language);

// display table header 

echo "<table class=\"claroTable\" width=\"100%\">
<thead>
<tr class=\"headerX\">
<th>N°</th>
<th>language</th>
<th>varName</th>
<th>varContent</th>
<th>sourceFile</th>
</tr>
</thead>
<tbody>";

$varContent="";
$i = $offset;
$color = true;

foreach ($results as $result)
{
     if ($result['varContent'] != $varContent)
     {
        $varContent = $result['varContent'];
        $color = !$color;
     }
     if ($color == true)
     {
        echo "<tr style=\"background-color: #ccc;\">";
     } 
     else
     {
        echo "<tr>";
     }
     echo  "<td>" . ++$i . "</td>
           <td>" . $result['language'] . "</td>
           <td>" . $result['varName'] . "</td>
           <td>" . $result['varContent'] . "</td>
           <td>" . $result['sourceFile'] . "</td>";
     echo "</tr>";
}

echo "</tbody>\n</table>\n";

// display pager

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?language='.$language);

// display nb results

echo '<p>' . get_lang('Total') . ': ' . $myPager->totalResultCount . '</p>' ;

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>";

// display footer 

include($includePath."/claro_init_footer.inc.php");

?>
