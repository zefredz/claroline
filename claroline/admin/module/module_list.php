<?php // $Id$
/**
 * CLAROLINE
 * @version 1.8
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 *
 * @author claro team <cvs@claroline.net>
 */

require '../../inc/claro_init_global.inc.php';

//SECURITY CHECK

if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

//DECLARE NEEDED LIBRARIES

require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/sqlxtra.lib.php';
require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/fileUpload.lib.php';
require_once $includePath . '/../admin/module/module.inc.php';

//SQL table name

$tbl_name        = claro_sql_get_main_tbl();
$tbl_module      = $tbl_name['module'];
$tbl_module_info = $tbl_name['module_info'];
$tbl_dock        = $tbl_name['dock'];


$nameTools = get_lang('Module list');
$interbredcrump[]= array ('url' => $rootAdminWeb,'name' => get_lang('Administration'));

//NEEDED CSS

$htmlHeadXtra[] = "
<style type=\"text/css\">
#moduletypelist
{
padding: 3px 0;
margin-left: 0;
border-bottom: 1px solid #778;
font: bold 12px Verdana, sans-serif;
}

#moduletypelist li
{
list-style: none;
margin: 0;
display: inline;
}

#moduletypelist li a
{
padding: 3px 0.5em;
margin-left: 3px;
border: 1px solid #778;
border-bottom: none;
background: #DDE;
text-decoration: none;
}

#moduletypelist li a:link { color: #448; }
#moduletypelist li a:visited { color: #667; }

#moduletypelist li a:hover
{
color: #000;
background: #AAE;
border-color: #227;
}

#moduletypelist li a#current
{
background: white;
border-bottom: 1px solid white;
}
</style>
";

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
    if (confirm(\" ".clean_str_for_javascript(get_lang("Are you sure to uninstall the module "))." \"+ name + \" ?\"))
        {return true;}
    else
        {return false;}
}
</script>";

//CONFIG and DEVMOD vars :

$modulePerPage = get_conf('modulePerPage' , 10);
$maxFilledSpaceForModule = get_conf('maxFilledSpaceForModule' , 10000000); //needed for the installation of a new module

$typeList[] = 'applet';
$typeList[] = 'coursetool';


//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : null);

if  (isset($_REQUEST['module_id']) ) $module_id = $_REQUEST['module_id'];
if  (isset($_REQUEST['dockname']) ) $dockname = $_REQUEST['dockname'];

switch ( $cmd )
{
    case 'activ' :
    {
        activate_module($module_id);
    }
    break;

    case 'desactiv' :
    {
        desactivate_module($module_id);
    }
    break;

    case 'up' :
    {

        move_module_in_dock($module_id, $dockname,'up');
    }
    break;

    case 'down' :
    {
        move_module_in_dock($module_id, $dockname,'down');
    }
    break;

    case 'uninstall' :
    {
        $result_log = uninstall_module($module_id);
        $dialogBox  = get_lang('Module uninstallation.') . ' : <br>';
        foreach ( $result_log as $log) $dialogBox .= $log . '<br>';

    }
    break;

    case 'show_install' :
    {
        $dialogBox = '<p>'
        .            get_lang('Imported modules must consist of a zip file and be compatible with your Claroline version.') . '<br>'
        .            get_lang('Find more available modules <a href="http://www.claroline.net/">here</a>.')
        .            '</p>'
        .            '<form enctype="multipart/form-data" action="" method="post">'
        .            '<input name="uploadedModule" type="file" /><br><br>'
        .            '<input type="submit" value="cancel" /> '
        .            '<input name="cmd" type="hidden" value="do_install" />'
        .            '<input value="' . get_lang('Install Module') . '" type="submit" />'
        .            '<br><br>'
        .            '<small>' . get_lang('Max file size') . ' :  2&nbsp;MB</small>'
        .            '</form>'
        ;
    }
    break;

    case 'do_install' :
    {
        //include needed librabries for treatment

        $result_log = install_module();
        $dialogBox = '';

        //display the result message (fail or success)

        foreach ($result_log as $log)
        {
            $dialogBox .= $log . '<br>';
        }
    }
}

//----------------------------------
// FIND INFORMATION
//----------------------------------

if (isset($_REQUEST['selected_type'])) $selected_type = $_REQUEST['selected_type']; else $selected_type = 'applet';


$sql = "SELECT M.`id`              AS `id`,
               M.`label`           AS `label`,
               M.`name`            AS `name`,
               M.`activation`      AS `activation`,
               M.`type`            AS `type`,
               M.`module_info_id`  AS `module_info_id`
        FROM `" . $tbl_module . "` AS M
        WHERE M.`type` = '" . addslashes($selected_type) . "'
        ORDER BY `id`
        ";


//pager creation

$offset       = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0 ;
$myPager      = new claro_sql_pager($sql, $offset, $modulePerPage);
$pagerSortDir = isset($_REQUEST['dir' ]) ? $_REQUEST['dir' ] : SORT_ASC;
$moduleList = $myPager->get_result_list();

//find docks in which the modules do appear.

$module_docks = array(); 

foreach ($moduleList as $module)
{
    $module_dock[$module['id']] = array();

    $sql = "SELECT D.`id`    AS dock_id,
                   D.`name`  AS dockname
            FROM `" . $tbl_dock . "` AS D
            WHERE D.`module_id`=".(int)$module['id'];

    $module_dock[$module['id']] = claro_sql_query_fetch_all($sql);
}



//----------------------------------
// DISPLAY
//----------------------------------

include $includePath . '/claro_init_header.inc.php';

//display title

echo claro_disp_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) ) echo claro_html::message_box($dialogBox);

