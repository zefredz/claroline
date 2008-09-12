<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Function to update course tool 1.8 to 1.9
 *
 * - READ THE SAMPLE AND COPY PASTE IT
 *
 * - ADD TWICE MORE COMMENT THAT YOU THINK NEEDED
 *
 * This code would be splited by task for the 1.8 Stable but code inside
 * function won't change, so let's go to write it.
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe GeschÃ© <moosh@claroline.net>
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

function tool_list_upgrade_to_19 ($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'TOOLLIST';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "tool_list` 
                              ADD `activated` ENUM('true','false') NOT NULL DEFAULT 'true'";
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
            case 2 :
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "tool_list` 
                              ADD `installed` ENUM('true','false') NOT NULL DEFAULT 'true'";
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, 0, $course_code);
                else return $step ;
                
            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }
    return false;
}

function chat_upgrade_to_19 ($course_code)
{

    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLCHT';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        $coursePath  = get_path('coursesRepositorySys') . claro_get_course_path($course_code);
        $courseChatPath  = $coursePath . '/chat/';
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            
            case 1 : 
                // get all chat files
                log_message("Search in ". $courseChatPath);
                $it = new DirectoryIterator($courseChatPath);
                $error = false;
                
                foreach( $it as $file )
                {
                    if( ! $file->isFile() ) continue;

                    if( $file->getFilename() == $course_code . '.chat.html' )
                    {
                        // chat de cours
                        log_message("Try to export course chat : " . $file->getFilename() );
                        $exportFileDir = $coursePath.'/document/recovered_chat/';
                        $groupId = null;
                    }
                    else
                    {
                        // group chat
                        log_message("Try to export group chat : " . $file->getFilename() );
                        // get groupId 
                        $matches = array();
                        preg_match('/\w+\.(\d+)\.chat\.html/', $file->getFilename(), $matches);
                        if( isset($matches[1]) )
                        {
                            $groupId = (int) $matches[1];
                        }
                        else
                        {
                            log_message('Cannot find group id in chat filename : '. $file->getFilename());
                            break;
                        }
                        
                        if( ! ($groupData = claro_get_group_data(array(CLARO_CONTEXT_COURSE => $course_code, CLARO_CONTEXT_GROUP => $groupId))) )
                        {
                            // group cannot be found, save in document 
                            $exportFileDir = $coursePath.'/document/recovered_chat/';
                            log_message('Cannot find group so save chat filename in course : '. $file->getFilename());
                        }
                        else
                        {
                            $exportFileDir = $coursePath.'/group/'.$groupData['directory'].'/recovered_chat/';
                        }
                    }
                    
                    // create dire
                    claro_mkdir($exportFileDir, CLARO_FILE_PERMISSIONS, true);

                    // try to find a unique filename
                    $fileNamePrefix = 'chat.'.date('Y-m-j').'_';
                    if( !is_null($groupId) )
                    {
                        $fileNamePrefix .=  $groupId . '_';
                    }

                    $i = 1;
                    while ( file_exists($exportFileDir.$fileNamePrefix.$i.'.html') ) $i++;

                    $savedFileName = $fileNamePrefix.$i.'.html';

                    // prepare output
                    $out = '<html>'
                    . '<head>'
                    . '<title>Discussion - archive</title>'
                    . '</head>'
                    . '<body>'
                    . file_get_contents($file->getPathname())
                    . '</body>'
                    . '</html>';
                    
                    
                    // write to file
                    if( ! file_put_contents($exportFileDir.$savedFileName, $out) )
                    {
                        log_message('Cannot save chat : '. $exportFileDir.$savedFileName);
                        $error = true;
                    }
                }
                // save those with group id in group space

                
                if ( !$error ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
            case 2 : 
                // activate new chat in each course
                $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);
                
                $toolId = get_tool_id_from_module_label( 'CLCHAT' );

                if( ! register_module_in_single_course( $toolId, $course_code ) )
                {
                    log_message("register_module_in_single_course( $toolId, $course_code ) failed");
                    return $step;
                }
                
                $sqlForUpdate[] = "UPDATE `" . $currentCourseDbNameGlu . "tool_list`
                SET `activated` = 'true',
                    `installed` = 'false'
                WHERE tool_id = " . (int) $toolId;
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }
    return false;

}

