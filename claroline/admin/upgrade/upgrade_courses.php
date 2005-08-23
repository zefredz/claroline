<?php // $Id$
/**
 * CLAROLINE 
 * 
 * This script Upgrade course database and course space.
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 * @package UPGRADE
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

/*=====================================================================
  Init Section
 =====================================================================*/ 

// Initialise Claroline
require '../../inc/claro_init_global.inc.php';

// Security Check
if (!$is_platformAdmin) claro_disp_auth_form();

// Include Libraries
include ('upgrade.lib.php');
include ('upgrade_course_16.lib.php');
include ('upgrade_course_17.lib.php');

// Initialise Upgrade
upgrade_init_global();

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course = $tbl_mdb_names['course'];
$tbl_rel_course_user   = $tbl_mdb_names['rel_course_user'];
$tbl_course_tool       = $tbl_mdb_names['tool'];

/**
 * Displays flags
 * Using __LINE__ to have an arbitrary value
 */
DEFINE ('DISPLAY_WELCOME_PANEL', __LINE__ );
DEFINE ('DISPLAY_RESULT_PANEL', __LINE__);

/*=====================================================================
  Statements Section
 =====================================================================*/

if ( isset($_REQUEST['verbose']) ) $verbose = (bool) $_REQUEST['verbose'];
else                               $verbose = FALSE;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = FALSE;

if ( isset($_REQUEST['forceUpgrade']) ) $forceUpgrade = $_REQUEST['forceUpgrade'];
else                           $forceUpgrade = FALSE;

$upgradeCoursesError = isset($_REQUEST['upgradeCoursesError']) 
                     ? $_REQUEST['upgradeCoursesError']
                     : FALSE;

if ( $cmd == 'run')
{
    $display = DISPLAY_RESULT_PANEL;
}
else 
{
    $display = DISPLAY_WELCOME_PANEL;
}

// Get start time
$mtime = microtime();$mtime = explode(' ',$mtime);$mtime = $mtime[1] + $mtime[0];$starttime = $mtime;$steptime =$starttime;

// force upgrade for debug
if ( $forceUpgrade ) $newDbVersion = md5(uniqid('')); // for debug

$count_error_total = 0;

$count_course_upgraded = count_course_upgraded($newDbVersion, $newClarolineVersion);

$count_course = $count_course_upgraded['total'];
$count_course_error = $count_course_upgraded['error'];
$count_course_upgraded = $count_course_upgraded['upgraded'];

$count_course_upgraded_at_start =  $count_course_upgraded;

/*=====================================================================
  Main Section
 =====================================================================*/

/*---------------------------------------------------------------------
  Steps of Display 
 ---------------------------------------------------------------------*/

// auto refresh

if ( $display == DISPLAY_RESULT_PANEL && ($count_course_upgraded + $count_course_error ) < $count_course )
{
    $refresh_time = 20;
    $htmlHeadXtra[] = '<meta http-equiv="refresh" content="'. $refresh_time  .'" />'."\n";
}

// Display Header
echo upgrade_disp_header();

/*---------------------------------------------------------------------
  Main
 ---------------------------------------------------------------------*/

