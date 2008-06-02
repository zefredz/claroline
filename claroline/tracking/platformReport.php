<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 *
 * @package CLTRACK
 */

/*
 * Kernel
 */
require_once dirname( __FILE__ ) . '../../inc/claro_init_global.inc.php';



/*
 * Permissions
 */
if( ! get_conf('is_trackingEnabled') ) claro_die(get_lang('Tracking has been disabled by system administrator.'));

if( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if( ! claro_is_platform_admin() ) claro_die( get_lang('Not allowed') );

/*
 * Libraries
 */
uses( 'user.lib', 'courselist.lib' );

// todo move this lib in tracking/lib
require_once get_path('incRepositorySys') . '/lib/statsUtils.lib.inc.php';


// regroup table names for maintenance purpose
/*
 * DB tables definition
 */

$tbl_mdb_names       = claro_sql_get_main_tbl();
$tbl_cdb_names       = claro_sql_get_course_tbl();
$tbl_course          = $tbl_mdb_names['course'           ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_user            = $tbl_mdb_names['user'             ];
$tbl_track_e_default = $tbl_mdb_names['track_e_default'];
$tbl_track_e_login   = $tbl_mdb_names['track_e_login'];
$tbl_track_e_open    = $tbl_mdb_names['track_e_open'];

$tbl_document        = $tbl_cdb_names['document'];

$toolNameList = claro_get_tool_name_list();


// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";


/*
 * Output
 */
$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'tracking', 'screen');

$claroline->setDisplayType( CL_PAGE );

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools = get_lang('Platform statistics');

$html = '';    

$html .= claro_html_tool_title( $nameTools );


/*
 * Platform access and logins
 */

$header = get_lang('Access');

$content = '';

//--  all
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_open."`";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Total').' : '.$count.'<br />'."\n";

//--  last 31 days
$sql = "SELECT count(*)
          FROM `" . $tbl_track_e_open . "`
         WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Last 31 days').' : '.$count.'<br />'."\n";

//--  last 7 days
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_open."`
            WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Last 7 days').' : '.$count.'<br />'."\n";

//--  yesterday
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_open."`
            WHERE (`open_date` > DATE_ADD(CURDATE(), INTERVAL -1 DAY))
              AND (`open_date` < CURDATE() )";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Yesterday').' : '.$count.'<br />'."\n";

//--  today
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_open."`
            WHERE (`open_date` > CURDATE() )";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('This day').' : '.$count.'<br />'."\n";

$footer = '<a href="platform_access_details.php">'.get_lang('Traffic Details').'</a>';

$html .= renderStatBlock( $header, $content, $footer);

//----------------------------  logins
$header = get_lang('Logins');

$content = '';

//--  all
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_login."`";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Total').' : '.$count.'<br />'."\n";

//--  last 31 days
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_login."`
            WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -31 DAY))";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Last 31 days').' : '.$count.'<br />'."\n";

//--  last 7 days
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_login."`
            WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -7 DAY))";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Last 7 days').' : '.$count.'<br />'."\n";

//--  yesterday
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_login."`
            WHERE (`login_date` > DATE_ADD(CURDATE(), INTERVAL -1 DAY))
              AND (`login_date` < CURDATE() )";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Yesterday').' : '.$count.'<br />'."\n";

//--  today
$sql = "SELECT count(*)
            FROM `".$tbl_track_e_login."`
            WHERE (`login_date` > CURDATE() )";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('This day').' : '.$count.'<br />'."\n";

$footer = '';

$html .= renderStatBlock( $header, $content, $footer);

    /***************************************************************************
     *
     *        Main
     *
     ***************************************************************************/

$header = get_lang('Courses');

$content = '';

//--  number of courses
$sql = "SELECT count(*)
          FROM `" . $tbl_course . "`";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;' . get_lang('Number of courses') . ' : ' . $count.'<br />'."\n";

//--  number of courses by faculte
$sql = "SELECT `faculte`, count( * ) AS `nbr`
          FROM `" . $tbl_course . "`
         WHERE `faculte` IS NOT NULL
         GROUP BY `faculte`";
$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'
.    get_lang('Number of courses by faculty')
.    ' : '
.    '<br />' . "\n"
;
$content .= buildTab2Col($results);
$content .= '<br />'."\n";

//--  number of courses by language
$sql = "SELECT `language`, count( * ) AS `nbr`
          FROM `" . $tbl_course . "`
         WHERE `language` IS NOT NULL
         GROUP BY `language`";

$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by language').' : <br />'."\n";
$content .= buildTab2Col($results);
$content .= '<br />'."\n";
//--  number of courses by access
$sql = "SELECT `access`, count( * ) AS `nbr`
            FROM `" . $tbl_course . "`
            WHERE `access` IS NOT NULL
            GROUP BY `access`";

$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by access').' : <br />'."\n";
$content .= buildTab2Col($results);
$content .= '<br />'."\n";

//--  number of courses by registration
$sql = "SELECT `registration`, count( * ) AS `nbr`
            FROM `" . $tbl_course . "`
            WHERE `registration` IS NOT NULL
            GROUP BY `registration`";