function course_description_upgrade_to_19 ($course_code)
{
    global $currentCourseVersion;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLDSC';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                // id becomes int(11)
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "course_description` 
                              CHANGE `id` `id` int(11) NOT NULL auto_increment";
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            case 2 :
                // add category field
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "course_description` 
                              ADD `category` int(11) NOT NULL DEFAULT '-1'";
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
            
            case 3 :
                // rename update to lastEditDate
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "course_description` 
                              CHANGE `upDate` `lastEditDate` datetime NOT NULL";
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);

            case 4 :
                // change possible values of visibility fields (show/hide -> visible/invisible)
                // so to do that we: #1 change the possible enum values to have them all
                //                   #2 change fields with show to visible / fields with hide to invisible
                //                   #3 change the possible enum values to keep only the good ones

                // #1
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "course_description`
                              CHANGE `visibility` `visibility` enum('SHOW','HIDE','VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE'";
                //#2
                $sqlForUpdate[] = "UPDATE `" . $currentCourseDbNameGlu . "course_description`
                                SET `visibility` = IF(`visibility` = 'SHOW' ,'VISIBLE','INVISIBLE')";
                
                //#3
                $sqlForUpdate[] = "ALTER IGNORE TABLE `" . $currentCourseDbNameGlu . "course_description` 
                              CHANGE `visibility` `visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE'";
                
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }
    return false;
}

/**
 * Upgrade foo tool to 1.9
 *
 * explanation of task
 *
 * @param $course_code string
 * @return boolean whether true if succeed
 */

function quiz_upgrade_to_19 ($course_code)
{
    // PRIMARY KEY (`exerciseId`,`questionId`)
    global $currentCourseVersion, $currentcoursePathSys;

    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLQWZ';
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        // On init , $step = 1
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                // qwz_rel_exercise_question - fix key and index
                $sqlForUpdate[] = "ALTER TABLE `". $currentCourseDbNameGlu ."qwz_rel_exercise_question`
                  DROP PRIMARY KEY,
                  ADD PRIMARY KEY(`exerciseId`, `questionId`)";
                   
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            case 2 :
                // qwz_tracking - rename table
                $sqlForUpdate[] = "ALTER IGNORE TABLE `". $currentCourseDbNameGlu . "track_e_exercices` 
                                RENAME TO `". $currentCourseDbNameGlu ."qwz_tracking`";
                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            case 3 : 
                // qwz_tracking - rename fields
                $sqlForUpdate[] = "ALTER IGNORE TABLE `". $currentCourseDbNameGlu . "qwz_tracking`
                                CHANGE `exe_id`         `id`        int(11) NOT NULL auto_increment,
                                CHANGE `exe_user_id`    `user_id`   int(11) default NULL,
                                CHANGE `exe_date`       `date`      datetime NOT NULL default '0000-00-00 00:00:00',
                                CHANGE `exe_exo_id`     `exo_id`    int(11) NOT NULL default '0',
                                CHANGE `exe_result`     `result`    float NOT NULL default '0',
                                CHANGE `exe_time`       `time`      mediumint(8) NOT NULL default '0',
                                CHANGE `exe_weighting`  `weighting` float NOT NULL default '0'";

                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);

            case 4 : 
                 // qwz_tracking_questions - rename table
                $sqlForUpdate[] = "ALTER TABLE `". $currentCourseDbNameGlu . "track_e_exe_details` 
                                RENAME TO `". $currentCourseDbNameGlu . "qwz_tracking_questions`";

                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            case 5 : 
                // qwz_tracking_answers - rename table
                $sqlForUpdate[] = "ALTER TABLE `". $currentCourseDbNameGlu . "track_e_exe_answers` 
                                RENAME TO `". $currentCourseDbNameGlu . "qwz_tracking_answers`";

                if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step ;
                
                unset($sqlForUpdate);
                
            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }

    return false;
}

/**
 * Upgrade tracking tool to 1.8
 */

function tracking_upgrade_to_19($course_code)
{
    /*
     * DO NOT get the old tracking data to put it in this table here
     * as it is a very heavy process it will be done in another script dedicated to that.
     */
    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLSTATS';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sql = "CREATE TABLE IF NOT EXISTS `".$currentCourseDbNameGlu."tracking_event` (
                      `id` int(11) NOT NULL auto_increment,
                      `tool_id` int(11) DEFAULT NULL,
                      `user_id` int(11) DEFAULT NULL,
                      `group_id` int(11) DEFAULT NULL,
                      `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `type` varchar(60) NOT NULL DEFAULT '',
                      `data` text NOT NULL DEFAULT '',
                      PRIMARY KEY  (`id`)
                    ) TYPE=MyISAM;";
                
                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step;

            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }

    return false;
}

function calendar_upgrade_to_19($course_code)
{
    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLCAL';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sql = "ALTER IGNORE TABLE `".$currentCourseDbNameGlu."calendar_event` 
                        ADD `location` varchar(50)";
                
                if ( upgrade_sql_query($sql) ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step;

            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }

    return false;
}

function convert_crl_from_18_to_19( $crl )
{
	$matches =array();
	
    if (preg_match(
        '!(crl://claroline\.net/\w+/[^/]+/groups/\d+/)([^/]+)(.*)!',
        $crl, $matches ) )
    {
        $crl = $matches[1] . rtrim( $matches[2], '_' ) . $matches[3];
    }
    elseif (preg_match(
        '!(crl://claroline\.net/\w+/[^/]+/)([^/]+)(.*)!',
        $crl, $matches ) )
    {
        $crl = $matches[1] . rtrim( $matches[2], '_' ) . $matches[3];
    }
    else
    {
        $crl = $crl;
    }
    
    log_message($crl);
    
    return $crl;
}


function linker_upgrade_to_19($course_code)
{
    $versionRequiredToProceed = '/^1.8/';
    $tool = 'CLLNK';
    
    global $currentCourseVersion;
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);

    if ( preg_match($versionRequiredToProceed,$currentCourseVersion) )
    {
        switch( $step = get_upgrade_status($tool,$course_code) )
        {
            case 1 :
                $sql = "SELECT `crl` FROM `".$currentCourseDbNameGlu."lnk_resources`";
                
                $res = claro_sql_query_fetch_all_rows( $sql );
                $success = ($res !== false);
                
                log_message("found " . count($res) . " crls to convert");

                foreach( $res as $resource )
                {
                    $sql = "UPDATE `".$currentCourseDbNameGlu."lnk_resources`
                    SET `crl` = '" . claro_sql_escape( convert_crl_from_18_to_19($resource['crl']) ) ."'
                    WHERE `crl` = '" .claro_sql_escape( $resource['crl'] ) ."'";
                    
                    $success = upgrade_sql_query( $sql );
                    
                    if ( ! $success )
                    {
                        break;
                    }
                }
                
                if ( $success ) $step = set_upgrade_status($tool, $step+1, $course_code);
                else return $step;

            default :
                $step = set_upgrade_status($tool, 0, $course_code);
                return $step;
        }
    }

    return false;
}
