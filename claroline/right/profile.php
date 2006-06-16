<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool manage profile of the course
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 *
 * @package RIGHT
 *
 */

require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Course profile');
$dialogBox = '';
$tidReset = true;

if ( ! $_cid || ! $_uid) claro_disp_auth_form(true);

$is_allowedToEdit = $is_courseAdmin;

if ( ! $is_allowedToEdit )
{
    claro_die(get_lang('Not allowed'));
}

require_once $includePath . '/lib/right/courseProfileToolAction.class.php' ;
require_once $includePath . '/lib/right/profileToolRightHtml.class.php' ;
require_once $includePath . '/lib/pager.lib.php';

// Main section

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;
$display = isset($_REQUEST['display'])?$_REQUEST['display']:null;
$profile_id = isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:null;
$tool_id = isset($_REQUEST['tool_id'])?$_REQUEST['tool_id']:null;
$right_value = isset($_REQUEST['right_value'])?$_REQUEST['right_value']:null;

if ( !empty($profile_id) )
{
    // load profile
    $profile = new RightProfile();

    if ( $profile->load($profile_id) )
    {
        // load profile tool right    
        $courseProfileRight = new RightCourseProfileToolRight();
        $courseProfileRight->setCourseId($_cid);
        $courseProfileRight->load($profile);

        if ( ! $profile->isLocked() )
        {
            if ( $cmd == 'set_right' && !empty($tool_id) )
            {        
                $courseProfileRight->setToolRight($tool_id,$right_value);
                $courseProfileRight->save();
            }
        }
    }
    else
    {
        $profile_id = null;
    }
}

// Display section

// define bredcrumb
$interbredcrump[] = array ('url' => 'profile_list.php', 'name' => get_lang('Course Profile List'));

if ( !empty($profile_id) )
{
      $nameTools = get_lang('Course profile : %name',array('%name' => $profile->getName() ));
//    $interbredcrump[] = array ('url' => 'profile.php?profile_id=' . $profile->getId(), 'name' => $profile->getName() );
}

// Display header

include $includePath . '/claro_init_header.inc.php';

if ( !empty($profile_id) )
{
    // display tool title
    echo claro_html_tool_title(array('mainTitle'=>get_lang('Course Profile'),'subTitle'=>$profile->getName()));

    if ( $profile->isLocked() )
    {
        $display = 'view';
        echo '<p><em>' . get_lang('The profile is locked') . '</em></p>' . "\n" ;
    }
    else
    {
        // Display edit link
        echo '<p>' 
        . '<a href="' . $_SERVER['PHP_SELF'] . '?profile_id=' . $profile->getId() . '&amp;display=view">' . get_lang('View') . '</a>' 
        . ' - '
        . '<a href="' . $_SERVER['PHP_SELF'] . '?profile_id=' . $profile->getId() . '&amp;display=edit">' . get_lang('Edit') . '</a>'
        . '</p>' . "\n" ; 
    }
    
    // load display class
    $profileRightHtml = new RightProfileToolRightHtml($courseProfileRight);

    $profileRightHtml->addUrlParam('display',$display);

    if ( $display == 'edit' )
    {
        $profileRightHtml->setDisplayMode('edit');
        echo $profileRightHtml->displayProfileToolRightList();
    }
    else
    {
        $profileRightHtml->setDisplayMode('view');
        echo $profileRightHtml->displayProfileToolRightList();
    }
}
else
{
    echo claro_html_message_box(get_lang('Profile not found'));
}

// Display footer

include $includePath . '/claro_init_footer.inc.php';

?>
