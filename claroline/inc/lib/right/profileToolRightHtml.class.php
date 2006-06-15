<?php // $Id$

/**
 * CLAROLINE
 *
 * Class to display manage profile and tool right (none, user, manager)
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
require_once dirname(__FILE__) . '/profileToolRight.class.php';

class RightProfileToolRightHtml 
{

    /**
     * @var $rightProfileToolRight RightProfileToolRight object
     */

    var $rightProfileToolRight;

    /**
     * @var $displayMode
     */

    var $displayMode = '';

    /**
     * @var $urlParamAppend
     */

    var $urlParamAppendList = array();

    /**
     * Constructor
     */

    function RightProfileToolRightHtml($rightProfileToolRight)
    {
        $this->rightProfileToolRight = &$rightProfileToolRight;
        $this->displayMode='view';
    }

    /**
     * Set display mode
     */    

    function setDisplayMode($value)
    {
        $this->displayMode = $value ;
    }

    /**
     * Set Url param append
     */

    function addUrlParam($paramName,$paramValue)
    {
        $this->urlParamAppendList[$paramName] = $paramValue;
    }

    /**
     * Display table with tool/right of the profile 
     */

    function displayProfileToolRightList()
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

        foreach ( $this->rightProfileToolRight->toolActionList as $tool_id => $action_list )
        {
            $action_right = $this->rightProfileToolRight->getToolRight($tool_id);
        
            if ( $this->displayMode == 'edit' )
            {
                $param_append = '?profile_id=' . urlencode($this->rightProfileToolRight->profile->getId())
                              . '&amp;tool_id=' . urlencode($tool_id)
                              . '&amp;cmd=set_right' ;

                foreach ( $this->urlParamAppendList as $name => $value )
                {
                    $param_append .= '&amp;' . $name . '=' . $value;
                }
            }

            $html .= '<tr>' . "\n"
            . '<td>' . claro_get_tool_name($tool_id) ; 

            if ( $this->displayMode == 'edit' )
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
    
}

?>
