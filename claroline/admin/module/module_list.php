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

$cidReset=true;$gidReset=true;
require '../../inc/claro_init_global.inc.php';

//SECURITY CHECK

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

//DECLARE NEEDED LIBRARIES

require_once get_path('incRepositorySys') . '/lib/pager.lib.php';
require_once get_path('incRepositorySys') . '/lib/sqlxtra.lib.php';
require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
require_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
require_once get_path('incRepositorySys') . '/lib/file.lib.php';
require_once get_path('incRepositorySys') . '/lib/html.lib.php';
require_once get_path('incRepositorySys') . '/lib/module.manage.lib.php';
require_once get_path('incRepositorySys') . '/lib/backlog.class.php';

//OLD TOOLS ;

$old_tool_array = array('CLANN',
                        'CLCAL',
                        'CLFRM',
                        'CLCHT',
                        'CLDOC',
                        'CLDSC',
                        'CLUSR',
                        'CLLNP',
                        'CLQWZ',
                        'CLWRK',
                        'CLWIKI',
                        'CLLNK',
                        'CLGRP'
                        );

//UNDEACTIVABLE	TOOLS array

$undeactivable_tool_array = array('CLDOC',
                                  'CLGRP'
                                  );

//NONUNINSTALABLE TOOLS array

$nonuninstalable_tool_array = array('CLANN',
                        'CLCAL',
                        'CLFRM',
                        'CLCHT',
                        'CLDOC',
                        'CLDSC',
                        'CLUSR',
                        'CLLNP',
                        'CLQWZ',
                        'CLWRK',
                        'CLWIKI',
                        'CLLNK',
                        'CLGRP'
                        );

//SQL table name

$tbl_name        = claro_sql_get_main_tbl();
$tbl_module      = $tbl_name['module'];
$tbl_module_info = $tbl_name['module_info'];
$tbl_dock        = $tbl_name['dock'];
$tbl_course_tool = $tbl_name['tool'];
$tbl = claro_sql_get_tbl(array('module_tool'));


$nameTools = get_lang('Modules');
$interbredcrump[]= array ('url' => get_path('rootAdminWeb'),'name' => get_lang('Administration'));
$msgList= array();

