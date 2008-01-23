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
 
require_once dirname(__FILE__) . '/../../../../claroline/inc/lib/user.lib.php';

class defaut extends portlet
{
    function __construct()
    {
        $output = '';
        
        $userData = user_get_properties(claro_get_current_user_id());

        $output .= 
             '<div id="portletUser">' . "\n"
        .	 ' <div id="picture"><div class="pic"><br /><br /><br />No picture</div></div>' . "\n"
        .	 ' <div id="details">'
        .	 '  <p><span>' . get_lang('Last name') . '</span><br /> ' . htmlspecialchars($userData['lastname']) . '</p>' . "\n"
        .	 '  <p><span>' . get_lang('First name') . '</span><br /> ' . htmlspecialchars($userData['firstname']) . '</p>' . "\n"
        .	 '  <p><span>' . get_lang('Email') . '</span><br /> ' . htmlspecialchars($userData['email']) . '</p>' . "\n"
        .	 ' </div>' . "\n"
        .	 '</div>' . "\n"
        ;
                
        $this->title = get_lang('My profil') . '<span class="test"><a href="../../claroline/auth/profile.php"><img src="' . get_icon('edit') . '" alt="' . get_lang('edit') . '" /></a></span>' . "\n";
        $this->content = $output;
    }
    
    function renderContent()
    {
        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title;
    }
}

?>