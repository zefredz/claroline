<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

FromKernel::uses('user.lib');

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

class UserProfileBox implements Display
{

    protected $condensedMode;
    
    public function __construct( $condensedMode = false )
    {
        $this->condensedMode = $condensedMode;
    }

    // render content
    public function render()
    {
        CssLoader::getInstance()->load( 'profile', 'all' );
        
        load_kernel_config('user_profile');
        
        $userData = user_get_properties( claro_get_current_user_id() );
        
        if ( get_conf('allow_profile_picture') )
        {
            $picturePath = user_get_picture_path( $userData );
            
            if ( $picturePath && file_exists( $picturePath ) )
            {
                $pictureUrl = user_get_picture_url( $userData );
            }
            else
            {
                $pictureUrl = get_icon_url('nopicture');
            }
        }
        $output = '<div id="userProfileBox">' . "\n"
            . '<div class="header" id="userProfileTitle">' . "\n"
            . ($this->condensedMode ? '<a href="'.get_path('clarolineRepositoryWeb').'desktop/index.php">' : '')
            . htmlspecialchars($userData['firstname']) . '&nbsp;' . htmlspecialchars($userData['lastname'])
            . ($this->condensedMode ? '</a>' : '')
            . '</div>' . "\n"
            . '<div id="userProfile">' . "\n"
            ;
        
        if ( get_conf('allow_profile_picture') )
        {
            $output .= '<div id="userPicture"><img src="' . $pictureUrl . '" alt="' . get_lang('avatar') . '" /></div>' . "\n";
        }
        
        $output .='<div id="userDetails">'
            . '<p><span>' . get_lang('User') . '</span><br /> ' . htmlspecialchars(get_lang('%firstName %lastName', array('%firstName' => $userData['firstname'], '%lastName' => $userData['lastname']) ) ) . '</p>' . "\n"
            . '<p><span>' . get_lang('Email') . '</span><br /> '
            . (!empty($userData['email']) ? htmlspecialchars($userData['email']) : '-' )
            . '</p>' . "\n"
            ;
        
        if ( ! $this->condensedMode )
        {
            $output .= '<p><span>' . get_lang('Phone') . '</span><br /> '
                . (!empty($userData['phone']) ? htmlspecialchars($userData['phone']) : '-' )
                . '</p>' . "\n"
                . '<p><span>' . get_lang('Administrative code') . '</span><br /> '
                . (!empty($userData['officialCode']) ? htmlspecialchars($userData['officialCode']) : '-' )
                . '</p>' . "\n"
                ;
        }
        
        $output .= '<p>'
            . '<a class="claroCmd" href="'.get_path('clarolineRepositoryWeb').'auth/profile.php">' . "\n"
            . '<img src="' . get_icon_url('edit') . '" alt="" />' . "\n"
            . ' ' . get_lang('Edit')
            . '</a>'
            . '</p>'
            ;
            
        $dock = new ClaroDock( 'userProfileBox' );
        
        $output .= '</div>' . "\n" // details
            . '</div>' . "\n" // portletContent
            . ( !$this->condensedMode ? '<div id="userProfileBoxDock">'. $dock->render() .'</div>' : '' )
            . '</div>' . "\n" // portletRightMenu
            ;

        $this->content = $output;

        return $this->content;
    }
}
