<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool list classes and prupose to subscribe it  to the current course.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLUSR
 *
 */

$tlabelReq = 'CLUSR';
$gidReset = true;
$dialogBoxMsg = array();
require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$can_import_user_class  = (bool) (claro_is_course_manager()
                        && get_conf('is_coursemanager_allowed_to_import_user_class') )
                        || claro_is_platform_admin();

// TODO replace calro_die by best usage.

if ( !$can_import_user_class ) claro_die(get_lang('Not allowed'));

require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/class.lib.php';
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

/*---------------------------------------------------------------------*/
/*----------------------EXECUTE COMMAND SECTION------------------------*/
/*---------------------------------------------------------------------*/

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;

$form_data['class_id'] = isset($_REQUEST['class_id'])?$_REQUEST['class_id']:0;
$form_data['class_name'] = isset($_REQUEST['class_name'])?trim($_REQUEST['class_name']):'';

switch ( $cmd )
{
    // Open a class in the tree
    case 'exOpen' :

        $_SESSION['class_add_visible_class'][$form_data['class_id']] = 'open';
        break;

    // Close a class in the tree
    case 'exClose' :

        $_SESSION['class_add_visible_class'][$form_data['class_id']] = 'close';
        break;

    // Enrol a class to the course

    case 'exEnrol' :

        if ( register_class_to_course( $form_data['class_id'], claro_get_current_course_id()) )
        {
            $dialogBoxMsg[]  = get_lang('Class has been enroled') ;
        }
        break;

    // Unenrol a class to the course

    case 'exUnenrol' :

        if ( unregister_class_to_course( $form_data['class_id'], claro_get_current_course_id()) )
        {
            $dialogBoxMsg[]  = get_lang('Class has been unenroled') ;
        }
        break;
}

/*---------------------------------------------------------------------*/
/*----------------------FIND information SECTION-----------------------*/
/*---------------------------------------------------------------------*/

$classList = get_class_list_by_course(claro_get_current_course_id());

/*---------------------------------------------------------------------*/
/*----------------------DISPLAY SECTION--------------------------------*/
/*---------------------------------------------------------------------*/

// set bredcrump

$nameTools = get_lang('Enrol class');
$interbredcrump[] = array ('url' => 'user.php' . claro_url_relay_context('?') , 'name' => get_lang('Users'));
// javascript confirm pop up declaration for header

$htmlHeadXtra[] =
'<script>
    function confirmation_enrol (name)
    {
        if (confirm("' . clean_str_for_javascript(get_lang('Are you sure you want to enrol the whole class on the course ?')) . '"))
            {return true;}
        else
            {return false;}
    }
    function confirmation_unenrol (name)
    {
        if (confirm("' . clean_str_for_javascript(get_lang('Are you sure you want to unenrol the whole class on the course ?')) . '"))
            {return true;}
        else
            {return false;}
    }
</script>';


// display top banner

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

// Display tool title

echo claro_html_tool_title(get_lang('Enrol class'))

// Display Forms or dialog box (if needed)

.    claro_html_msg_list($dialogBoxMsg)

// display tool links
.    '<p>'
.    claro_html_cmd_link('user.php'  . claro_url_relay_context('?') , get_lang('Back to list'))
.    '</p>'
// display cols headers
.    '<table class="claroTable" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">' . "\n"
.    '<th>' . get_lang('Classes') . '</th>' . "\n"
.    '<th>' . get_lang('Users') . '</th>' . "\n"
.    '<th>' . get_lang('Enrol to course') . '</th>' . "\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n"
.    '<tbody>' . "\n"
// display Class list (or tree)
.    display_tree_class_in_user($classList, claro_get_current_course_id())
.    '</tbody>' . "\n"
.    '</table>' . "\n"
;

// display footer banner

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>