$htmlHeadXtra[] =
"<script type=\"text/javascript\">
function confirmation (name)
{
    if (confirm(\" ".clean_str_for_javascript(get_lang("Are you sure you want to uninstall the module "))." \"+ name + \" ?\"))
        {return true;}
    else
        {return false;}
}
</script>";

//CONFIG and DEVMOD vars :

//TODO remove pagination
$modulePerPage = 1000;

$typeLabel['']        = get_lang('No name');
$typeLabel['tool']    = get_lang('Tools');
$typeLabel['applet']  = get_lang('Applets');
$typeLabel['lang']    = get_lang('Language packs');
$typeLabel['theme']   = get_lang('Themes');
$typeLabel['extauth'] = get_lang('External authentication drivers');

$moduleTypeList = array( 'tool', 'applet' );


$cmd          = (isset($_REQUEST['cmd'])          ? $_REQUEST['cmd']          : null);
$module_id    = (isset($_REQUEST['module_id'])    ? $_REQUEST['module_id']    : null );
$courseToolId = (isset($_REQUEST['courseToolId']) ? $_REQUEST['courseToolId'] : null );
$dockname     = (isset($_REQUEST['dockname'])     ? $_REQUEST['dockname']     : null );
$typeReq      = (isset($_REQUEST['typeReq'])      ? $_REQUEST['typeReq']      : 'tool');
$offset       = (isset($_REQUEST['offset'])       ? $_REQUEST['offset']       : 0 );
$pagerSortDir = (isset($_REQUEST['dir' ])         ? $_REQUEST['dir' ]         : SORT_ASC);


//----------------------------------
// EXECUTE COMMAND
//----------------------------------
// TODO improve status message and backlog display
switch ( $cmd )
{
    case 'activ' :
        {
            list( $backlog, $success ) = activate_module($module_id);
            $details = $backlog->output();
            if ( $success )
            {
                $summary  = get_lang('Module activation succeeded');
            }
            else
            {
                $summary  = get_lang('Module activation failed');
            }
            $msgList[][] = Backlog_Reporter::report( $summary, $details );
            break;
        }
    case 'desactiv' :
            list( $backlog, $success ) = deactivate_module($module_id);
            $details = $backlog->output();
            if ( $success )
            {
                $summary  = get_lang('Module deactivation succeeded');
            }
            else
            {
                $summary  = get_lang('Module deactivation failed');
            }
            $msgList[][] = Backlog_Reporter::report( $summary, $details );
            break;
    case 'mvUp' :
            if(!is_null($courseToolId)) move_module_tool($courseToolId, 'up');
            break;
    case 'mvDown' :
            if(!is_null($courseToolId)) move_module_tool($courseToolId, 'down');
            break;
    case 'exUninstall' :
        {
            $moduleInfo = get_module_info($module_id);
            if (in_array($moduleInfo['label'], $old_tool_array))
            {
                $msgList['error'][]  = get_lang('This tool can not be uninstalled.');
            }
            else
            {
                list( $backlog, $success ) = uninstall_module($module_id);
                $details = $backlog->output();
                if ( $success )
                {
                    $summary  = get_lang('Module uninstallation succeeded');
                }
                else
                {
                    $summary  = get_lang('Module uninstallation failed');
                }
                $msgList[][] = Backlog_Reporter::report( $summary, $details );
            }
            break;
        }
    case 'exInstall' :
        {
            //include needed librabries for treatment
            if ( array_key_exists( 'uploadedModule', $_FILES ) )
            {
                if ( file_upload_failed( $_FILES['uploadedModule'] ) )
                {
                    $summary = get_lang('Module upload failed');
                    $details = get_file_upload_error_message( $_FILES['uploadedModule'] );

                    $msgList['error'][] = Backlog_Reporter::report( $summary, $details );
                }
                else
                {
                    if( false !== ($modulePath = get_and_unzip_uploaded_package()) )
                    {
                        list( $backlog, $module_id ) = install_module($modulePath);
                        $details = $backlog->output();
                        if ( false !== $module_id )
                        {
                            $summary  = get_lang('Module installation succeeded');
                            $moduleInfo = get_module_info($module_id);
                            $typeReq = $moduleInfo['type'];
                        }
                        else
                        {
                            $summary  = get_lang('Module installation failed');
                        }
                        $msgList[][] = Backlog_Reporter::report( $summary, $details );
                    }
                    else
                    {
                        $summary = get_lang('Module unpackaging failed');
                        $details = implode( "<br />\n", claro_failure::get_last_failure() );
                        $msgList['error'][] = Backlog_Reporter::report( $summary, $details );
                    }
                }
            }
            else
            {
                $summary = get_lang('Module upload failed');
                $details = get_lang('Missing or wrong name (developer was drunk)');
                claro_die( Backlog_Reporter::report( $summary, $details ) );
            }

            break;
        }
    case 'rqInstall' :
        {
            $msgList['warn'][] = '<p>' . "\n"
            .            get_lang('Imported modules must consist of a zip file and be compatible with your Claroline version.') . '<br />' . "\n"
            .            get_lang('Find more available modules on <a href="http://www.claroline.net/">Claroline.net</a>.')
            .            '</p>' . "\n\n"
            ;
            $msgList['form'][] = '<form enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
            .            '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
            .            '<input name="cmd" type="hidden" value="exInstall" />' . "\n"
            .            '<input name="uploadedModule" type="file" /><br /><br />' . "\n"
            .            '<input value="' . get_lang('Install module') . '" type="submit" />&nbsp;' . "\n"
            .            claro_html_button( $_SERVER['PHP_SELF'], get_lang('Cancel'))
            .            '</form>' . "\n"
            ;
            break;
        }
    case 'exLocalInstall' :
        {
            if ( isset( $_REQUEST['moduleDir'] ) )
            {
                $moduleDir = str_replace ( '../', '', $_REQUEST['moduleDir']);
                $moduleRepositorySys = get_path('rootSys') . 'module/';
                $modulePath = $moduleRepositorySys.$moduleDir.'/';

                if ( file_exists( $modulePath ) )
                {
                    list( $backlog, $module_id ) = install_module($modulePath, true);
                    $details = $backlog->output();
                    if ( false !== $module_id )
                    {
                        $summary  = get_lang('Module installation succeeded');
                        $moduleInfo = get_module_info($module_id);
                        $typeReq = $moduleInfo['type'];
                    }
                    else
                    {
                        $summary  = get_lang('Module installation failed');
                    }
                }
                else
                {
                    $summary  = get_lang('Module installation failed');
                    $details = get_lang('Module directory not found');
                }

            }
            else
            {
                $summary  = get_lang('Module installation failed');
                $details = get_lang('Missing module directory');
            }

            $msgList[][] = Backlog_Reporter::report( $summary, $details );
        }
}

if ( empty( $typeReq ) && $module_id )
{
    $moduleInfo = get_module_info($module_id);
    $typeReq = $moduleInfo['type'];
}

//----------------------------------
// FIND INFORMATION
//----------------------------------

// $moduleTypeList = claro_get_module_types();
// $moduleTypeList = array_merge($moduleTypeList, array_keys($typeLabel));

switch($typeReq)
{
    case 'applet' :

        $sqlSelectType = "       D.`id`    AS dock_id, " . "\n"
        .                "       D.`name`  AS dockname," . "\n"
        ;

        $sqlJoinType = " LEFT JOIN `" . $tbl_dock . "` AS D " . "\n"
        .              "        ON D.`module_id`= M.id " . "\n"
        ;
        $orderType = "";
        break;

    case 'tool'   :

        $sqlSelectType = "       CT.`id`    AS courseToolId, " . "\n"
        .                "       CT.`icon`  AS icon," . "\n"
        .                "       CT.`script_url` AS script_url," . "\n"
        .                "       CT.`def_rank` AS rank," . "\n"
        ;
        $sqlJoinType = " LEFT JOIN `" . $tbl_course_tool . "` AS CT " . "\n"
        .              "        ON CT.`claro_label`= M.label " . "\n"
        ;
        $orderType = "ORDER BY `def_rank` \n";
        break;
    default       : $sqlSelectType=""; $sqlJoinType = ""; $orderType = "";

}

$sql = "SELECT M.`id`              AS `id`,         \n"
.      "       M.`label`           AS `label`,      \n"
.      "       M.`name`            AS `name`,       \n"
.      "       M.`activation`      AS `activation`, \n"
.      $sqlSelectType
.      "       M.`type`            AS `type`        \n"
.      "FROM `" . $tbl_module . "` AS M             \n"
.      $sqlJoinType . "\n "
.      "WHERE M.`type` = '" . addslashes($typeReq) . "' \n"
.      "GROUP BY `id` \n"
.      $orderType . "\n "
;

//pager creation

$myPager    = new claro_sql_pager($sql, $offset, $modulePerPage);
$moduleList = $myPager->get_result_list();

//find docks in which the modules do appear.

$module_dock = array();
$dockList = get_dock_list($typeReq); // will be usefull to display correctly the dock names

foreach ($moduleList as $module)
{
    $module_dock[$module['id']] = array();

    $module_dock[$module['id']] = get_module_dock_list($module['id']);

    if (!file_exists(get_module_path($module['label'])))
    {
        $msgList['warn'][] = get_lang('<b>Warning : </b>') . get_lang('There is a module installed in DB : <b><i>%module_name</i></b> for which there is no folder on the server.',array('%module_name'=>$module['label'])).'<br/>' . "\n";
    }

}

//do a check of modules to see if there is anyhting to install

$modules_found = check_module_repositories();

foreach ($modules_found['folder'] as $module_folder)
{
    $url = $_SERVER['PHP_SELF'] . '?cmd=exLocalInstall&amp;moduleDir=' . rawurlencode($module_folder);
    $msgList['warn'][] = get_lang('There is a folder called <b><i>%module_name</i></b> for which there is no module installed.', array('%module_name'=>$module_folder))
    . get_lang( 'To install this module click <a href="%url">here</a>.', array('%url' => $url ) )
    . '<br/>' . "\n"
    ;
}

//needed info for reorder buttons to known if we must display action (or not)

$course_tool_min_rank = get_course_tool_min_rank();
$course_tool_max_rank = get_course_tool_max_rank();


$moduleMenu[] = claro_html_cmd_link('module_list.php?cmd=rqInstall', get_lang('Install module'));
//----------------------------------
// DISPLAY
//----------------------------------

$noQUERY_STRING = true;
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

//display title

echo claro_html_tool_title($nameTools)

//Display Forms or dialog box(if needed)

.    claro_html_msg_list($msgList)
.    claro_html_menu_horizontal($moduleMenu)

//display tabbed navbar

.    '<div>' . "\n"
.    '<ul id="navlist">' . "\n"
;

//display the module type tabbed naviguation bar

foreach ($moduleTypeList as $type)
{
    if ($typeReq == $type)
    {
        echo '<li><a href="module_list.php?typeReq=' . $type . '" class="current">' . $typeLabel[$type] . '</a></li>' . "\n";
    }
    else
    {
        echo '<li><a href="module_list.php?typeReq=' . $type . '">' . $typeLabel[$type] . '</a></li>' . "\n";
    }
}

echo '</ul>' . "\n"
.    '</div>' . "\n";

//Display list

//Display Pager list

echo $myPager->disp_pager_tool_bar('module_list.php?typeReq=' . $typeReq);

// start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">' . "\n"
.    '<th>' . get_lang('Icon')                . '</th>' . "\n"
.    '<th>' . get_lang('Module name')         . '</th>' . "\n";
if ($typeReq!='tool')
{
    echo '<th>' . get_lang('Display')             . '</th>' . "\n";
}
else
{
    echo '<th colspan="2">' . get_lang('Order')       . '</th>' . "\n";
}
echo '<th>' . get_lang('Properties')          . '</th>' . "\n"
.    '<th>' . get_lang('Uninstall')           . '</th>' . "\n"
.    '<th>' . get_lang('Activated')          . '</th>' . "\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n\n"
.    '<tbody>'
;

// Start the list of modules...
foreach($moduleList as $module)
{
    //display settings...
    $class_css = ($module['activation'] == 'activated') ? '' : ' class="invisible" ';

    //find icon
    $modulePath = get_module_path($module['label']);

    if (array_key_exists('icon',$module) && file_exists(get_module_path($module['label']) . '/' . $module['icon']))
    {
        $icon = '<img src="' . get_module_url($module['label']) . '/' . $module['icon'] . '" alt="" />';
    }
    elseif (file_exists(get_module_path($module['label']) . '/icon.png'))
    {
        $icon = '<img src="' . get_module_url($module['label']) . '/icon.png" alt="" />';
    }
    elseif (file_exists(get_module_path($module['label']) . '/icon.gif'))
    {
        $icon = '<img src="' . get_module_url($module['label']) . '/icon.gif" alt="" />';
    }
    else
    {
        $icon = '<small>' . get_lang('No icon') . '</small>';
    }


    //module_id and icon column

    echo  "\n"  . '<tr ' . $class_css . '>' . "\n"
    .    '<td align="center">' . $icon . '</td>' . "\n";

    //name column

    $moduleName = $module['name'];

    if (file_exists(get_module_path($module['label']) . '/admin.php') && ($module['type']!='tool'))
    {
        echo '<td align="left"><a href="' . get_module_url($module['label']) . '/admin.php" >' . get_lang($moduleName) . '</a></td>' . "\n";
    }
    else
    {
        echo '<td align="left">' . get_lang($moduleName) . '</td>' . "\n";
    }

    //displaying location column

    if ($module['type']!='tool' )
    {
        echo '<td align="left"><small>';
        if (empty($module_dock[$module['id']]))
        {
            echo '<span align="center">' . get_lang('No dock chosen') . '</span>';
        }
        else
        {
            foreach ($module_dock[$module['id']] as $dock)
            {
                echo '<a href="module_dock.php?dock=' . $dock['dockname'] . '">' . $dockList[$dock['dockname']] . '</a> <br/>';
            }
        }

        echo '</small></td>' . "\n";
    }
    else
    {
        //up command
        if ($course_tool_min_rank!=$module['rank'])
        {
            echo '<td align="center">'
            .    '<a href="module_list.php?courseToolId='.$module['courseToolId'].'&amp;cmd=mvUp">'
            .    '<img src="' . get_path('imgRepositoryWeb') . 'up.gif" alt="'.get_lang('Move up').'">'
            .    '</a>'
            .    '</td>' . "\n";
        }
        else
        {
            echo '<td>&nbsp;</td>' . "\n";
        }

        //down command
        if ($course_tool_max_rank!=$module['rank'])
        {
            echo '<td align="center">'
            .    '<a href="module_list.php?courseToolId='.$module['courseToolId'].'&amp;cmd=mvDown">'
            .    '<img src="' . get_path('imgRepositoryWeb') . 'down.gif" alt="'.get_lang('Move down').'">'
            .    '</a>'
            .    '</td>' . "\n";
        }
        else
        {
            echo '<td>&nbsp;</td>' . "\n";
        }
    }

    //Properties link

    echo '<td align="center">'
    .    '<a href="module.php?module_id='.$module['id'].'">'
    .    '<img src="' . get_path('imgRepositoryWeb') . 'settings.gif" border="0" alt="' . get_lang('Properties') . '" />'
    .    '</a>'
    .    '</td>' . "\n";

    //uninstall link

    if (!in_array($module['label'],$nonuninstalable_tool_array))
    {
        echo '<td align="center">'
        .    '<a href="module_list.php?module_id=' . $module['id'] . '&amp;typeReq='.$typeReq.'&amp;cmd=exUninstall"'
        .    ' onClick="return confirmation(\'' . $module['name'].'\');">'
        .    '<img src="' . get_path('imgRepositoryWeb') . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />'
        .    '</a>'
        .    '</td>' . "\n";
        ;
    }
    else
    {
        echo '<td align="center">-</td>' . "\n";
    }

    //activation link

    echo '<td align="center" >';

    if (in_array($module['label'],$undeactivable_tool_array))
    {
        echo '-';
    }
    else
    {
        if ( 'activated' == $module['activation'] )
        {
            echo '<a href="module_list.php?cmd=desactiv&amp;module_id='
            . $module['id'] . '&amp;typeReq=' . $typeReq .'" '
            . 'title="'.get_lang('Activated - Click to deactivate').'">'
            . '<img src="' . get_path('imgRepositoryWeb')
            . 'mark.gif" border="0" alt="'. get_lang('Activated') . '" /></a>'
            ;
        }
        else
        {
            echo '<a href="module_list.php?cmd=activ&amp;module_id='
            . $module['id'] . '&amp;typeReq='.$typeReq.'" '
            . 'title="'.get_lang('Deactivated - Click to activate').'">'
            . '<img src="' . get_path('imgRepositoryWeb')
            . 'block.gif" border="0" alt="'. get_lang('Deactivated') . '"/></a>';
        }
    }
    echo '</td>' . "\n";

    //end table line

    echo '</tr>' . "\n\n";
}

//end table...
echo '</tbody>' . "\n"
.    '</table>' . "\n\n";

//Display BOTTOM Pager list

echo $myPager->disp_pager_tool_bar('module_list.php?typeReq='.$typeReq);

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

/**
 * Return list of dock where a module is docked
 *
 * @param integer $moduleId
 * @return array of array ( id, name)
 */

function get_module_dock_list($moduleId)
{
    static $dockListByModule = array();

    if(!array_key_exists($moduleId,$dockListByModule))
    {
        $tbl_name        = claro_sql_get_main_tbl();
        $sql = "SELECT `id`    AS dock_id,
                       `name`  AS dockname
            FROM `" . $tbl_name['dock'] . "`
            WHERE `module_id`=" . (int) $moduleId;
        $dockListByModule[$moduleId] = claro_sql_query_fetch_all($sql);

    }
    return $dockListByModule[$moduleId];
}
?>