<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*=====================================================================
  Init Section
 =====================================================================*/ 

require '../../inc/claro_init_global.inc.php';

/*---------------------------------------------------------------------
  Security Check
 ---------------------------------------------------------------------*/ 

if (!$is_platformAdmin) claro_disp_auth_form();

/*---------------------------------------------------------------------
  Include version file and initialize variables
 ---------------------------------------------------------------------*/

require ($includePath."/installedVersion.inc.php");

/*---------------------------------------------------------------------
  Mysql Handling
 ---------------------------------------------------------------------*/

if (!function_exists(mysql_info)) 
{
    function mysql_info() {return "";} // mysql_info is used in verbose mode
}
                
/*
 * List of accepted error - See MySQL error codes : 
 * http://dev.mysql.com/doc/mysql/en/error-handling.html
 */

$accepted_error_list = array(1017,1050,1060,1062,1065,1146);

/*---------------------------------------------------------------------
  Steps of Display 
 ---------------------------------------------------------------------*/

DEFINE ("DISPLAY_WELCOME_PANEL",1);
DEFINE ("DISPLAY_RESULT_PANEL",2);

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
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$starttime = $mtime;$steptime =$starttime;

// force upgrade for debug
if ( isset($_REQUEST['forceUpgrade']) ) $versionDb = md5 (uniqid (rand())); // for debug

$count_error_total = 0;

/*
 * count courses, courses upgraded and upgrade failed
 */

$count_course = 0; $count_course_error = 0; $count_course_upgraded = 0;

$sql = "SELECT versionDb, count(*) as count_course 
        FROM `".$mainDbName."`.`cours`
        GROUP BY versionDb ";

$result = claro_sql_query($sql);

