<?php // $Id$

/**
 * initialize conf settings
 * try to read  old values in old conf files
 * build new conf file content with these settings
 * write it.
 */

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
include ($includePath.'/lib/config.lib.inc.php');
include ($includePath.'/lib/fileManage.lib.php');
    
$thisClarolineVersion = $version_file_cvs;

$error = 0;

if ($_REQUEST['cmd'] == 'run')
{
    $backupRepositorySys = $includePath .'/conf/bak.'.date('Y-z-B').'/';

    // Main conf file

    $output = '<h3>'
            . 'Configuration file'
            . '</h3>'
            . '<ul>'."\n"
            ;
    
    // Prepare repository to backup files
    claro_mkdir($backupRepositorySys);
    // Gen conf file from def    
    
    $def_file_list = get_def_file_list();
    if(is_array($def_file_list))
    {
        $current_value_list =array();
        foreach ( $def_file_list as $def_file_bloc)
        {
            if (is_array($def_file_bloc['conf']))
            {
                foreach ( $def_file_bloc['conf'] as $config_code => $def_name)
                {
                    unset($conf_def, $conf_def_property_list);
                
                    $def_file  = get_def_file($config_code);
                
                    if ( file_exists($def_file) )
                        require($def_file);
                    // load old conf file content
                    $conf_def['old_config_file'][] = $conf_def['config_file'];
                    if (is_array($conf_def['old_config_file']))
                    {
                        foreach ($conf_def['old_config_file'] as $old_file_name) 
                        {
                            $current_value_list =array_merge($current_value_list,get_values_from_confFile($includePath.'/conf/'.$old_file_name,$conf_def_property_list));
                        }
                    }
                }
            }
        }
       
        reset( $def_file_list );
        foreach ( $def_file_list as $def_file_bloc)
        {
            if (is_array($def_file_bloc['conf']))
            {
                foreach ( $def_file_bloc['conf'] as $config_code => $def_name)
                {
                    $okToSave = TRUE;
                    if ( $config_code == 'CLMAIN' ) continue;
                    unset($conf_def, $conf_def_property_list);
            
                    $def_file  = get_def_file($config_code);
            
                    if ( file_exists($def_file) )
                        require($def_file);
                    
                    if ( is_array($conf_def_property_list) )
                    {
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
                            }
                        }
                    }
                    else
                    {
                        $okToSave = FALSE;
                    }
            
                    if ($okToSave)
                    {
                        reset($conf_def_property_list);
                        foreach($conf_def_property_list as $propName => $propDef )
                        {
                            $propertyList[] = array('propName'=>$propName
                                                   ,'propValue'=>$propDef['default']);
                        }
            
                        $conf_file = get_conf_file($config_code);
            
                        if ( !file_exists($conf_file) ) touch($conf_file);
            
                        if ( is_array($propertyList) && count($propertyList)>0 )
                        {
        
                            // backup old file 
                            $output .= '<li>' 
                                    .  sprintf ('Back-up %s in: <code>%s</code>',basename($conf_file),$backupRepositorySys) ;
                            $fileBackup = $backupRepositorySys.basename($conf_file);
                            if (!@copy($conf_file, $fileBackup) )
                            {
                                $output .= '<br />'."\n";
                                $output .= sprintf ("<span class=\"warning\"><code>%s</code> copy failed !</span>",$configurationFile);
                            }
                            $output .= '</li>'."\n";
                            // change permission
                            @chmod( $fileBackup, 600 );
                            @chmod( $fileBackup, 0600 );
                            
                            if ( write_conf_file($conf_def,$conf_def_property_list,$propertyList,$conf_file,realpath(__FILE__)) )
                            {
                                // calculate hash of the config file
                                $conf_hash = md5_file($conf_file); // md5_file not in PHP 4.1
                                //$conf_hash = filemtime($conf_file);
                                save_config_hash_in_db($config_code,$conf_hash);
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
    * Config file to undist
    */
    
    $arr_file_to_undist =
    array (
    $newIncludePath.'../../textzone_top.inc.html',
    $newIncludePath.'../../textzone_right.inc.html',
    $newIncludePath.'conf/auth.conf.php'
    );
    
    $output .= '<h3>'.'Others conf files'.'</h3>'."\n";
    
    $output .= '<ul>'."\n";
    foreach ($arr_file_to_undist As $undist_this)
    {
        $output .='<li>Conf file: <code>'.basename ($undist_this).'</code>';
        if (claro_undist_file($undist_this))
        {
            $output .=' added';
        }
        else
        {
            $output .=' not changed.';
        };
        $output .='</li>'."\n";
    }
    $output .= '</ul>'."\n";
    
    if (!$error)
    {
        $display = DISPLAY_RESULT_SUCCESS_PANEL;
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
<!--
<tr bgcolor="#E6E6E6">
<td valign="top"align="left">
<div id="menu">
<?php
 echo sprintf("<p><a href=\"upgrade.php\">%s</a> - %s</p>", "upgrade", $langUpgradeStep1);
?>
</div>
</td>
</tr>
-->
<tr valign="top" align="left">
<td>

<div id="content">    

<?php

switch ($display)
{
    case DISPLAY_WELCOME_PANEL: 
                echo sprintf ("<h2>%s</h2>",$langUpgradeStep1);
                echo $langIntroStep1;
        echo "<center>" . sprintf ($langLaunchStep1, $_SERVER['PHP_SELF']."?cmd=run") . "</center>";
        break;
        case DISPLAY_RESULT_ERROR_PANEL:
                echo sprintf ("<h2>%s</h2>",$langUpgradeStep1 . " - " . $langFailed);
                echo $output;
                break;
                
    case DISPLAY_RESULT_SUCCESS_PANEL:

                echo sprintf ("<h2>%s</h2>",$langUpgradeStep1 . " - " . $langSucceed);

                echo "<p>Here are the main settings that has been recorded in claroline/inc/conf/claro_main.conf.php</p>";
                
                // display the main setting of the new configuration file.
                
                echo "<fieldset>
        <legend>Database authentification</legend>
                <p>Host: $dbHost<br />
        Username: $dbLogin<br />
        Password: ".(empty($dbPass)?"--empty--":$dbPass)."</p>
        </fieldset>
                <br />
                <fieldset>
        <legend>Claroline databases</legend>
                <p>Course database Prefix: ".($dbNamePrefix?$dbNamePrefix:$langNo)."<br />
                Main database Name: $mainDbName <br />
        Statistics and Tracking database Name: $statsDbName <br />
        Enable Single database: ".($singleDbEnabled?$langYes:$langNo)."</p>
        </fieldset>
                <br />
                <fieldset>
                    <legend>Administrator</legend>
                    Name: ".$administrator["name"]."<br />
                    Mail: ".$administrator["email"]."<br />
        </fieldset>
                <br />
        <fieldset>
                 <legend>Campus</legend>
                 <p>
                    Language: $platformLanguage<br />
                    Your organisation: ".$institution["name"]."<br />
                    URL of this organisation: ".$institution["url"]."
                </p>
        </fieldset>
                <br />
        <fieldset>
                    <legend>Config</legend>
                    <p>
                    Enable Tracking: ".($is_trackingEnabled?$langYes:$langNo)."<br />
                    Self registration allowed: ".($allowSelfReg?$langYes:$langNo)."<br />
                    Self course creator allowed : ".($allowSelfRegProf?$langYes:$langNo)."<br />
                    Encrypt user passwords in database: " .($userPasswordCrypted?$langYes:$langNo)."
                    </p>
                </fieldset>";
                
                echo "<div align=\"right\">" . sprintf($langNextStep,"upgrade_main_db.php") . "</div>";
                
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
