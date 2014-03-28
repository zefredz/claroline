<?php // $Id$

/**
 * Define version number variables and stability and upgrade availability
 * 
 * Infromation concerning the stability :
 * 
 * $GLOBALS['stable']                   is the current version considered a stable one (i.e. not a development version)
 * $GLOBALS['is_upgrade_available']     is the upgrade available for this version
 * 
 * Information used for the upgrade that have to be changed on each major release :
 * 
 * $GLOBALS['new_version']              the new version number (for example 1.12.0)
 * $GLOBALS['new_version_branch']       the new version branch (for example 1.12)
 * 
 * $GLOBALS['new_patternVarVersion']    the PCRE pattern for the new version (used by the upgrade script)
 * $GLOBALS['new_patternSqlVersion']    the SQL pattern for the new version (used by the upgrade script)
 * 
 * Information about the Claroline API that have to be changed anytime a change is made :
 * 
 * $GLOBALS['clarolineAPIVersion']      Claroline API version indicates the last version in which the API has been altered. If 
 *                                      someone modifies the signature of a function, class, method, or change a (global) 
 *                                      variable or mark a function/method/class/variable has deprecated, the API version 
 *                                      MUST be changed. 
 * 
 *                                      WARNING : this does not concern the implementation and internals of methods, 
 *                                      functions or classes but only the public API of those classes methods and functions.
 *                                      In this case, the version number is given by the revision number in each file or the
 *                                      version tag in the API documentation
 * 
 * $GLOBALS['clarolineDBVersion']       Claroline Database version indicates the last version in which the database schemas has been altered.
 * 
 *                                      WARNING : the database structure MUST only be altered when releasing a new major version, since an
 *                                      upgrade script is needed to apply the API change.
 * 
 * Information used by the installation :
 * 
 * $GLOBALS['requiredPhpVersion']       minimum required PHP version to run Claroline
 * $GLOBALS['requiredMySqlVersion']     minimum required MySQL version to run Claroline
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel
 * @author      Claro Team <cvs@claroline.net>
 */

// stability

$GLOBALS['stable'] = false;
$GLOBALS['is_upgrade_available'] = true;

// version strings : max. 10 chars

$GLOBALS['new_version'] = '1.12.0-alpha3';
$GLOBALS['new_version_branch'] = '1.12';
$GLOBALS['new_patternVarVersion'] = '/^1.12/';
$GLOBALS['new_patternSqlVersion'] = '1.12%';

// API versions

$GLOBALS['clarolineAPIVersion'] = '1.12.0';
$GLOBALS['clarolineDBVersion'] = '1.12.0';


// required versions

$GLOBALS['requiredPhpVersion'] = '5.3.0';
$GLOBALS['requiredMySqlVersion'] = '5.0';

// Some magick occurs here...
if (!$GLOBALS['stable'])
{
    $GLOBALS['new_version'] = $GLOBALS['new_version'] . '.[unstable:' . date('yzBs') . ']';
}

if (!$GLOBALS['is_upgrade_available'])
{
    $GLOBALS['new_version'] = $GLOBALS['new_version'] . '[NO UPGRADE]';
}
