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


class mybox extends portlet
{
    function __construct()
    {
    }
    
    function renderContent()
    {
	
        $output = '<ul id="navlist">' . "\n"
    	.	 '<li><a class="current" href="' . $_SERVER['PHP_SELF'] . '">' . get_lang('Inbox') . '</a></li>' . "\n"
        .    '<li><a  href="' . $_SERVER['PHP_SELF'] . '">' . get_lang('Outbox') . '</a></li>' . "\n"
        .    '<li><a  href="' . $_SERVER['PHP_SELF'] . '">' . get_lang('Archives') . '</a></li>' . "\n"
        .    '<li><a  href="' . $_SERVER['PHP_SELF'] . '">' . get_lang('Bloqués') . '</a></li>' . "\n"
        .    '</ul>' . "\n\n"
        ;
        
        $output .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
    	.    '<thead>' . "\n"
    	.      '<tr class="headerX" align="center" valign="top">' . "\n"
    	.	    '<th>&nbsp;</th>' . "\n"
    	.	    '<th>' . get_lang('De :') . '</th>' . "\n"
    	.	    '<th>' . get_lang('Sujet :') . '</th>' . "\n"
    	.	    '<th>' . get_lang('Reçu : ') . '</th>' . "\n"
    	.	    '<th>' . get_lang('Archiver') . '</th>' . "\n"
    	.	    '<th>' . get_lang('Delete') . '</th>' . "\n"
    	.	    '<th>' . get_lang('Bloquer') . '</th>' . "\n"
        .      '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        .      '<tr>' . "\n"
        .       '<td align="center"><img src="' . get_icon('email') . '" alt="' . get_lang('email') . '" /></td>' . "\n"
        .       '<td>Marcel</td>' . "\n"
        .       '<td>Promotion</td>' . "\n"
        .       '<td align="center">' . claro_html_localised_date( get_locale('dateFormatLong'), time() ) . '</td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('move') . '" alt="' . get_lang('move') . '" /></td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('Delete') . '" alt="' . get_lang('Delete') . '" /></td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('block') . '" alt="' . get_lang('block') . '" /></td>' . "\n"
        .      '</tr>' . "\n"
        
        .      '<tr>' . "\n"
        .       '<td align="center"><img src="' . get_icon('emaillu') . '" alt="' . get_lang('emaillu') . '" /></td>' . "\n"
        .       '<td>Fred</td>' . "\n"
        .       '<td>Bonjour</td>' . "\n"
        .       '<td align="center">' . claro_html_localised_date( get_locale('dateFormatLong'), time() ) . '</td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('move') . '" alt="' . get_lang('move') . '" /></td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('Delete') . '" alt="' . get_lang('Delete') . '" /></td>' . "\n"
        .       '<td align="center"><img src="' . get_icon('block') . '" alt="' . get_lang('block') . '" /></td>' . "\n"
        .      '</tr>' . "\n"
        .    '</tbody>' . "\n"
        .    '</table>' . "\n"
        ;
        
        return $output;
    }
    
    function renderTitle()
    {
        $this->title = get_lang('My MailBox');
        return $this->title;
    }
}

?>