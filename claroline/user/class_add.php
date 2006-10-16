<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool list classes and prupose to subscribe it  to the current course.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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
require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);
if ( !$is_courseAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/sendmail.lib.php';

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

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_users      = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['class'];
$tbl_class_user = $tbl_mdb_names['rel_class_user'];
$tbl_course_class = $tbl_mdb_names['rel_course_class'];


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

        if ( register_class_to_course( $form_data['class_id'], $_cid) )
        {
            $dialogBox  = get_lang('Class has been enroled') ;
        }
        else
        {

        }
        break;

    // Unenrol a class to the course

    case 'exUnenrol' :

        if ( unregister_class_to_course( $form_data['class_id'], $_cid) )
        {
            $dialogBox  = get_lang('Class has been unenroled') ;
        }
        else
        {

        }
        break;
}

/*---------------------------------------------------------------------*/
/*----------------------FIND information SECTION-----------------------*/
/*---------------------------------------------------------------------*/

$sql = "SELECT C.id,
               C.name,
               C.class_parent_id,
               CC.courseId as course_id
        FROM `" . $tbl_class . "` C
              LEFT JOIN `" . $tbl_course_class . "` CC ON CC.`classId` = C.`id`
              AND CC.`courseId` = '" . addslashes($_cid) . "'
        ORDER BY C.`name`";
$class_list = claro_sql_query_fetch_all($sql);

/*---------------------------------------------------------------------*/
/*----------------------DISPLAY SECTION--------------------------------*/
/*---------------------------------------------------------------------*/

// set bredcrump

$nameTools = get_lang('Enrol class');
$interbredcrump[] = array ('url' => 'user.php', 'name' => get_lang('Users'));

// display top banner

include $includePath . '/claro_init_header.inc.php';

// Display tool title

echo claro_html_tool_title(get_lang('Enrol class'));

// Display Forms or dialog box (if needed)

if(isset($dialogBox) && $dialogBox!='')
{
    echo claro_html_message_box($dialogBox);
}

// display tool links
echo '<p><a class="claroCmd" href="user.php">' . get_lang('Back to list') . '</a></p>' ;

// display cols headers

echo '<table class="claroTable" width="100%" border="0" cellspacing="2">' . "\n"
    .    '<thead>' . "\n"
    .    '<tr class="headerX">' . "\n"
    .    '<th>' . get_lang('Classes') . '</th>' . "\n"
    .    '<th>' . get_lang('Users') . '</th>' . "\n"
    .    '<th>' . get_lang('Enrol to course') . '</th>' . "\n"
    .    '</tr>' . "\n"
    .    '</thead>' . "\n"
    .    '<tbody>' . "\n" ;

// display Class list (or tree)
echo display_tree_class_in_user($class_list, $_cid);

echo '</tbody>' . "\n"
    . '</table>' . "\n" ;

// display footer banner

include $includePath . '/claro_init_footer.inc.php';

?>