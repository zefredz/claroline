<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
/**
 * CLAROLINE
 *
 * SQL Statement to DROP TABLE IF EXISTS of central database
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package INSTALL
 *
 */


############# claroline DB DROP #############################

/**
 * @var $mainTblPrefixForm prefix set during  install, and keep in mainconf
 * @private $sql var where build sql request.
 */

$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "admin` ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "cours` ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "cours_user` ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "faculte`  ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "user`  ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "course_tool` ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "class`  ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "rel_class_user`  ";
claro_sql_query($sql);
$sql ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "config_file`  ";
claro_sql_query($sql);
$sql = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "sso`  ";
claro_sql_query($sql);
$sql = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "notify`  ";
claro_sql_query($sql);
$sql = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "upgrade_status`  ";
claro_sql_query($sql);

?>