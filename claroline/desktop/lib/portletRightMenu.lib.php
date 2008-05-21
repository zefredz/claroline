<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
* CLAROLINE
*
* User account summary
* TODO : Merge with user account display in tracking and move to inc/lib
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

class PortletRightMenu
{

    private $content = '';

    // render content
    public function renderContent()
    {

        $userData = user_get_properties( claro_get_current_user_id() );

        $picturePath = user_get_picture_path( $userData );

        if ( $picturePath && file_exists( $picturePath ) )
        {
            $pictureUrl = user_get_picture_url( $userData );
        }
        else
        {
            $pictureUrl = get_icon_url('nopicture');
        }

        $output = '<div class="portletRightMenu">' . "\n"
        .    '<div class="header portletTitle">' . "\n"

        .    '<span class="porletIcon">' . "\n"
        .      '<a href="../../claroline/auth/profile.php">' . "\n"
        .        '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('edit') . '" />' . "\n"
        .      '</a>' . "\n"
        .    '</span>' . "\n"
        .      htmlspecialchars($userData['firstname']) . '&nbsp;' . htmlspecialchars($userData['lastname'])
        .    '</div>' . "\n"
        .    '<div class="portletContent" id="portletMyprofil">' . "\n"
        .     '  <div id="picture"><img src="' . $pictureUrl . '" alt="' . get_lang('avatar') . '" /></div>' . "\n"
        .     '    <div id="details">'
        .     '      <p><span>' . get_lang('Email') . '</span><br /> ' . htmlspecialchars($userData['email']) . '</p>' . "\n"
        .     '    </div>' . "\n"
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