while ($row = mysql_fetch_array($result) )
{

    // Count courses upgraded and upgrade failed    
    if ($row['versionDb'] == $versionDb)  $count_course_upgraded += $row['count_course'];
    elseif ($version == 'error') $count_course_error += $row['count_course'];

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

if ( $display==DISPLAY_RESULT_PANEL && ($count_course_upgraded + $count_course_error )< $count_course)
{
	$refresh_time = 20;
	echo "<meta http-equiv=\"refresh\" content=\"". $refresh_time  ."\" />\n";
}

?>

  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>

<body bgcolor="white" dir="<?php echo $text_dir ?>">

<center>

<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
<tbody>
<tr bgcolor="navy">
<td valign="top" align="left">
<div id="header">
<?php
 echo sprintf("<h1>Claroline (%s) - upgrade</h1>",$clarolineVersion);
?>
</div>
</td>
</tr>
<tr valign="top" align="left">
<td>
<div id="content">
<?php 

/*---------------------------------------------------------------------
  Main
 ---------------------------------------------------------------------*/

switch ($display)
{
	case DISPLAY_WELCOME_PANEL :
        echo  sprintf("<h2>%s</h2>",$langUpgradeStep3)
            . '<p>' . $langIntroStep3 . '</p>' 
            . sprintf($langNbCoursesUpgraded, $count_course_upgraded, $count_course)
		    . '<center>' . sprintf ($langLaunchStep3, $_SERVER['PHP_SELF']."?cmd=run") . '</center>';
		break;
                
	case DISPLAY_RESULT_PANEL : 

        echo sprintf("<h2>%s</h2>",$langUpgradeStep3)
             . '<p>' . $langIntroStep3Run . '</p>'; 

		// display course upgraded
        echo sprintf($langNbCoursesUpgraded,$count_course_upgraded,$count_course);

		/*
         * display block with list of course where upgrade failed
         * add a link to retry upgrade of this course
         */

		$sql = "SELECT code 
                FROM `".$mainDbName."`.`cours` 
		        WHERE versionDb = 'error' ";

		$result = claro_sql_query($sql);

		if (mysql_num_rows($result))
		{
			echo '<p  class="error">' . 'Upgrade failed for course(s)' . ' ';
			while ($course = mysql_fetch_array($result))
			{
				echo $course['code'] . ' ; ';	
			}
			echo  '-' . sprintf('You can <a href="%s">retry to upgrade</a> these courses', $_SERVER['PHP_SELF'] . '?cmd=run&upgradeCoursesError=1')
                . '</p>';
			
		}
        flush();
                
		/*
         * display refresh bloc
         */

		echo  '<div class="help" id="refreshIfBlock">'
		    . '<p>' . 'Few seconds after the load of the page<sup>*</sup>, the <em>Claroline Upgrade tool</em> will
                       automatically continue its job. If it doesn\'t, click yourself on the button below.' 
            . '</p>'
		    . '<p style="text-align: center">'
            . sprintf ("<button onclick=\"document.location='%s';\">Continue courses data upgrade</button>", $_SERVER['PHP_SELF']."?cmd=run")
		    . '</p>'
		    . '<p><small>(*) see in the status bar of your browser.</small></p>'
            . '</div>'; 

        flush();

		/*
         * Build query to select course to upgrade
         */

		$sql_course_to_upgrade = " SELECT cours.dbName dbName, cours.code sysCode, cours.fake_code officialCode, directory coursePath ".
		                         " FROM `" . $mainDbName . "`.`cours` ";

		if ( $_REQUEST['upgradeCoursesError'] == 1)
		{
            // retry to upgrade course where upgrade failed
			$sql_course_to_upgrade .= "where versionDb != '".$versionDb."' order by dbName";
		}
		else
		{
            // not upgrade course where upgrade failed ( versionDb == error)
			$sql_course_to_upgrade .= "where versionDb != '".$versionDb."' and versionDb !='error' order by dbName";
		}
		$res_course_to_upgrade = mysql_query($sql_course_to_upgrade);
		
        /*
         * Upgrade course
         */

		while ($course = mysql_fetch_array($res_course_to_upgrade))
		{
			$currentCourseDbName    = $course['dbName'];
			$currentcoursePathSys   = $coursesRepositorySys.$course['coursePath'].'/';
			$currentcoursePathWeb   = $coursesRepositoryWeb.$course['coursePath'].'/';
			$currentCourseIDsys	    = $course['sysCode'];
			$currentCourseCode      = $course['officialCode'];
			$currentCourseDbNameGlu = $courseTablePrefix . $currentCourseDbName . $dbGlu; // use in all queries
		
            $count_course_upgraded++;
			$count_error = 0;

			echo  '<p>'
                . sprintf("<strong>%1\$s. </strong>Upgrading database of course <strong>%2\$s</strong> - DB Name : %3\$s - Course ID: %4\$s", 
                          $count_course_upgraded, $currentCourseCode, $currentCourseDbName, $currentCourseIDsys);
				
			echo '<ol>' . "\n";
			
            /*
             * Include array with sql statement ($sqlForUpdate)
             */

	    	unset($sqlForUpdate);
    		include('./sql_statement_course.php');
    		include('./repair_tables.php');
			reset($sqlForUpdate);

            /*
             * Process sql statement
             */

			while ( list($key,$sqlTodo) = each($sqlForUpdate) )
			{
				$res = claro_sql_query($sqlTodo);

				if ($verbose)
				{
					echo '<li>' . "\n";
					echo '<p class="tt"><strong>' . $currentCourseDbName. ':</strong>' . $sqlTodo .  '</p>' . "\n";
					echo '<p>' . mysql_affected_rows() . ' affected rows <br />' . "\n" .mysql_info() . '</p>' . "\n";
					if (mysql_errno() > 0 )
					{
						echo '<p class="error">n° <strong>' . mysql_errno() . ': </strong> ' . mysql_error() . '</p>' . "\n";
					}
					echo '</li>' . "\n";
				}             
                
				if ( mysql_errno() > 0 && !in_array(mysql_errno(),$accepted_error_list) )
				{
					++$count_error;
					echo '<p class="error">'
				       . '<strong>' . $count_error . '</strong> '
					   . '<strong>n°: ' . mysql_errno() . '</strong> : ' . mysql_error() . ' ' . $currentCourseDbName . ':' . $sqlTodo
					   . '</p>';
				}
			}
			echo '</ol>';

			if ( $count_error>0 )
			{
				echo '<p class="error"><strong>' . $count_error . ' errors found</strong></p>';

				$count_error_total += $count_error;
                
                /*
				 * Error: set versionDB of course to error
                 */
				$sqlFlagUpgrade = " UPDATE `".$mainDbName."`.`cours`
							        SET versionDb='error'
							        WHERE code = '".$currentCourseIDsys."'";

				$res = claro_sql_query($sqlFlagUpgrade);
				if (mysql_errno() > 0)
				{
					echo '<p class="error">n° <strong>' . mysql_errno() . '</strong>: ' . mysql_error() . '</p>';
    				echo '<p>' . $sqlFlagUpgrade . '</p>';
                }
			}
			else
			{
                /*
				 * Success: set versionDB of course to new version
                 */
				$sqlFlagUpgrade = " UPDATE `".$mainDbName."`.`cours`
							        SET versionDb='".$versionDb."'
							        WHERE code = '".$currentCourseIDsys."'";				
				$res = @mysql_query($sqlFlagUpgrade);
				if (mysql_errno() > 0)
				{
					echo '<p class="error">n° <strong>'.mysql_errno().'</strong>: '.mysql_error().'</p>';
					echo '<p>' . $sqlFlagUpgrade . '</p>';
                }
            }
		
            $mtime = microtime(); $mtime = explode(" ",$mtime);	$mtime = $mtime[1] + $mtime[0]; $endtime = $mtime;
			$totaltime = ($endtime - $starttime);
			$stepDuration = ($endtime - $steptime);
			$steptime = $endtime;
			$stepDurationAvg = $totaltime / ($count_course_upgraded-$count_course_upgraded_at_start);
            $leftCourses = (int) ($count_course-$count_course_upgraded);
			$leftTime = strftime('%T',$leftCourses *$avgDuration);
			$str_execution_time = sprintf("execution time for this course [%01.2f s] - average [%01.2f s] - total [%s] - left courses [%d]. <b>left Time [%s]</b>.",$stepDuration,$stepDurationAvg,strftime('%T',$totaltime),$leftCourses,$leftTime);

            if ($count_error==0)
            {
                echo '<p class="success">Upgrade Ok - ' . $str_execution_time . '</p>';
            }
            else 
            {
                echo '<p class="error">Upgrade Failed - ' . $str_execution_time . '</p>';
            }
			echo '<hr noshade="noshade" />';           
            flush();

		} // end of course upgrade
                
        $mtime = microtime(); $mtime = explode(" ",$mtime);	$mtime = $mtime[1] + $mtime[0];	$endtime = $mtime; $totaltime = ($endtime - $starttime);
		
		if ( $count_error_total > 0 )
		{
			echo '<p class="error">' . $count_course_error . ' course(s) not upgraded.';
			echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?verbose=true">Retry</a></p>';
		}
		else
		{
			echo '<p class="success">The claroline upgrade tool has successfullly upgrade all your platform courses</p>' . "\n";
            echo '<div align="right">' . sprintf($langNextStep,"upgrade.php") . '</div>';
		}
			
		mysql_close();

        /*
         * Hide Refresh Block
         */
                       
        echo "<script type=\"text/javascript\">\n";
        echo "document.getElementById('refreshIfBlock').style.visibility = \"hidden\"";
        echo "</script>";
                
		break;

	default : 
		echo "<p>nothing to do</p>\n";
}

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
