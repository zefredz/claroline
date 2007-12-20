<?php // $Id$
/**
 * CLAROLINE
 *
 * Try to delete flders of assignments that was not completly removed
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
/*=====================================================================
  Init Section
 =====================================================================*/

// Initialise Upgrade
require 'upgrade_init_global.inc.php';

// Security Check
if ( !claro_is_platform_admin() ) upgrade_disp_auth_form();

$acceptedCmdList = array( 'exPurgeWork' );

if( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )   $cmd = $_REQUEST['cmd'];
else                                                                            $cmd = null;


if( isset($_REQUEST['offset']) )	$offset = $_REQUEST['offset'];
else								$offset = '0';

$step = '20';

/*=====================================================================
  Main Section
 =====================================================================*/

$nameTools = get_lang('Purge work repositories');

$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_course = $tbl_mdb_names['course'];

if( $cmd == 'exPurgeWork' )
{
	$html = '';
	// get course list
	$sqlCourseList = " SELECT `code` as  `sysCode`, `directory` as `coursePath` ".
	                  " FROM `". $tbl_course . "` " .
	                  " ORDER BY `sysCode`" .
	                  " LIMIT ".(int) $offset.", ".(int) $step;

	$courseList = claro_sql_query_fetch_all($sqlCourseList);
	if( is_array($courseList) && !empty($courseList) )
	{
		$html .= '<ul>' . "\n";
		foreach( $courseList as $aCourse )
		{
			$html .= '<li>'.$aCourse['sysCode'] . "\n";
			$tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($aCourse['sysCode']));

			$tbl_assignment = $tbl_cdb_names['wrk_assignment'];
			$tbl_submission = $tbl_cdb_names['wrk_submission'];

			// get existing assignments list
			$sqlAssignmentList = " SELECT id" .
								 " FROM `".$tbl_assignment."` ";

			$assignmentList = claro_sql_query_fetch_all($sqlAssignmentList);

			// we really need an array
			$existingAssignmentList = array();
			if( is_array($assignmentList) )
			{
				foreach( $assignmentList as $assignment )
				{
					$existingAssignmentList[] = $assignment['id'];
				}
			}


			// get existing dirs
			$assignmentDirList = array();

			$dir = get_path('coursesRepositorySys').$aCourse['coursePath'] . '/work/' ;
			if (is_dir($dir))
			{
			    if ($dh = opendir($dir))
			    {
			        while( ($file = readdir($dh)) !== false)
			        {
			        	if( preg_match('/^assig_(\d+)$/', $file, $matches) )
			            {
			            	$assignmentDirList[] = $matches[1];
			            }
			    	}
		        	closedir($dh);
			    }
		    }

		    // now I have an array with existing assignment and one with existing directory ids
			// merge them
			$idList = array_merge($existingAssignmentList, $assignmentDirList);

			$html .= '<ul>' . "\n";

			foreach( $idList as $id )
			{
				// directory exists but not directory
				if( !in_array($id, $existingAssignmentList) && in_array($id, $assignmentDirList) )
				{
					$dirToDelete = $dir . 'assig_' . $id;
					$html .= '<li>Delete directory for missing assignment : ' . $dirToDelete . '</li>' . "\n";

					// remove dir and submissions, the dir does not match a still existing assignment
					$sql = "DELETE FROM `".$tbl_submission."`
							WHERE `assignment_id` = '".$id."'";

					if( claro_sql_query($sql) )
					{
						claro_delete_file($dirToDelete);
					}
				}
				// assignment exist but not directory
				elseif( in_array($id, $existingAssignmentList) && !in_array($id, $assignmentDirList) )
				{
					$dirToCreate = $dir . 'assig_' . $id;
					$html .= '<li>Create missing directory : ' . $dirToCreate . '</li>' . "\n";

					claro_mkdir($dirToCreate, CLARO_FILE_PERMISSIONS, true);
				}
			}

			$html .= '</ul>' . "\n";

			$html .= '</li>' . "\n";
		}
		$html .= '</ul>' . "\n";
	}
	// display confirmation
	// display next button

}
/*=====================================================================
  Display
 =====================================================================*/
// Display Header
echo upgrade_disp_header();

echo claro_html_tool_title($nameTools);

// display result

if( !empty($html) )
{
	echo $html;
}

$nextStep = (int)($offset+$step);

if(  $cmd != 'exPurgeWork' )
{
	echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?cmd=exPurgeWork&amp;offset=0">Purge courses from 0 to '.$nextStep.'</a></p>';
}
else
{
	echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?cmd=exPurgeWork&amp;offset='.$nextStep.'">Purge courses from '.$nextStep.' to '.($nextStep + $step).' &gt;</a></p>';
}

// Display footer
echo upgrade_disp_footer();
?>