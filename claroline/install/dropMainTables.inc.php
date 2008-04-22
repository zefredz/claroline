<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * SQL Statement to DROP TABLE IF EXISTS of central database
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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
 * @private $dropStatementList[] var where build sql request.
 */

$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "admin` ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "cours` ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "cours_user` ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "faculte`  ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "user`  ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "course_tool` ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "class`  ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "rel_class_user`  ";
$dropStatementList[] ="DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "config_file`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "sso`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "notify`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "upgrade_status`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "module`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "module_info`  ";
//$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "module_tool`  ";
//$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "module_rel_tool_context`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "dock`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "user_property`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "property_definition`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "right_rel_profile_action`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "right_profile`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "right_action`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "rel_course_class`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "log`  ";
$dropStatementList[] = "DROP TABLE IF EXISTS `" . $mainTblPrefixForm . "tracking_event`  ";

?>