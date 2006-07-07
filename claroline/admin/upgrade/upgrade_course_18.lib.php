<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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

function group_upgrade_to_18($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.7/';
    $tool = 'CLGRP';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :

                $sql_step1 = " CREATE TABLE 
                        `".$currentCourseDbNameGlu."course_properties` 
                        (
                            `id` int(11) NOT NULL auto_increment,
                            `name` varchar(255) NOT NULL default '',
                            `value` varchar(255) default NULL,
                            `category` varchar(255) default NULL,
                            PRIMARY KEY  (`id`)
                        )";

                if ( upgrade_apply_sql($sql_step1) )
                {
                    $step = set_upgrade_status($tool, 2, $course_code);
                }

            case 2 :

                $sql = "SELECT self_registration,
                               private,
                               nbGroupPerUser,
                               forum, 
                               document,
                               wiki,
                               chat
                    FROM `".$currentCourseDbNameGlu."group_property`";

                $groupSettings = claro_sql_query_get_single_row($sql);

                if ( is_array($groupSettings) )
                {
                    $sql = "INSERT 
                            INTO `".$currentCourseDbNameGlu."course_properties` 
                                   (`name`, `value`, `category`) 
                            VALUES  
                            ('self_registration', '".$groupSettings['self_registration']."', 'GROUP'),
                            ('nbGroupPerUser',    '".$groupSettings['nbGroupPerUser'   ]."', 'GROUP'),
                            ('private',           '".$groupSettings['private'          ]."', 'GROUP'),
                            ('forum',             '".$groupSettings['forum'            ]."', 'GROUP'),
                            ('document',          '".$groupSettings['document'         ]."', 'GROUP'),
                            ('wiki',              '".$groupSettings['wiki'             ]."', 'GROUP'),
                            ('chat',              '".$groupSettings['chat'             ]."', 'GROUP')";
                }

                if ( upgrade_apply_sql($sql) )
                {
                    $step = set_upgrade_status($tool, 3, $course_code);
                }

            case 3 :
                $sql = "DROP TABLE IF EXISTS`".$currentCourseDbNameGlu."group_property`";

                if ( upgrade_apply_sql($sql) )
                {
                    $step = set_upgrade_status($tool, 3, $course_code);
                }

                $step = set_upgrade_status($tool, 0, $course_code);

            default : 
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }

    return false;
}
?>