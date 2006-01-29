<?php // $Id$
/**
 * CLAROLINE
 *
 * Function to update course tool 1.7 to 1.8
 * 
 * - READ THE SAMPLE AND COPY PASTE IT 
 * 
 * - ADD TWICE MORE COMMENT THAT YOU THINK NEEDED
 * 
 * This code would be splited by task for the 1.8 Stable but code inside
 * function won't change, so let's go to write it.
 *
 * @version 1.8 $Revision$
 * 
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 * 
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

/*===========================================================================
 Upgrade to claroline 1.8
 ===========================================================================*/

/**
 * Upgrade foo tool to 1.8 
 * 
 * explanation of task
 *  
 * @param $course_code string
 * @return boolean whether true if succeed
 */

/*
function foo_upgrade_to_18($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.7/';
    $tool = 'CLFOO';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);
    
    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :  // add visibility fields in calendar
                // taskjob 
                // for sql task use following line  
                // if ( ! upgrade_apply_sql($sql_step1) ) return $step;

                // if task success call set_upgrade_status & set tonext step to run.
                $step = set_upgrade_status($tool, 2, $course_code);
            case 2 :  // add visibility fields in calendar
                // taskjob 
                
                // if last task success call set_upgrade_status & set to 0
                $step = set_upgrade_status($tool, 0, $course_code);
            default : 
                return $step;
        }
    }
    return false;
}
*/
?>