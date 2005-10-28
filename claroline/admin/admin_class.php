<?php // $Id$
/**
 * CLAROLINE
 *
 * this tool manage the
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

/*
* DB tables definition
*/
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];

// USED SESSION VARIABLES

if (!isset($_SESSION['admin_visible_class']))
{
    $_SESSION['admin_visible_class'] = array();
}

// Deal with interbredcrumps  and title variable
$nameTools = $langAdministrationClassTools;
$interbredcrump[]    = array ('url' => $rootAdminWeb, 'name' => $langAdministration);

// javascript confirm pop up declaration for header

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
    if (confirm(\"".clean_str_for_javascript($langAreYouSureToDelete)."\"+' '+ name + \"? \"))
        {return true;}
    else
        {return false;}
}
</script>";

/*-----------------------------------*/
/*    EXECUTE COMMAND                 */
/*-----------------------------------*/
if (isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
else                         $cmd = null;

switch ($cmd)
{
    //Delete an existing class
    case 'del' :

    //check if class contains some children

    $sql = "SELECT count(id)
            FROM `" . $tbl_class . "`
            WHERE class_parent_id = " . (int) $_REQUEST['class'];
    $has_children = (bool) claro_sql_query_get_single_value($sql);

    // delete the class itself
    if ($has_children)
    {
        $dialogBox = $langErrorClassNotEmpty;
    }
    else
    {
        $sql = "DELETE FROM `" . $tbl_class . "`
                WHERE id = " . (int) $_REQUEST['class'];
        claro_sql_query($sql);
    }

    break;

    //Display form to create a new class
    case 'formNew' :
    $dialogBox = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" >' . "\n"
    .            '<table>' . "\n"
    .            '<tr>' . "\n"
    .            '<td>' . $langNewClassName.' : ' . '</td>' . "\n"
    .            '<td>' . "\n"
    .            '<input type="hidden" name="cmd" value="new" />' . "\n"
    .            '<input type="text" name="classname" />' . "\n"
    .            '</td>' . "\n"
    .            '</tr>' . "\n"
    .            '<tr>' . "\n"
    .            '<td>'. $langLocation.' :' . '</td>' . "\n"
    .            '<td>' . "\n"
    .            displaySelectBox()
    .            '<input type="submit" value=" Ok " />' . "\n"
    .            '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
    .            '</td>' . "\n"
    .            '</tr>' . "\n"
    .            '</table>' . "\n"
    .            '</form>'."\n "
    ;
    break;

    //Create a new class
    case 'new' :
    if ($_REQUEST['classname']=='')
    {
        $dialogBox = $langCannotBeBlank;
    }
    else
    {
        $dialogBox = $langNewClassCreated;
        $sql = "INSERT INTO `" . $tbl_class . "`
                SET `name`='". addslashes($_REQUEST['classname']) ."'";

        if ($_REQUEST['theclass'] && ($_REQUEST['theclass']!='') && ($_REQUEST['theclass']!='root'))
        {
            $sql.=", `class_parent_id`= ". (int) $_REQUEST['theclass'];
        }

        claro_sql_query($sql);

    }
    break;

    //Edit class properties with posted form
    case 'exEdit' :

    if ($_REQUEST['classname']=='')
    {
        $dialogBox = $langCannotBeBlank;
    }
    else
    {
        $sql_update = "UPDATE `".$tbl_class."`
                       SET name='". addslashes($_REQUEST['classname']) ."'
                       WHERE id= " . (int) $_REQUEST['class'];
        claro_sql_query($sql_update);
        $dialogBox = $langNameChanged;

    }
    break;

    //Show form to edit class properties (display form)
    case 'edit' :

    $sqlGetClassName = "SELECT name
                            FROM `" . $tbl_class . "`
                            WHERE `id`= ". (int) $_REQUEST['class'];

    $class_name =  claro_sql_query_get_single_value($sqlGetClassName);

    $dialogBox= '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" >' . "\n"
    .           '<table>' . "\n"
    .           '<tr>' . "\n"
    .           '<td>' . "\n"
    .           $langClassName.' : ' . "\n"
    .           '</td>' . "\n"
    .           '<td>' . "\n"
    .           '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
    .           '<input type="hidden" name="class" value="' . $_REQUEST['class'].'" />' . "\n"
    .           '<input type="text" name="classname" value="' . htmlspecialchars($class_name) . '" />' . "\n"
    .           '<input type="submit" value=" ' . $langOk . ' " />' . "\n"
    .           '</td>' . "\n"
    .           '</tr>' . "\n"
    .           '</table>' . "\n"
    .           '</form>'."\n "
    ;
    break;

    //Open a class in the tree
    case 'exOpen' :
    $_SESSION['admin_visible_class'][$_REQUEST['class']] = 'open';
    break;

    //Close a class in the tree
    case 'exClose' :
    $_SESSION['admin_visible_class'][$_REQUEST['class']] = 'close';
    break;

    //Move a class in the tree (do it from posted info)
    case 'exMove' :

    if ($_REQUEST['theclass'] ==$_REQUEST['movedClassId'])
    {
        $dialogBox = $langErrorMove;
    }
    else
    {
        if ($_REQUEST['theclass']=="root") $parent = "null";
        else                               $parent = $_REQUEST['theclass'];

        if (!is_null($parent)) $parent = (int) $parent;

        $sql_update="UPDATE `" . $tbl_class . "`
                     SET class_parent_id= " . $parent . "
                     WHERE id= " . (int) $_REQUEST['movedClassId'] ;
        claro_sql_query($sql_update);
        $dialogBox = $langClassMoved;
    }
    break;

    //Move a class in the tree (display form)
    case "move" :

    $dialogBox = '<form action="'.$_SERVER['PHP_SELF'].'">'
    . '<table>'
    . '<tr>' . "\n"
    . '<td>' . "\n"
    . $langMove ." ". $_REQUEST['classname'].' : '
    . '</td>' . "\n"
    . '<td>' . "\n"
    . '<input type="hidden" name="cmd" value="exMove" />' . "\n"
    . '<input type="hidden" name="movedClassId" value="'.$_REQUEST['class'].'" />' . "\n"
    . displaySelectBox()
    . '<input type="submit" value=" Ok " />' . "\n"
    . '</td>' . "\n"
    . '</tr>' . "\n"
    . '</table>'
    . '</form>'
    ;
    break;

}

/*-----------------------------------*/
/*    Get information                  */
/*-----------------------------------*/

$sql = "SELECT *
        FROM `" . $tbl_class . "`
        ORDER BY `name`";
$class_list = claro_sql_query_fetch_all($sql);

/*-----------------------------------*/
/*    Display                          */
/*-----------------------------------*/

// Display Header

include $includePath . '/claro_init_header.inc.php';

// display title

echo claro_disp_tool_title($nameTools);

// display dialog Box (or any forms)

if ( isset($dialogBox))
{
    echo claro_disp_message_box($dialogBox);
    echo '<br />';
}

//display tool links

echo '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=formNew">'
.    '<img src="' . $imgRepositoryWeb . 'class.gif" />' . $langCreateNewClass
.    '</a>'
.    '<br /><br />' . "\n"
//display cols headers
.    '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">'
.    '<th>' . $langClassName    . '</th>'
.    '<th>' . $langUsers        . '</th>'
.    '<th>' . $langEditSettings . '</th>'
.    '<th>' . $langMove         . '</th>'
.    '<th>' . $langDelete       . '</th>'
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