$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by enrollment').' : <br />'."\n";
$content .= buildTab2Col($results);
$content .= '<br />'."\n";

//--  number of courses by visibility
$sql = "SELECT `visibility`, count( * ) AS `nbr`
            FROM `" . $tbl_course . "`
            WHERE `visibility` IS NOT NULL
            GROUP BY `visibility`";

$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of courses by visibility').' : <br />'."\n";
$content .= buildTab2Col($results);
$content .= '<br />'."\n";

$footer = '';

$html .= renderStatBlock( $header, $content, $footer);

//-- USERS
$header = get_lang('Users');

$content = '';
//--  total number of users
$sql = "SELECT count(*)
            FROM `".$tbl_user."`";
$count = claro_sql_query_get_single_value($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users').' : '.$count.'<br />'."\n";

//--  number of users by course
$sql = "SELECT C.`code`, count( CU.user_id ) as `nb`
            FROM `" . $tbl_course . "` C, `" . $tbl_rel_course_user . "` CU
            WHERE CU.`code_cours` = C.`code`
                AND `code` IS NOT NULL
            GROUP BY C.`code`
            ORDER BY nb DESC";
$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;' . get_lang('Number of users by course') . ' : <br />'."\n";
$content .= buildTab2Col($results);
$content .= '<br />'."\n";

//--  number of users by faculte
$sql = "SELECT C.`faculte`, count( CU.`user_id` ) AS `nbr`
            FROM `" . $tbl_course . "` C, `" . $tbl_rel_course_user . "` CU
            WHERE CU.`code_cours` = C.`code`
                AND C.`faculte` IS NOT NULL
            GROUP BY C.`faculte`";
$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users by faculty').' : <br />'."\n";
$content .= buildTab2Col($results);
$content .= '<br />'."\n";

//--  number of users by status
$sql = "SELECT `isCourseCreator`, count( `user_id` ) AS `nbr`
            FROM `".$tbl_user."`
            WHERE `isCourseCreator` IS NOT NULL
            GROUP BY `isCourseCreator`";
$results = claro_sql_query_fetch_all($sql);
$content .= '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users by status').' : <br />'."\n";
$content .= buildTab2Col($results);

$footer = '';

$html .= renderStatBlock( $header, $content, $footer);
 

/*
 * Access to tools
 * // due to the moving of access tables in course DB this part of the code exec (nbCourser+1) queries
 * // this can create heavy overload on servers ... should be reconsidered
 *
 */
/*
$header = get_lang('`Tools');

$content = '';

// display list of course of the student with links to the corresponding userLog
$sql = "SELECT code, dbName
      FROM    `" . $tbl_course . "`
      ORDER BY code ASC";

$resCourseList = claro_sql_query_fetch_all($sql);
$resultsTools=array();
foreach ( $resCourseList as $course )
{
    // TODO use a archive page that get's stats of all course and resume everything in a single table
    // TODO : use claro_sql_get_course_tbl_name
    $TABLEACCESSCOURSE = get_conf('courseTablePrefix') . $course['dbName'] . get_conf('dbGlu') . "track_e_access";
    $sql = "SELECT count( `access_id` ) AS nb, `access_tlabel`
            FROM `".$TABLEACCESSCOURSE."`
            WHERE `access_tid` IS NOT NULL
            GROUP BY `access_tid`";

    $access = claro_sql_query_fetch_all($sql);

    // look for each tool of the course in re
    foreach( $access as $count )
    {
        if ( !isset($resultsTools[$count['access_tlabel']]) )
        {
            $resultsTools[$count['access_tlabel']] = $count['nb'];
        }
        else
        {
            $resultsTools[$count['access_tlabel']] += $count['nb'];
        }
    }
}

$content .= '<table cellpadding="2" cellspacing="1" class="claroTable" align="center">'
 . '<thead>'
 . '<tr class="headerX">'."\n"
 . '<th>&nbsp;'.get_lang('Name of the tool').'</th>'."\n"
 . '<th>&nbsp;'.get_lang('Total Clicks').'</th>'."\n"
 . '</tr>'
 . '</thead>'."\n"
 . '<tbody>'."\n"
 ;

if (is_array($resultsTools))
{
  arsort($resultsTools);
  foreach( $resultsTools as $tool => $nbr)
  {
      $content .= '<tr>' . "\n"
         . '<td>' . $toolNameList[$tool].'</td>'."\n"
         . '<td>' . $nbr.'</td>'."\n"
         . '</tr>' . "\n\n"
         ;
  }
}
else
{
  $content .= '<tr>'."\n"
     . '<td colspan="2"><center>'.get_lang('No result').'</center></td>'."\n"
     . '</tr>'."\n"
     ;
}

$content .= '</tbody>'."\n"
 . '</table>'."\n\n"
 ;

$footer = '';

$html .= renderStatBlock( $header, $content, $footer);

*/

/*
 * Output rendering
 */
$claroline->display->body->setContent($html);

echo $claroline->display->render();
?>