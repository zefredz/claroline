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

// SECURITY CHECK

if (!$is_platformAdmin) claro_disp_auth_form();

/*
 * This script displays all the variables 
 * with the same name and a different content.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

// table

$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

// get start time

$starttime = get_time();

// start content

$nameTools = 'Display variables difference';

$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$urlTranslation = $urlSDK . 'translation_index.php';
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> $langSDK);
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> $langTranslationTools);

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($nameTools);

// start form

echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";

if (isset($_REQUEST['language'])) 
{
    $language = $_REQUEST['language'];
}
else 
{
    $language = DEFAULT_LANGUAGE ;
}

echo "<p>Language: $language</p>";

// display select box with tables in the database

// display select box with language in the table

echo "<p>Change Language: ";
echo "<select name=\"language\">";
$sql = "SELECT DISTINCT language 
        FROM ". $tbl_translation . "
        ORDER BY language ";
$results = mysql_query($sql);

while($result=mysql_fetch_row($results))
{
    if ($result[0] == $language) 
    {
        echo "<option value=$result[0] selected=\"selected\">" . $result[0] . "</option>";
    }
    else
    {
        echo "<option value=$result[0]>" . $result[0] . "</option>";
    }
}
echo "</select></p>";

echo "<p><input type=\"submit\" value=\"OK\" /></p>";
echo "</form>";

// find all variables with same names and different content

// select variables with different content

$sql = " SELECT DISTINCT L1.language , L1.varName, L1.varContent , L1.sourceFile
    FROM ". $tbl_translation . " L1,
         ". $tbl_translation . " L2
    WHERE L1.language = \"". $language ."\" and
        L1.language = L2.language and
        L1.varName = L2.varName and
        L1.varContent <> L2.varContent
    ORDER BY L1.varName";

$results = mysql_query($sql) or die($problemMessage);

// display table header

echo "<table class=\"claroTable\">
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

$varName="";
$i = 0;
$color = true;

while($result=mysql_fetch_array($results))
{
     if ($result['varName'] != $varName)
     {
        $varName = $result['varName'];
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

echo "</tbody>\n</table>";

// get end time

$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>";

// display footer 

include($includePath."/claro_init_footer.inc.php");

?>
