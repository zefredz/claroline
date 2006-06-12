<?php // $Id$
/**
 * CLAROLINE
 *
 * this tool manage the
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author  Guillaume Lederer <lederer@cerdecam.be>
 */

//used libraries

require '../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/class.lib.php';
require_once $includePath . '/lib/user.lib.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

/*
* DB tables definition
*/
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_class      = $tbl_mdb_names['user_category'];

// USED SESSION VARIABLES

if (!isset($_SESSION['admin_visible_class']))
{
    $_SESSION['admin_visible_class'] = array();
}

// Deal with interbredcrumps  and title variable
$nameTools = get_lang('Classes');
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

// javascript confirm pop up declaration for header

$htmlHeadXtra[] =
'<script>
function confirmation (name)
{
    if (confirm("' . clean_str_for_javascript(get_lang('Are you sure to delete')) . '"+\' \'+ name + "? "))
        {return true;}
    else
        {return false;}
}
</script>';

/*-----------------------------------*/
/*    EXECUTE COMMAND                 */
/*-----------------------------------*/
if (isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
else                         $cmd = null;

if ( isset($_REQUEST['class']) ) $class_id = (int) $_REQUEST['class'];
else                             $class_id = null;


switch ($cmd)
{
    //Delete an existing class
    case 'del' :

        $done = delete_class($class_id);

        if ( $done !== true )
        {
            $dialogBox = $done;
        }

        break;

    //Display form to create a new class
    case 'formNew' :
    {
        $dialogBox = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" >' . "\n"
        .            '<table>' . "\n"
        .            '<tr>' . "\n"
        .            '<td>' . get_lang('New Class name').' : ' . '</td>' . "\n"
        .            '<td>' . "\n"
        .            '<input type="hidden" name="cmd" value="new" />' . "\n"
        .            '<input type="text" name="classname" />' . "\n"
        .            '</td>' . "\n"
        .            '</tr>' . "\n"
        .            '<tr>' . "\n"
        .            '<td>'. get_lang('Location').' :' . '</td>' . "\n"
        .            '<td>' . "\n"
        .            displaySelectBox()
        .            '<input type="submit" value=" Ok " />' . "\n"
        .            '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
        .            '</td>' . "\n"
        .            '</tr>' . "\n"
        .            '</table>' . "\n"
        .            '</form>'."\n "
        ;
    }   break;

    //Create a new class
    case 'new' :
    if ($_REQUEST['classname']=='')
    {
        $dialogBox = get_lang('You cannot give a blank name to a class');
    }
    else
    {
        $className = isset($_REQUEST['classname'])?$_REQUEST['classname']:'';
        $classParent = isset($_REQUEST['theclass'])?$_REQUEST['theclass']:0;
        
        if ( ! is_int($classParent) )
        {
            $classParent = 0;
        }

        if ( class_create($className,$classParent) )
        {
            $dialogBox = get_lang('The new class has been created');
        }

    }
    break;

    //Edit class properties with posted form
    case 'exEdit' :

    if ($_REQUEST['classname']=='')
    {
        $dialogBox = get_lang('You cannot give a blank name to a class');
    }
    else
    {
        if ( class_set_properties($_REQUEST['class'],$_REQUEST['classname']) ) 
        {
            $dialogBox = get_lang('Name of the class has been changed');
        }
    }

    break;

    //Show form to edit class properties (display form)
    case 'edit' :
    {
        if ( ( $thisClass = class_get_properties($_REQUEST['class']) ) !== false )
        {
            $classId =  $thisClass['id'];
            $className =  $thisClass['name'];

            $dialogBox= '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" >' . "\n"
            .           '<table>' . "\n"
            .           '<tr>' . "\n"
            .           '<td>' . "\n"
            .           get_lang('Name').' : ' . "\n"
            .           '</td>' . "\n"
            .           '<td>' . "\n"
            .           '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
            .           '<input type="hidden" name="class" value="' . $classId . '" />' . "\n"
            .           '<input type="text" name="classname" value="' . htmlspecialchars($className) . '" />' . "\n"
            .           '<input type="submit" value=" ' . get_lang('Ok') . ' " />' . "\n"
            .           '</td>' . "\n"
            .           '</tr>' . "\n"
            .           '</table>' . "\n"
            .           '</form>'."\n "
            ;
        }
        else
        {
            $dialogBox = get_lang('Class not found');
        }
    }   break;

    //Open a class in the tree
    case 'exOpen' :
    {
        $_SESSION['admin_visible_class'][$_REQUEST['class']] = 'open';
    }   break;

    //Close a class in the tree
    case 'exClose' :
    {
        $_SESSION['admin_visible_class'][$_REQUEST['class']] = 'close';
    }   break;

    //Move a class in the tree (do it from posted info)
    case 'exMove' :
    {
        $dialogBox = move_class($_REQUEST['movedClassId'],$_REQUEST['theclass']);

    }   break;

    //Move a class in the tree (display form)
    case "move" :
    {

        $dialogBox = '<form action="'.$_SERVER['PHP_SELF'].'">'
        .            '<table>'
        .            '<tr>' . "\n"
        .            '<td>' . "\n"
        .            get_lang('Move') ." ". $_REQUEST['classname'].' : '
        .            '</td>' . "\n"
        .            '<td>' . "\n"
        .            '<input type="hidden" name="cmd" value="exMove" />' . "\n"
        .            '<input type="hidden" name="movedClassId" value="'.$_REQUEST['class'].'" />' . "\n"
        .            displaySelectBox()
        .            '<input type="submit" value=" Ok " />' . "\n"
        .            '</td>' . "\n"
        .            '</tr>' . "\n"
        .            '</table>'
        .            '</form>'
        ;
    }   break;

}

/*-----------------------------------*/
/*    Get information                  */
/*-----------------------------------*/

$sql = "SELECT id,
               class_parent_id,
               name
        FROM `" . $tbl_class . "`
        ORDER BY `name`";
$class_list = claro_sql_query_fetch_all($sql);

/*-----------------------------------*/
/*    Display                          */
/*-----------------------------------*/

// Display Header

include $includePath . '/claro_init_header.inc.php';

// display title

echo claro_html_tool_title($nameTools);

// display dialog Box (or any forms)

if( isset($dialogBox) ) echo claro_html_message_box($dialogBox);

//display tool links

echo '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=formNew">'
.    '<img src="' . $imgRepositoryWeb . 'class.gif" />' . get_lang('Create a new class')
.    '</a>'
.    '<br /><br />' . "\n"
//display cols headers
.    '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">'
.    '<th>' . get_lang('Classes')    . '</th>'
.    '<th>' . get_lang('Users')        . '</th>'
.    '<th>' . get_lang('Courses') . '</th>'
.    '<th>' . get_lang('Edit settings') . '</th>'
.    '<th>' . get_lang('Move')         . '</th>'
.    '<th>' . get_lang('Delete')       . '</th>'
.    '</tr>' . "\n"
.    '</thead>' . "\n"
//display Class list
.    '<tbody>' . "\n"
;
display_tree_class_in_admin($class_list);
echo '</tbody>' . "\n"
.    '</table>'
;

// Display footer
include $includePath . '/claro_init_footer.inc.php';

?>
