<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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

    function RightCourseProfileToolRight()
    {
        $this->RightProfileToolAction();
        $this->RightProfileToolRight();
    }

    /**
     * Load rights of a profile/course
     */

    function load($profile)
    {
        // Load toolAction of the parent
        parent::load($profile);

        $this->defaultToolActionList = $this->getToolActionList();

        // load value of action of the courseId
        $sql = " SELECT PA.action_id, PA.value, A.tool_id, A.name
                 FROM `" . $this->tbl['rel_profile_action'] . "` `PA`,
                      `" . $this->tbl['action'] . "` `A`
                 WHERE PA.profile_id = " . $this->profile->id . "
                 AND PA.action_id = A.id
                 AND PA.courseId = '" . addslashes($this->courseId) . "'";

        $action_list = claro_sql_query_fetch_all($sql);

        // load all actions value for the profile
        foreach ( $action_list as $this_action )
        {
            $actionName = $this_action['name'];
            $actionValue = (bool) $this_action['value'];
            $toolId = $this_action['tool_id'];

            if ( isset($this->toolActionList[$toolId][$actionName]) )
            {
                $this->toolActionList[$toolId][$actionName] = $actionValue;
            }
        }
    }

    /**
     * Save profile tool list action value
     */

    function save()
    {
        // delete all relation
        $sql = "DELETE FROM `" . $this->tbl['rel_profile_action'] . "`
                WHERE profile_id=" . $this->profile->id . "
                AND courseId = '" . addslashes($this->courseId) . "'";

        claro_sql_query($sql);

        // insert new relation

        foreach ( $this->toolActionList as $toolId => $actionList )
        {
            // get difference between default and course
            $toolActionListDiff = array_diff_assoc($this->defaultToolActionList[$toolId],$actionList);

            if ( !empty($toolActionListDiff) )
            {
                foreach ( $actionList as $actionName => $actionValue )
                {
                    if ( $actionValue == true ) $actionValue = 1;
                    else                        $actionValue = 0;

                    $action = new RightToolAction();

                    $action->load($actionName, $toolId);

                    $actionId = $action->getId();

                    $sql = "INSERT INTO `" . $this->tbl['rel_profile_action'] . "`
                            SET profile_id = " . $this->profile->id . ",
                            action_id = " . $actionId . ",
                            value = " . $actionValue . ",
                            courseId = '" . addslashes($this->courseId) . "'";

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
        $this->courseId = $value;
    }
    
    /**
     * Reset the values of the profile/course
     */

    function reset()
    {
        // Empty tool action list
        $this->toolActionList = array();

        // Set tool action list to default values
        $this->toolActionList = $this->defaultToolActionList ;

        // Delete all relations
        $sql = "DELETE FROM `" . $this->tbl['rel_profile_action'] . "`
                WHERE profile_id=" . $this->profile->id . "
                AND courseId = '" . addslashes($this->courseId) . "'";

        return claro_sql_query($sql);
    }

}

?>
