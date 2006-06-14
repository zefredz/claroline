<?php // $Id$

/**
 * CLAROLINE
 *
 * Class to manage profile and tool right (none, user, manager)
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLMAIN
 *
 * @author Claro Team <cvs@claroline.net>
 */

require_once dirname(__FILE__) . '/constants.inc.php';
require_once dirname(__FILE__) . '/profileToolAction.class.php';

class RightProfileToolRight extends RightProfileToolAction
{

    /**
     * Set the tool right (none, user, manager)
     *
     * @param integer $toolId tool identifier
     * @param string $right the right value
     */

    function setToolRight($toolId,$right)
    {
        if ( $right == 'none' )
        {
            $this->setAction($toolId,'read',false);
            $this->setAction($toolId,'edit',false);
        }
        elseif ( $right == 'user' )
        {
            $this->setAction($toolId,'read',true);
            $this->setAction($toolId,'edit',false);
        }
        elseif ( $right == 'manager' )
        {
            $this->setAction($toolId,'read',true);
            $this->setAction($toolId,'edit',true);
        }
    }

    /**
     * Get the tool right (none, user, manager)
     *
     * @param integer $toolId tool identifier
     */

    function getToolRight($toolId)
    {
        $readAction = (bool) $this->getAction($toolId,'read');
        $manageAction = (bool) $this->getAction($toolId,'edit');

        if ( $readAction ==  false && $manageAction == false )
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

    function setToolListRight($toolList,$right)
    {
        foreach ( $toolList as $toolId )
        {
             $this->setToolRight($toolId,$right);
        }
    }

    /**
     * Display table with tool/right of the profile 
     */

    function displayProfileToolRightList($mode='view')
    {
        global $imgRepositoryWeb;

        $html = '';
       
        $html .= '<table class="claroTable emphaseLine" >' . "\n"
        .    '<thead>' . "\n"
        .    '<tr class="headerX">' . "\n"
        .    '<th>' . get_lang('Tools') . '</th>' . "\n"
        .    '<th>' . get_lang('Right') . '</th>' . "\n"
        .    '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' ;

        foreach ( $this->toolActionList as $tool_id => $action_list )
        {
            $action_right = $this->getToolRight($tool_id);
        
            if ( $mode == 'edit' )
            {
                $param_append = '?profile_id=' . urlencode($this->profile->getId())
                              . '&amp;tool_id=' . urlencode($tool_id)
                              . '&amp;cmd=set_right' ;
            }

            $html .= '<tr>' . "\n"
            . '<td>' . claro_get_tool_name($tool_id) ; 

            if ( $mode == 'edit' )
            {
                $html .= '<br />'
                . '<small><a href="' . $_SERVER['PHP_SELF'] . $param_append . '&amp;right_value=none">' . get_lang('None') . '</a> - '
                . '<a href="' . $_SERVER['PHP_SELF'] . $param_append . '&amp;right_value=user">' . get_lang('User') . '</a> - '
                . '<a href="' . $_SERVER['PHP_SELF'] . $param_append . '&amp;right_value=manager">' . get_lang('Manager') . '</a></small>';
            }

            $html .= '</td>' . "\n" ;

            $html .= '<td align="center">';

            if ( $action_right == 'none' )
            {
                $html .= '-' . "\n" ;
            }
            elseif ( $action_right == 'user' )
            {
                $html .= '<img src="' . $imgRepositoryWeb . 'user.gif" alt="' . get_lang('User') . '" />' . "\n" ;
            }
            else
            {
                $html .= '<img src="' . $imgRepositoryWeb . 'manager.gif" alt="' . get_lang('Manager') . '" />' . "\n" ;
            }
        
            $html .= '</tr>' . "\n";

        }

        $html .= '</tbody></table>'; 
        
        return $html ;
    }
    
    /**
     * Display table with tool/right of the profile in edition mode
     */
    
    function displayProfileToolRightEditList()
    {
        return $this->displayProfileToolRightList('edit');
    }
}

?>
