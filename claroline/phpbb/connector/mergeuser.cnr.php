<?php

class CLWIKI_MergeUser implements Module_MergeUser
{
    public function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $moduleCourseTbl = get_module_course_tbl( array('bb_posts'), $courseId );
        
        $sql = "UPDATE `{$moduleCourseTbl['bb_posts']}`
                SET   poster_id = ".(int)$uidToKeep."
                WHERE poster_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot update wiki_pages in {$thisCourseCode}");
        }
    }
    
    public function mergeUsers( $uidToRemove, $uidToKeep )
    {
        // empty
    }
}
