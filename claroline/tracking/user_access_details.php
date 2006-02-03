<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.6 *
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author see CREDITS.txt
 *
 */ 
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('UserAccessDetails');

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> get_lang('Statistics'));

$tbl_mdb_names       = claro_sql_get_main_tbl();
$TABLEUSER           = $tbl_mdb_names['user'  ];
$tbl_cdb_names       = claro_sql_get_course_tbl();
$TABLETRACK_ACCESS        = $tbl_cdb_names['track_e_access'];
$TABLETRACK_DOWNLOADS        = $tbl_cdb_names['track_e_downloads'];

include($includePath."/lib/statsUtils.lib.inc.php");

$toolTitle['mainTitle'] = $nameTools;

$is_allowedToTrack = $is_courseAdmin;

include($includePath."/claro_init_header.inc.php");

if( $is_allowedToTrack && $is_trackingEnabled )
{
     if( isset($_REQUEST['cmd']) && ( $_REQUEST['cmd'] == 'tool' && !empty($_REQUEST['id']) ) )
    {
            // set the subtitle for the echo claro_disp_tool_title function
            $sql = "SELECT `access_tlabel` AS `label`
                    FROM `".$TABLETRACK_ACCESS."`
                    WHERE `access_tid` = ". (int)$_REQUEST['id']."
                    GROUP BY `access_tid`" ;

            $viewedToolLabel = claro_sql_query_get_single_row($sql);

            if( isset($viewedToolLabel['label']) && isset($toolNameList[$viewedToolLabel['label']]) )
                    $toolTitle['subTitle'] = get_lang('Tool')." : ".$toolNameList[$viewedToolLabel['label']];
                    
                    
            // prepare SQL query
            $sql = "SELECT `nom` AS `lastName`,
                        `prenom` AS `firstName`,
                        MAX(UNIX_TIMESTAMP(`access_date`)) AS `data`,
                        COUNT(`access_date`) AS `nbr`
                    FROM `".$TABLETRACK_ACCESS."`
                    LEFT JOIN `".$TABLEUSER."`
                    ON `access_user_id` = `user_id`
                    WHERE `access_tid` = '". (int)$_REQUEST['id']."'
                    GROUP BY `nom`, `prenom`
                    ORDER BY `nom`, `prenom`";
    }
    elseif( isset($_REQUEST['cmd']) && ( $_REQUEST['cmd'] == 'doc' && !empty($_REQUEST['path']) ) )
    {
            // set the subtitle for the echo claro_disp_tool_title function
            $toolTitle['subTitle'] = get_lang('Documents and Links')." : ".$_REQUEST['path'];
            // prepare SQL query
            $sql = "SELECT `nom` as `lastName`,
                        `prenom` as `firstName`,
                        MAX(UNIX_TIMESTAMP(`down_date`)) AS `data`,
                        COUNT(`down_date`) AS `nbr`
                    FROM `".$TABLETRACK_DOWNLOADS."`
                    LEFT JOIN `".$TABLEUSER."`
                    ON `down_user_id` = `user_id`
                    WHERE `down_doc_path` = '". addslashes($_REQUEST['path']) ."'
                    GROUP BY `nom`, `prenom`
                    ORDER BY `nom`, `prenom`";
    }
    else
    {
        $dialogBox = get_lang('WrongOperation');
    }

    echo claro_disp_tool_title($toolTitle);

    if( isset($dialogBox) ) echo claro_html::message_box($dialogBox);


    echo '<br />'."\n\n"
        .'<table class="claroTable" border="0" cellpadding="5" cellspacing="1">'."\n"
        .'<tr class="headerX">'."\n"
        .'<th>'.get_lang('UserName').'</th>'."\n"
        .'<th>'.get_lang('LastAccess').'</th>'."\n"
        .'<th>'.get_lang('NbrAccess').'</th>'."\n"
        .'</tr>'."\n"
        .'<tbody>'."\n\n";

    $i = 0;
    $anonymousCount = 0;
    if( isset($sql) )
    {
        $accessList = claro_sql_query_fetch_all($sql);
        // display the list
        foreach ( $accessList as $userAccess )
        {
            $userName = $userAccess['lastName']." ".$userAccess['firstName'];
            if( empty($userAccess['lastName']) )
            {
                 $anonymousCount = $userAccess['nbr'];
                continue;
            }
            $i++;
            echo '<tr>'."\n"
                .'<td>'.$userName.'</td>'."\n"
                .'<td>'.claro_disp_localised_date($dateTimeFormatLong, $userAccess['data']).'</td>'."\n"
                .'<td>'.$userAccess['nbr'].'</td>'."\n"
                .'</tr>'."\n\n";
        }
    }
    // in case of error or no results to display
    if( $i == 0 || !isset($sql) ) echo '<td colspan="3"><center>'.get_lang('NoResult').'</center></td>'."\n\n";
 
    echo '</tbody>'."\n\n".'</table>'."\n\n";
    
    if( $anonymousCount != 0 )
        echo '<p>'.get_lang('AnonymousUserAccessCount').' '.$anonymousCount.'</p>'."\n";
 
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        echo get_lang('TrackingDisabled');
    }
    else
    {
        echo get_lang('Not allowed');
    }
}

// footer
include($includePath."/claro_init_footer.inc.php");
?>
