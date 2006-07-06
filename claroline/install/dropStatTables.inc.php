<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * DROP Statistics Tables
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
 * @author Christophe Gesch� <moosh@claroline.net>
 *
 * @package INSTALL
 *
 */

/**
 * @var $statsTblPrefixForm prefix set during  install, and keep in mainconf
 * @private $sql var where build sql request.
 */
$dropStatementList[] = "DROP TABLE IF EXISTS `".$statsTblPrefixForm."track_e_default` ";
$dropStatementList[] = "DROP TABLE IF EXISTS `".$statsTblPrefixForm."track_e_login` ";
$dropStatementList[] = "DROP TABLE IF EXISTS `".$statsTblPrefixForm."track_e_open` ";
?>