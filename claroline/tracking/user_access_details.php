<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*			                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+
      |   Authors : see CREDITS.txt                                     |
      +----------------------------------------------------------------------+

*/ 
$langFile = "tracking";
@include('../inc/claro_init_global.inc.php');

$nameTools = $langUserAccessDetails;

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langToolName);

$htmlHeadXtra[] = "<style type='text/css'>
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

$TABLEUSER              = $mainDbName."`.`user";
$TABLETRACK_ACCESS = $_course['dbNameGlu']."track_e_access";
$TABLETRACK_DOWNLOADS     = $_course['dbNameGlu']."track_e_downloads";

include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/statsUtils.lib.inc.php");


$toolTitle['mainTitle'] = $nameTools;
switch ($cmd)
{
	case 'tool' : 
		$toolTitle['subTitle'] = $langTool.$data;
		$sql = "SELECT nom, prenom, MAX(UNIX_TIMESTAMP(`access_date`)) AS data
			FROM `".$TABLETRACK_ACCESS."`, 
			     `".$TABLEUSER."`
			WHERE `access_user_id` = `user_id`
			AND `access_tool` = '".$data."'
			AND `access_user_id` IS NOT NULL
			GROUP BY nom, prenom
			ORDER BY nom, prenom	";
		break;
	case 'doc'  :	
		$toolTitle['subTitle'] = $langDocument.$data;
		$sql = "SELECT nom, prenom, MAX(UNIX_TIMESTAMP(`down_date`)) AS data
			FROM `".$TABLETRACK_DOWNLOADS."`,
			     `".$TABLEUSER."`
			WHERE `down_user_id` = `user_id`
			AND `down_doc_path` = '".$data."'
			AND `down_user_id` IS NOT NULL
			GROUP BY nom, prenom
			ORDER BY nom, prenom	";
		break;
}
claro_disp_tool_title($toolTitle);

$is_allowedToTrack = $is_courseAdmin; 
if(  $is_allowedToTrack && $is_trackingEnabled )
{

?>
   
       <table class="claroTable" border="0" cellpadding="5" cellspacing="1">
              	<tr class="headerX">
                  <th><?echo $langFirstName;?></th>
                  <th><?echo $langLastName;?></th>
                  <th><?echo $langLastAccess;?></th>                  
              	</tr>
		<tbody>	
            
<?php

    $result = mysql_query($sql);  
    $i = 0;
    while ($userAccess = mysql_fetch_array ($result))
    {
    	$i++;
    	echo "<tr>";
    	
    	
    	echo "<td> ".$userAccess['nom']." </td> <td> ".$userAccess['prenom']." </td> <td> ".dateLocalizer($dateTimeFormatLong, $userAccess['data'])." </td>";
    	
    	echo "</tr>";
    }	
    // in case of error or no results to display
    if($i == 0 ) 
	echo "<td colspan=\"3\">".$langNoResult."</td>";
 
 echo "</tbody>\n</table>";         
 
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        echo $langTrackingDisabled;
    }
    else
    {
        echo $langNotAllowed;
    }
}

// footer
@include($includePath."/claro_init_footer.inc.php");
?>
