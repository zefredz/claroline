<?php // $Id: userLog.php 9858 2008-03-11 07:49:45Z gregk84 $
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision: 9858 $
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 *
 * @package CLSTAT
 */

/*
 * Kernel
 */
require_once dirname( __FILE__ ) . '../../inc/claro_init_global.inc.php';



/*
 * Permissions
 */
if( ! get_conf('is_trackingEnabled') )  
if( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
if( ! claro_is_course_manager() ) claro_die(get_lang('Not allowed'));

/*
 * Libraries
 */
require_once dirname( __FILE__ ) . '/lib/trackingRenderer.class.php';
require_once dirname( __FILE__ ) . '/lib/trackingRendererRegistry.class.php';
/*
 * Init some other vars
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_course_tracking_event = $tbl_cdb_names['tracking_event'];


/*
 * Output
 */
$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'tracking', 'screen');

// initialize output
$claroline->setDisplayType( CL_PAGE );

$nameTools = get_lang('Statistics');

$html = '';

$html .= claro_html_tool_title(
                array(
                    'mainTitle' => $nameTools,
                    'subTitle'  => get_lang('Statistics of course : %courseCode', array('%courseCode' => claro_get_current_course_data('officialCode')))
                )
            );

            
/*
 * Users access to course
 */

$header = get_lang('Users access to course');
$content = '';
$footer = '';

$content .= '<ul>' . "\n";

//-- Total access
$sql = "SELECT count(*)
          FROM `".$tbl_course_tracking_event."`
         WHERE `type` = 'course_access'";
$count = claro_sql_query_get_single_value($sql);
$content .= '<li>' . get_lang('Total').' : '.$count.'</li>'."\n";

// last 31 days
$sql = "SELECT count(*)
          FROM `".$tbl_course_tracking_event."`
         WHERE `type` = 'course_access'
           AND `date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY)";
$count = claro_sql_query_get_single_value($sql);
$content .= '<li>' . get_lang('Last 31 days').' : '.$count.'</li>'."\n";

// last 7 days
$sql = "SELECT count(*)
          FROM `".$tbl_course_tracking_event."`
         WHERE `type` = 'course_access'
           AND `date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY)";
$count = claro_sql_query_get_single_value($sql);
$content .= '<li>' . get_lang('Last 7 days').' : '.$count.'</li>'."\n";

// today
$sql = "SELECT count(*)
          FROM `".$tbl_course_tracking_event."`
         WHERE `type` = 'course_access'
           AND `date` > CURDATE()";
$count = claro_sql_query_get_single_value($sql);
$content .= '<li>' . get_lang('Today').' : '.$count.'</li>'."\n";

//-- students not connected for more than 1 month
$sql = "SELECT  U.`user_id`, U.`nom` AS `lastname`, U.`prenom` AS `firstname`, MAX(CTE.`date`) AS `last_access_date`
    FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."` AS CU
    LEFT JOIN `".$tbl_course_tracking_event."` AS `CTE`
    ON `CTE`.`user_id` = CU.`user_id`
    WHERE U.`user_id` = CU.`user_id`
    AND CU.`code_cours` = '" . addslashes(claro_get_current_course_id()) . "'
    GROUP BY U.`user_id`
    HAVING  `last_access_date` IS NULL
        OR  `last_access_date` < ( NOW() - INTERVAL 15 DAY )
    ";
$content .= '<li>' . get_lang('Not recently connected students :');

$results = claro_sql_query_fetch_all($sql);
if( !empty($results) && is_array($results) )
{
    $content .= '<ul>'."\n";
    foreach( $results as $result )
    {
        $content .= '<li>'
        .   '<a href="../user/userInfo.php?uInfo='.$result['user_id'].'">'
        .   $result['firstname'].' '.$result['lastname']
        .   '</a> ';
        
        if( is_null($result['last_access_date']) )
        {
            $content .= '( <b>'.get_lang('Never connected').'</b> )';
        }
        else
        {
            $content .= '( '.get_lang('Last access').' : '.$result['last_access_date'].' )';
        }
        
        $content .= '</li>'."\n";
    }
    $content .= '</ul>' . "\n";
}
else
{
    $content .= ' <small>'.get_lang('No result').'</small><br />'."\n";
}
$content .= '</li>' . "\n";

$content .= '<li><a href="course_access_details.php">'.get_lang('Traffic Details').'</a></li>';

$html .= renderStatBlock($header, $content, $footer);


/*
 * Users access to tools
 */

$header = get_lang('Users access to tools');
$content = '';
$footer = '';

$sql = "SELECT `tool_id`,
        COUNT(DISTINCT `user_id`) AS `nbr_distinct_users_access`,
        COUNT( `tool_id` )            AS `nbr_access`
            FROM `" . $tbl_course_tracking_event . "`
            WHERE `type` = 'tool_access'
              AND `tool_id` IS NOT NULL
              AND `tool_id` <> ''
            GROUP BY `tool_id`";

$results = claro_sql_query_fetch_all($sql);
$content .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
.   '<tr class="headerX">'."\n"
.   '<th>&nbsp;'.get_lang('Name of the tool').'&nbsp;</th>'."\n"
.   '<th>&nbsp;'.get_lang('Users\' Clicks').'&nbsp;</th>'."\n"
.   '<th>&nbsp;'.get_lang('Total Clicks').'&nbsp;</th>'."\n"
.   '</tr>'."\n"
.   '<tbody>'."\n"
;

if( !empty($results) && is_array($results))
{
    foreach( $results as $result )
    {
        $thisTid = (int) $result['tool_id'];

        $thisToolName = claro_get_tool_name($thisTid);
        
        $content .= '<tr>' . "\n"
        .    '<td>'
        .    '<a href="toolaccess_details.php?toolId='.$thisTid.'">'
        .    $thisToolName . '</a></td>' . "\n"
        .    '<td align="right"><a href="user_access_details.php?cmd=tool&amp;id='.$thisTid.'">'.(int) $result['nbr_distinct_users_access'] . '</a></td>' . "\n"
        .    '<td align="right">' . (int) $result['nbr_access'] . '</td>' . "\n"
        .    '</tr>'
        .    "\n\n"
        ;
    }

}
else
{
    $content .= '<tr>'."\n"
    .    '<td colspan="3"><div align="center">'.get_lang('No result').'</div></td>'."\n"
    .    '</tr>'."\n"
    ;
}
$content .= '</tbody>'
.    '</table>'."\n"
;
        
$html .= renderStatBlock($header, $content, $footer);

/*
 * Loop through modules to find displayable tracking
 */
// get all renderers by using registry
$trackingRendererRegistry = TrackingRendererRegistry::getInstance();

// here we need course tracking renderers
$courseTrackingRendererList = $trackingRendererRegistry->getCourseRendererList();

foreach( $courseTrackingRendererList as $ctr )
{
    $renderer = new $ctr();
    $html .= $renderer->render();
}





/*
 * Output rendering
 */
$claroline->display->body->setContent($html);

echo $claroline->display->render();







function renderStatBlock($header,$content,$footer)
{
	$html = '<div class="statBlock">' . "\n"
	.	 ' <div class="blockHeader">' . "\n"
	.	 $header
	.	 ' </div>' . "\n"
	.	 ' <div class="blockContent">' . "\n"
	.	 $content
	.	 ' </div>' . "\n"
	.	 ' <div class="blockFooter">' . "\n"
	.	 $footer
	.	 ' </div>' . "\n"
	.	 '</div>' . "\n";

	return $html;
}
?>