<?php // $Id$
/**
 *
 * @version 0.1 $Revision$
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claroline team <info@claroline.net>
 *
 * @package CLUSR
 *
 */

include get_path('incRepositorySys') . '/lib/csv.class.php';


class csvUserList extends csv
{
    var $course_id;
    var $exId;
    
    function csvUserList( $course_id )
    {
        parent::csv(); // call constructor of parent class
        
        $this->course_id = $course_id;
    }
    
    function buildRecords()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user = $tbl_mdb_names['user'];
        $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
        
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_team = $tbl_cdb_names['group_team'];        
        $tbl_rel_team_user = $tbl_cdb_names['group_rel_team_user'];
        
                 
        // get user list
        $sql = "SELECT `U`.`user_id`      AS `userId`,
                       `U`.`nom`          AS `lastname`,
                       `U`.`prenom`       AS `firstname`,
                       `U`.`username`     AS `username`,
                       `U`.`email`        AS `email`, 
                       `U`.`officialCode`     AS `officialCode`,
                       GROUP_CONCAT(`G`.`id`) AS `groupId`,
                       GROUP_CONCAT(`G`.`name`) AS `groupName`
               FROM 
                    (
                    `" . $tbl_user . "`           AS `U`,
                    `" . $tbl_rel_course_user . "` AS `CU`
                    )
               LEFT JOIN `" . $tbl_rel_team_user . "` AS `GU`
                ON `U`.`user_id` = `GU`.`user`
               LEFT JOIN `" . $tbl_team . "` AS `G`
                ON `GU`.`team` = `G`.`id`
               WHERE `U`.`user_id` = `CU`.`user_id`
               AND   `CU`.`code_cours`= '" . claro_sql_escape($this->course_id) . "'
               GROUP BY U.`user_id`
               ORDER BY U.`user_id`";

        $userList = claro_sql_query_fetch_all($sql);

        // build recordlist with good values for answers
        if( is_array($userList) && !empty($userList) )
        {
            // add titles at row 0, for that get the keys of the first row of array
            $this->recordList[0] = array_keys($userList[0]); 
            $i = 1;
            foreach( $userList as $user )
            {
                // $this->recordList is defined in parent class csv
                $this->recordList[$i] = $user;
                // if password is exported and must be encrypted and is not already encrypted : crypt it
                if( get_conf('export_user_password') && get_conf('export_user_password_encrypted') && !get_conf('userPasswordCrypted') )
                {
                    $this->recordList[$i]['password'] = md5($this->recordList[$i]['password']);
                }
                
                $i++;
            }

            if( is_array($this->recordList) && !empty($this->recordList) ) return true;
        }
        
        return false;
    }
}

function export_user_list( $course_id )
{
    $csvUserList = new csvUserList( $course_id );
    
    $csvUserList->buildRecords();
    $csvContent = $csvUserList->export();
    
    return $csvContent;
}
?>
