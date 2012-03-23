<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Merge User class for the Wiki tool.
 *
 * @version     1.9 $Revision$
 * @copyright   (c) 2001-2012, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      claroline Team <cvs@claroline.net>
 * @package     CLWIKI
 */

class CLWIKI_MergeUser implements Module_MergeUser
{
    public function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $moduleCourseTbl = get_module_course_tbl( array('wiki_pages', 'wiki_pages_content'), $courseId );
        
        // Update wiki_pages
        $sql = "UPDATE `{$moduleCourseTbl['wiki_pages']}`
                SET   owner_id = ".(int)$uidToKeep."
                WHERE owner_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            Console::error("Cannot update wiki_pages from -{$uidToRemove} to +{$uidToKeep} in {$courseId}");
            return false;
        }
        
        // Update wiki_pages_content
        $sql = "UPDATE `{$moduleCourseTbl['wiki_pages_content']}`
                SET   editor_id = ".(int)$uidToKeep."
                WHERE editor_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            Console::error("Cannot update wiki_pages_content from -{$uidToRemove} to +{$uidToKeep} in {$courseId}");
            return false;
        }
        
        return true;
        
    }
    
    public function mergeUsers( $uidToRemove, $uidToKeep )
    {
        // empty
        return true;
    }
}
