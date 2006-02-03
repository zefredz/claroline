<?php // $Id$
/**
 * CLAROLINE
 * This tool run some check to detect abnormal situation
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/ADMIN
 * @author Sébastien Piraux <pir@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 *
 */

/**
 * This script is a set of independant tests on the data
 *
 * Theses tests check if data are logical.
 *
 * This script use Cache_lite
 *
 * @todo TODO : separate checking and output.
 * @todo TODO : protect "showall" when there is nothing in cache.
 *
 */

define('DISP_RESULT',__LINE__);
define('DISP_NOT_ALLOWED',__LINE__);


require '../inc/claro_init_global.inc.php';

include_once $includePath . '/lib/statsUtils.lib.inc.php';
include_once $includePath . '/lib/pear/Lite.php';


// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

// right
$is_allowedToCheckProblems = $is_platformAdmin;


// Cache_lite setting & init
$cache_options = array(
'cacheDir' => get_conf('rootSys') . 'cache/campusProblem/',
'lifeTime' => get_conf('cache_lifeTime',3600*48),
'automaticCleaningFactor' => 50,
);
if (get_conf('CLARO_DEBUG_MODE',false) ) $cache_options['pearErrorMode'] = CACHE_LITE_ERROR_DIE;
if (! file_exists($cache_options['cacheDir']) )
{
    include_once $includePath . '/lib/fileManage.lib.php';
    claro_mkdir($cache_options['cacheDir'],CLARO_FILE_PERMISSIONS,true);
}
$Cache_Lite = new Cache_Lite($cache_options);

/**
 * DB tables definition
 */

$tbl_mdb_names       = claro_sql_get_main_tbl();
$tbl_cdb_names       = claro_sql_get_course_tbl();
$tbl_course          = $tbl_mdb_names['course'];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
$tbl_user            = $tbl_mdb_names['user'];
$tbl_track_e_login   = $tbl_mdb_names['track_e_login'];
$tbl_document        = $tbl_cdb_names['document'];
$toolNameList = claro_get_tool_name_list();




// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";


// Prepare output
$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Administration'));

$nameTools = get_lang('Scan technical fault');

$htmlHeadXtra[] = "
<style media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</style>";



if( $is_allowedToCheckProblems)
{
    $display = DISP_RESULT;
}
else
{
    $display = DISP_NOT_ALLOWED;
}

////////////// OUTPUT ///////////////

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title(
array(
'mainTitle'=>$nameTools    )
);

