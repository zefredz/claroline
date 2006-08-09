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
require_once $includePath . '/lib/html.lib.php';
require_once $includePath . '/lib/module.manage.lib.php';

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


$nameTools = get_lang('Module list');
$interbredcrump[]= array ('url' => $rootAdminWeb,'name' => get_lang('Administration'));
$dialogBox  ='';

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
"<script type=\"text/javascript\">
function confirmation (name)
{
    if (confirm(\" ".clean_str_for_javascript(get_lang("Are you sure to uninstall the module "))." \"+ name + \" ?\"))
        {return true;}
    else
        {return false;}
}
</script>";

//CONFIG and DEVMOD vars :

$modulePerPage = get_conf('modulePerPage' , 20);

$typeLabel['']        = get_lang('No name');
$typeLabel['tool']    = get_lang('Tools');
$typeLabel['applet']  = get_lang('Applet');
$typeLabel['lang']    = get_lang('Languages');
$typeLabel['theme']   = get_lang('Themes');
$typeLabel['extauth'] = get_lang('External authentication');


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

switch ( $cmd )
{
    case 'activ' :
        activate_module($module_id);
        break;

    case 'desactiv' :
        deactivate_module($module_id);
        break;

    case 'up' :
        move_module_tool($courseToolId, 'up');
        break;

    case 'down' :
        move_module_tool($courseToolId, 'down');
        break;

    case 'uninstall' :

        $moduleInfo = get_module_info($module_id);
        if (in_array($moduleInfo['label'], $old_tool_array))
        {
            $dialogBox  = get_lang('This tool can not be uninstalled.');
        }
        else
        {
            $result_log = uninstall_module($module_id);
            $dialogBox  = get_lang('Module uninstallation') . ' : <br>';
            foreach ( $result_log as $log) $dialogBox .= $log . '<br>';
        }
        break;

    case 'show_install' :
        $dialogBox = '<p>' . "\n"
        .            get_lang('Imported modules must consist of a zip file and be compatible with your Claroline version.') . '<br />' . "\n"
        .            get_lang('Find more available modules on <a href="http://www.claroline.net/">Claroline.net</a>.')
        .            '</p>' . "\n\n"
        .            '<form enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        .            '<input name="cmd" type="hidden" value="do_install" />' . "\n"
        .            '<input name="uploadedModule" type="file" /><br /><br />' . "\n"
        .            '<input value="' . get_lang('Install module') . '" type="submit" /> ' . "\n"
        .            claro_html_button( $_SERVER['PHP_SELF'], get_lang('Cancel'))
        .            '</form>' . "\n"
        ;
        break;

    case 'do_install' :
        {
            //include needed librabries for treatment

            if( false !== $modulePath= get_and_unzip_uploaded_package())

            $result_log = install_module($modulePath);
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

$moduleTypeList = claro_get_module_types();
//$moduleTypeList = array_merge($moduleTypeList, array_keys($typeLabel));

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

$module_docks = array();

foreach ($moduleList as $module)
{
    $module_dock[$module['id']] = array();

    $sql = "SELECT D.`id`    AS dock_id,
                   D.`name`  AS dockname
            FROM `" . $tbl_dock . "` AS D
            WHERE D.`module_id`=" . (int) $module['id'];

    $module_dock[$module['id']] = claro_sql_query_fetch_all($sql);

    if (!file_exists(get_module_path($module['label'])))
    {
        $dialogBox .= get_lang('<b>Warning : </b>') . get_lang('There is a module installed in DB : <b><i>%module_name</i></b> for which there is no folder on the server.',array('%module_name'=>$module['label'])).'<br/>' . "\n";
    }

}

//do a check of modules to see if there is anyhting to install

$modules_found = check_module_repositories();

foreach ($modules_found['folder'] as $module_folder)
{
    if (!isset($dialogBox)) $dialogBox= '';
    $dialogBox .= get_lang('<b>Warning : </b>') . get_lang('There is a folder called <b><i>%module_name</i></b> for which there is no module installed.', array('%module_name'=>$module_folder)) . '<br/>' . "\n";
}

//needed info for reorder buttons to known if we must display action (or not)

$course_tool_min_rank = get_course_tool_min_rank();
$course_tool_max_rank = get_course_tool_max_rank();

//----------------------------------
// DISPLAY
//----------------------------------

$noQUERY_STRING = true;
include $includePath . '/claro_init_header.inc.php';

//display title

echo claro_html_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if ($dialogBox != '' ) echo claro_html_message_box($dialogBox);

//display action links
echo '<a class="claroCmd" href="module_list.php?cmd=show_install">' . get_lang('Install module') . '</a>' . "\n"
.    '<br/><br/>' . "\n\n"


//display tabbed navbar

.    '<div id="moduletypecontainer">' . "\n"
.    '<ul id="moduletypelist">' . "\n"
;

//display the module type tabbed naviguation bar

foreach ($moduleTypeList as $type)
{
    if ($typeReq == $type)
    {
        echo '<li id="active"><a href="module_list.php?typeReq=' . $type . '" id="current">' . $typeLabel[$type] . '</a></li>' . "\n";
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

echo $myPager->disp_pager_tool_bar('module_list.php?typeReq='.$typeReq);

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
    echo '<th colspan="2">' . get_lang('Display order')       . '</th>' . "\n";
}
echo '<th>' . get_lang('Properties')          . '</th>' . "\n"
.    '<th>' . get_lang('Uninstall')           . '</th>' . "\n"
.    '<th>' . get_lang('Activation')          . '</th>' . "\n"
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

    if (file_exists(get_module_path($module['label']) . '/admin.php') && ($module['type']!='tool'))
    {
        echo '<td align="left"><a href="' . get_module_url($module['label']) . '/admin.php" >' . $module['name'] . '</a></td>' . "\n";
    }
    else
    {
        echo '<td align="left">' . $module['name'] . '</td>' . "\n";
    }

    //displaying location column

    if ($module['type']!='tool' )
    {
        echo '<td align="left"><small>';
        foreach ($module_dock[$module['id']] as $dock)
        {
           echo '<a href="module_dock.php?dock=' . $dock['dockname'] . '">' . $dock['dockname'] . '</a> <br/>';
        }

        if (empty($module_dock[$module['id']]))
        {
            echo '<span align="center">' . get_lang('No dock chosen') . '</span>';
        }
        echo '</small></td>' . "\n";
   }
   else
   {
        //up command
        if ($course_tool_min_rank!=$module['rank'])
        {
            echo '<td align="center">'
            .    '<a href="module_list.php?courseToolId='.$module['courseToolId'].'&amp;cmd=up">'
            .    '<img src="' . $imgRepositoryWeb . 'up.gif" alt="'.get_lang('Move up').'">'
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
            .    '<a href="module_list.php?courseToolId='.$module['courseToolId'].'&amp;cmd=down">'
            .    '<img src="' . $imgRepositoryWeb . 'down.gif" alt="'.get_lang('Move down').'">'
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
    .    '<a href="module.php?module_id='.$module['id'].'&amp;item=GENERAL">'
    .    '<img src="' . $imgRepositoryWeb . 'settings.gif" border="0" alt="' . get_lang('Properties') . '" />'
    .    '</a>'
    .    '</td>' . "\n";

    //uninstall link
	
	if (!in_array($module['label'],$nonuninstalable_tool_array))
	{  	
    	echo '<td align="center">'
        .    '<a href="module_list.php?module_id=' . $module['id'] . '&amp;typeReq='.$typeReq.'&amp;cmd=uninstall"'
    	.    ' onClick="return confirmation(\'' . $module['name'].'\');">'
    	.    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />'
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
        	echo '<a href="module_list.php?cmd=desactiv&amp;module_id=' . $module['id'] . '&amp;typeReq=' . $typeReq . '"><small>'
         	. get_lang('Activated') . '</small></a>'
        	;
    	}
    	else
    	{
        	echo '<a href="module_list.php?cmd=activ&amp;module_id=' . $module['id'] . '&amp;typeReq='.$typeReq.'"><small>' . get_lang('Deactivated') . '</small></a>';
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

include $includePath . '/claro_init_footer.inc.php';

?>