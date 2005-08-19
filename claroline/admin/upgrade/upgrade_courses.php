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
    include('./sql_statement_course.php');

    /** TODO
    include('./repair_tables.php');
    */
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
                
            // course repository doesn't exists

            if ( !file_exists($currentcoursePathSys) )
            {            

                $error = true;
                $count_error_total++;

                $errorMsgs .=  '<p class="help">'.sprintf($lang_CourseHasNoRepository_s_NotFound , $currentcoursePathSys).'</p>' . "\n";
                
                $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                    SET versionClaro='error'
                                        versionDb='error';
                                    WHERE code = '".$currentCourseIDsys."'";                

                $res = @mysql_query($sqlFlagUpgrade);
                
                if (mysql_errno() > 0)
                {
                    $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>'.'<p>' . $sqlFlagUpgrade . '</p>';
                }

                $errorMsgs .= '<p class="comment">'.$lang_upgradeToolCannotUpgradeThisCourse.'</p>';
            }

            if ( ! $error ) 
            {
                            
                /*---------------------------------------------------------------------
                  Upgrade Course Table
                 ---------------------------------------------------------------------*/ 

                if ( $currentCourseDbVersion != $newDbVersion)
                {
                    // TODO
                    upgrade_assignments_to_16();
    
                    $errorMsgs .= '<ol>' . "\n";
                    
                    /*
                     * Upgrade course table
                     */
        
                    // NOT USED 
                    //$tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($currentCourseIDsys));
    
                    // Include array with sql statement ($sqlForUpdate)
                    
                    $sqlForUpdate = query_to_upgrade_course_database_to_16();
                    // TODO $sqlForUpdate = query_to_upgrade_course_database_to_17();
        
                    /*
                     * Process sql statement
                     */
        
                    foreach ( $sqlForUpdate as $key => $sqlTodo )
                    {
                        $res = mysql_query($sqlTodo);
                        if ($verbose) // verbose is set when user retry upgrade
                        {
                            $errorMsgs .= '<li>' . "\n";
                            $errorMsgs .= '<p class="tt"><strong>' . $currentCourseDbName. ':</strong>' . $sqlTodo .  '</p>' . "\n";
                            $errorMsgs .= '<p>' . mysql_affected_rows() . ' affected rows <br />' . "\n" .mysql_info() . '</p>' . "\n";
                            if (mysql_errno() > 0 )
                            {
                                $errorMsgs .= '<p class="error">n° <strong>' . mysql_errno() . ': </strong> ' . mysql_error() . '</p>' . "\n";
                            }
                            $errorMsgs .= '</li>' . "\n";
                        }             
                        
                        if ( mysql_errno() > 0 && !in_array(mysql_errno(),$accepted_error_list) )
                        {
                            $error = true;
                            $db_error_counter++;
                            $errorMsgs .= '<p class="error">'
                               . '<strong>' . $db_error_counter . '</strong> '
                               . '<strong>n°: ' . mysql_errno() . '</strong> : ' . mysql_error() . ' ' . $currentCourseDbName . ':' . $sqlTodo
                               . '</p>';
                        }
                    }
                    $errorMsgs .= '</ol>';
        
                
                    if ( $db_error_counter > 0 )
                    {
                        $errorMsgs .= '<p class="error"><strong>' . $db_error_counter . ' errors found</strong></p>';
        
                        $count_error_total += $db_error_counter;
                        
                        // TODO : function to update version in course table

                        /*
                         * Error: set versionDB of course to error
                         */
                        $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                            SET versionDb='error'
                                            WHERE code = '".$currentCourseIDsys."'";
        
                        $res = claro_sql_query($sqlFlagUpgrade);
                        if (mysql_errno() > 0)
                        {
                            $error = true;
                            $errorMsgs .= '<p class="error">n° <strong>' . mysql_errno() . '</strong>: ' . mysql_error() . '</p>';
                            $errorMsgs .= '<p>' . $sqlFlagUpgrade . '</p>';
                        }
                    }
                    else
                    {
                        /*
                         * Success: set versionDB of course to new version
                         */
                        $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                            SET versionDb='". $newDbVersion ."'
                                            WHERE code = '".$currentCourseIDsys."'";                
                        $res = @mysql_query($sqlFlagUpgrade);
    
                        if (mysql_errno() > 0)
                        {
                            $error = true;
                            $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>';
                            $errorMsgs .= '<p>' . $sqlFlagUpgrade . '</p>';
                        }
                    }
    
                }
                
                /*---------------------------------------------------------------------
                  Upgrade Course Files
                 ---------------------------------------------------------------------*/ 

                if ( $currentCourseClarolineVersion != $newClarolineVersion)
                {
                
                    // rename folder image in course folder to exercise 
                    if ( is_dir($currentcoursePathSys.'image') ) 
                    {   
                        if ( ! @rename($currentcoursePathSys.'image',$currentcoursePathSys.'exercise') )
                        {
                            $error = true;
                            $fs_error_counter++;
                            $errorMsgs .= '<p class="error">'
                               . '<strong>' . sprintf($lang_p_CannotRename_s_s,$currentcoursePathSys.'/image',$currentcoursePathSys.'/exercise') . '</strong> '
                               . '</p>';
                        } 
                    }
                    elseif ( !is_dir($currentcoursePathSys.'exercise') ) 
                    {
                        if ( !@mkdir($currentcoursePathSys.'exercise', 0777) )
                        {
                            $error = true;
                            $fs_error_counter++;
                            $errorMsgs .= '<p class="error">'
                               . '<strong>' . sprintf($lang_p_CannotCreate_s,$currentcoursePathSys.'exercise') . '</strong> '
                               . '</p>';
                        }
                    }
    
                    // create work assig_1 folder    
                    $work_dirname = $currentcoursePathSys.'work/';
                    $assignment_dirname = $work_dirname . 'assig_1/';
    
                    if ( !is_dir($assignment_dirname) )
                    {
                        if ( !@mkdir($assignment_dirname, 0777) )
                        {
                            $error = true;
                            $fs_error_counter++;
                            $errorMsgs .= '<p class="error">'
                               . '<strong>' . sprintf($lang_p_CannotCreate_s,$assignment_dirname) . '</strong> '
                               . '</p>';
                        }
                    }
                    
                    // move assignment from work to work/assig_1    
                    if ( is_dir($work_dirname) )
                    {
                        if ( $handle=opendir($work_dirname) )
                        {   
                            while ( FALSE !== ($file = readdir($handle)) )
                            {
                                if ( is_dir($work_dirname.$file) ) continue;
    
                                if ( @rename($work_dirname.$file,$assignment_dirname.$file) === FALSE )
                                {
                                    $error = true;
                                    $fs_error_counter++;
                                    $errorMsgs .= '<p class="error">'
                                   . '<strong>' . sprintf($lang_p_CannotRename_s_s,$work_dirname.$file,$assignment_dirname.$file) . '</strong> '
                                   . '</p>';
    
                                }
    
                            }
                            closedir($handle);
                        }                    
                    }                
                    
                    if ( $fs_error_counter > 0 )
                    {
                        $count_error_total += $fs_error_counter;
                        $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                            SET versionClaro='error'
                                            WHERE code = '".$currentCourseIDsys."'";                
                        $res = @mysql_query($sqlFlagUpgrade);
                        
                        if (mysql_errno() > 0)
                        {   
                            $error = true;
                            $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>';
                            $errorMsgs .= '<p>' . $sqlFlagUpgrade . '</p>';
                        }
                    }
                    else
                    {
                        /*
                         * Success: set versionClaro of course to new version
                         */
                        $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                            SET versionClaro='".$newClarolineVersion."'
                                            WHERE code = '".$currentCourseIDsys."'";                
                        
                        $res = @mysql_query($sqlFlagUpgrade);

                        if (mysql_errno() > 0)
                        {
                            $error = true;
                            $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>';
                            $errorMsgs .= '<p>' . $sqlFlagUpgrade . '</p>';
                        }
                    }

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
