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
 * @since 1.8
 */

require '../../inc/claro_init_global.inc.php';

//SECURITY CHECK

if ( ! $_uid )
{
    claro_disp_auth_form();
}

if ( ! $is_platformAdmin )
{
    claro_die(get_lang('Not allowed'));
}

//CONFIG and DEVMOD vars :

//SQL table name

$tbl_name        = claro_sql_get_main_tbl();
$tbl_module      = $tbl_name['module'];
$tbl_module_info = $tbl_name['module_info'];
$tbl_dock        = $tbl_name['dock'];

//NEEDED LIBRAIRIES

require_once $includePath . '/lib/module.manage.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => 'module_list.php', 'name' => get_lang('Module list'));

$nameTools = get_lang('Module settings');
$undeactivable_tool_array = array('CLDOC',
								  'CLGRP'
								 );	

//NEEDED CSS

$htmlHeadXtra[] = "
<style type=\"text/css\">
#modulesettinglist
{
padding: 3px 0;
margin-left: 0;
border-bottom: 1px solid #778;
font: bold 12px Verdana, sans-serif;
}

#modulesettinglist li
{
list-style: none;
margin: 0;
display: inline;
}

#modulesettinglist li a
{
padding: 3px 0.5em;
margin-left: 3px;
border: 1px solid #778;
border-bottom: none;
background: #DDE;
text-decoration: none;
}

#modulesettinglist li a:link { color: #448; }
#modulesettinglist li a:visited { color: #667; }

#modulesettinglist li a:hover
{
color: #000;
background: #AAE;
border-color: #227;
}

#modulesettinglist li a#current
{
background: white;
border-bottom: 1px solid white;
}

#navlist, .tabTitle
{
padding: 3px 0;
margin-left: 0;
border-bottom: 1px solid #778;
font: bold 12px Verdana, sans-serif;
}

#navlist li, .tabTitle li
{
list-style: none;
margin: 0;
display: inline;
}

#navlist li a, .tabTitle li a
{
padding: 3px 0.5em;
margin-left: 3px;
border: 1px solid #778;
border-bottom: none;
background: #DDDEBC;
text-decoration: none;
}

#navlist li a:link { color: #448; }
#navlist li a:visited { color: #667; }

#navlist li a:hover
{
color: #000;
background: #AAE;
border-color: #227;
}

#navlist li a.current
{
color: #000;
background: white;
border-bottom: 1px solid white;
}

#navlist li a.viewall
{
align : right;
background: white;
border-right: 0px solid white;
border-top: 0px solid white;
border-left: 0px solid white;
}

.configSectionDesc
{
    padding: 3px 0.5em;
    margin-left: 10px;
    background: #eD2;
    border: 1px solid #778;
    // Yes its awfull but volontary to be changed

}
</style>
";

