<?php // $Id$
/** 
 * CLAROLINE 
 *
 * Initialize conf settings
 * Try to read  old values in old conf files
 * Build new conf file content with these settings
 * write it.
 *
 * @version 1.6
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

$platform_id =  md5(realpath('../../inc/conf/def/CLMAIN.def.conf.inc.php'));
require '../../inc/claro_init_global.inc.php';

if (!$is_platformAdmin) claro_disp_auth_form();

DEFINE ('DISPLAY_WELCOME_PANEL', __LINE__);
DEFINE ('DISPLAY_RESULT_ERROR_PANEL', __LINE__);
DEFINE ('DISPLAY_RESULT_SUCCESS_PANEL', __LINE__);

DEFINE ('ERROR_WRITE_FAILED', __LINE__);

$display = DISPLAY_WELCOME_PANEL;

/**
 * include file
 */
    
include ($includePath.'/installedVersion.inc.php');
if(file_exists($includePath.'/currentVersion.inc.php'))
{
    include ($includePath.'/currentVersion.inc.php');
}
include ($includePath.'/lib/config.lib.inc.php');
include ($includePath.'/lib/fileManage.lib.php');
    
$thisClarolineVersion = $version_file_cvs;

$error = FALSE;

if ($_REQUEST['cmd'] == 'run')
{
    // Prepare repository to backup files
    $backupRepositorySys = $includePath .'/conf/bak.'.date('Y-z-B').'/';
    claro_mkdir($backupRepositorySys);

    $output = '<h3>' . $langConfigurationFile . '</h3>' . "\n" ;
    
    $output.= '<ol>' . "\n" ;
    
    // Gen conf file from def       
    $def_file_list = get_def_file_list();

    if(is_array($def_file_list))
    {
        
        /**
         Build table with old values in conf
         */
        
        $current_value_list = array();
        
        foreach ( array_keys($def_file_list) as $config_code )
        {
            unset($conf_def, $conf_def_property_list);
        
            $def_file  = get_def_file($config_code);
        
            if ( file_exists($def_file) ) 
            {
                require($def_file);
            }

            // load old conf file content
            $conf_def['old_config_file'][] = $conf_def['config_file'];
            if (is_array($conf_def['old_config_file']))
            {
                foreach ($conf_def['old_config_file'] as $old_file_name) 
                {
                    $current_value_list =array_merge($current_value_list,get_values_from_confFile($includePath.'/conf/'.$old_file_name,$conf_def_property_list));
                }
            }

            // set platform_id if not set in old claroline version
            $current_value_list['platform_id'] = $platform_id;
        }
       
        reset( $def_file_list );
        
        foreach ( $def_file_list as $config_code => $def)
        {
            $conf_file = get_conf_file($config_code);

            $output .= '<li>'.basename($conf_file)
                    .  '<ul >' . "\n";

            $okToSave = TRUE;
            
            unset($conf_def, $conf_def_property_list);
    
            $def_file  = get_def_file($config_code);
    
            if ( file_exists($def_file) )
                require($def_file);
            
            if ( is_array($conf_def_property_list) )
            {
                
                $propertyList = array();
                
                foreach($conf_def_property_list as $propName => $propDef )
                {
                    
                    if(isset($current_value_list[$propName]))
                    {  
                        $propValue = $current_value_list[$propName];
                        // get old value
                    }
                    else 
                    {
                        $propValue = $propDef['default'];                                 
                        // value never set, use default from .def
                    }

                    /**
                     * @todo user can be better informed how to react to this error.
                     */
                    if ( !validate_property($propValue, $propDef) )
                    {
                        $okToSave = FALSE;
                        $error = TRUE;
                        $output .= '<span class="warning">'.sprintf($lang_p_s_s_isInvalid, $propName, $propValue).'</span>' . '<br>' . "\n"
                                . sprintf( $lang_rules_s_in_s,$propDef['type'] ,basename($def_file)).' <br>' . "\n"
                                . var_export($propDef['acceptedValue'],1) . '<br>' . "\n" ;
                    }
                    else
                    {
                        $propertyList[] = array('propName'=>$propName
                                               ,'propValue'=>$propValue);
                    }
                }
            }
            else
            {
                $okToSave = FALSE;
                $error = TRUE;
            }
    
            if ($okToSave)
            {
                if ( !file_exists($conf_file) ) touch($conf_file);
    
                if ( is_array($propertyList) && count($propertyList)>0 )
                {

                    // backup old file 
                    $output .= '<li>' . $lang_oldFileBackup . ' ' ;
                    $fileBackup = $backupRepositorySys.basename($conf_file);
                    if (!@copy($conf_file, $fileBackup) )
                    {
                        $output .= '<span class="warning">' . $langFailed . '</span>';
                    }
                    else
                    {
                        $output .= '<span class="success">'. $langSucceeded . '</span>';
                    }

                    $output .= '</li>' . "\n" ;
                    // change permission
                    @chmod( $fileBackup, 600 );
                    @chmod( $fileBackup, 0600 );
                    $output .= '<li>' . $lang_fileUpgrade . ' ';
                    if ( write_conf_file($conf_def,$conf_def_property_list,$propertyList,$conf_file,realpath(__FILE__)) )
                    {
                        $output .= '<span class="success">'. $langSucceeded . '</span>';
                    }
                    else 
                    {
                        $output .= '<span class="warning">' . $langFailed . '</span>';
                        $error = TRUE;
                    }
                    $output .= '</li>'."\n";
                }
            }
            $output .= '</ul>' . "\n" 
                     . '</li>' . "\n";
        }
    }
    
    /**
    * Config file to undist
    */
    
    $arr_file_to_undist = array ( $includePath.'/../../textzone_top.inc.html',
                                  $includePath.'/../../textzone_right.inc.html',
                                  $includePath.'/conf/auth.conf.php'
                                );

    foreach ($arr_file_to_undist As $undist_this)
    {
        $output .= '<li>'. basename ($undist_this) . "\n"
                . '<ul><li>'.$langUndist.' : ' . "\n" ;
        if (claro_undist_file($undist_this))
        {
            $output .= '<span class="success">'. $langSucceeded . '</span>';
        }
        else
        {
            $output .= '<span class="warning">' . $langFailed . '</span>';
            $error = TRUE;
        }
        $output .= '</li>' . "\n" . '</ul>' . "\n"
                 . '</li>' . "\n";
    }
    $output .= '</ol>' . "\n";
    
    if (!$error)
    {
        $display = DISPLAY_RESULT_SUCCESS_PANEL;
        
        /**
         * Update config file
         * Set version db
         */

       $fp_currentVersion = fopen($includePath .'/currentVersion.inc.php','w');
       $currentVersionStr = '<?php 
$clarolineVersion = "'.$version_file_cvs.'";
$versionDb = "'.$versionDb.'";
?>';
       fwrite($fp_currentVersion, $currentVersionStr);
       fclose($fp_currentVersion);
    }
    else
    {
        $display = DISPLAY_RESULT_ERROR_PANEL;
    }
    
} // end if run 

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>  
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {    border: thin double Black;    margin-left: 15px;    margin-right: 15px;}
  </style>
