<?php

class MergeUser
{
    public static function merge( $uidToRemove, $uidToKeep )
    {
        $mainTbl = claro_sql_get_main_tbl();
        
        // Get course list for the user to remove        
        $sql = "SELECT c.`code` AS `code`
              FROM `{$mainTbl['course']}` c, `{$mainTbl['rel_course_user']}` cu
            WHERE cu.user_id = ".(int)$uidToRemove."
              AND   c.code = cu.code_cours";

        $tmpResult = claro_sql_query_fetch_all_cols($sql);
        $courseList = $tmpResult['code'];
        
        foreach ( $courseList as $thisCourseCode )
        {
            // Check if the user to keep is registered to the course
            $sql = "SELECT `code_cours`
                  FROM `{$mainTbl['rel_course_user']}`
                WHERE code_cours = '".claro_sql_escape($thisCourseCode)."'
                  AND user_id = ".(int)$uidToKeep;

            $userToKeepCourseList = claro_sql_query_fetch_all($sql);
            
            if ( !empty( $userToKeepCourseList ) )
            {
                // Remove the user to remove from the course
                $sql = "DELETE FROM `{$mainTbl['rel_course_user']}` 
                    WHERE user_id    = ".(int)$uidToRemove."
                      AND code_cours = '".claro_sql_escape($thisCourseCode)."'";
            }
            else
            {
                // Replace the user id of the user to remove
                $sql = "UPDATE `{$mainTbl['rel_course_user']}` 
                    SET   user_id    = ".(int)$uidToKeep."
                    WHERE user_id    = ".(int)$uidToRemove."
                      AND code_cours = '".claro_sql_escape($thisCourseCode)."'";
            }
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change rel_course_user in {$thisCourseCode}");
            }
            
            $sql = "UPDATE `{$mainTbl['rel_class_user']}` 
                SET   user_id    = ".(int)$uidToKeep."
                WHERE user_id    = ".(int)$uidToRemove;

            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change rel_class_user in {$thisCourseCode}");
            }
            
            // Update course
            
            self::mergeCourseUsers( $uidToRemove, $uidToKeep, $thisCourseCode );
            self::mergeCourseModuleUsers( $uidToRemove, $uidToKeep, $thisCourseCode );
        }
        
        // Update modules
        self::mergeModuleUsers( $uidToRemove, $uidToKeep );
        
        // Update main tracking
        self::mergeMainTrackingUsers( $uidToRemove, $uidToKeep );
        
        // Delete old user
        $sql = "DELETE FROM `{$mainTbl['user']}`
            WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot delete old use");
        }
    }
    
    public static function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $courseTbl = claro_sql_get_course_tbl( claro_get_course_db_name_glued( $courseId ) );
        
        // Get groups for the user to remove
        $sql = "SELECT team
                FROM `{$courseTbl['group_rel_team_user']}`
                WHERE user= ".(int)$uidToRemove;

        $result   = claro_sql_query_fetch_all_cols($sql);
        $teamList = $result['team'];
        
        foreach ( $teamList as $thisTeam )
        {
            $sql = "SELECT user 
                    FROM `{$courseTbl['group_rel_team_user']}`
                    WHERE user = ".(int)$uidToKeep."
                      AND team = ".(int)$thisTeam;

            $result = claro_sql_query_fetch_all($sql);

            if ( !empty($result) )
            {
                $sql = "DELETE FROM `{$courseTbl['group_rel_team_user']}`
                         WHERE user  = ".(int)$uidToRemove."
                           AND team  = ".(int)$thisTeam;
            }
            else
            {
                $sql = "UPDATE `{$courseTbl['group_rel_team_user']}`
                           SET user = ".(int)$uidToKeep."
                         WHERE user = ".(int)$uidToRemove."
                           AND team = ".(int)$thisTeam;
            }
        }
        
        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot change group_rel_team_user in {$thisCourseCode}");
        }
        
        // Update tracking
        $sql = "UPDATE `{$courseTbl['tracking_event']}`
                SET   user_id = ".(int)$uidToKeep."
                WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot change tracking_event in {$thisCourseCode}");
        }

        
        $qwz_tbl_names = get_module_course_tbl( array( 'qwz_tracking' ), $courseId );
        
        $sql = "UPDATE `{$qwz_tbl_names['qwz_tracking']}`
                SET   user_id  = ".(int)$uidToKeep."
                WHERE user_id  = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot change qwz_tracking in {$thisCourseCode}");
        }

        // Update user info in course
        $sql = "DELETE FROM `{$courseTbl['userinfo_content']}`
                WHERE user_id = ".(int)$uidToRemove;
        
        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot remove user info in {$thisCourseCode}");
        }
    }
    
    public static function mergeMainTrackingUsers( $uidToRemove, $uidToKeep )
    {
        $mainTbl = claro_sql_get_main_tbl();
        
        $sql = "UPDATE `{$mainTbl['tracking_event']}`
            SET   user_id = ".(int)$uidToKeep."
            WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot update tracking_event in main DB");
        }

    }
    
    public static function mergeCourseModuleUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $courseModuleList = module_get_course_tool_list( $courseId );
        
        foreach ( $courseModuleList as $courseModule )
        {
            $moduleMergeUserPath = get_module_path( $courseModule['label'] ) . '/connector/mergeuser.cnr.php';
            
            if ( file_exists( $moduleMergeUserPath ) )
            {
                require_once $moduleMergeUserPath;
                $moduleMergeClass = $courseModule['label'].'_MergeUser';
                
                if ( class_exists( $moduleMergeClass ) )
                {
                    $moduleMerge = new $moduleMergeClass;
                    
                    if ( method_exists( $moduleMerge, 'mergeCourseUsers' ) )
                    {
                        $moduleMerge->mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId );
                    }
                }
            }
        }
    }
    
    public static function mergeModuleUsers( $uidToRemove, $uidToKeep )
    {
        $courseModuleList = get_module_label_list();
        
        foreach ( $courseModuleList as $courseModule )
        {
            $moduleMergeUserPath = get_module_path( $courseModule['label'] ) . '/connector/mergeuser.cnr.php';
            
            if ( file_exists( $moduleMergeUserPath ) )
            {
                require_once $moduleMergeUserPath;
                $moduleMergeClass = $courseModule['label'].'_MergeUser';
                
                if ( class_exists( $moduleMergeClass ) )
                {
                    $moduleMerge = new $moduleMergeClass;
                    
                    if ( method_exists( $moduleMerge, 'mergeUsers' ) )
                    {
                        $moduleMerge->mergeUsers( $uidToRemove, $uidToKeep );
                    }
                }
            }
        }
    }
}

interface Module_MergeUser
{
    public function mergeUsers( $uidToRemove, $uidToKeep );
    public function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId );
}
