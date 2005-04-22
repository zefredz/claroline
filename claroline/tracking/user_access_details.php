<?php // $Id$
/**
 * CLAROLINE
 *
 * @version  1.6 *
 *
 * @copyright 2001, 2005 Universite catholique de Louvain (UCL)
 * @license GPL
 * @author see CREDITS.txt
 *
 */ 
require '../inc/claro_init_global.inc.php';

$nameTools = $langUserAccessDetails;

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langStatistics);

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
$tbl_mdb_names       = claro_sql_get_main_tbl();
$TABLEUSER           = $tbl_mdb_names['user'  ];
$tbl_cdb_names       = claro_sql_get_course_tbl();
$TABLETRACK_ACCESS        = $tbl_cdb_names['track_e_access'];
$TABLETRACK_DOWNLOADS        = $tbl_cdb_names['track_e_downloads'];

include($includePath."/lib/statsUtils.lib.inc.php");

$toolTitle['mainTitle'] = $nameTools;
switch ($_GET['cmd'])
{
	case 'tool' : 
	    	// set the subtitle for the claro_disp_tool_title function
		$toolTitle['subTitle'] = $langTool." : ".$toolNameList[$_GET['label']];
		// prepare SQL query
		$sql = "SELECT nom, prenom, MAX(UNIX_TIMESTAMP(`access_date`)) AS data, COUNT(`access_date`) AS nbr
			FROM `".$TABLETRACK_ACCESS."`
			LEFT JOIN `".$TABLEUSER."`
			ON `access_user_id` = `user_id`
			WHERE `access_tid` = '".$_GET['data']."'
			GROUP BY nom, prenom
			ORDER BY nom, prenom	";
		break;
	case 'doc'  :	
	    	// set the subtitle for the claro_disp_tool_title function
		$toolTitle['subTitle'] = $langDocument." : ".$_GET['data'];	
		// prepare SQL query
		$sql = "SELECT nom, prenom, MAX(UNIX_TIMESTAMP(`down_date`)) AS data, COUNT(`down_date`) AS nbr
			FROM `".$TABLETRACK_DOWNLOADS."`
			LEFT JOIN `".$TABLEUSER."`
			ON `down_user_id` = `user_id`
			WHERE `down_doc_path` = '".$_GET['data']."'
			GROUP BY nom, prenom
			ORDER BY nom, prenom	";
		break;
}

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($toolTitle);

$is_allowedToTrack = $is_courseAdmin; 
if(  $is_allowedToTrack && $is_trackingEnabled )
{

?>
   
       <table class="claroTable" border="0" cellpadding="5" cellspacing="1">
              	<tr class="headerX">
                  <th><?php echo $langFirstName;?></th>
                  <th><?php echo $langLastName;?></th>
                  <th><?php echo $langLastAccess;?></th>                  
                  <th><?php echo $langNbrAccess;?></th>                  
              	</tr>
		<tbody>	
            
<?php

    $result = claro_sql_query($sql);  
    $i = 0;
    // display the list
    while ($userAccess = mysql_fetch_array ($result))
    {
	if($userAccess['nom'] == "" )
	{
	 	$anonymousCount = $userAccess['nbr'];
		continue;
	}
	$i++;    	
	echo "<tr>";
    	   	
    	echo "<td> ".$userAccess['nom']." </td> <td> "
		.$userAccess['prenom']." </td> <td> "
		.claro_disp_localised_date($dateTimeFormatLong, $userAccess['data'])." </td> <td> "
		.$userAccess['nbr']." </td>";
    	
    	echo "</tr>";
    }	
    // in case of error or no results to display
    if($i == 0 ) 
	echo "<td colspan=\"4\"><center>".$langNoResult."</center></td>";
 
    echo "</tbody>\n</table>";         
	
    if( $anonymousCount && $anonymousCount != "" )
	echo "<p>".$langAnonymousUserAccessCount.$anonymousCount."</p>";
 
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
include($includePath."/claro_init_footer.inc.php");
?>
