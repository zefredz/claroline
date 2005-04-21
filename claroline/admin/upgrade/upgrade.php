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
if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
include ($includePath.'/installedVersion.inc.php');

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
define('DISPVAL_upgrade_done'           ,__LINE__);
/**#@-*/

/*=====================================================================
  Statements Section
 =====================================================================*/

if ($_GET['reset_confirm_backup'] == 1 || $_SESSION['confirm_backup'] == 0) 
{
    // reset confirm backup 
    session_unregister('confirm_backup');
    $confirm_backup = 0;
}

if (!isset($_SESSION['confirm_backup'])) 
{
    if ($_GET['confirm_backup'] == 1 ) 
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
if (file_exists($includePath.'/currentVersion.inc.php'))
{
    include ($includePath.'/currentVersion.inc.php');
}

if (!$confirm_backup) 
{
    // ask to confirm backup
    $display = DISPVAL_upgrade_backup_needed;    
}
elseif (!preg_match($patternVarVersion,$clarolineVersion))
{
    
    // config file not upgraded go to first step
    header("Location: upgrade_conf.php");
}
elseif (!preg_match($patternVarVersion,$versionDb))
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
    case DISPVAL_upgrade_backup_needed :

        $str_confirm_backup = '<input type="radio" id="confirm_backup_yes" name="confirm_backup" value="1" />'
                            . '<label for="confirm_backup_yes">' . $langYes . '</label><br>'
                            . '<input type="radio" id="confirm_backup_no" name="confirm_backup" value="" checked="checked" />'
                            . '<label for="confirm_backup_no">' . $langNo . '</label><br>'
                            ;

        echo  sprintf($langTitleUpgrade,'1.5.*','1.6') . "\n"
            . '<form action="' . $_SERVER['PHP_SELF'] . '" method="GET">' . "\n"
            . '<p>' . sprintf($langMakeABackupBefore,$str_confirm_backup) . '</p>' . "\n"
            . '<div align="right"><input type="submit" value="' . $langNext . ' > " /></div>' . "\n"
            . '</form>' . "\n"
            ;

        break;

    case DISPVAL_upgrade_main_db_needed :

        echo sprintf($langTitleUpgrade,'1.5.*','1.6') . "\n"
           . '<h2>' . $langDone . ':</h2>' . "\n"
           . '<ul>' . "\n"
           . sprintf ('<li>%s (<a href="' . $_SERVER['PHP_SELF'] . '?reset_confirm_backup=1">%s</a>)</li>',$langUpgradeStep0,$langCancel)
           . sprintf ('<li>%s (<a href="upgrade_conf.php">%s</a>)</li>',$langUpgradeStep1,$langStartAgain)
           . '</ul>' . "\n"
           . '<h2>' . $langTodo . ':</h2>' . "\n"
           . '<ul>' . "\n"
           . sprintf('<li><a href="upgrade_main_db.php">%s</a></li>',$langUpgradeStep2) . "\n"
           . '<li>' . $langUpgradeStep3 . '</li>' . "\n"
           . '</ul>' . "\n"
           ;

        break;

    case DISPVAL_upgrade_courses_needed :

        echo  sprintf($langTitleUpgrade,'1.5.*','1.6') . "\n"
            . '<h2>' . $langDone . ':</h2>' . "\n"
            . '<ul>' . "\n"
            . sprintf ('<li>%s (<a href="' . $_SERVER['PHP_SELF'] . '?reset_confirm_backup=1">'. $langCancel . '</a>)</li>',$langUpgradeStep0) . "\n"
            . sprintf ('<li>%s (<a href="upgrade_conf.php">%s</a>)</li>',$langUpgradeStep1,$langStartAgain) . "\n"
            . sprintf ('<li>%s (<a href="upgrade_main_db.php">%s</a>)</li>',$langUpgradeStep2,$langStartAgain) . "\n"
            . '</ul>' . "\n"
            . '<h2>' . $langRemainingSteps . ':</h2>' . "\n"
            . '<ul>' . "\n"
            . sprintf('<li><a href="upgrade_courses.php">%s</a> - '.$lang_p_d_coursesToUpgrade.'</li>',$langUpgradeStep3,$nbCourses['nb']) . "\n"
            . '</ul>' . "\n"
            ;

        break;

    case DISPVAL_upgrade_done :

        echo  sprintf($langTitleUpgrade,'1.5.*','1.6') . "\n"
            . '<p>' . $langUpgradeSucceed . '</p>' . "\n"
            . '<ul>' . "\n"
            . '<li><a href="../../..">' . $langPlatformAccess . '</a></li>' . "\n"
            . '</ul>' . "\n"
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