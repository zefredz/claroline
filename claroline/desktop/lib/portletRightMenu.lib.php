<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLPAGES
 *
 * @author Claroline team <info@claroline.net>
 *
 */
    // vim: expandtab sw=4 ts=4 sts=4 foldmethod=marker:

    
    class portletrightmenu 
    {
    
        //private $title = '';
        private $content = '';
        /*        
        // render title
        public function renderTitle()
        {
            return $this->title;
        }
        */
        // render content
        public function renderContent()
        {
    
            $userData = user_get_properties( claro_get_current_user_id() );
            
            $output = '<div class="portletRightMenu">' . "\n"
            .    '<div class="portletTitle">' . "\n"
            .      '<a class="porletIcon" href="../../claroline/auth/profile.php">' . "\n"
            .        '<img src="' . get_icon('edit') . '" alt="' . get_lang('edit') . '" />' . "\n"
            .      '</a>' . "\n"
            .      htmlspecialchars($userData['firstname']) . '&nbsp;' . htmlspecialchars($userData['lastname'])
            .    '</div>' . "\n"
            .    '<div class="portletContent" id="portletMyprofil">' . "\n"
            .	 '  <div id="picture"><div class="pic"><br /><br /><br />No picture</div></div>' . "\n"
            .	 '    <div id="details">'
            //.	 '      <p><span>' . get_lang('Last name') . '</span><br /> ' . htmlspecialchars($userData['lastname']) . '</p>' . "\n"
            //.	 '      <p><span>' . get_lang('First name') . '</span><br /> ' . htmlspecialchars($userData['firstname']) . '</p>' . "\n"
            .	 '      <p><span>' . get_lang('Email') . '</span><br /> ' . htmlspecialchars($userData['email']) . '</p>' . "\n"
            .	 '      <p><a href="">' . get_lang('Send message') . '</a></p>' . "\n"
            .	 '    </div>' . "\n"
            .    '  </div>' . "\n"
            .    '</div>' . "\n"
            ;
            
            $this->content = $output;
            
            return $this->content;
        }

        // render all
        public function render()
        {
            return $this->renderContent();
        }

    }

?>