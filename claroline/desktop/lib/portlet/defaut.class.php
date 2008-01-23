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
        // user's data
        $userData = user_get_properties(claro_get_current_user_id());

        $output .= '<div id="userCart">' . "\n"
        .	 ' <div id="picture"><div style="border: 1px solid #AAA; background-color: #DDD; width: 100px; height: 125px; margin: auto;font-size: small; color: #AAA;"><br /><br /><br />No picture</div></div>' . "\n"
        .	 ' <div id="details">'
        .	 '  <p><span>' . get_lang('Last name') . '</span><br /> ' . htmlspecialchars($userData['lastname']) . '</p>'
        .	 '  <p><span>' . get_lang('First name') . '</span><br /> ' . htmlspecialchars($userData['firstname']) . '</p>'
        .	 '  <p><span>' . get_lang('Email') . '</span><br /> ' . htmlspecialchars($userData['email']) . '</p>'
        .	 ' </div>' . " \n"
        .	 '</div>' . "\n"
        .	 '<div class="spacer"></div>' . "\n";
        
        $this->title = get_lang('My profil');
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