$htmlHeadXtra[] =
"<script type=\"text/javascript\">
function confirmMakeVisible ()
{
    if (confirm(\" ".clean_str_for_javascript(get_lang("Are you sure you want to make this module visible in all courses ?"))."\"))
        {return true;}
    else
        {return false;}
}
function confirmMakeInVisible ()
{
    if (confirm(\" ".clean_str_for_javascript(get_lang("Are you sure you want to make this module invisible in all courses ?"))."\"))
        {return true;}
    else
        {return false;}
}
</script>";

//----------------------------------
// GET REQUEST VARIABLES
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : null);
$item = (isset($_REQUEST['item'])? $_REQUEST['item'] : 'GLOBAL');
$section_selected = (isset($_REQUEST['section'])? $_REQUEST['section'] : null);
$moduleId = (isset($_REQUEST['module_id'])? $_REQUEST['module_id'] : null);
$module = get_module_info($moduleId);
$dockList = get_dock_list($module['type']);

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

switch ( $cmd )
{
    case 'activ' :
    {
        if (activate_module($moduleId))
        {
            $dialogBox = get_lang('Module sucessfully activated');
            $module['activation']  = 'activated';
        }
        else
        {
            $dialogBox = get_lang('Cannot activate module');
        }
        break;
    }
    case 'deactiv' :
    {
        if (deactivate_module($moduleId))
        {
            $dialogBox = get_lang('Module sucessfully deactivated');
            $module['activation']  = 'deactivated';
        }
        else
        {
            $dialogBox = get_lang('Cannot deactivate module');
            $module['activation']  = 'activated';
        }
        break;
    }
    case 'movedock' :
    {
        if(is_array($dockList))
        {
            foreach ($dockList as $thedock)
            {
                if (isset($_REQUEST[$thedock]))
                {
                    add_module_in_dock($moduleId, $thedock);
                }
                else
                {
                    remove_module_dock($moduleId, $thedock);
                }
            }
            $dialogBox = get_lang('Changes in the display of the module have been applied');
        }
        break;
    }
    case 'makeVisible':
    case 'makeInvisible':
    {
        $visibility = ( 'makeVisible' == $cmd ) ? true : false;
        
        list ( $log, $success ) = set_module_visibility( $moduleId, $visibility );
        
        if ( $success )
        {
            $dialogBox = get_lang('Module visibility updated');
        }
        else
        {
            $dialogBox = get_lang('Failed to update module visibility');
        }
        
        break;
    }
}

// create an array with only dock names

$sql = "SELECT `name` AS `dockname`
        FROM `" . $tbl_dock        . "`
        WHERE `module_id` = " . (int) $moduleId;

$module_dock = claro_sql_query_fetch_all($sql);

$dock_checked = array();

foreach($module_dock as $thedock)
{
    $dock_checked[] = $thedock['dockname'];
}

//----------------------------------
// DISPLAY
//----------------------------------

include $includePath . '/claro_init_header.inc.php';

// find module icon, if any

if (array_key_exists('icon',$module) && file_exists(get_module_path($module['label']) . '/' .$module['icon']))
{
    $icon = '<img src="' . get_module_url($module['label']) . '/' . $module['icon'] . '" />';
}
elseif (file_exists(get_module_path($module['label']) . '/icon.png'))
{
    $icon = '<img src="' . get_module_url($module['label']) . '/icon.png" />';
}
elseif (file_exists(get_module_path($module['label']) . '/icon.gif'))
{
    $icon = '<img src="' . get_module_url($module['label']) . '/icon.gif" />';
}
else
{
    $icon = '<small>' . get_lang('No icon') . '</small>';
}

//display title

echo claro_html_tool_title($nameTools . ' : ' . $module['module_name']);

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) )
{
    echo claro_html_message_box($dialogBox);
}

//display tabbed navbar

echo  '<div id="modulesettingscontainer">'
    . '<ul id="modulesettinglist">'
    . "\n"
    ;

//display the module type tabbed naviguation bar

if ($item == 'GLOBAL')
{
    echo '<li><a href="module.php?module_id='.$moduleId
        . '&amp;item=GLOBAL" id="current">'
        . get_lang('Global settings').'</a></li>'
        . "\n"
        ;
}
else
{
    echo '<li><a href="module.php?module_id='.$moduleId.'&amp;item=GLOBAL">'
        . get_lang('Global settings').'</a></li>'
        . "\n"
        ;
}

$config_code = $module['label'];

// new config object
require_once $includePath . '/lib/configHtml.class.php';

$config = new ConfigHtml($config_code);
    	
if ( $config->load() )
{
	if ($item == 'LOCAL')
    {
		echo '<li><a href="module.php?module_id='.$moduleId
            . '&amp;item=LOCAL" id="current">'
            . get_lang('Local settings').'</a></li>'
            . "\n"
            ;
    }
	else
    {
		echo '<li><a href="module.php?module_id='.$moduleId.'&amp;item=LOCAL">'
            . get_lang('Local settings').'</a></li>'
            . "\n"
            ;
    }
}

if ($item == 'GENERAL' || is_null($item))
{
	echo '<li><a href="module.php?module_id='.$moduleId
        . '&amp;item=GENERAL" id="current">'
        . get_lang('About').'</a></li>'
        . "\n"
        ;
}
else
{
	echo '<li><a href="module.php?module_id='.$moduleId.'&amp;item=GENERAL">'
        . get_lang('About').'</a></li>'
        . "\n"
        ;
}

echo '</ul>'. "\n"
    . '</div>'. "\n"
    ;


switch ($item)
{
	case 'GLOBAL':
    {
        echo claro_html_tool_title(array('subTitle' => get_lang('Platform Settings')));
        
        echo '<table>' . "\n";
    
        //Activation form
        if (in_array($module['label'],$undeactivable_tool_array))
        {
            $action_link = get_lang('This module cannot be deactivated');
	    }
        elseif ( 'activated' == $module['activation'] )
    	{
            $activ_form  = 'deactiv';
        	$action_link = '<a href="' . $_SERVER['PHP_SELF'] 
                . '?cmd='.$activ_form.'&module_id='.$module['module_id']
                . '&item=GLOBAL" title="'
                . get_lang('Activated - Click to deactivate').'">' 
                . '<img src="' . $imgRepositoryWeb 
                . 'mark.gif" border="0" alt="'. get_lang('Activated') . '" /></a>'
                ;
    	}
    	else
    	{
        	$activ_form  = 'activ';
            $action_link = '<a href="' . $_SERVER['PHP_SELF'] 
                . '?cmd='.$activ_form.'&module_id=' 
                . $module['module_id'].'&item=GLOBAL" '
                . 'title="'.get_lang('Deactivated - Click to activate').'">' 
                . '<img src="' . $imgRepositoryWeb 
                . 'block.gif" border="0" alt="'. get_lang('Deactivated') . '"/></a>';
    	}
          
        echo '<td align="right" valign="top">'
          .    get_lang('Activation')
          .    ' : ' . "\n"
          .    '</td>' . "\n"
          .    '<td>' . "\n"
          .    $action_link . "\n"
          .    '</td>' . "\n"
          .    '</tr>' . "\n"
          .    '<tr>' . "\n"
          .    '<td>' . "\n"
          .    '<br/>' . "\n"
          .    '</td>' . "\n"
          .    '</tr>' . "\n"
          ;
          
        if ($module['type'] == 'tool')
        {
            echo '<tr><td>' 
                . get_lang( 'Visibility' )
                . ' : '
                .    '</td>' . "\n"
                .    '<td>' . "\n"
                . '<small><a href="'
                . $_SERVER['PHP_SELF'] . '?module_id=' . $module['module_id'].'&amp;cmd=makeVisible&amp;item=GLOBAL"'
                . 'title="'.get_lang( 'Make module visible in all courses' ).'"'
                . ' onclick="return confirmMakeVisible();">'
                . '<img src="' . $imgRepositoryWeb 
                . 'visible.gif" border="0" alt="'. get_lang('visible') . '"/> '
                . get_lang( 'make visible' )
                . '</a></small>'
                . " | "
                . '<small><a href="'
                . $_SERVER['PHP_SELF'] . '?module_id=' . $module['module_id'].'&amp;cmd=makeInvisible&amp;item=GLOBAL"'
                . 'title="'.get_lang( 'Make module invisible in all courses' ).'"'
                . ' onclick="return confirmMakeInVisible();">'
                . '<img src="' . $imgRepositoryWeb 
                . 'invisible.gif" border="0" alt="'. get_lang('invisible') . '"/> '
                . get_lang( 'make invisible' )
                . '</a></small>'
                . '<td><tr>' . "\n"
                ;
        }
        elseif ($module['type'] == 'applet')
        {
            echo '<tr><td><form action="' . $_SERVER['PHP_SELF'] . '?module_id=' . $module['module_id'] . '&amp;item='.$item.'" method="POST">';
    
            //choose the dock radio button list display
    
            $isfirstline = get_lang('Display') . ' : ';
    
            //display each option
            if (is_array($dockList) && $module['type']!='tool')
            {
                foreach ($dockList as $dock)
                {
        
                    if (in_array($dock,$dock_checked)) $is_checked = 'checked="checked"'; else $is_checked = "";
        
                    echo '<tr>' ."\n"
                    .    '<td syle="align:right">' . $isfirstline . '</td>' ."\n"
                    .    '<td>' ."\n"
                    .    '<input type="checkbox" name="' . $dock . '" value="' . $dock . '" ' . $is_checked . ' />'
                    .    $dock
                    .    '</td>' ."\n"
                    .    '</tr>' ."\n"
                    ;
                    $isfirstline = '';
                }
            }
            
            echo '</td></tr>';
    
              // display submit button
    
            echo '<tr>' ."\n"
            .    '<td style="text-align:right">' . get_lang('Save') . '&nbsp;:</td>' . "\n"
            .    '<td >'
            .    '<input type="hidden" name="cmd" value="movedock" />'. "\n"
            .    '<input type="submit" value="' . get_lang('Ok') . '" /> '. "\n"
            .    claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')) . '</td>' . "\n"
            .    '</tr>' . "\n"
            .    '</form>'
            ;
        }
        else // not a tool, not an applet
        {
            // nothing to do at the moment
        }
    
        echo '</table>' . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</table>' . "\n"
        ;
        break;
    }
	case 'LOCAL':
    {
		$form = '';

    	$url_params = '&module_id='. $moduleId .'&item='. htmlspecialchars($item); 
    	
    	$form = $config->display_section_menu($section_selected,$url_params);
    	
       	// init config name
    	$config_name = $config->config_code;
    	   
    	if ( isset($_REQUEST['cmd']) && isset($_REQUEST['property']) )
        {
            if ( 'save' == $_REQUEST['cmd'] )
            {
                if ( ! empty($_REQUEST['property']) )
                {
                    list ($message, $error) = generate_conf($config,$_REQUEST['property']);
                }
            }
            // display form
            $form .= $config->display_form($_REQUEST['property'],$section_selected,$url_params);
        }
        else
        {
            // display form
            $form .= $config->display_form(null,$section_selected,$url_params);
        }
    	
    	echo '<div style=padding-left:1em;padding-right:1em;>';

        if ( ! empty($message) )
        {
            echo claro_html_message_box(implode('<br />',$message));
        }

    	echo $form.'</div>';

        break;
    }
	default:
    {
        $moduleDescription = trim( $module['description'] );
        
        $moduleDescription = (empty( $moduleDescription ) )
            ? get_lang('No description given')
            : $moduleDescription
            ;
            
		echo claro_html_tool_title(array('subTitle' => get_lang('Description')))
		.    '<p>'
		.    htmlspecialchars( $moduleDescription )
		.    '</p>' . "\n"
        ;
        
        echo claro_html_tool_title(array('subTitle' => get_lang('General Informations'))) . "\n"
		.    '<table>' . "\n"
		.    '<tr>' . "\n"
		.    '<td colspan="2">' . "\n"
		.    '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">'
		.    get_lang('Icon')
		.    ' : </td>' . "\n"
		.    '<td>' . "\n"
		.    $icon . "\n"
		.    '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('Module name') . ' : </td>' . "\n"
		.    '<td >' . $module['module_name'] . '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('Type') . ' : </td>' . "\n"
		.    '<td>' . $module['type'] . '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('Version') . ' : </td>' . "\n"
		.    '<td >' . $module['version'] . '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('License') . ' : </td>' . "\n"
		.    '<td >General Public License</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('Author') . ' : </td>' . "\n"
		.    '<td >' . $module['author'] . '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('Contact') . ' : </td>' . "\n"
		.    '<td >' . $module['author_email'] . '</td>' . "\n"
		.    '</tr>' . "\n"
		.    '<tr>' . "\n"
		.    '<td align="right">' . get_lang('Website') . ' : </td>' . "\n"
		.    '<td><a href="' . $module['website'] . '">' . $module['website'] . '</a></td>' . "\n"
		.    '</tr>' . "\n"
		.    '</table>' . "\n"
		.    '</td>' . "\n"
		.    '<td>' . "\n"
		.    '<table>' . "\n"
		;
    }
}

echo '</table>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

include $includePath . '/claro_init_footer.inc.php';

?>