switch ($display)
{
    case DISPLAY_WELCOME_PANEL :

        echo  sprintf('<h2>%s</h2>',$langUpgradeStep3)
            . '<p>' . $langIntroStep3 . '</p>' 
            . sprintf($langNbCoursesUpgraded, $count_course_upgraded, $count_course)
            . '<center>' 
            . sprintf ($langLaunchStep3, $_SERVER['PHP_SELF'].'?cmd=run') 
            . '</center>';
        break;
                
    case DISPLAY_RESULT_PANEL : 

        printf("<h2>%s</h2>". '<p>' . $langIntroStep3Run . '</p>',$langUpgradeStep3); 

        // display course upgraded

        echo sprintf($langNbCoursesUpgraded,$count_course_upgraded,$count_course);
        flush();
                
        /*
         * display refresh bloc
         */

        echo  '<div class="help" id="refreshIfBlock">'
            . '<p>' 
            . $langAFewSecondsAfterTheLoadOfPageUpgradeToolWillAutomaticallyContinueItsJobIfItDoesntClickOnTheButtonBelow
            . '</p>'
            . '<p style="text-align: center">'
            . sprintf ("<button onclick=\"document.location='%s';\">".$lang_continueCoursesDataUpgrade."</button>", $_SERVER['PHP_SELF']."?cmd=run")
            . '</p>'
            . '<p><small>'. $lang_seeInTheStatusBarOfYourBrowser .'</small></p>'
            . '</div>'; 

        flush();

        /*
         * Build query to select course to upgrade
         */

        $sql_course_to_upgrade = " SELECT c.dbName dbName, 
                                          c.code sysCode, 
                                          c.fake_code officialCode, 
                                          c.directory coursePath,
                                          c.creationDate,
                                          c.versionDb,
                                          c.versionClaro "
                               . " FROM `" . $tbl_course . "` `c` ";
        
        if ( isset($_REQUEST['upgradeCoursesError']) )
        {
            // retry to upgrade course where upgrade failed
            $sql_course_to_upgrade .= " WHERE c.versionDb != '". $newDbVersion ."'
                                        or c.versionClaro != '". $newClarolineVersion."'
                                        ORDER BY c.dbName";
        }
        else
        {
            // not upgrade course where upgrade failed ( versionDb == error)
            $sql_course_to_upgrade .= " WHERE ( c.versionDb != '". $newDbVersion ."' 
                                                or  c.versionClaro != '". $newClarolineVersion."' )
                                              and c.versionDb != 'error' 
                                              and c.versionClaro != 'error' 
                                        ORDER BY c.dbName ";
        }
        
        $res_course_to_upgrade = mysql_query($sql_course_to_upgrade);
        
        /*
         * Upgrade course
         */

        while ( $course = mysql_fetch_array($res_course_to_upgrade) )
        {   
            // initialise variables

            $currentCourseDbName    = $course['dbName'];
            $currentcoursePathSys   = $coursesRepositorySys.$course['coursePath'].'/';
            $currentcoursePathWeb   = $coursesRepositoryWeb.$course['coursePath'].'/';
            $currentCourseIDsys     = $course['sysCode'];
            $currentCourseCode      = $course['officialCode'];
            $currentCourseCreationDate = $course['creationDate'];
            $currentCourseDbVersion = $course['versionDb'];
            $currentCourseClarolineVersion = $course['versionClaro'];
            $currentCourseDbNameGlu = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries

            $count_course_upgraded++;
            $db_error_counter = 0;
            $fs_error_counter = 0;

            $error = false;
            $errorMsgs ='';
            
            printf($lang_p_UpgradingDatabaseOfCourse, 
            $count_course_upgraded, $currentCourseCode, $currentCourseDbName, $currentCourseIDsys);
            
            /**
             * Make some check.
             * For next versions these test would be set in separate process and available out of upgrade
             */

            // repair tables
            sql_repair_course_database($currentCourseDbNameGlu);
                
            // course repository doesn't exists

            if ( !file_exists($currentcoursePathSys) )
            {            
                $error = true;
                $count_error_total++;

                $errorMsgs .= '<p class="help">'.sprintf($lang_CourseHasNoRepository_s_NotFound , $currentcoursePathSys).'</p>' . "\n";
                $errorMsgs .= '<p class="comment">'.$lang_upgradeToolCannotUpgradeThisCourse.'</p>';
            }

            if ( ! $error ) 
            {
                /*---------------------------------------------------------------------
                  Upgrade 1.6 to 1.7
                 ---------------------------------------------------------------------*/                

                if ( preg_match('/^1.5/',$currentCourseDbVersion) )
                {
                    // function to upgrade tool to 1.6

                    assignment_upgrade_to_16();
                    forum_upgrade_to_16();
                    group_upgrade_to_16();
                    quizz_upgrade_to_16();
                    tracking_upgrade_to_16();

                    if ( $db_error_counter > 0 )
                    {
                        $error = true;
                        $errorMsgs .= '<p class="error"><strong>' . $db_error_counter . ' errors found</strong></p>';
                        $count_error_total += $db_error_counter;
                        $currentCourseDbVersion = 'error-1.5';
                    }
                    else
                    {
                        $currentCourseDbVersion = '1.6';
                    }

                    if ( $fs_error_counter > 0 )
                    {
                        $error = true;
                        $errorMsgs .= '<p class="error"><strong>' . $fs_error_counter . ' errors found</strong></p>';
                        $count_error_total += $fs_error_counter;
                        $currentCourseClarolineVersion = 'error-1.5';
                    }
                    else
                    {
                        $currentCourseClarolineVersion = '1.6';
                    }

                    save_course_current_version($currentCourseIDsys,$currentCourseClarolineVersion,$currentCourseDbVersion);
    
                }
                
                /*---------------------------------------------------------------------
                  Upgrade 1.6 to 1.7
                 ---------------------------------------------------------------------*/                

                if ( preg_match('/^1.6/',$currentCourseDbVersion) )
                {
                    // function to upgrade tool to 1.7
                    agenda_upgrade_to_17();
                    announcement_upgrade_to_17();
                    course_despcription_upgrade_to_17();
                    forum_upgrade_to_17();
                    introtext_upgrade_to_17();
                    linker_upgrade_to_17();
                    tracking_upgrade_to_17();
                    wiki_upgrade_to_17();
                    
                    if ( $db_error_counter > 0 )
                    {
                        $error = true;
                        $errorMsgs .= '<p class="error"><strong>' . $db_error_counter . ' errors found</strong></p>';
                        $count_error_total += $db_error_counter;
                        $currentCourseDbVersion = 'error-1.6';
                    }
                    else
                    {
                        $currentCourseDbVersion = '1.7';
                    }

                    if ( $fs_error_counter > 0 )
                    {
                        $error = true;
                        $errorMsgs .= '<p class="error"><strong>' . $fs_error_counter . ' errors found</strong></p>';
                        $count_error_total += $fs_error_counter;
                        $currentCourseClarolineVersion = 'error-1.6';
                    }
                    else
                    {
                        $currentCourseClarolineVersion = '1.7';
                    }

                    save_course_current_version($currentCourseIDsys,$currentCourseClarolineVersion,$currentCourseDbVersion);
                
                }

            }

            $mtime = microtime(); $mtime = explode(' ',$mtime);    $mtime = $mtime[1] + $mtime[0]; $endtime = $mtime;
            $totaltime = ($endtime - $starttime);
            $stepDuration = ($endtime - $steptime);
            $steptime = $endtime;
            $stepDurationAvg = $totaltime / ($count_course_upgraded-$count_course_upgraded_at_start);
            $leftCourses = (int) ($count_course-$count_course_upgraded);
            $leftTime = strftime('%H:%M:%S',$leftCourses * $stepDurationAvg);
            
            $str_execution_time = sprintf( $lang_p_expectedRemainingTime
                                          ,$stepDuration
                                          ,$stepDurationAvg
                                          ,strftime('%H:%M:%S',$totaltime)
                                          ,$leftCourses
                                          ,$leftTime
                                         );
            
            if ( ! $error )
            {
                echo '<p class="success">'.$langUpgradeCourseSucceed.' - ' . $str_execution_time . '</p>';
            }
            else 
            {
                echo '<p class="error">'.$langUpgradeCourseFailed.' - ' . $str_execution_time . '</p>';
            }
            
            echo $errorMsgs;
            unset($errorMsgs);
            echo '<hr noshade="noshade" />';           
            flush();

        } // end of course upgrade
                
        $mtime = microtime(); $mtime = explode(" ",$mtime);    $mtime = $mtime[1] + $mtime[0];    $endtime = $mtime; $totaltime = ($endtime - $starttime);
        
        if ( $count_error_total > 0 )
        {
//            echo '<p class="error">' . sprintf($lang_p_d_coursesNotUpgraded,  $count_course_error);
            echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?verbose=true&cmd=run&upgradeCoursesError=1">'.$lang_RetryWithMoreDetails.'</a></p>';
        }
        else
        {
            echo '<p class="success">'.$lang_theClarolineUpgradeToolHasSuccessfulllyUpgradeAllYourPlatformCourses.'</p>' . "\n";
            echo '<div align="right">' . sprintf($langNextStep,"upgrade.php") . '</div>';
        }

        /*
         * display block with list of course where upgrade failed
         * add a link to retry upgrade of this course
         */

        $sql = "SELECT code 
                FROM `" . $tbl_course . "` 
                WHERE versionDb = 'error' or versionClaro = 'error' ";

        $result = claro_sql_query($sql);

        if (mysql_num_rows($result))
        {
            echo '<p  class="error">' . $lang_UpgradeFailedForCourses . ' ';
            while ($course = mysql_fetch_array($result))
            {
                echo $course['code'] . ' ; ';    
            }
            echo  '</p>' 
                . '<p class="comment">'
                . sprintf($lang_p_YouCan_url_retryToUpgradeTheseCourse, $_SERVER['PHP_SELF'] . '?cmd=run&upgradeCoursesError=1')
                . '</p>';
            
        }
            
        mysql_close();

        /*
         * Hide Refresh Block
         */
                       
        echo "<script type=\"text/javascript\">\n";
        echo "document.getElementById('refreshIfBlock').style.visibility = \"hidden\"";
        echo "</script>";
                
        break;

} // end of switch display

/*---------------------------------------------------------------------
  Display Footer
 ---------------------------------------------------------------------*/

// Display footer
echo upgrade_disp_footer();

?>
