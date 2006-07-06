<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Class to manage tool action
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

class RightToolAction
{
    var $name ;
    var $description ;
    var $toolId ;
    var $type ;
    var $tbl = array();

    /**
     * Constructor
     */

    function RightToolAction ()
    {
        $this->id = '';
        $this->name = '';
        $this->description = '';
        $this->toolId = '';
        $this->type = PROFILE_TYPE_COURSE;

        $tbl_mdb_names = claro_sql_get_main_tbl();
        $this->tbl['action'] = $tbl_mdb_names['right_action'];
        $this->tbl['rel_profile_action'] = $tbl_mdb_names['right_rel_profile_action'];
    }

    /**
     * Load action from DB
     *
     * @param $action_name
     * @param $toolId
     * @return boolean load successfull ?
     */

    function load ($actionName,$toolId)
    {
        $sql = "SELECT id,
                       name,
                       description,
                       tool_id,
                       type
                FROM `" . $this->tbl['action'] . "`
                WHERE name = '" . addslashes($actionName) . "'
                AND `tool_id` =  " . (int) $toolId ;

        $data = claro_sql_query_get_single_row($sql);

        if ( !empty($data) )
        {
            $this->id = $data['id'];
            $this->name = $data['name'];
            $this->description = $data['description'];
            $this->toolId = $data['tool_id'];
            $this->type = $data['type'];

            return true;
        }
        else
        {
            return false;
        }

    }

    /**
     * Save action
     */

    function save ()
    {
        if ( empty($this->name) || empty($this->toolId) || empty($this->type) )
        {
            return false;
        }
        elseif ( ! $this->exists() )
        {
            // insert action
            $sql = "INSERT INTO `" . $this->tbl['action'] . "`
                    SET `name` = '" . addslashes($this->name) . "',
                        `description` = '" . addslashes($this->description) . "',
                        `type` = '" . addslashes($this->type) . "',
                        `tool_id` =" . (int)$this->toolId ;

            return claro_sql_query($sql);
        }
        else
        {
            // update action
            $sql = "UPDATE `" . $this->tbl['action'] . "`
                    SET `description` = '" . addslashes($this->description) . "'
                    WHERE name ='" . addslashes($this->name) . "' AND
                          type ='" . addslashes($this->type) . "' AND
                          tool_id = " . (int) $this->toolId ;

            return claro_sql_query($sql);
        }
    }

    /**
     * Delete action
     */

    function delete()
    {
        // Delete from rel_profile_action
        $sql = "DELETE FROM `" . $this->tbl['rel_profile_action'] . "`
                WHERE action_id = " . (int) $this->id ;
        claro_sql_query($sql);

        // Delete from action
        $sql = "DELETE FROM `" . $this->tbl['action'] . "`
                WHERE id = " . (int) $this->id ;

        claro_sql_query($sql);

        $this->id = -1;

        return true;
    }

    /**
     * Check if action already exists
     */

    function exists()
    {
        $sql = " SELECT count(*)
                 FROM `" . $this->tbl['action'] . "`
                 WHERE name ='" . addslashes($this->name) . "' AND
                       type ='" . addslashes($this->type) . "' AND
                       tool_id = " . (int) $this->toolId ;

        if ( claro_sql_query_get_single_value($sql) == 0 ) return false;
        else                                               return true;
    }

    /**
     * Get action id
     */

    function getId()
    {
        return $this->id;
    }

    /**
     * Get action name
     */

    function getName()
    {
        return $this->name;
    }

    /**
     * Get action description
     */

    function getDescription()
    {
        return $this->description;
    }

    /**
     * Get tool identifier
     */

    function getToolId()
    {
        return $this->toolId;
    }

    /**
     * Get type
     */

    function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     */

    function setName($value)
    {
        $this->name = $value;
    }

    /**
     * Set description
     */

    function setDescription($value)
    {
        $this->description=$value;
    }

    /**
     * Set tool identifier
     */

    function setToolId($value)
    {
        $this->toolId=$value;
    }

    /**
     * set type
     */

    function setType($value)
    {
        $this->type=$value;
    }
}

?>
