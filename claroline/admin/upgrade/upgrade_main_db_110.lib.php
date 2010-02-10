<?php // $Id: upgrade_main_db_19.lib.php 11825 2009-07-07 15:57:02Z dkp1060 $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Sql query to update main database
 *
 * @version 1.10 $Revision: 11825 $
 *
 * @copyright (c) 2001-2010 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Antonin Bourguignon <antonin.bourguignon@claroline.net>
 *
 */


/*===========================================================================
 Upgrade to claroline 1.10
 ===========================================================================*/

function upgrade_main_database_category_to_110 ()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tool = 'CATEGORY_110';

    switch( $step = get_upgrade_status($tool) )
    {           
        case 1 :

            // Create new tables `category` and `rel_course_category`
            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['category_dev'] . "` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(100) NOT NULL DEFAULT '',
                                `code` varchar(12) NOT NULL DEFAULT '',
                                `idParent` int(11) DEFAULT '0',
                                `rank` int(11) NOT NULL DEFAULT '0',
                                `visible` tinyint(1) NOT NULL DEFAULT '1',
                                `canHaveCoursesChild` tinyint(1) NOT NULL DEFAULT '1',
                                PRIMARY KEY (`id`),
                                UNIQUE KEY `code` (`code`)
                                ) TYPE=MyISAM";
            
            $sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `" . $tbl_mdb_names['rel_course_category'] . "` (
                                `courseId` int(11) NOT NULL,
                                `categoryId` int(11) NOT NULL,
                                `rootCourse` tinyint(1) NOT NULL DEFAULT '0',
                                PRIMARY KEY (`courseId`,`categoryId`)
                                ) TYPE=MyISAM";
                        
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);

        case 2 :
            
            // Insert root category
            $sqlForUpdate[] = "INSERT INTO `" . $tbl_mdb_names['category_dev'] . "` 
                                (`id`, `name`, `code`, `idParent`, `rank`, `visible`, `canHaveCoursesChild`) 
                                VALUES
                                (0, 'Root', 'ROOT', NULL, 0, 0, 0)";
                        
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
            
        case 3:
            
            // Insert all previous categories ("faculties") in the new table `category`
            $sql = "SELECT f1.`id`, f1.`code`, f1.`code_P`, f1.`treePos`, f1.`nb_childs`, f1.`canHaveCoursesChild`, f1.`canHaveCatChild`, f2.`id` as idParent 
                    FROM `" . $tbl_mdb_names['category'] . "` f1, `" . $tbl_mdb_names['category'] . "` f2
                    WHERE f1.code_P = f2.code OR f1.code_P IS NULL
                    GROUP BY f1.id 
                    ORDER BY idParent ASC, f1.`treePos` ASC";
            
            $categoriesList = claro_sql_query_fetch_all_rows( $sql );
            
            $tempIdParent     = null;
            $rank             = 0;
            $visibile         = 1; // Change this value if you want to change the default value of visibility (1 or 0)
            foreach ( $categoriesList as $category )
            {
                // Manage the rank
                if ($tempIdParent != $category['idParent'])
                {
                    $tempIdParent = $category['idParent'];
                    $rank = 1;
                }
                else
                {
                    $rank++;
                }
                
                $sqlForUpdate[] = "INSERT INTO `" . $tbl_mdb_names['category_dev'] . "` 
                                    (`id`, `name`, `code`, `idParent`, `rank`, `visible`, `canHaveCoursesChild`) 
                                    VALUES
                                    ('', '" . $category['name'] . "', '" . $category['code'] . "', " . $category['idParent'] . ", " . $rank . ", $visibile, " . $category['canHaveCoursesChild'] . ")";
            }
            
            if ( upgrade_apply_sql($sqlForUpdate) ) $step = set_upgrade_status($tool, $step+1);
            else return $step ;

            unset($sqlForUpdate);
            
        case 4 :
            
            // Link new categories to courses in `rel_course_categories`
            
        case 5 :
            
            // Drop deprecated attribute "faculte" from `cours` table
            
        case 6 :
            
            // Drop deprecated table `faculty`

        default :

            $step = set_upgrade_status($tool, 0);
            return $step;
    
    }
      
    return false;    
}
