<?php // $Id$

/**
 * CLAROLINE
 *
 * Class to manage relation between profile and tool action in a course
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package RIGHT
 *
 * @author Claro Team <cvs@claroline.net>
 */

require_once dirname(__FILE__) . '/constants.inc.php';
require_once dirname(__FILE__) . '/profileToolRight.class.php';

class RightCourseProfileToolRight extends RightProfileToolRight
{

    /**
     * @var $courseId
     */

    var $courseId ;
    
    /**
     * @array $defaultToolActionList list action of the profile and their values
     */

    var $defaultToolActionList = array(); 

    /**
     * Constructor
     */
     
    function RightProfileToolActionCourse()
    {
        $this->RightProfileToolAction();
        $this->RightProfileToolRight();
    }    

    /**
     * Load rights of a profile/course
     */

    function load(&$profile)
    {
        // Load toolAction of the parent
        parent::load($profile);

        $defaultToolActionList = $this->getToolActionList();

        // load value of action of the courseId
        $sql = " SELECT PA.action_id, A.tool_id, A.name
                 FROM `" . $this->tbl['rel_profile_action'] . "` `PA`,
                      `" . $this->tbl['action'] . "` `A`
                 WHERE PA.profile_id = " . $this->profile->id . "
                 AND PA.action_id = A.id 
                 AND PA.courseId = '" . addslashes($this->courseId) . "'";

        $action_value_result = claro_sql_query_fetch_all($sql);

        // load all actions value for the profile
        foreach ( $action_value_result as $action_value )
        {   
            $actionName = $action_value['name'];
            $toolId = $action_value['tool_id'];

            if ( isset($this->toolActionList[$toolId][$actionName]) )
            {
                $this->toolActionList[$toolId][$actionName] = true;
            }
        }
        
    }

    /**
     * Save profile tool list action value
     */

    function save()
    {
        // difference between default and course
    
        /*
        array_diff_assoc();
        $this->toolActionList;
        $this->defaultToolActionList; 
        */

        // delete all relation
        $sql = "DELETE FROM `" . $this->tbl['rel_profile_action'] . "`
                WHERE profile_id=" . $this->profile->id . "
                AND courseId = '" . addslashes($this->courseId) . "'";

        claro_sql_query($sql);        

        // insert new relation

        foreach ( $this->toolActionList as $toolId => $actionList )
        {
            foreach ( $actionList as $actionName => $actionValue )
            {            
                if ( $actionValue == true )
                {
                    $action = new RightToolAction();

                    $action->load($actionName, $toolId);

                    $actionId = $action->getId();
        
        
                    $sql = "INSERT INTO `" . $this->tbl['rel_profile_action'] . "`
                            SET profile_id = " . $this->profile->id . ", 
                            action_id = " . $actionId . ", 
                            courseId = '" . addslashes($courseId) . "'";

                    claro_sql_query($sql);        
                }
            }
        }        
    }

    /**
     * Get courseId
     */

    function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set courseId
     */

    function setCourseId($value)
    {
        $this->courseId($value);
    }

}

?>
