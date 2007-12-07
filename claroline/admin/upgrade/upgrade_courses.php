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

/**
 * Init Section
 */ 

require '../../inc/claro_init_global.inc.php';

/*---------------------------------------------------------------------
  Security Check
 ---------------------------------------------------------------------*/ 

if (!$is_platformAdmin) claro_disp_auth_form();

/*---------------------------------------------------------------------
  Include version file and initialize variables
 ---------------------------------------------------------------------*/
if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
require ($includePath.'/installedVersion.inc.php');
require_once($includePath.'/lib/claro_main.lib.php');

/**#@+
 * DB tables definition
 * @var $tbl_mdb_names array table name for the central database
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course = $tbl_mdb_names['course'];
$tbl_rel_course_user   = $tbl_mdb_names['rel_course_user'];
$tbl_course_tool       = $tbl_mdb_names['tool'];


/**#@-*/

/*---------------------------------------------------------------------
  Mysql Handling
 ---------------------------------------------------------------------*/

if (!function_exists(mysql_info)) 
{
    function mysql_info() {return '';} // mysql_info is used in verbose mode
}
                
/**
 * List of accepted error - See MySQL error codes : 
 *
 * Error: 1017 SQLSTATE: HY000 (ER_FILE_NOT_FOUND) : already upgraded
 * Error: 1050 SQLSTATE: 42S01 (ER_TABLE_EXISTS_ERROR) : already upgraded
 * Error: 1054 SQLSTATE: 42S22 (ER_BAD_FIELD_ERROR) : already upgraded
 * Error: 1060 SQLSTATE: 42S21 (ER_DUP_FIELDNAME)  : already upgraded
 * Error: 1062 SQLSTATE: 23000  ( ER_DUP_ENTRY  )Message: Duplicate entry '%s' for key %d
 * Error: 1065 SQLSTATE: 42000 (ER_EMPTY_QUERY) : when  sql contain only a comment
 * Error: 1146 SQLSTATE: 42S02 (ER_NO_SUCH_TABLE) : already upgraded
 * 
 * @see http://dev.mysql.com/doc/mysql/en/error-handling.html
 */

$accepted_error_list = array(1017,1050,1054,1060,1062,1065,1146);

/**#@+
 * Displays flags
 * Using __LINE__ to have an arbitrary value
 */
DEFINE ('DISPLAY_WELCOME_PANEL', __LINE__ );
DEFINE ('DISPLAY_RESULT_PANEL', __LINE__);
/**#@-*/

/*=====================================================================
  Statements Section
 =====================================================================*/

if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'run')
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
if ( isset($_REQUEST['forceUpgrade']) ) $versionDb = md5 (uniqid (rand())); // for debug

$count_error_total = 0;

/**
 * count courses, courses upgraded and upgrade failed
 *
 * In cours table, versionDb & versionClaro 
 * contain 
 *  - 'error' if upgrade already tried but failed
 * or
 *  - version of last upgrade succeed(so previous or current)
 */

$count_course = 0; $count_course_error = 0; $count_course_upgraded = 0;

$sql = "SELECT versionDb, versionClaro, count(*) as count_course 
        FROM `" . $tbl_course . "`
        GROUP BY versionDb , versionClaro";

$result = claro_sql_query($sql);

while ($row = mysql_fetch_array($result) )
{
    // Count courses upgraded and upgrade failed    
    if ($row['versionDb'] == $versionDb && $row['versionClaro'] == $clarolineVersion) 
    {
        // upgrade succeed
        $count_course_upgraded += $row['count_course'];
    }
    elseif ($row['versionDb'] == 'error' || $row['versionClaro'] == 'error') 
    {
        // upgrade failed
        $count_course_error += $row['count_course'];
    }

    // Count courses
    $count_course += $row['count_course'];
}

$count_course_upgraded_at_start =  $count_course_upgraded;

/*=====================================================================
  Main Section
 =====================================================================*/

/*---------------------------------------------------------------------
  Steps of Display 
 ---------------------------------------------------------------------*/

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>  

