<?php // $Id$
/**
 * CLAROLINE
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 *
 * @author claro team <cvs@claroline.net>
 */


$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
$includePath = null;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! get_init('_uid') ) claro_disp_auth_form();
if ( ! get_init('is_platformAdmin') ) claro_die(get_lang('Not allowed'));

// Include libraries
require_once $includePath . '/lib/user.lib.php';


// Initialise variables
$nameTools = get_lang('System mail : recipients list');
$error = false;
$messageList = array();

/*=====================================================================
Main Section
=====================================================================*/


$platformAdminUidList = claro_get_uid_of_platform_admin();

if ( isset($_REQUEST['cmd']) )  //for formular modification
{
    $notifiedList = $_REQUEST['notifiedList'];
    $requestList = $_REQUEST['requestList'];
    $contactList = $_REQUEST['contactList'];
    foreach ($platformAdminUidList as $platformAdminUid )
    {
        claro_set_uid_of_platform_contact($platformAdminUid,in_array($platformAdminUid,$contactList));
        claro_set_uid_recipient_of_system_notification($platformAdminUid,in_array($platformAdminUid,$notifiedList));
        claro_set_uid_recipient_of_request_admin($platformAdminUid,in_array($platformAdminUid,$requestList));
    }


} // if apply changes

/**
 * PREPARE DISPLAY
 */

$interbredcrump[]= array ('url' => get_conf('rootAdminWeb'), 'name' => get_lang('Administration'));

$contactUidList = claro_get_uid_of_platform_contact();
$requestUidList = claro_get_uid_of_request_admin();
$notifiedUidList = claro_get_uid_of_system_notification_recipient();


foreach ($platformAdminUidList as $k => $platformAdminUid )
{
    $userData = user_get_properties($platformAdminUid);
    $userDataGrid[$k]['id'] = $userData['user_id'];
    $userDataGrid[$k]['name'] = $userData['lastname'];
    $userDataGrid[$k]['firstname'] = $userData['firstname'];
    $userDataGrid[$k]['email'] = $userData['email'];
    $userDataGrid[$k]['authsource'] = $userData['authsource'];
    $userDataGrid[$k]['contact_switch'] = '<input name="contactList[]" type="checkbox" value="' . $platformAdminUid . '" '
    .    ((bool) in_array($platformAdminUid,$contactUidList)  ? 'checked="checked" > (' . get_lang('Yes') . ')' : '> (' . get_lang('No') . ')');
    $userDataGrid[$k]['request_switch'] = '<input name="requestList[]" type="checkbox" value="' . $platformAdminUid . '" '
    .    ((bool) in_array($platformAdminUid,$requestUidList)  ? 'checked="checked" > (' . get_lang('Yes') . ')' : '> (' . get_lang('No') . ')');
    $userDataGrid[$k]['notification_switch'] = '<input name="notifiedList[]" type="checkbox" value="' . $platformAdminUid . '" '
    .    ((bool) in_array($platformAdminUid,$notifiedUidList)  ? 'checked="checked" > (' . get_lang('Yes') . ')' : '> (' . get_lang('No') . ')');

}
$adminDataGrid = new claro_datagrid($userDataGrid);
$adminDataGrid->set_idLineType('none');
$adminDataGrid->set_colHead('name');
$adminDataGrid->set_colTitleList(array ( 'user id'              => get_lang('user id')
                                        , 'name'                => get_lang('lastname')
                                        , 'firstname'           => get_lang('firstname')
                                        , 'email'               => get_lang('email')
                                        , 'authsource'          => get_lang('authentication source')
                                        , 'contact_switch'      => get_lang('Contact')
                                        , 'request_switch'      => get_lang('request')
                                        , 'notification_switch' => get_lang('notify')
                                        )
                                        );

$adminDataGrid->set_colAttributeList( array ( 'contact_switch' => array ('align' => 'left')
                                             , 'request_switch' => array ('align' => 'left')
                                             , 'notification_switch' => array ('align' => 'left')
                                             , 'authsource'  => array ('align' => 'center')
                                             ));
/**
 * DISPLAY
 */

// Disdplay header
include $includePath . '/claro_init_header.inc.php';

// Display tool title
echo claro_html_tool_title($nameTools)
.    claro_html_msg_list($messageList)
.    '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
.    '<input type="hidden" name="cmd" value="setRecipient" />' . "\n"
.    '<fieldset>'
.    '<legend>' . get_lang('Recipient List') . '</legend>'
.    $adminDataGrid->render()
.   '</fieldset>'
.    get_lang('Submit') . ': ' . "\n"
.    '<input type="submit" value="' . get_lang('Ok') . '"> ' . "\n"
.    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
.    '</form>' . "\n"
;


include $includePath . '/claro_init_footer.inc.php';

?>