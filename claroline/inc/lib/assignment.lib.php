<?php // $Id$
/**
 * CLAROLINE 
 *
 * The script works with the 'assignment' tables in the main claroline table
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLWRK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <pir@cerdecam.be>
 */

/**
 * Initialise assignment data
 *
 * @return array with assignment data
 * @since  1.7
 */
function assignment_initialise()
{
    $data = array();

    $data['title'] = '';
    $data['description'] = '';
    $data['visibility'] = 'VISIBLE';
    $data['def_submission_visibility'] = 'VISIBLE';
    $data['assignment_type'] = 'INDIVIDUAL';
    $data['authorized_content'] = 'FILE';
    $data['allow_late_upload'] = 'NO';
    $data['start_date'] = '';
    $data['end_date'] = '';
    $data['prefill_text'] = '';
    $data['prefill_doc_path'] = '';
    $data['prefill_submit'] = 'ENDDATE';

    return $data;
}

/**
 * Get assignment data
 *
 * @param integer $assignment_id
 *
 * @return array with readed assignment data
 * @since  1.7
 */
function assignment_get_data($assignment_id)
{
	$tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
    
    $sql = "SELECT
				`title`,
				`description`,
				`visibility`,
				`start_date`,
				`end_date`,
				`authorized_content`,
				`def_submission_visibility`,
				`assignment_type`,
				`allow_late_upload`
        FROM `" . $tbl_wrk_assignment . "`
        WHERE `id` = " . (int) $assignment_id;
    
    $result = claro_sql_query($sql);
    
    if( mysql_num_rows($result) )
    {
		$data = mysql_fetch_array($result);
		return $data;
	}
	else
	{
		return claro_failure::set_failure('ASSIGNMENT_NOT_FOUND');
	}
}

/**
 * Get assignment feedback
 *
 * @param integer $assignment_id
 *
 * @return array with assignment data
 * @since  1.7
 */
function assignment_get_feedback($assignment_id)
{
	$tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];

    $sql = "SELECT
				`prefill_text`,
				`prefill_doc_path`,
				`prefill_submit`
        FROM `" . $tbl_wrk_assignment . "`
        WHERE `id` = " . (int) $assignment_id;

    $result = claro_sql_query($sql);

    if( mysql_num_rows($result) )
    {
		$data = mysql_fetch_array($result);
		return $data;
	}
	else
	{
		return claro_failure::set_failure('ASSIGNMENT_FEEDBACK_NOT_FOUND');
	}
}

/**
 * Add a new assignment
 *
 * @param array $data array like the one returned by assignment_initialise
 * @param string $wrkDir path to workRepository
 *
 */
function assignment_insert($data, $wrkDir)
{
	$tbl_cdb_names = claro_sql_get_course_tbl();
	$tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
    
	$sql = "INSERT INTO `".$tbl_wrk_assignment."`
			SET `title` = '".addslashes($data['title'])."',
				`description` = '".addslashes($data['description'])."',
				`visibility` = '".addslashes($data['visibility'])."',
				`def_submission_visibility` = '".addslashes($data['def_submission_visibility'])."',
				`assignment_type` = '".addslashes($data['assignment_type'])."',
				`authorized_content` = '".addslashes($data['authorized_content'])."',
				`allow_late_upload` = '".addslashes($data['allow_late_upload'])."',
				`start_date` = '".addslashes($data['start_date'])."',
    			`end_date` = '".addslashes($data['end_date'])."'";

	// on creation of an assignment the automated feedback take the default values from mysql

	// execute the creation query and return id of inserted assignment
    $lastAssigId = claro_sql_query_insert_id($sql);
    
	if( $lastAssigId )
	{
	   	// create the assignment directory if query was successfull and dir not already exists
		$wrkAssigDir = $wrkDir."assig_".$lastAssigId;
		
		if( !is_dir( $wrkAssigDir ) ) mkdir( $wrkAssigDir , CLARO_FILE_PERMISSIONS );
		return $lastAssigId;
	}
	else
	{
		return false;
	}
	
}

/**
 * Update an assignment in the given or current course
 *
 * @param integer $assignment_id id the requested assignment
 * @since  1.7
 */
