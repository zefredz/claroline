<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */

class CourseHomePagePortletIterator implements CountableIterator
{
    private     $courseId;
    
    /**
     * @var Database_ResultSet
     */
    private     $portlets = array();
    
    public function __construct($courseId)
    {
        $this->courseId = $courseId;
        $courseCode     = ClaroCourse::getCodeFromId($this->courseId);
        
        $tbl_mdb_names          = claro_sql_get_main_tbl();
        $tbl_rel_course_portlet = $tbl_mdb_names['rel_course_portlet'];
        
        $sql = "SELECT id, courseId, rank, label, visible
                FROM `{$tbl_rel_course_portlet}`
                WHERE `courseId` = {$this->courseId}
                ORDER BY `rank` ASC";
        
        $this->portlets = Claroline::getDatabase()->query($sql);
    }
    
    public function rewind()
    {
        $this->portlets->rewind();
    }
    
    public function next()
    {
        $this->portlets->next();
    }
    
    public function key()
    {
        return $this->portlets->key();
    }
    
    public function current()
    {
        $portlet = $this->portlets->current();
        
        $portletObj = '';
        
        
        // Require the proper portlet class
        $portletPath = get_module_path( $portlet['label'] )
        . '/connector/coursehomepage.cnr.php';
        
        $portletName = $portlet['label'] . '_Portlet';
        
        if ( file_exists($portletPath) )
        {
            set_current_module_label($portlet['label']);
            
            load_module_config($portlet['label']);
            Language::load_module_translation($portlet['label']);
                
            require_once $portletPath;
            
            if (class_exists($portletName))
            {
                $courseCode     = ClaroCourse::getCodeFromId($this->courseId);

                $portletObj = new $portletName($portlet['id'], $courseCode,
                $portlet['courseId'], $portlet['rank'],
                $portlet['label'], $portlet['visible']);

                
            }
            else
            {
                echo get_lang("Can't find the class %portletName_portlet", array('%portletName' => $portletName));
                $portletObj = false;
            }
            
            clear_current_module_label();
            
            return $portletObj;
        }
        else
        {
            throw new Exception("Can\'t find the file %portletPath", array('%portletPath' => $portletPath));
        }
    }
    
    public function valid()
    {
        return $this->portlets->valid();
    }
    
    public function count()
    {
        return count( $this->portlets );
    }
}
