<?php

class Claro_Context
{
    public static function getCurrentContext()
    {
        $context = array();
        
        if (claro_is_in_a_course())
        {
            $context['cid'] = claro_get_current_course_id();
        }

        if (claro_is_in_a_group())
        {
            $context['gid'] = claro_get_current_group_id();
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
        
        return $context();
    }
}
