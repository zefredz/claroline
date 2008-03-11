<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package Auth
 *
 */


class myblocnotes extends portlet
{
    function __construct()
    {
    }
    
    function renderContent()
    {
        $output = '';
        
        $output = '<p><a class="claroCmd" href="">' . get_lang('Add note') . '</a></p>';
        
        $output .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
    	.    '<thead>' . "\n"
    	.      '<tr class="headerX" align="center" valign="top">' . "\n"
    	.	    '<th>' . get_lang('Notes') . '</th>' . "\n"
    	.	    '<th>' . get_lang('Edit') . '</th>' . "\n"
        .	    '<th>' . get_lang('Delete') . '</th>' . "\n"
        .      '</tr>' . "\n"
        .    '</thead>' . "\n"
        
        .    '<tbody>' . "\n"
        .      '<tr>' . "\n"
        .       '<td>Première note...</td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('Edit') . '" alt="' . get_lang('Edit') . '" /></td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('Delete') . '" alt="' . get_lang('Delete') . '" /></td>' . "\n"
        .      '</tr>' . "\n"
        .    '</tbody>' . "\n"
        .    '</table>' . "\n"
        ;
        
        return $output;
    }
    
    function renderTitle()
    {
        return get_lang('My Bloc-notes');
    }
}

?>