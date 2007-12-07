<?php // $Id$
/**
 * CLAROLINE
 *
 * DROP Statistics Tables
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
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @package INSTALL
 *
 */

/**
 * @var $statsTblPrefixForm prefix set during  install, and keep in mainconf
 * @private $sql var where build sql request.
 */
$sql = "DROP TABLE IF EXISTS `".$statsTblPrefixForm."track_e_default` ";
claro_sql_query($sql);
$sql = "DROP TABLE IF EXISTS `".$statsTblPrefixForm."track_e_login` ";
claro_sql_query($sql);
$sql = "DROP TABLE IF EXISTS `".$statsTblPrefixForm."track_e_open` ";
claro_sql_query($sql);
?>