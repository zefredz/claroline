<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Context handling library
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     linker
 */

defined('CLARO_CONTEXT_PLATFORM')     || define('CLARO_CONTEXT_PLATFORM','platform');
defined('CLARO_CONTEXT_COURSE')       || define('CLARO_CONTEXT_COURSE','course');
defined('CLARO_CONTEXT_GROUP')        || define('CLARO_CONTEXT_GROUP','group');
defined('CLARO_CONTEXT_USER')         || define('CLARO_CONTEXT_USER','user');
defined('CLARO_CONTEXT_TOOLINSTANCE') || define('CLARO_CONTEXT_TOOLINSTANCE','toolInstance');
defined('CLARO_CONTEXT_TOOLLABEL')    || define('CLARO_CONTEXT_TOOLLABEL','toolLabel');
defined('CLARO_CONTEXT_MODULE')       || define('CLARO_CONTEXT_MODULE','moduleLabel');

class Claro_Context
{
    public static function getCurrentContext()
    {
        if (claro_is_in_a_course())
        {
            $context[CLARO_CONTEXT_COURSE] = claro_get_current_course_id();
        }

        if (claro_is_in_a_group())
        {
            $context[CLARO_CONTEXT_GROUP] = claro_get_current_group_id();
        }
        
        if ( claro_is_in_a_tool() )
        {
            if ( isset($GLOBALS['tlabelReq']) && $GLOBALS['tlabelReq'] )
            {
                $context[CLARO_CONTEXT_TOOLLABEL] = $GLOBALS['tlabelReq'];
            }
            
            if ( claro_get_current_tool_id() )
            {
                $context[CLARO_CONTEXT_TOOLINSTANCE] = claro_get_current_tool_id();
            }
        }
        
        if ( get_current_module_label() )
        {
            $context[CLARO_CONTEXT_MODULE] = get_current_module_label();
        }
        
        return $context;
    }
    
    
    public static function getCurrentUrlContext()
    {
        $context = array();
        
        if (claro_is_in_a_course())
        {
            $context['cidReq'] = claro_get_current_course_id();
        }

        if (claro_is_in_a_group())
        {
            $context['gidReq'] = claro_get_current_group_id();
        }
        
        if( isset( $_REQUEST['inPopup'] ) )
        {
            $context['inPopup'] = $_REQUEST['inPopup'];
        }
        
        if( isset( $_REQUEST['inFrame'] ) )
        {
            $context['inFrame'] = $_REQUEST['inFrame'];
        }
        
        if( isset( $_REQUEST['embedded'] ) )
        {
            $context['embedded'] = $_REQUEST['embedded'];
        }
        
        if( isset( $_REQUEST['hide_banner'] ) )
        {
            $context['hide_banner'] = $_REQUEST['hide_banner'];
        }
        
        if( isset( $_REQUEST['hide_footer'] ) )
        {
            $context['hide_footer'] = $_REQUEST['hide_footer'];
        }
        
        if( isset( $_REQUEST['hide_body'] ) )
        {
            $context['hide_body'] = $_REQUEST['hide_body'];
        }
        
        if ( $moduleLabel = claro_called_from() )
        {
            $context['calledFrom'] = $moduleLabel;
        }
        
        return $context;
    }
}
