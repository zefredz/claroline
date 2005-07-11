<?php // $Id$
/**
 * CLAROLINE 
 *
 * This script 
 * - read current version
 * - check if update of main conf is needed
 *         whether do it (upgrade_conf.php)
 * - check if update of main db   is needed
 *         whether do it (upgrade_main_db.php)
 * - scan course to check if update of db is needed
 *   whether do loop (upgrade_courses.php)
 * - update course db
 * - update course repository content
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

/*=====================================================================
  Init Section
 =====================================================================*/ 
if (!defined('NL')) define('NL',"");
$cidReset = TRUE;
$gidReset = TRUE;
$platform_id =  md5(realpath('../../inc/conf/def/CLMAIN.def.conf.inc.php'));
require '../../inc/claro_init_global.inc.php';
/*---------------------------------------------------------------------
  Security Check
 ---------------------------------------------------------------------*/ 

if (!$is_platformAdmin) claro_disp_auth_form();

/*---------------------------------------------------------------------
  Include version file and initialize variables
 ---------------------------------------------------------------------*/
if(file_exists($includePath . '/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');

include ($includePath . '/installedVersion.inc.php');

$thisClarolineVersion = $version_file_cvs;
$thisVersionDb        = $version_db_cvs;

$patternVarVersion = '/^1.6/';
$patternSqlVersion = '1.6%';

$configurationFile = $includePath.'/conf/claro_main.conf.php';

/**#@+
 * Displays flags
 * Using __LINE__ to have an arbitrary value
 */
define('DISPVAL_upgrade_backup_needed'  ,__LINE__);
define('DISPVAL_upgrade_main_db_needed' ,__LINE__);
define('DISPVAL_upgrade_courses_needed' ,__LINE__);
define('DISPVAL_claroVersionNotFound'   ,__LINE__);
define('DISPVAL_upgrade_done'           ,__LINE__);
 /**#@-*/

/*=====================================================================
  Statements Section
 =====================================================================*/
$reset_confirm_backup = isset($_REQUEST['reset_confirm_backup']) 
                          ? (bool) $_REQUEST['reset_confirm_backup'] 
                      : false;

$req_confirm_backup = isset($_REQUEST['confirm_backup']) 
                      ? (bool) $_REQUEST['confirm_backup'] 
                      : false;
                      
$is_backup_confirmed = isset($_SESSION['confirm_backup']) 
                      ? (bool) $_SESSION['confirm_backup'] 
                      : false;


if ($reset_confirm_backup
    || !$is_backup_confirmed)
{
    // reset confirm backup 
    session_unregister('confirm_backup');
    $confirm_backup = 0;
}

if (!isset($_SESSION['confirm_backup'])) 
{
    if ($req_confirm_backup) 
    {
        // confirm backup TRUE
        $_SESSION['confirm_backup'] = 1;
        $confirm_backup = 1;
    }
    else
    {
        $confirm_backup = 0;
    }
} 
else 
{
    // get value from session
    $confirm_backup  = $_SESSION['confirm_backup'];
}

/*---------------------------------------------------------------------
  Define Display
 ---------------------------------------------------------------------*/ 

/*
 * Include old configuration file
 */

if (file_exists($configurationFile))
{
    include ($configurationFile); // read Values in sources
}

if (file_exists($includePath . '/currentVersion.inc.php'))
{
    include ($includePath . '/currentVersion.inc.php');
}
else 
{
    $clarolineVersion = "unknow";
    $versionDb = "unknow";
}

if (!$confirm_backup) 
{
    // ask to confirm backup
    $display = DISPVAL_upgrade_backup_needed;    
}
/* this bloc is only use during debug
elseif (!isset($clarolineVersion))
{
    $display = DISPVAL_claroVersionNotFound;
    
}
*/
elseif (!preg_match($patternVarVersion, $clarolineVersion))
{
    
    // config file not upgraded go to first step
    header("Location: upgrade_conf.php");
}
elseif (!preg_match($patternVarVersion, $versionDb))
{
    // upgrade of main conf needed.
    $display = DISPVAL_upgrade_main_db_needed;
}
else
{
    // check course table to view wich courses aren't upgraded
    mysql_connect($dbServer,$dbLogin,$dbPass);
    $sqlNbCourses = "SELECT count(*) as nb 
                     FROM `".$mainDbName."`.`".$mainTblPrefix."cours`
                     WHERE not ( versionDb like '" . $patternSqlVersion . "' )";

    $res_NbCourses = mysql_query($sqlNbCourses);
    $nbCourses = mysql_fetch_array($res_NbCourses);
    
    if ($nbCourses['nb'] > 0)
    {
        // upgrade of main conf needed.
        $display = DISPVAL_upgrade_courses_needed;
    }
    else
    {
        $display = DISPVAL_upgrade_done;
    }
}

/*=====================================================================
  Display Section
 =====================================================================*/

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <title>-- Claroline upgrade -- version <?php echo $thisClarolineVersion ?></title>  
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
 echo sprintf('<h1>Claroline (%s) - %s</h1>',$thisClarolineVersion,$langUpgrade);
?>
</div>
</td>
</tr>
<tr bgcolor="#E6E6E6">
<td align="left">
<div id="content">
<?php

switch ($display)
{
    case DISPVAL_claroVersionNotFound :
    {
        echo 'le système ne trouve pas la valeur de la version du claroline acutellement installé sur votre ordinateur.<br>'
        .    '<H3>scripts version</H3>'
        .    'Files : ' . $version_file_cvs . '<br>'
        .    'DB : ' . $version_db_cvs . '<br>'
        .    'Data repository : unknow <br>' 
        .    'Central Db : unknow<br>' 
        ;
    
        
    }
    break;
    
    case DISPVAL_upgrade_backup_needed :

        $str_confirm_backup = '<input type="radio" id="confirm_backup_yes" name="confirm_backup" value="1" />'
                            . '<label for="confirm_backup_yes">' . $langYes . '</label><br>'
                            . '<input type="radio" id="confirm_backup_no" name="confirm_backup" value="" checked="checked" />'
                            . '<label for="confirm_backup_no">' . $langNo . '</label><br>'
                            ;

        echo  sprintf($langTitleUpgrade,'1.5.*','1.6') . NL
            . '<form action="' . $_SERVER['PHP_SELF'] . '" method="GET">' . NL
            . '<p>' . sprintf($langMakeABackupBefore,$str_confirm_backup) . '</p>' . NL
            . '<div align="right"><input type="submit" value="' . $langNext . ' > " /></div>' . NL
            . '</form>' . NL
            ;

        break;

    case DISPVAL_upgrade_main_db_needed :

        echo sprintf($langTitleUpgrade, '1.6.*', '1.7') . NL
           . '<h2>' . $langDone . ':</h2>' . NL
           . '<ul>' . NL
           . '<li>'
           . sprintf ('%s (<a href="' . $_SERVER['PHP_SELF'] . '?reset_confirm_backup=1">%s</a>)'
                     , $langUpgradeStep0
                     , $langCancel)
           . '</li>'
           . '<li>'
           . sprintf ('%s (<a href="upgrade_conf.php">%s</a>)', $langUpgradeStep1,$langStartAgain)
           . '</li>'
           . '</ul>' . NL
           . '<h2>' . $langTodo . ':</h2>' . NL
           . '<ul>' . NL
           . sprintf('<li><a href="upgrade_main_db.php">%s</a></li>', $langUpgradeStep2) . NL
           . '<li>' . $langUpgradeStep3 . '</li>' . NL
           . '</ul>' . NL
           ;

        break;

    case DISPVAL_upgrade_courses_needed :

        echo  sprintf($langTitleUpgrade,'1.5.*','1.6') . NL
            . '<h2>' . $langDone . ':</h2>' . NL
            . '<ul>' . NL
            . sprintf ('<li>%s (<a href="' . $_SERVER['PHP_SELF'] . '?reset_confirm_backup=1">'. $langCancel . '</a>)</li>',$langUpgradeStep0) . NL
            . sprintf ('<li>%s (<a href="upgrade_conf.php">%s</a>)</li>',$langUpgradeStep1,$langStartAgain) . NL
            . sprintf ('<li>%s (<a href="upgrade_main_db.php">%s</a>)</li>',$langUpgradeStep2,$langStartAgain) . NL
            . '</ul>' . NL
            . '<h2>' . $langRemainingSteps . ':</h2>' . NL
            . '<ul>' . NL
            . sprintf('<li><a href="upgrade_courses.php">%s</a> - '.$lang_p_d_coursesToUpgrade.'</li>',$langUpgradeStep3,$nbCourses['nb']) . NL
            . '</ul>' . NL
            ;

        break;

    case DISPVAL_upgrade_done :

        echo  sprintf($langTitleUpgrade,'1.5.*','1.6') . NL
            . '<p>' . $langUpgradeSucceed . '</p>' . NL
            . '<ul>' . NL
            . '<li><a href="../../..">' . $langPlatformAccess . '</a></li>' . NL
            . '</ul>' . NL
            ;
}


?>

</div>
</td>
</tr>
</tbody>
</table>
</body>
</html>