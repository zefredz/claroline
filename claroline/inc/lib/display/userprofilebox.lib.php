<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

require_once __DIR__ . '/../user.lib.php';

 /**
 * CLAROLINE
 *
 * User account summary.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claroline team <info@claroline.net>
 * @package     kernel.display
 */

/**
 * Profile box of a user
 */
class UserProfileBox implements Display
{
    protected $condensedMode;
    protected $userId;
    
    /**
     * Create a profile box
     * @param bool $condensedMode display a consensed profile box instead of the full one (default false)
     * @param int $userId optional user id, if not given the current user will be used
     */
    public function __construct( $condensedMode = false, $userId = null )
    {
        $this->condensedMode = $condensedMode;
        $this->userId = $userId ? $userId : claro_get_current_user_id();
    }
    
    /**
     * Set the user id
     * @param int $userId
     */
    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }
    
    
    /**
     * Render the profile box in HTML
     * @see Display
     * @return string
     */
    public function render()
    {
        CssLoader::getInstance()->load( 'profile', 'all' );
        
        load_kernel_config('user_profile');
        
        $userData = user_get_properties( $this->userId );
        
        $pictureUrl = '';
        
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
        
        $userFullName = claro_htmlspecialchars(
            get_lang('%firstName %lastName',
                array('%firstName' => $userData['firstname'],
                      '%lastName' => $userData['lastname'])
            )
        );
        
        $dock = new ClaroDock('userProfileBox');
        
        $template = new CoreTemplate('user_profilebox.tpl.php');
        $template->assign('userId', $this->userId);
        $template->assign('pictureUrl', $pictureUrl);
        $template->assign('userFullName', $userFullName);
        $template->assign('dock', $dock);
        $template->assign('condensedMode', $this->condensedMode);
        $template->assign('userData', $userData);
        
        return $template->render();
    }
}
