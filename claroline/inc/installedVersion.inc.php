<?php // $Id$
/**
 * CLAROLINE
 *
 * Set value to detect if script set version is same than upgrade state
 * @var $version_file_cvs contain the version of script set
 *                        (kernel sctructure, document structure, config value, ...)
 * @var $version_db_cvs   contain the version of script set
 *                        (different from _file_ because some time there is nothing to change in db)
 *
 * @version 1.6 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
$stable = TRUE;
$clarolinePhase = 'stable';
$is_upgrade_available = TRUE;

// var version_db  max. 10 chars

$version_file_cvs = '1.6.4';
$version_db_cvs   = '1.6.4';

if (!$is_upgrade_available)
{
    $version_file_cvs = $version_file_cvs .".[unstable:".date("yzBs")."]";
    $version_db_cvs   = $version_db_cvs .".[unstable:".date("yzBs")."]";
}

?>
