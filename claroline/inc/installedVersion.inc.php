<?php // $Id$

if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * Set value to detect if script set version is same than upgrade state
 *
 * @var $version_file_cvs contain the version of script set
 *                        (kernel sctructure, document structure, config value, ...)
 * @var $version_db_cvs   contain the version of script set
 *                        (different from _file_ because some time there is nothing to change in db)
 *
 * @version 1.10 $Revision$
 *
 * @copyright (c) 2001-2010 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');

$stable = false;
$is_upgrade_available = false;

// var version_db  max. 10 chars

$new_version = '1.10.0';
$new_version_branch = '1.10';

if (!$stable)
{
    $new_version = $new_version . '.[unstable:' . date('yzBs') . ']';
}

if (!$is_upgrade_available)
{
    $new_version = $new_version . '[NO UPGRADE]';
}

$requiredPhpVersion = '5.2.0';
$requiredMySqlVersion = '5.0';

