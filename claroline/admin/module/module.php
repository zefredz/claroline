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

if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

//CONFIG and DEVMOD vars :

//SQL table name

$tbl_name        = claro_sql_get_main_tbl();
$tbl_module      = $tbl_name['module'];
$tbl_module_info = $tbl_name['module_info'];
$tbl_dock        = $tbl_name['dock'];

//NEEDED LIBRAIRIES

include_once(dirname(__FILE__) . '/module.inc.php');

require_once $includePath . '/lib/admin.lib.inc.php';

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => 'module_list.php', 'name' => get_lang('Module list'));
$nameTools = get_lang('Module settings');

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : null);
$moduleId = (isset($_REQUEST['module_id'])? $_REQUEST['module_id'] : null);
$module = get_module_info($moduleId);
$dockList = get_dock_list($module['type']);


switch ( $cmd )
{
    case 'activ' :
        activate_module($moduleId);
        break;

    case 'desactiv' :
        desactivate_module($moduleId);
        break;

    case 'movedock' :
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



$sql = "SELECT `name` AS `dockname`
        FROM `" . $tbl_dock        . "`
        WHERE `module_id` = " . (int) $moduleId;

$module_dock = claro_sql_query_fetch_all($sql);

//create an array with only dock names


$dock_checked = array();

foreach($module_dock as $thedock)
{
    $dock_checked[] = $thedock['dockname'];
}

//----------------------------------
// DISPLAY
//----------------------------------

include $includePath . '/claro_init_header.inc.php';

//display title

echo claro_html_tool_title($nameTools . ' : ' . $module['module_name']);

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) ) echo claro_html_message_box($dialogBox);

    if (array_key_exists('icon',$module) && file_exists(get_module_path($module['label']) . '/img/' . $module['icon']))
    {
        $icon = '<img src="' . get_module_url($module['label']) . '/img/' . $module['icon'] . '" />';
    }
    elseif (file_exists(get_module_path($module['label']) . '/icon.png'))
    {
        $icon = '<img src="' . get_module_path($module['label']) . '/icon.png" />';
    }
    elseif (file_exists(get_module_path($module['label']) . '/icon.gif'))
    {
        $icon = '<img src="' . get_module_path($module['label']) . '/icon.gif" />';

    }
    else $icon = '<small>' . get_lang('No icon') . '</small>';

echo claro_html_tool_title(array('subTitle' => get_lang('Description')))
.    '<p>'
.    $module['description']
.    '</p>' . "\n"
.    '<table name="main">' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td>' . "\n"
.    '<table>' . "\n"
.    '<tr>' . "\n"
.    '<td colspan="2">' . "\n"
.    claro_html_tool_title(array('subTitle' => get_lang('General Informations'))) . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr>' . "\n"
.    '<td align="right">' . "\n"
.    get_lang('Id') . "\n"
.    ': </td>' . "\n"
.    '<td>'
.    $module['module_id'] . "\n"
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
.    '<tr>' . "\n"
.    '<td colspan="2">' . "\n"
.    claro_html_tool_title(array('subTitle' => get_lang('Settings'))) . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr>' . "\n"
;
//Activation form

  if ('activated' == $module['activation'] )
  {
      $activ_form  = 'desactiv';
      $action_link = '[<b><small>'.get_lang('Activated').'</small></b>] | [<small><a href="' . $_SERVER['PHP_SELF'] . '?cmd='.$activ_form.'&module_id='.$module['module_id'].'">'.get_lang("Desactivate").'</a></small>]';
  }
  else
  {
      $activ_form  = 'activ';
      $action_link = '[<small><a href="' . $_SERVER['PHP_SELF'] . '?cmd='.$activ_form.'&module_id='.$module['module_id'].'">'.get_lang("Activate").'</a></small>] | [<small><b>'.get_lang('Desactivated').'</b></small>]';
  }

  echo '<td align="right" valign="top">'
  .    get_lang('Module status')
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

if ('coursetool' == $module['type'])
{
    echo '<tr>' . "\n"
    .    '<td>' . get_lang('Display') . ':</td>' . "\n"
    .    '<td>' . get_lang('Course tool list') . '</td>' . "\n"
    .    '</tr>'
    ;
}
else
{
    echo '<form action="' . $_SERVER['PHP_SELF'] . '?module_id=' . $module['module_id'] . '" method="POST">';

    //choose the dock radio button list display

    $isfirstline = get_lang('Display') . ' : ';

    //display each option
    if (is_array($dockList))
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

echo '</table>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

include $includePath . '/claro_init_footer.inc.php';

?>