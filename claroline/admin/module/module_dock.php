<?php
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
require_once $includePath . '/lib/module.manage.lib.php';

//SQL table name

$tbl_name        = claro_sql_get_main_tbl();
$tbl_module      = $tbl_name['module'];
$tbl_module_info = $tbl_name['module_info'];
$tbl_dock        = $tbl_name['dock'];

if ( isset($_REQUEST['dock']) )
{
	$dockList = get_dock_list('applet');
	$dock = $_REQUEST['dock'];
    $dockName = isset($dockList[$dock]) ? $dockList[$dock] : $dock ;
    $nameTools = get_lang('Dock') . ' : ' . $dockName;
}
else
{
    $dock = null;
    $dialogBox = get_lang('No dock selected');
    $nameTools = get_lang('Dock');
}

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => 'module_list.php','name' => get_lang('Module list'));

//CONFIG and DEVMOD vars :

$modulePerPage = get_conf('moduleDockPerPage' , 10);

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : null);
$module_id = (isset($_REQUEST['module_id'])? $_REQUEST['module_id'] : null);

if ( !empty($dock))
{
    switch ( $cmd )
    {
        case 'up' :
        {
            move_module_in_dock($module_id, $dock,'up');
        }
        break;

        case 'down' :
        {
            move_module_in_dock($module_id, $dock,'down');
        }
        break;

        case 'remove' :
        {
            remove_module_dock($module_id,$dock);
            $dialogBox = get_lang('The module has been removed from this dock');
        }
        break;
    }

    //----------------------------------
    // FIND INFORMATION
    //----------------------------------

    $sql = "SELECT M.`id`              AS `id`,
                   M.`label`           AS `label`,
                   M.`name`            AS `name`,
                   M.`activation`      AS `activation`,
                   M.`type`            AS `type`,
                   D.`rank`            AS `rank`
            FROM `" . $tbl_module . "` AS M, `" . $tbl_dock . "` AS D
            WHERE D.`module_id`= M.`id`
              AND D.`name` = '".$dock."'
            ORDER BY `rank`
            ";

    //pager creation

    $offset       = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0 ;
    $myPager      = new claro_sql_pager($sql, $offset, $modulePerPage);
    $pagerSortDir = isset($_REQUEST['dir' ]) ? $_REQUEST['dir' ] : SORT_ASC;
    $moduleList   = $myPager->get_result_list();

}

//----------------------------------
// DISPLAY
//----------------------------------

include $includePath . '/claro_init_header.inc.php';

//display title

echo claro_html_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) ) echo claro_html_message_box($dialogBox);

if ( !empty($dock) )
{

    //Display TOP Pager list

    echo $myPager->disp_pager_tool_bar('module_dock.php?dock='.$dock);

    // start table...

    echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
    .    '<thead>'
    .    '<tr class="headerX" align="center" valign="top">'
    .    '<th>' . get_lang('Icon')               . '</th>'
    .    '<th>' . get_lang('Module name')        . '</th>'
    .    '<th colspan="2">' . get_lang('Order')           .'</th>'
    .    '<th>' . get_lang('Remove from the dock')          . '</th>'
    .    '</tr><tbody>'
    ;

    $iteration = 1;
    $enditeration = sizeof($moduleList);

    foreach($moduleList as $module)
    {
        //display settings...
        $class_css= ($module['activation']=='activated' ? 'item' : 'invisible item');

        //find icon

        if (file_exists(get_module_path($module['label']) . '/icon.png'))
        {
            $icon = '<img src="' . get_module_url($module['label']) . '/icon.png" />';
        }
        elseif (file_exists(get_module_path($module['label']) . '/icon.gif'))
        {
            $icon = '<img src="' . get_module_url($module['label']) . '/icon.gif" />';
        }
        else $icon = '<small>' . get_lang('No icon') . '</small>';


        //module_id and icon column

        echo '<tr>'
        .    '<td align="center">' . $icon . '</td>' . "\n";

        //name column

        if (file_exists(get_module_path($module['label']) . '/admin.php'))
        {
            echo '<td align="left" class="' . $class_css . '" ><a href="'. get_module_url($module['label']) . '/admin.php" >' . $module['name'] . '</a></td>' . "\n";
        }
        else
        {
            echo '<td align="left" class="' . $class_css . '" >' . $module['name'] . '</td>' . "\n";
        }

        //reorder column

        //up

        echo '<td align="center">' . "\n";
        if (!($iteration==1))
        {
            echo '<a href="module_dock.php?cmd=up&amp;module_id=' . $module['id'] . '&amp;dock='.urlencode($dock).'">'
            .    '<img src="' . $imgRepositoryWeb . 'up.gif" border="0" alt="' . get_lang('Up') . '" />'
            .    '</a>' . "\n"
            ;
        }
        else
        {
        	echo '&nbsp;';
        }
        echo '</td>' . "\n";

        //down

        echo '<td align="center">' . "\n";
        if ($iteration != $enditeration)
        {
            echo '<a href="module_dock.php?cmd=down&amp;module_id=' . $module['id'] . '&amp;dock=' . urlencode($dock) . '">'
            .    '<img src="' . $imgRepositoryWeb . 'down.gif" border="0" alt="' . get_lang('Down') . '" />'
            .    '</a>'
            ;
        }        
        else
        {
        	echo '&nbsp;';
        }
        echo '</td>' . "\n";

        //remove links

        echo '<td align="center">' . "\n"
        .    '<a href="module_dock.php?cmd=remove&amp;module_id=' . $module['id'] . '&amp;dock=' . urlencode($dock) . '">'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />'
        .    '</a>'
        .    '</td>' . "\n";

        $iteration++;
    }

    //end table...

    echo '</tbody>'
    .    '</table>';


    //Display BOTTOM Pager list

    echo $myPager->disp_pager_tool_bar('module_dock.php?dock='.$dock);

}

include $includePath . '/claro_init_footer.inc.php';
?>