<?php

// auto refresh

if ( $display == DISPLAY_RESULT_PANEL && ($count_course_upgraded + $count_course_error ) < $count_course )
{
    $refresh_time = 20;
    echo '<meta http-equiv="refresh" content="'. $refresh_time  .'" />'."\n";
}

?>

  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {    border: thin double Black;    margin-left: 15px;    margin-right: 15px;}
  </style>
</head>

<body bgcolor="white" dir="<?php echo $text_dir ?>">

<center>

<table cellpadding="10" cellspacing="0" border="0" width='650' bgcolor='#E6E6E6'>
<tbody>
<tr bgcolor='navy'>
<td valign='top' align='left'>
<div id='header'>
<?php
    echo sprintf('<h1>Claroline (%s) - ' . $langUpgrade . '</h1>', $clarolineVersion);
?>
</div>
</td>
</tr>
<tr valign='top' align='left'>
<td>
<div id='content'>
<?php 

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
                                          directory coursePath,
                                          creationDate "
                               . " FROM `" . $tbl_course . "` `c` ";
        
        if ( $_REQUEST['upgradeCoursesError'] == 1)
        {
            // retry to upgrade course where upgrade failed
            $sql_course_to_upgrade .= " WHERE c.versionDb != '".$versionDb."'
                                        or c.versionClaro != '".$clarolineVersion."'
                                        ORDER BY c.dbName";
        }
        else
        {
            // not upgrade course where upgrade failed ( versionDb == error)
            $sql_course_to_upgrade .= " WHERE c.versionDb != '".$versionDb."' 
                                        and c.versionDb != 'error' 
                                        and c.versionClaro != 'error' 
                                        ORDER BY c.dbName ";
        }
        
        $res_course_to_upgrade = mysql_query($sql_course_to_upgrade);
        
        /*
         * Upgrade course
         */

        while ($course = mysql_fetch_array($res_course_to_upgrade))
        {
            // initialise variables

            $currentCourseDbName    = $course['dbName'];
            $currentcoursePathSys   = $coursesRepositorySys.$course['coursePath'].'/';
            $currentcoursePathWeb   = $coursesRepositoryWeb.$course['coursePath'].'/';
            $currentCourseIDsys     = $course['sysCode'];
            $currentCourseCode      = $course['officialCode'];
            $currentCourseCreationDate = $course['creationDate'];
            $currentCourseDbNameGlu = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries

            $count_course_upgraded++;
            $db_error_counter = 0;
            $fs_error_counter = 0;
            $check_integrity_error = 0;
            $errorMsgs ='';
            
            printf($lang_p_UpgradingDatabaseOfCourse, 
            $count_course_upgraded, $currentCourseCode, $currentCourseDbName, $currentCourseIDsys);
            
            /**
             * Make some check.
             * For next versions these test would be set in separate process and aivailable  out of upgrade
             */

            if ( !file_exists($currentcoursePathSys) )
            {            
                // course repository doesn't exists

                $count_error_total++;
                $check_integrity_error++;

                $errorMsgs .=  '<p class="help">'.sprintf($lang_CourseHasNoRepository_s_NotFound , $currentcoursePathSys).'</p>' . "\n";
                
                $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                    SET versionClaro='error'
                                    WHERE code = '".$currentCourseIDsys."'";                

                $res = @mysql_query($sqlFlagUpgrade);
                
                if (mysql_errno() > 0)
                {
                    $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>'.'<p>' . $sqlFlagUpgrade . '</p>';
                }

                $errorMsgs .= '<p class="comment">'.$lang_upgradeToolCannotUpgradeThisCourse.'</p>';
            }
            else 
            {
            
                // get work intro

                $sql_work_intro = "SELECT ti.texte_intro
                                    FROM `" . $currentCourseDbNameGlu . "tool_list` tl,
                                         `" . $currentCourseDbNameGlu . "tool_intro` ti,
                                         `" . $tbl_course_tool . "` ct
                                    WHERE ti.id = tl.id
                                        AND tl.tool_id =  ct.id
                                        AND ct.claro_label = 'CLWRK___'";

                $work_intro = claro_sql_query_get_single_value($sql_work_intro);

                if ( $work_intro === FALSE ) $work_intro = '';

                // get course manager of the course
            
                $sql_get_id_of_one_teacher = "SELECT `user_id` `uid` FROM `". $tbl_rel_course_user . "` "
                                   . " WHERE `code_cours` = '".$currentCourseIDsys."' LIMIT 1";
                
                $res_id_of_one_teacher = claro_sql_query($sql_get_id_of_one_teacher);
                
                $teacher = claro_sql_fetch_all($res_id_of_one_teacher);
    
                $teacher_uid = $teacher[0]['uid'];

                // if no course manager, you are enrolled in as

                if (!is_numeric($teacher_uid))
                {
                    $teacher_uid = $_uid;
                    if (!is_numeric($teacher_uid))
                    $teacher_uid = 0;
                    $sql_set_teacher = "INSERT INTO `". $tbl_rel_course_user . "`  
                                        SET `user_id` = '".$teacher_uid."'
                                             ,  `code_cours` = '".$currentCourseIDsys."'
                                             ,  `role` = 'Course missing manager';";
                    claro_sql_query($sql_set_teacher);
                    $errorMsgs .= '<p class="error">Course '.$currentCourseCode.' has no teacher, you are enrolled in as course manager. </p>' . "\n";
                }

                $errorMsgs .= '<ol>' . "\n";
                
                /*
                 * Upgrade course table
                 */
    
                $tbl_cdb_names = claro_sql_get_course_tbl($currentCourseDbNameGlu);

                // Include array with sql statement ($sqlForUpdate)
                unset($sqlForUpdate);
                include('./sql_statement_course.php');
                include('./repair_tables.php');
                reset($sqlForUpdate);
    
                /*
                 * Process sql statement
                 */
    
                while ( list($key,$sqlTodo) = each($sqlForUpdate) )
                {
                    $res = mysql_query($sqlTodo);
                    if ($_REQUEST['verbose']) // verbose is set when user retry upgrade
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
                    
                    /*
                     * Error: set versionDB of course to error
                     */
                    $sqlFlagUpgrade = " UPDATE `" . $tbl_course . "`
                                        SET versionDb='error'
                                        WHERE code = '".$currentCourseIDsys."'";
    
                    $res = claro_sql_query($sqlFlagUpgrade);
                    if (mysql_errno() > 0)
                    {
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
                                        SET versionDb='".$versionDb."'
                                        WHERE code = '".$currentCourseIDsys."'";                
                    $res = @mysql_query($sqlFlagUpgrade);

                    if (mysql_errno() > 0)
                    {
                        $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>';
                        $errorMsgs .= '<p>' . $sqlFlagUpgrade . '</p>';
                    }
                }
    
                /*
                 * Upgrade course file structure
                 */
                
                // rename folder image in course folder to exercise 
                if ( is_dir($currentcoursePathSys.'image') ) 
                {   
                    if ( ! @rename($currentcoursePathSys.'image',$currentcoursePathSys.'exercise') )
                    {
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
                                        SET versionClaro='".$versionDb."'
                                        WHERE code = '".$currentCourseIDsys."'";                
                    
                    $res = @mysql_query($sqlFlagUpgrade);
                    if (mysql_errno() > 0)
                    {
                        $errorMsgs .= '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>';
                        $errorMsgs .= '<p>' . $sqlFlagUpgrade . '</p>';
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
            
            if ($db_error_counter== 0 && $fs_error_counter == 0  && $check_integrity_error == 0)
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
            //echo '<p class="error">' . sprintf($lang_p_d_coursesNotUpgraded,  $count_course_error);
            //echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?verbose=true&cmd=run&upgradeCoursesError=1">'.$lang_RetryWithMoreDetails.'</a></p>';
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

?>

</div>

</td>
</tr>
</tbody>
</table>

</body>
</html>