<?php // $Id$

/**
 * CLAROLINE
 *
 * Edit right & action of a profile
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

include_once $includePath . '/lib/right/profileToolRight.class.php';
include_once $includePath . '/lib/right/profileToolRightHtml.class.php';

// Security check

if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

// Init section

$error_list = array();

// Main section

$profile_id = isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:null;
$tool_id = isset($_REQUEST['tool_id'])?$_REQUEST['tool_id']:null;
$right_value = isset($_REQUEST['right_value'])?$_REQUEST['right_value']:null;
$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;

if ( !empty($profile_id) )
{
    // load profile
    $profile = new RightProfile();

    if ( $profile->load($profile_id) )
    {
        // load profile tool right    
        $profileRight = new RightProfileToolRight();
        $profileRight->load($profile);

        // update tool right
        if ( $cmd == 'set_right' && !empty($tool_id) )
        {
            $profileRight->setToolRight($tool_id,$right_value);
            $profileRight->save();
        }

    }
    else
    {
        $profile_id = null;
    }
}

// Display section

// define bredcrumb
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[] = array ('url' => $rootAdminWeb . 'right/profile_list.php', 'name' => get_lang('Course Profile List'));

if ( !empty($profile_id) )
{
    $interbredcrump[] = array ('url' => $rootAdminWeb . 'right/profile.php?profile_id=' . $profile->getId(), 'name' => $profile->getName() );
}

// Display header

include $includePath . '/claro_init_header.inc.php';

if ( !empty($profile_id) )
{
    // display tool title
    echo claro_html_tool_title(array('mainTitle'=>get_lang('Course Profile'),'subTitle'=>$profile->getName()));

    $profileRightHtml = new RightProfileToolRightHtml($profileRight);
    $profileRightHtml->setDisplayMode('edit');
    echo $profileRightHtml->displayProfileToolRightList();
}
else
{
    echo claro_html_message_box(get_lang('Profile not found'));
}

// Display footer

include $includePath . '/claro_init_footer.inc.php';

?>
