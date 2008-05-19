<?php // $Id$
/**
 * CLAROLINE
 *
 * List profiles available on the platform
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package RIGHT
 *
 */

require '../../inc/claro_init_global.inc.php';

include_once get_path('incRepositorySys') . '/lib/right/profile.class.php';
include_once get_path('incRepositorySys') . '/lib/pager.lib.php';

// Security check

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

$error_list = array();

define('DISPLAY_LIST',__LINE__);
define('DISPLAY_FORM',__LINE__);

$display = DISPLAY_LIST;

// Main script

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;
$profile_id = isset($_REQUEST['profile_id'])?(int)$_REQUEST['profile_id']:null;

if ( $cmd )
{
    $profile = new RightProfile();

    if ( !empty($profile_id) )
    {
        // load profile
        if ( ! $profile->load($profile_id) )
        {
            $cmd = '';
            $profile_id = null;
            $display = DISPLAY_LIST ;
        }
    }

    if ( $cmd == 'exSave' )
    {
        if ( $profile->validateForm() )
        {
            $profile->save();
        }
        else
        {
            // get error message
            $message = '';

            if ( !empty($profile_id) ) $cmd = 'rqEdit';
            else                       $cmd = 'rqAdd';
        }
    }

    if ( $cmd == 'rqEdit' || $cmd == 'rqAdd' )
    {
        // create or edit a profile
        $form = $profile->displayProfileForm();
        $display = DISPLAY_FORM ;

    }

    if ( isset($profile_id) )
    {

        if ( $cmd == 'exDelete' )
        {
            $profile->delete();
        }

        if ( $cmd == 'exUnlock' || $cmd == 'exLock' )
        {
            // update locked status
            if ( $cmd == 'exUnlock' ) $profile->setIsLocked(false);
            if ( $cmd == 'exLock' ) $profile->setIsLocked(true);

            // save profile
            $profile->save();
        }

    }

}

// Build profile list

$itemPerPage = 10;

$tbl_mdb_names = claro_sql_get_main_tbl();
$tblProfile = $tbl_mdb_names['right_profile'];

$sql = " SELECT profile_id as id, name, description, locked, required
         FROM `" . $tblProfile . "`
         WHERE type = 'COURSE' ";

$offset = (isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) ) ? $_REQUEST['offset'] : 0;
$profilePager = new claro_sql_pager($sql,$offset, $itemPerPage);
$profileList = $profilePager->get_result_list();

// Display

// define breadcrumb
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools          = get_lang('Course profile list');
$noQUERY_STRING     = TRUE;

// Display header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

switch ( $display )
{
    case DISPLAY_FORM :

        // Display form

        if ( !empty($profile->id) )
        {
            echo claro_html_tool_title(get_lang('Add new profile'));
        }
        else
        {
            echo claro_html_tool_title(get_lang('Edit profile'));
        }

        if ( ! empty($form) )
        {
            echo claro_html_message_box($form);
        }

    case DISPLAY_LIST :

        // List of course profile

        echo claro_html_tool_title(get_lang('Course profile list'));

        echo '<p><a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqAdd">'
             . get_lang('Add new profile')
             . '</a></p>' . "\n" ;

        // Pager display
        echo $profilePager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

        // Display table header

        echo '<table class="claroTable emphaseLine" width="100%" >' . "\n"
            . '<thead>' . "\n"
            . '<tr class="headerX">' . "\n"
            . '<th>' . get_lang('Name') . '</th>' . "\n"
            . '<th>' . get_lang('Description') . '</th>' . "\n"
            . '<th>' . get_lang('Edit') .'</th>' . "\n"
            . '<th>' . get_lang('Rights') .'</th>' . "\n"
            . '<th>' . get_lang('Delete') .'</th>' . "\n"
            . '<th>' . get_lang('Lock') .'</th>' . "\n"
            . '</tr>' . "\n"
            . '</thead>' . "\n"
            . '<tbody>' ;

        foreach ( $profileList as $thisProfile )
        {
            echo '<tr align="center">' . "\n"
                . '<td align="left">' . get_lang($thisProfile['name']) . '</td>' . "\n"
                . '<td align="left">' . get_lang($thisProfile['description']) . '</td>' . "\n"
                . '<td><a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&profile_id='. $thisProfile['id'].'"><img src="' . get_path('imgRepositoryWeb') . 'edit.gif" alt="' . get_lang('Edit') . '" /></td>' . "\n"
                . '<td><a href="profile.php?display_profile='. $thisProfile['id'].'"><img src="' .  get_path('imgRepositoryWeb') . 'settings.gif" alt="' . get_lang('Edit') . '" /></td>' . "\n" ;

            if ( $thisProfile['required'] == '0' )
            {
                echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&profile_id='. $thisProfile['id'].'&amp;offset='.$offset.'"><img src="' . get_path('imgRepositoryWeb') . 'delete.gif" alt="' . get_lang('Delete') . '" /></td>' . "\n";
            }
            else
            {
                echo '<td>' . '-' . '</td>' . "\n";
            }

            if ( $thisProfile['locked'] == '0' )
            {
                echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?cmd=exLock&profile_id='. $thisProfile['id'].'&amp;offset='.$offset.'"><img src="' . get_path('imgRepositoryWeb') . 'unlock.gif" alt="' . get_lang('Lock') . '" /></td>' . "\n";
            }
            else
            {
                echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?cmd=exUnlock&profile_id='. $thisProfile['id'].'&amp;offset='.$offset.'"><img src="' . get_path('imgRepositoryWeb') . 'locked.gif" alt="' . get_lang('Unlock') . '" /></td>' . "\n";
            }
            echo '</tr>' . "\n\n";
        }

        echo '</tbody></table>';

        break;

} // end switch display

// Display footer
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>