function assignment_update($assignment_id, $data)
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];

	$sql = "UPDATE `".$tbl_wrk_assignment."`
			SET `title` = '".addslashes($data['title'])."',
				`description` = '".addslashes($data['description'])."',
				`visibility` = '".addslashes($data['visibility'])."',
				`def_submission_visibility` = '".addslashes($data['def_submission_visibility'])."',
				`assignment_type` = '".addslashes($data['assignment_type'])."',
				`authorized_content` = '".addslashes($data['authorized_content'])."',
				`allow_late_upload` = '".addslashes($data['allow_late_upload'])."',
				`start_date` = '".addslashes($data['start_date'])."',
				`end_date` = '".addslashes($data['end_date'])."'
    		WHERE `id` = '" . (int) $assignment_id . "'";

	return claro_sql_query($sql);
}
/**
 * Delete an assignment in the given or current course
 *
 * @param integer  $assignment_id id the requested assignment
 * @param string $wrkDir path to  workRepository
 * @return result of deletion query
 * @since  1.7
 */
function assignment_delete_assignment($assignment_id, $wrkDir)
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_wrk_submission = $tbl_cdb_names['wrk_submission'];
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];

    // delete all works in this assignment if the delete of the files worked
    if( claro_delete_file( $wrkDir . 'assig_' . $assignment_id ))
    {
        $sql = "DELETE FROM `" . $tbl_wrk_submission . "`
                WHERE `assignment_id` = " . (int) $assignment_id;
        claro_sql_query($sql);
    }
    
    $sql = "DELETE FROM `".$tbl_wrk_assignment."`
                WHERE `id` = " . (int) $assignment_id;
        
    claro_sql_query($sql);
    return null;
    
};

/**
 * Validate a assignment creation/edit form, set a claro_failure when the form don't validate
 *
 * @param array $data array like the one returned by assignment_initialise containing waht have been posted
 * @param integer $assignment_id needed only when the form is for edition
 * @return boolean true if the form validate, false in other cases
 * @since  1.7
 */
function assignment_validate_form($data, $assignment_id = '')
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];
    
    // title is a mandatory element
    $title = trim( strip_tags($data['title']) );

    if( empty($title) )
    {
        claro_failure::set_failure('assignment_no_title');
        return false;
    }
    else
    {
        // check if title already exists
        if( !empty($assignment_id) )
        {
            // if assigId isset it means we are modifying an assignment
            // and assignment can have the same title as itself
            $sql = "SELECT `title`
                    FROM `".$tbl_wrk_assignment."`
                    WHERE `title` = '" . addslashes($data['title']) . "'
                    AND `id` != " . (int) $assignment_id;
        }
        else
        {
            // creating an assignment
            $sql = "SELECT `title`
	                FROM `" . $tbl_wrk_assignment . "`
	                WHERE `title` = '" . addslashes($data['title']) . "'";
        }

        $query = claro_sql_query($sql);

        if(mysql_num_rows($query) != 0 )
        {
			claro_failure::set_failure('assignment_title_already_exists');
			return false;
        }
    }

    // dates : check if start date is lower than end date else we will have a paradox
    $unixStartDate = mktime( $_REQUEST['startHour'], $_REQUEST['startMinute'], 0, $_REQUEST['startMonth'],$_REQUEST['startDay'], $_REQUEST['startYear'] );
    $unixEndDate = mktime( $_REQUEST['endHour'], $_REQUEST['endMinute'], 0, $_REQUEST['endMonth'],$_REQUEST['endDay'], $_REQUEST['endYear'] );

    if( $unixEndDate <= $unixStartDate )
    {
		claro_failure::set_failure('assignment_incorrect_dates');
		return false;
    }

	return true; // no errors, form is validate
}
/**
 * Change visibility of an assignment in the given or current course
 *
 * @param integer $assignment_id id the requested assignment
 * @return result of change visibility query
 * @since  1.7
 */
function assignment_set_item_visibility($assignment_id, $visibility, $course_id=NULL)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_wrk_assignment = $tbl_cdb_names['wrk_assignment'];

    $visibility == 'v' ? $visibility = 'VISIBLE' : $visibility = 'INVISIBLE';

    $sql = "UPDATE `" . $tbl_wrk_assignment . "`
               SET `visibility` = '" . $visibility . "'
             WHERE `id` = " . (int) $assignment_id . "
               AND `visibility` != '" . $visibility . "'";
    return  claro_sql_query($sql);
}
?>