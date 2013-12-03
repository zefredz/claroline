<?php // $Id$

/**
 * CLAROLINE
 *
 * Sql query to update main database
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE *
 * @package     UPGRADE *
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 *              (upgrades regarding session courses and categories)
 */

function upgrade_user_to_112()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    
    $label = 'USERS_112';
    
    switch( $step = get_upgrade_status($tool) )
    {
        case 1:
            // check if column already exists
            $result = mysqli_query($GLOBALS["___mysqli_ston"],"SHOW COLUMNS FROM `{$tbl_mdb_names['user']}` LIKE 'lastLogin'");
            $exists = (mysqli_num_rows($result))?TRUE:FALSE;
            
            if ( $exists )
            {
                set_upgrade_status($tool, $step+1);
                break;
            }
            
            $sqlForUpdate[] = "ALTER TABLE `{$tbl_mdb_names['user']}` ADD `lastLogin` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step;
            
            unset($sqlForUpdate);
            
            
        case 2:
            if ( get_conf('is_trackingEnabled') )
            {
                // for each user get lastLogin date from tracking and insert it into user table

                $result = claro_sql_query_fetch_all_rows("
                    SELECT 
                        DISTINCT(`tracking`.`user_id`) AS userId,
                        MAX(`tracking`.`date`) AS lastLogin
                    FROM 
                        `{$tbl_mdb_names['tracking_event']}` AS `tracking`
                    WHERE 
                        `tracking`.`type` = 'user_login'
                    GROUP BY 
                        `tracking`.`user_id`;");

                foreach ( $result as $row )
                {
                    upgrade_sql_query("UPDATE `{$tbl_mdb_names['user']}` SET `lastLogin` = '{$row['lastLogin']}' WHERE `user_id` = {$row['userId']};");
                }
            }
            else
            {
                upgrade_sql_query("UPDATE `{$tbl_mdb_names['user']}` SET `lastLogin` = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE 1 = 1;");
            }
            
            set_upgrade_status($tool, $step+1);            
            
        default :
            
            $step = set_upgrade_status($tool, 0);
            return $step;
    }
    
    return false;
}