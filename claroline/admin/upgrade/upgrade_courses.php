<?php // $Id$
/**
 * CLAROLINE 
 * 
 * This script Upgrade course database and course space.
 *
 * @version 1.7 $Revision$
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

// Initialise Claroline
require 'upgrade_init_global.inc.php';

// Include Libraries
include ('upgrade.lib.php');
include ('upgrade_course_16.lib.php');
include ('upgrade_course_17.lib.php');

// Initialise Upgrade
upgrade_init_global();

// Security Check
if (!$is_platformAdmin) upgrade_disp_auth_form();

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

if ( isset($_REQUEST['verbose']) ) $verbose = true;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = FALSE;

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

// count course to upgrade
$count_course_upgraded = count_course_upgraded($new_version_branch);

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

        if ( isset($_REQUEST['upgradeCoursesError']) )
        {
            // retry to upgrade course where upgrade failed
            claro_sql_query(" UPDATE `" . $tbl_course . "` SET `versionClaro` = '1.5' WHERE `versionClaro` = 'error-1.5'");
            claro_sql_query(" UPDATE `" . $tbl_course . "` SET `versionClaro` = '1.6' WHERE `versionClaro` = 'error-1.6'");
        }

        $sql_course_to_upgrade = " SELECT c.dbName dbName, 
                                          c.code , 
                                          c.fake_code , 
                                          c.directory coursePath,
                                          c.creationDate,
                                          c.versionClaro "
                               . " FROM `" . $tbl_course . "` `c` ";
        
        if ( isset($_REQUEST['upgradeCoursesError']) )
        {
            // retry to upgrade course where upgrade failed
            $sql_course_to_upgrade .= " WHERE c.versionClaro not like '". $new_version_branch ."%'
                                        ORDER BY c.dbName";
        }
        else
        {
            // not upgrade course where upgrade failed ( versionClaro == error* )
            $sql_course_to_upgrade .= " WHERE ( c.versionClaro not like '". $new_version_branch . "%' )
                                              and c.versionClaro not like 'error%' 
                                        ORDER BY c.dbName ";
        }
              
        $res_course_to_upgrade = mysql_query($sql_course_to_upgrade);
        
        /*
         * Upgrade course
         */

        while ( ($course = mysql_fetch_array($res_course_to_upgrade) ) )
        {   
            // initialise variables

            $currentCourseDbName       = $course['dbName'];
            $currentcoursePathSys      = $coursesRepositorySys . $course['coursePath'].'/';
            $currentcoursePathWeb      = $coursesRepositoryWeb . $course['coursePath'].'/';
            $currentCourseCode         = $course['code'];
            $currentCourseFakeCode     = $course['fake_code'];
            $currentCourseCreationDate = $course['creationDate'];
            $currentCourseVersion      = $course['versionClaro'];
            $currentCourseDbNameGlu    = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries

            // course upgraded
            $count_course_upgraded++;

            // initialise
            $error = false;
            $message = '';
            
            printf($lang_p_UpgradingOfCourse, 
            $count_course_upgraded, $currentCourseFakeCode, $currentCourseDbName, $currentCourseCode);
            
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
                $message .= '<p class="help">'.sprintf($lang_CourseHasNoRepository_s_NotFound , $currentcoursePathSys).'</p>' . "\n";
                $message .= '<p class="comment">'.$lang_upgradeToolCannotUpgradeThisCourse.'</p>';
            }

            if ( ! $error ) 
            {
                /*---------------------------------------------------------------------
                  Upgrade 1.6 to 1.7
                 ---------------------------------------------------------------------*/                

                if ( preg_match('/^1.5/',$currentCourseVersion) )
                {
                    // Function to upgrade tool to 1.6
                    $function_list = array('assignment_upgrade_to_16',
                                           'forum_upgrade_to_16',
                                           'quizz_upgrade_to_16',
                                           'tracking_upgrade_to_16' );

                    foreach ( $function_list as $function )
                    {
                        if ( $function($currentCourseCode) > 0 )
                        {
                            echo 'Error : ' . $function ;
                            $error = true;
                        }
                    }
                   
                    if ( ! $error )
                    {
                        // Upgrade succeeded
                        clean_upgrade_status($currentCourseCode);
                        $currentCourseVersion = '1.6';
                    }
                    else
                    {
                        // Upgrade failed
                        $currentCourseVersion = 'error-1.5';
                    }
                    // Save version
                    save_course_current_version($currentCourseCode,$currentCourseVersion);
                }
                
                /*---------------------------------------------------------------------
                  Upgrade 1.6 to 1.7
                 ---------------------------------------------------------------------*/                

                if ( preg_match('/^1.6/',$currentCourseVersion) )
                {
                    // Function to upgrade tool to 1.7
                    $function_list = array( 'agenda_upgrade_to_17',
                                            'announcement_upgrade_to_17',
                                            'course_description_upgrade_to_17',
                                            'forum_upgrade_to_17',
                                            'introtext_upgrade_to_17',
                                            'linker_upgrade_to_17',
                                            'tracking_upgrade_to_17',
                                            'wiki_upgrade_to_17');

                    foreach ( $function_list as $function )
                    {
                        if ( $function($currentCourseCode) > 0 )
                        {
                            echo 'Error : ' . $function ;
                            $error = true;
                        }
                    }
                    
                    if ( ! $error )
                    {
                        // Upgrade succeeded
                        clean_upgrade_status($currentCourseCode);
                        $currentCourseVersion = '1.7';
                    }
                    else
                    {
                        // Upgrade failed
                        $currentCourseVersion = 'error-1.6';
                    }
                    // Save version
                    save_course_current_version($currentCourseCode,$currentCourseVersion);
                
                }

            }

            // Calculate time            
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
                $message .= '<p class="success">'.$langUpgradeCourseSucceed.' - ' . $str_execution_time . '</p>';
            }
            else 
            {
                $count_course_error++;
                $message .= '<p class="error">'.$langUpgradeCourseFailed.' - ' . $str_execution_time . '</p>';
            }
            
            echo $message;
            echo '<hr noshade="noshade" />';           
            flush();

        } // end of course upgrade
                
        $mtime = microtime(); $mtime = explode(" ",$mtime);    $mtime = $mtime[1] + $mtime[0];    $endtime = $mtime; $totaltime = ($endtime - $starttime);
        
        if ( $count_course_error > 0 )
        {
            /*
             * display block with list of course where upgrade failed
             * add a link to retry upgrade of this course
             */
    
            $sql = "SELECT code 
                    FROM `" . $tbl_course . "` 
                    WHERE versionClaro like 'error-%' ";
    
            $result = claro_sql_query($sql);
    
            if ( mysql_num_rows($result) )
            {
                echo '<p  class="error">' . $lang_UpgradeFailedForCourses . ' ';
                while ( ( $course = mysql_fetch_array($result)) )
                {
                    echo $course['code'] . ' ; ';    
                }
                echo  '</p>' 
                    . '<p class="comment">'
                    . sprintf($lang_p_YouCan_url_retryToUpgradeTheseCourse, $_SERVER['PHP_SELF'] . '?cmd=run&upgradeCoursesError=1')
                    . '</p>';
            }
        }
        else
        {
            // display next step
            echo '<p class="success">'. $lang_theClarolineUpgradeToolHasSuccessfulllyUpgradeAllYourPlatformCourses . '</p>' . "\n";
            echo '<div align="right">' . sprintf($langNextStep,"upgrade.php") . '</div>';
        }

        /*
         * Hide Refresh Block
         */
                       
        echo '<script type="text/javascript">' . "\n";
        echo 'document.getElementById(\'refreshIfBlock\').style.visibility = "hidden"';
        echo '</script>';
                
        break;

} // end of switch display

/*---------------------------------------------------------------------
  Display Footer
 ---------------------------------------------------------------------*/

// Display footer
echo upgrade_disp_footer();

?>