switch ($display)
{
    case DISP_NOT_ALLOWED :
    {
        echo claro_html::message_box(get_lang('Not allowed'));
    } break;

    case DISP_RESULT :
    {
        // in $view, a 1 in X posof the $view string means that the 'category' number X
        // will be show, 0 means don't show
        echo '<small>'
        .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=1111111">' . get_lang('ShowAll') . '</a>]'
        .    '&nbsp;'
        .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=0000000">' . get_lang('ShowNone') . '</a>]'
        .    '</small>' . "\n\n"
        ;

        if( isset($_REQUEST['view'])) $view = $_REQUEST['view'];
        else                          $view = "0000000";

        $levelView=-1;

        /***************************************************************************
        *        Main
        ***************************************************************************/
        $tempView = $view;
        $levelView++;
        echo '<p>' . "\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql = "SELECT DISTINCT username , count(*) as nb
                        FROM `" . $tbl_user . "`
                        GROUP BY username
                        HAVING nb > 1
                        ORDER BY nb DESC";
                $data = claro_sql_query_fetch_all($sql);
                if (!is_array($data) || sizeof($data)==0) $data[] = array( '-','-');
                $option['colTitleList'] = array(get_lang('username'),get_lang('count'));
                $datagrid[$levelView] = claro_disp_datagrid($data,$option);
                $Cache_Lite->save($datagrid[$levelView],$levelView);
            }
            echo '-'
            .    ' &nbsp;&nbsp;'
            .    '<b>'
            .    get_lang('MultipleLogins')
            .    '</b>'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<small>'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Close')
            .    '</a>]'
            .    '</small>'
            .    '<br />' . "\n"
            .    $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br />' . "\n"
            ;
        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('MultipleLogins')
            .    '</a>' . "\n"
            ;
        }
        echo '</p>' . "\n\n";

        /***************************************************************************
        *        Platform access and logins
        ***************************************************************************/
        $tempView = $view;
        $levelView++;
        echo '<p>' . "\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            echo '- '
            .    '&nbsp;&nbsp;'
            .    '<b>'
            .    get_lang('MultipleEmails')
            .    '</b>'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<small>'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .     get_lang('Close')
            .    '</a>]'
            .    '</small>'
            .    '<br />' . "\n"
            ;
            //--  multiple account with same email

            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql = "SELECT DISTINCT             email ,
                                        count(*) AS nb
                        FROM `" . $tbl_user . "`
                        GROUP BY email
                        HAVING nb > 1
                        ORDER BY nb DESC";
                $option['colTitleList'] = array(get_lang('email'), get_lang('count'));
                $data = claro_sql_query_fetch_all($sql);
                if (!is_array($data) || sizeof($data)==0) $data[] = array( '-', '-');
                $datagrid[$levelView] = claro_disp_datagrid($data, $option);
                $Cache_Lite->save($datagrid[$levelView], $levelView);
            }

            echo $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br>'
            ;
        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('MultipleEmails')
            .    '</a>'
            ;
        }
        echo '</p>' . "\n";


        $tempView = $view;
        $levelView++;
        echo "<p>\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            //--  courses without professor
            echo '- '
            .    '&nbsp;&nbsp;'
            .    '<b>'
            .    get_lang('Courses without a lecturer')
            .    '</b>'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<small>'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Close')
            .    '</a>]'
            .    '</small>'
            .    '<br />' . "\n"
            ;

            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql = "SELECT CONCAT(c.code,' (<a href=\"admincourseusers.php?cidToEdit=',c.code,'\">',c.fake_code,'</a>)'), count( cu.user_id ) nbu
                    FROM `" . $tbl_course . "` c
                    LEFT JOIN `" . $tbl_rel_course_user . "` cu
                        ON c.code = cu.code_cours
                        AND cu.statut = 1
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

                $data = claro_sql_query_fetch_all($sql);
                if (!is_array($data) || sizeof($data)==0)
                $data[] = array( '-','-');
                $option['colTitleList'] = array(get_lang('code'),get_lang('count'));
                $datagrid[$levelView] = claro_disp_datagrid($data, $option);
                $Cache_Lite->save($datagrid[$levelView],$levelView);
            }

            echo $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br>'
            ;
        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Courses without a lecturer')
            .    '</a>'
            ;
        }
        echo '</p>' . "\n\n";

        $tempView = $view;
        $levelView++;
        echo '<p>' . "\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            //-- courses without students
            echo '- '
            .    '&nbsp;&nbsp;'
            .    '<b>'
            .    get_lang('Courses without student')
            .    '</b>'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<small>'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Close')
            .    '</a>]'
            .    '</small>'
            .    '<br />' . "\n"
            ;

            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql = "SELECT CONCAT(c.code,' (<a href=\"admincourseusers.php?cidToEdit=',c.code,'\">',c.fake_code,'</a>)'),
                               count( cu.user_id ) AS nbu
                    FROM `" . $tbl_course . "`               AS c
                    LEFT JOIN `" . $tbl_rel_course_user . "` AS cu
                        ON c.code = cu.code_cours
                        AND cu.statut = 5
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";
                $option['colTitleList'] = array('code','count');
                $data = claro_sql_query_fetch_all($sql);
                if (!is_array($data) || sizeof($data)==0)
                $option['colTitleList'] = array(get_lang('code'),get_lang('count'));
                $datagrid[$levelView] = claro_disp_datagrid($data,$option);
                $Cache_Lite->save($datagrid[$levelView],$levelView);
            }

            echo $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br>'
            ;
        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Courses without student')
            .    '</a>'
            ;
        }
        echo '</p>' . "\n\n";


        $tempView = $view;
        $levelView++;
        echo '<p>' . "\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            //-- logins not used for $limitBeforeUnused
            echo '- '
            .    '&nbsp;&nbsp;'
            .    '<b>'
            .    get_lang('LoginWithoutAccess')
            .    '</b>'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<small>'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Close')
            .    '</a>]'
            .    '</small>'
            .    '<br />' . "\n"
            ;

            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql = "SELECT `us`.`username`,
                               MAX(`lo`.`login_date`)
                    FROM `" . $tbl_user . "`               AS us
                    LEFT JOIN `" . $tbl_track_e_login . "` AS lo
                    ON`lo`.`login_user_id` = `us`.`user_id`
                    GROUP BY `us`.`username`
                    HAVING ( MAX(`lo`.`login_date`) < (NOW() - " . $limitBeforeUnused . " ) ) OR MAX(`lo`.`login_date`) IS NULL";


                $loginWithoutAccessResults = claro_sql_query_fetch_all($sql);
                for($i = 0; $i < sizeof($loginWithoutAccessResults); $i++)
                {
                    if ( !isset($loginWithoutAccessResults[$i][1]) )
                    {
                        $loginWithoutAccessResults[$i][1] = get_lang('NeverUsed');
                    }
                }


                $loginWithoutAccessResults = claro_sql_query_fetch_all($sql);
                if (!is_array($loginWithoutAccessResults) || sizeof($loginWithoutAccessResults)==0)
                $loginWithoutAccessResults[] = array( '-','-');
                $option['colTitleList'] = array(get_lang('username'), get_lang('login_date'));
                $datagrid[$levelView] = claro_disp_datagrid($loginWithoutAccessResults, $option);
                $Cache_Lite->save($datagrid[$levelView], $levelView);
            }

            echo $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br>'
            ;

        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('LoginWithoutAccess')
            .    '</a>'
            ;
        }
        echo '</p>' . "\n\n";

        $tempView = $view;
        $levelView++;
        echo '<p>' . "\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            //--  multiple account with same username AND same password (for compatibility with previous versions)
            echo '- &nbsp;&nbsp;'
            .    '<b>'
            .    get_lang('MultipleUsernameAndPassword')
            .    '</b>'
            .    '&nbsp;&nbsp;&nbsp;<small>'
            .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Close')
            .    '</a>]'
            .    '</small>'
            .    '<br />' . "\n"
            ;

            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql = "SELECT DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb
                        FROM `" . $tbl_user . "`
                        GROUP BY paire
                        HAVING nb > 1
                        ORDER BY nb DESC";
                $data = claro_sql_query_fetch_all($sql);
                if (!is_array($data) || sizeof($data)==0)
                $data[] = array( '-','-');
                $option['colTitleList'] = array(get_lang('paire'),get_lang('count'));
                $datagrid[$levelView] = claro_disp_datagrid($data, $option);
                $Cache_Lite->save($datagrid[$levelView],$levelView);
            }

            echo $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br>'
            ;

        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('MultipleUsernameAndPassword')
            .    '</a>'
            ;
        }
        echo '</p>' . "\n\n";

        $tempView = $view;
        $levelView++;
        echo '<p>' . "\n";
        if($view[$levelView] == '1')
        {
            $tempView[$levelView] = '0';
            //-- courses without access, not used for $limitBeforeUnused
            if (false === $datagrid[$levelView] = $Cache_Lite->get($levelView))
            {
                $sql ="SELECT code, dbName
                   FROM `" . $tbl_course . "`
                   ORDER BY code ASC";
                $resCourseList = claro_sql_query($sql);
                $i = 0;
                while ( $course = mysql_fetch_array($resCourseList) )
                {
                    $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
                    $sql = "SELECT IF( MAX(`access_date`)  < (NOW() - " . $limitBeforeUnused . " ), MAX(`access_date`) , 'recentlyUsedOrNull' )  as lastDate, count(`access_date`) as nbrAccess
                        FROM `" . $TABLEACCESSCOURSE . "`";
                    $coursesNotUsedResult = claro_sql_query($sql);

                    $courseWithoutAccess = array();
                    if( $courseAccess = mysql_fetch_array($coursesNotUsedResult) )
                    {
                        if ( $courseAccess['lastDate'] == 'recentlyUsedOrNull' && $courseAccess['nbrAccess'] != 0 ) continue;
                        $courseWithoutAccess[$i][0] = $course['code'];
                        if ( $courseAccess['lastDate'] == 'recentlyUsedOrNull') // if no records found ,course was never accessed
                        $courseWithoutAccess[$i][1] = get_lang('NeverUsed');
                        else                                                   $courseWithoutAccess[$i][1] = $courseAccess['lastDate'];
                    }

                    $i++;
                }

                $courseWithoutAccess = claro_sql_query_fetch_all($sql);
                if (!is_array($courseWithoutAccess) || sizeof($courseWithoutAccess)==0)
                $courseWithoutAccess[] = array( '-','-');
                $option['colTitleList'] = array(get_lang('code'),get_lang('count'));

                $datagrid[$levelView] = '- '
                .    '&nbsp;&nbsp;'
                .    '<b>'
                .    get_lang('Courses not used')
                .    '</b>'
                .    '&nbsp;&nbsp;&nbsp;'
                .    '<small>'
                .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
                .    get_lang('Close')
                .    '</a>]'
                .    '</small>'
                .    '<br />' . "\n"
                .    claro_disp_datagrid($courseWithoutAccess, $option)
                ;
                $Cache_Lite->save($datagrid[$levelView],$levelView);
            }

            echo $datagrid[$levelView]
            .    '<small>'
            .    get_lang('Last computing')
            .    ' '
            .    claro_disp_localised_date($dateTimeFormatLong.':%S', $Cache_Lite->lastModified())
            .    '</small>'
            .    '<br>'
            ;



        }
        else
        {
            $tempView[$levelView] = '1';
            echo '+'
            .    '&nbsp;&nbsp;&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $tempView . '">'
            .    get_lang('Courses not used')
            .    '</a>'
            ;
        }
        echo '</p>' . "\n\n";
    }

    break;
    default:trigger_error('display (' . $display . ') unknown', E_USER_NOTICE);
}
include $includePath . '/claro_init_footer.inc.php';
?>