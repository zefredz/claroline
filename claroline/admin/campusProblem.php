<?php // $Id$
/**
 * CLAROLINE
 * This tool run some check to detect abnormal situation
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/ADMIN
 *
 * @author S�bastien Piraux <pir@claroline.net>
 * @author Christophe Gesch� <moosh@claroline.net>
 *
 */

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('NotAllowed'));

$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Administration'));

$nameTools = get_lang('ViewPlatFormError');

$htmlHeadXtra[] = "
<style media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</style>";

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

include_once $includePath . '/lib/statsUtils.lib.inc.php';

// used in strange cases, a course is unused if not used since $limitBeforeUnused
// INTERVAL SQL expr. see http://www.mysql.com/doc/en/Date_and_time_functions.html
$limitBeforeUnused = "INTERVAL 6 MONTH";

$is_allowedToTrack     = $is_platformAdmin;

include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title(
array(
'mainTitle'=>$nameTools    )
);

if( $is_allowedToTrack && $is_trackingEnabled)
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo "\n"
    .    '<small>'
    .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=1111111">' . get_lang('ShowAll') . '</a>]'
    .    '&nbsp;'
    .    '[<a href="' . $_SERVER['PHP_SELF'] . '?view=0000000">' . get_lang('ShowNone') . '</a>]'
    .    '</small>' . "\n\n"
    ;

    if( isset($_REQUEST['view']))   $view = $_REQUEST['view'];
    else                            $view = "0000000";

    $levelView=-1;

    /***************************************************************************
    *        Main
    ***************************************************************************/
    $tempView = $view;
    $levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        echo '- &nbsp;&nbsp;<b>'.get_lang('MultipleLogins').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        //--  multiple logins |
        //--     multiple logins are not possible in the new version but this page can be used with previous versions
        $sql = "SELECT DISTINCT username , count(*) as nb
                    FROM `".$tbl_user."`
                    GROUP BY username
                    HAVING nb > 1
                    ORDER BY nb DESC";

        buildTabDefcon(claro_sql_query_fetch_all($sql));


        echo '<br />'."\n";
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('MultipleLogins').'</a>'."\n";
    }
    echo '</p>'."\n\n";

    /***************************************************************************
    *
    *        Platform access and logins
    *
    ***************************************************************************/
    $tempView = $view;
    $levelView++;
    echo '<p>'."\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        echo '- &nbsp;&nbsp;<b>'.get_lang('MultipleEmails').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        //--  multiple account with same email

        $sql = "SELECT DISTINCT email , count(*) as nb
                    FROM `".$tbl_user."`
                    GROUP BY email
                    HAVING nb > 1
                    ORDER BY nb DESC";

        buildTabDefcon(claro_sql_query_fetch_all($sql));


    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('MultipleEmails').'</a>';
    }
    echo '</p>'."\n";


    $tempView = $view;
    $levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //--  courses without professor
        echo '- &nbsp;&nbsp;<b>'.get_lang('CourseWithoutProf').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT CONCAT(c.code,' (<a href=\"admincourseusers.php?cidToEdit=',c.code,'\">',c.fake_code,'</a>)'), count( cu.user_id ) nbu
                    FROM `".$tbl_course."` c
                    LEFT JOIN `".$tbl_rel_course_user."` cu
                        ON c.code = cu.code_cours
                        AND cu.statut = 1
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

        buildTabDefcon(claro_sql_query_fetch_all($sql));

    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('CourseWithoutProf').'</a>';
    }
    echo "</p>\n\n";

    $tempView = $view;
    $levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //-- courses without students
        echo '- &nbsp;&nbsp;<b>'.get_lang('CourseWithoutStudents').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        $sql = "SELECT CONCAT(c.code,' (<a href=\"admincourseusers.php?cidToEdit=',c.code,'\">',c.fake_code,'</a>)'), count( cu.user_id ) nbu
                    FROM `".$tbl_course."` c
                    LEFT JOIN `".$tbl_rel_course_user."` cu
                        ON c.code = cu.code_cours
                        AND cu.statut = 5
                    GROUP BY c.code, statut
                    HAVING nbu = 0
                    ORDER BY code_cours";

        buildTabDefcon(claro_sql_query_fetch_all($sql));

    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('CourseWithoutStudents').'</a>';
    }
    echo '</p>'."\n\n";


    $tempView = $view;
    $levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //-- logins not used for $limitBeforeUnused
        echo '- &nbsp;&nbsp;<b>'.get_lang('LoginWithoutAccess').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT `us`.`username`, MAX(`lo`.`login_date`)
                    FROM `".$tbl_user."` AS us
                    LEFT JOIN `".$tbl_track_e_login."` AS lo
                    ON`lo`.`login_user_id` = `us`.`user_id`
                    GROUP BY `us`.`username`
                    HAVING ( MAX(`lo`.`login_date`) < (NOW() - ".$limitBeforeUnused." ) ) OR MAX(`lo`.`login_date`) IS NULL";


        $loginWithoutAccessResults = claro_sql_query_fetch_all($sql);
        for($i = 0; $i < sizeof($loginWithoutAccessResults); $i++)
        {
            if ( !isset($loginWithoutAccessResults[$i][1]) )
            {
                $loginWithoutAccessResults[$i][1] = get_lang('NeverUsed');
            }
        }
        buildTabDefcon($loginWithoutAccessResults);
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('LoginWithoutAccess').'</a>';
    }
    echo '</p>'."\n\n";


    $tempView = $view;
    $levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //--  multiple account with same username AND same password (for compatibility with previous versions)
        echo '- &nbsp;&nbsp;<b>'.get_lang('MultipleUsernameAndPassword').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb
                    FROM `".$tbl_user."`
                    GROUP BY paire
                    HAVING nb > 1
                    ORDER BY nb DESC";

        buildTabDefcon(claro_sql_query_fetch_all($sql));
    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('MultipleUsernameAndPassword').'</a>';
    }
    echo '</p>'."\n\n";


    $tempView = $view;
    $levelView++;
    echo "<p>\n";
    if($view[$levelView] == '1')
    {
        $tempView[$levelView] = '0';
        //-- courses without access, not used for $limitBeforeUnused
        echo '- &nbsp;&nbsp;<b>'.get_lang('CourseWithoutAccess').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";
        $sql ="SELECT code, dbName
                       FROM    `".$tbl_course."`
                     ORDER BY code ASC";
        $resCourseList = claro_sql_query($sql);
        $i = 0;
        while ( $course = mysql_fetch_array($resCourseList) )
        {
            $TABLEACCESSCOURSE = $courseTablePrefix . $course['dbName'] . $dbGlu . "track_e_access";
            $sql = "SELECT IF( MAX(`access_date`)  < (NOW() - ".$limitBeforeUnused." ), MAX(`access_date`) , 'recentlyUsedOrNull' )  as lastDate, count(`access_date`) as nbrAccess
                        FROM `".$TABLEACCESSCOURSE."`";
            $coursesNotUsedResult = claro_sql_query($sql);

            $courseWithoutAccess = array();
            if( $courseAccess = mysql_fetch_array($coursesNotUsedResult) )
            {
                if ( $courseAccess['lastDate'] == 'recentlyUsedOrNull' && $courseAccess['nbrAccess'] != 0 ) continue;
                $courseWithoutAccess[$i][0] = $course['code'];
                if ( $courseAccess['lastDate'] == 'recentlyUsedOrNull') // if no records found ,course was never accessed
                {
                    $courseWithoutAccess[$i][1] = get_lang('NeverUsed');
                }
                else
                {
                    $courseWithoutAccess[$i][1] = $courseAccess['lastDate'];
                }
            }

            $i++;
        }

        buildTabDefcon($courseWithoutAccess);

    }
    else
    {
        $tempView[$levelView] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('CourseWithoutAccess').'</a>';
    }
    echo "</p>\n\n";
}
else // not allowed to track
{
    if(!$is_trackingEnabled)
    {
        echo get_lang('TrackingDisabled');
    }
    else
    {
        echo get_lang('NotAllowed');
    }
}

include ($includePath . '/claro_init_footer.inc.php');
?>