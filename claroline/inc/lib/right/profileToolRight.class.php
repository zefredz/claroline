<?php // $Id$

/**
 * CLAROLINE
 *
 * Class to manage profile and tool right (none, user, manager)
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.right
 * @author      Claro Team <cvs@claroline.net>
 */
require_once dirname ( __FILE__ ) . '/constants.inc.php';
require_once dirname ( __FILE__ ) . '/profileToolAction.class.php';

/**
 * Right of a profile
 */
class RightProfileToolRight extends RightProfileToolAction
{

    /**
     * Set the tool right (none, user, manager)
     *
     * @param integer $toolId tool identifier
     * @param string $right the right value
     */
    public function setToolRight ( $toolId, $right )
    {
        if ( $right == 'none' )
        {
            $this->setAction ( $toolId, 'read', false );
            $this->setAction ( $toolId, 'edit', false );
        }
        elseif ( $right == 'user' )
        {
            $this->setAction ( $toolId, 'read', true );
            $this->setAction ( $toolId, 'edit', false );
        }
        elseif ( $right == 'manager' )
        {
            $this->setAction ( $toolId, 'read', true );
            $this->setAction ( $toolId, 'edit', true );
        }
    }

    /**
     * Get the tool right (none, user, manager)
     *
     * @param integer $toolId tool identifier
     */
    public function getToolRight ( $toolId )
    {
        $readAction = (bool) $this->getAction ( $toolId, 'read' );
        $manageAction = (bool) $this->getAction ( $toolId, 'edit' );

        if ( $readAction == false && $manageAction == false )
        {
            return 'none';
        }
        elseif ( $readAction == true && $manageAction == false )
        {
            return 'user';
        }
        else
        {
            return 'manager';
        }
    }

    /**
     * Set right of the tool list
     */
    public function setToolListRight ( $toolList, $right )
    {
        foreach ( $toolList as $toolId )
        {
            $this->setToolRight ( $toolId, $right );
        }
    }

}