</head>

<body bgcolor="white" dir="<?php echo $text_dir ?>">

<center>

<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
<tbody>
<tr bgcolor="navy">
<td valign="top" align="left">
<div id="header">
<?php
    echo sprintf ("<h1>Claroline (%s) - upgrade</h1>",$thisClarolineVersion);
?>
</div>
</td>
</tr>

<tr valign="top" align="left">
<td>

<div id="content">    

<?php

switch ($display)
{
    case DISPLAY_WELCOME_PANEL :
        echo sprintf ('<h2>%s</h2>',$langUpgradeStep1);
        echo $langIntroStep1;
        echo '<center>' . sprintf ($langLaunchStep1, $_SERVER['PHP_SELF'].'?cmd=run') . '</center>';
        break;
        
    case DISPLAY_RESULT_ERROR_PANEL :
        echo sprintf ('<h2>%s</h2>',$langUpgradeStep1 . ' - ' . $langFailed);
        echo $output;
        break;

    case DISPLAY_RESULT_SUCCESS_PANEL :
        echo sprintf ('<h2>%s</h2>',$langUpgradeStep1 . ' - ' . '<span class="success">' . $langSucceeded . '</span>');
        echo $output;
        echo '<div align="right">' . sprintf($langNextStep,'upgrade_main_db.php') . '</div>';
        break;
    
}
?>

</div>
</td>
</tr>
</tbody>
</table>

</body>
</html>