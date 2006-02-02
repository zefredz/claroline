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

//CONFIG and DEVMOD vars :

$tbl_module      = "cl_module";
$tbl_module_info = "cl_module_info";
$tbl_dock        = "cl_dock"; 

$docks= array();
$docks[] = "campusBannerLeft";
$docks[] = "campusBannerRight";
$docks[] = "userBannerLeft";
$docks[] = "userBannerRight";
$docks[] = "courseBannerLeft";
$docks[] = "courseBannerRight";
$docks[] = "homePageCenter";
$docks[] = "campusHomepageBottom";
$docks[] = "homePageRightMenu";
$docks[] = "campusFooterCenter";
$docks[] = "campusFooterLeft";
$docks[] = "campusFooterRight";

//NEEDED LIBRAIRIES

include('module.inc.php');

require_once $includePath . '/lib/admin.lib.inc.php';

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => 'module_list.php', 'name' => get_lang('Module list'));
$nameTools = get_lang('Module settings');

//----------------------------------
// EXECUTE COMMAND
//----------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : null);
$module_id = (isset($_REQUEST['module_id'])? $_REQUEST['module_id'] : null);

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
    
    case 'movedock' :
    {
        $new_dock = (isset($_REQUEST['dock'])? $_REQUEST['dock'] : null);
        set_module_dock($module_id, $new_dock);        
    }
    break;
}

//----------------------------------
// Find info needed for display
//----------------------------------

$sql = "SELECT M.`label` AS label,
               M.`id`,
               M.`name`AS `module_name`,
               M.`activation` AS `activation`,
               M.`type` AS type,
               M.`module_info_id`,
               MI.*           
        FROM `".$tbl_module."` AS M, `".$tbl_module_info."` AS MI
        WHERE  M.`id` = MI.`module_id`
        AND    M.`id` = '".$module_id."'";
        
$module = claro_sql_query_get_single_row($sql);       

$sql = "SELECT D.`name` AS `dockname`     
        FROM `".$tbl_module."` AS M, `".$tbl_module_info."` AS MI, `".$tbl_dock."` AS D
        WHERE  D.`module_id` = ".(int)$module_id;
        
$module_dock = claro_sql_query_get_single_row($sql);


//----------------------------------
// DISPLAY
//----------------------------------

include $includePath . '/claro_init_header.inc.php';

//display title

echo claro_disp_tool_title($nameTools." : ".$module['module_name']);

?>

<h4> Description</h4>

<p><?php echo $module['description'];?></p>

<table name="main">
<tr valign="top">
<td>

<table>
  <tr>
   <td colspan="2">
     <h4> <?php echo get_lang('General Informations'); ?></h4>
   </td>
  </tr>

  <tr>
    <td align="right"><?php echo get_lang('Id'); ?> : </td>
    <td><?php echo $module['module_id'];?></td>
  </tr>  
  <tr>
    <td align="right"><?php echo get_lang('Icon'); ?> : </td>
    <td>
    <?php 
    if (file_exists($includePath.'/../module/'.$module['label'].'/icon.png')) 
    {
        echo '<img src="'.$rootWeb.'claroline/module/'.$module['label'].'/icon.png" />';    
    }
    elseif (file_exists($includePath.'/../module/'.$module['label'].'/icon.gif')) 
    {
        echo '<img src="'.$rootWeb.'claroline/module/'.$module['label'].'/icon.gif" />';
    }
    else
    {
        echo '<small>'.get_lang('No icon').'</small>';
    }
    ?>
    </td>
  </tr>  
  <tr>
    <td align="right"><?php echo get_lang('Module name'); ?> : </td>
    <td ><?php echo $module['module_name'];?></td>
  </tr>
  <tr>
   <td align="right"><?php echo get_lang('Type'); ?> : </td>
   <td><?php echo $module['type'];?></td>
  </tr>
  <tr>
    <td align="right"><?php echo get_lang('Version'); ?> : </td>
    <td ><?php echo $module['version'];?></td>
  </tr>
  <tr>
    <td align="right"><?php echo get_lang('License'); ?> : </td>
    <td >General Public License</td>
  </tr>
  <tr>
    <td align="right"><?php echo get_lang('Author'); ?> : </td>
    <td ><?php echo $module['author'];?></td>
  </tr>
  <tr>
    <td align="right"><?php echo get_lang('Contact'); ?> : </td>
    <td ><?php echo $module['author_email'];?></td>
  </tr>
  <tr>
    <td align="right"><?php echo get_lang('Website'); ?> : </td>
    <td><a href="<?php echo $module['website'];?>"><?php echo $module['website'];?></a></td>
  </tr>
</table>
</td>

<td>
<table>

  <tr>
   <td colspan="2">
     <h4> Settings</h4>
   </td>
  </tr>

  <tr>
        
<?php 
  
  //Activation form
      
  if ($module['activation']=="activated") 
  {
      
      $activ_state = get_lang('Activated');
      $activ_form  = "desactiv"; 
      $action_link = '<a href="' . $_SERVER['PHP_SELF'] . '?cmd='.$activ_form.'&module_id='.$module['module_id'].'">'.get_lang("Desactivate this module").'</a>'; 
  }  
  else 
  {
      $activ_state = get_lang('Desactivated');
      $activ_form  = "activ";
      $action_link = '<a href="' . $_SERVER['PHP_SELF'] . '?cmd='.$activ_form.'&module_id='.$module['module_id'].'">'.get_lang("Activate this module").'</a>'; 
  }

  echo ''
      .'  <td align="right" valign="top">'.get_lang('Module status').' : '. "\n"
      .'  </td> '. "\n"
      .'  <td> '. "\n"
      .      $activ_state.'<br/> <small>['.$action_link.']</small>'. "\n"
      .'  </td>'. "\n"; 
    
?> 
  </tr>
    
  <tr>
    <td>
      <br/>
    </td>
  </tr>   
<?php 
if ($module['type']=='coursetool')
{
    echo '<tr>
             <td>'.get_lang('Display'). ':</td>
             <td>'.get_lang('Course tool list').'</td>
          </tr>';
}
else
{
    echo '<form action="' . $_SERVER['PHP_SELF'] . '?module_id='.$module['module_id'].'" method="POST">';


    //choose the dock radio button list display

    $isfirstline = get_lang("Display"). ":";

      //display each option

    foreach ($docks as $dock)
    {
    
        if ($module_dock['dockname']==$dock) $is_checked = 'checked="checked"'; else $is_checked = "";
     
        echo '<tr>
                <td syle="align:right">'.$isfirstline.'</td>
                <td><input type="radio" name="dock" value="'.$dock.'" '.$is_checked.'/>'.$dock.'</td>       
              </tr>
              ';
          
        $isfirstline = "";
    }

      // display submit button

    echo '<tr>' ."\n"
         . '<td style="text-align: right">' . get_lang('Save') . '&nbsp;:</td>' . "\n"
         . '<td >'
         . '<input type="hidden" name="cmd" value="movedock"/>'. "\n"
         . '<input type="submit" value="' . get_lang('Ok') . '" /> '. "\n"
         . claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')) . '</td>' . "\n"
         . '</tr>' . "\n";
              
    echo "</form>";     
}
?>

</table>
</td>
</tr>
</table>
<?php
include $includePath . '/claro_init_footer.inc.php';
?>