//display action links
echo '<a class="claroCmd" href="module_list.php?cmd=show_install">' . get_lang('Install a module') . '<a>'
.    '<br/><br/>'


//display tabbed navbar

.    '<div id="moduletypecontainer">'
.    '<ul id="moduletypelist">'
;

//display the module type tabbed naviguation bar

foreach ($typeList as $type)
{
    if ($selected_type == $type)
    {
        echo '<li id="active"><a href="module_list.php?selected_type='.$type.'" id="current">'.$type.'</a></li>';
    }
    else
    {
        echo '<li><a href="module_list.php?selected_type='.$type.'">'.$type.'</a></li>';
    }
}

echo '  </ul>
      </div>';

//Display list

//Display Pager list

echo $myPager->disp_pager_tool_bar('module_list.php?selected_type='.$selected_type);

// start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
.    '<thead>'
.    '<tr class="headerX" align="center" valign="top">'
.    '<th>' . get_lang('Id')                 . '</th>'
.    '<th>' . get_lang('Icon')               . '</th>'
.    '<th>' . get_lang('Module name')        . '</th>'
.    '<th>' . get_lang('Display')             . '</th>'
.    '<th>' . get_lang('Activation')          . '</th>'
.    '<th>' . get_lang('Edit settings')       .'</th>'
.    '<th>' . get_lang('Uninstall')           .'</th>'
.    '</tr><tbody>'
;

// Start the list of modules...

foreach($moduleList as $module)
{
    //display settings...
    $class_css= ($module['activation']=='activated' ? 'item' : 'invisible item');

    //find icon

    if (file_exists($includePath . '/../module/' . $module['label'] . '/icon.png'))
    {
        $icon = '<img src="' . $rootWeb . 'claroline/module/' . $module['label'] . '/icon.png" />';
    }
    elseif (file_exists($includePath . '/../module/' . $module['label'] . '/icon.gif'))
    {
        $icon = '<img src="' . $rootWeb . 'claroline/module/' . $module['label'] . '/icon.gif" />';
    }
    else $icon = '<small>' . get_lang('No icon') . '</small>';


    //module_id and icon column

    echo '<tr>'
    .    '<td align="center">' . $module['id'] . '</td>' . "\n"
    .    '<td align="center">' . $icon . '</td>' . "\n";

    //name column

    if (file_exists($includePath . '/../module/' . $module['label'] . '/admin.php'))
    {
        echo '<td align="left" class="' . $class_css . '" ><a href="'. $rootWeb . 'claroline/module/' . $module['label'] . '/admin.php" >' . $module['name'] . '</a></td>' . "\n";
    }
    else
    {
        echo '<td align="left" class="' . $class_css . '" >' . $module['name'] . '</td>' . "\n";
    }

    //displaying location column

    echo    '<td align="left" class="' . $class_css . '"><small>';

    foreach ($module_dock[$module['id']] as $dock)
    {
        echo '<a href="module_dock.php?dock='.$dock['dockname'].'">'.$dock['dockname']."</a> <br/>";
    }

    if (empty($module_dock[$module['id']]))
    {
        echo '<div align="center">'.get_lang('No dock chosen')."</div>";
    }

    echo '</small></td>' . "\n"

    .    '<td align="center" >'
    ;

    //activation link

    if ($module['activation'] == 'activated')
    {
        echo '<a class="item" href="module_list.php?cmd=desactiv&amp;module_id=' . $module['id'] . '&amp;selected_type=' . $selected_type . '">'
        .    '<img src="' . $imgRepositoryWeb . 'visible.gif" border="0" alt="' . get_lang('Activated') . '" /></a>'
        ;
    }
    else
    {
        echo '<a class="invisible item" href="module_list.php?cmd=activ&amp;module_id=' . $module['id'] . '&amp;selected_type='.$selected_type.'"><img src="' . $imgRepositoryWeb . 'invisible.gif" border="0" alt="' . get_lang('Desactivated') . '" /></a>';
    }

    echo '</td>' . "\n";

    //edit settings link

    echo '<td align="center">'
    .    '<a href="module.php?module_id='.$module['id'].'">'
    .    '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . get_lang('Edit') . '" />'
    .    '</a>'
    .    '</td>' . "\n"

    //uninstall link

    .    '<td align="center">'
    .    '<a href="module_list.php?module_id=' . $module['id'] . '&amp;selected_type='.$selected_type.'&cmd=uninstall"'
    .    ' onClick="return confirmation(\''.$module['name'].'\');">'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />'
    .    '</a>'
    .    '</td>' . "\n"
    .    '</tr>'
    ;
}

//end table...
echo '</tbody>'
.    '</table>';

//Display BOTTOM Pager list

echo $myPager->disp_pager_tool_bar('module_list.php?selected_type='.$selected_type);

include $includePath . '/claro_init_footer.inc.php';
?>