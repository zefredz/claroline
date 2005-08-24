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
 * @version 1.7
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

if ( ! file_exists('../../currentVersion.inc.php') )
{
    // if this file doesn't exist, the current version is < claroline 1.6
    $platform_id =  md5(realpath('../../inc/conf/def/CLMAIN.def.conf.inc.php'));
}

// Initialise

require '../../inc/claro_init_global.inc.php';

// Security Check

if (!$is_platformAdmin) claro_disp_auth_form();

// Pattern for this new stable version

$patternVarVersion = '/^1.7/';
$patternSqlVersion = '1.7%';

// Display definition

define('DISPVAL_upgrade_backup_needed'  ,__LINE__);
define('DISPVAL_upgrade_main_db_needed' ,__LINE__);
define('DISPVAL_upgrade_courses_needed' ,__LINE__);
define('DISPVAL_upgrade_done'           ,__LINE__);

// Library 
include ('upgrade.lib.php');

// Initialise Upgrade
upgrade_init_global();

/*=====================================================================
  Main Section
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

if ( $reset_confirm_backup || !$is_backup_confirmed )
{
    // reset confirm backup 
    session_unregister('confirm_backup');
    $confirm_backup = 0;
}

if ( !isset($_SESSION['confirm_backup']) ) 
{
    if ( $req_confirm_backup ) 
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

if ( !$confirm_backup ) 
{
    // ask to confirm backup
    $display = DISPVAL_upgrade_backup_needed;    
}
elseif ( !preg_match($patternVarVersion, $currentClarolineVersion) )
{ 
    // config file not upgraded go to first step
    header("Location: upgrade_conf.php");
}
elseif ( !preg_match($patternVarVersion, $currentDbVersion) )
{
    // upgrade of main conf needed.
    $display = DISPVAL_upgrade_main_db_needed;
}
else
{
    // count course to upgrade
    $count_course_upgraded = count_course_upgraded($new_version_branch);
    $count_course_to_upgrade =  $count_course_upgraded['total'] - $count_course_upgraded['upgraded'];
    
    if ( $count_course_to_upgrade > 0 )
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

// Display Header
echo upgrade_disp_header();

// Display Content

switch ($display)
{
    
    case DISPVAL_upgrade_backup_needed :

        $str_confirm_backup = '<input type="radio" id="confirm_backup_yes" name="confirm_backup" value="1" />'
                            . '<label for="confirm_backup_yes">' . $langYes . '</label><br>'
                            . '<input type="radio" id="confirm_backup_no" name="confirm_backup" value="" checked="checked" />'
                            . '<label for="confirm_backup_no">' . $langNo . '</label><br>'
                            ;

        echo  sprintf($langTitleUpgrade,$currentClarolineVersion,$new_version) . "\n"
            . '<form action="' . $_SERVER['PHP_SELF'] . '" method="GET">' . "\n"
            . '<p>' . sprintf($langMakeABackupBefore,$str_confirm_backup) . '</p>' . "\n"
            . '<div align="right"><input type="submit" value="' . $langNext . ' > " /></div>' . "\n"
            . '</form>' . "\n"
            ;

        break;

    case DISPVAL_upgrade_main_db_needed :

        echo  sprintf($langTitleUpgrade,$currentClarolineVersion,$new_version) . "\n"
           . '<h2>' . $langDone . ':</h2>' . "\n"
           . '<ul>' . "\n"
           . '<li>'
           . sprintf ('%s (<a href="' . $_SERVER['PHP_SELF'] . '?reset_confirm_backup=1">%s</a>)'
                     , $langUpgradeStep0
                     , $langCancel)
           . '</li>'
           . '<li>'
           . sprintf ('%s (<a href="upgrade_conf.php">%s</a>)', $langUpgradeStep1,$langStartAgain)
           . '</li>'
           . '</ul>' . "\n"
           . '<h2>' . $langTodo . ':</h2>' . "\n"
           . '<ul>' . "\n"
           . sprintf('<li><a href="upgrade_main_db.php">%s</a></li>', $langUpgradeStep2) . "\n"
           . '<li>' . $langUpgradeStep3 . '</li>' . "\n"
           . '</ul>' . "\n"
           ;

        break;

    case DISPVAL_upgrade_courses_needed :

        echo  sprintf($langTitleUpgrade,$currentClarolineVersion,$new_version) . "\n"
            . '<h2>' . $langDone . ':</h2>' . "\n"
            . '<ul>' . "\n"
            . sprintf ('<li>%s (<a href="' . $_SERVER['PHP_SELF'] . '?reset_confirm_backup=1">'. $langCancel . '</a>)</li>',$langUpgradeStep0) . "\n"
            . sprintf ('<li>%s (<a href="upgrade_conf.php">%s</a>)</li>',$langUpgradeStep1,$langStartAgain) . "\n"
            . sprintf ('<li>%s (<a href="upgrade_main_db.php">%s</a>)</li>',$langUpgradeStep2,$langStartAgain) . "\n"
            . '</ul>' . "\n"
            . '<h2>' . $langRemainingSteps . ':</h2>' . "\n"
            . '<ul>' . "\n"
            . sprintf('<li><a href="upgrade_courses.php">%s</a> - '.$lang_p_d_coursesToUpgrade.'</li>',$langUpgradeStep3,$count_course_to_upgrade) . "\n"
            . '</ul>' . "\n"
            ;

        break;

    case DISPVAL_upgrade_done :

        echo  sprintf($langTitleUpgrade,$currentClarolineVersion,$new_version) . "\n"
            . '<p>' . $langUpgradeSucceed . '</p>' . "\n"
            . '<ul>' . "\n"
            . '<li><a href="../../..">' . $langPlatformAccess . '</a></li>' . "\n"
            . '</ul>' . "\n"
            ;
}

// Display footer
echo upgrade_disp_footer();

?>
