<?php // $Id$
/**
 * CLAROLINE 
 *
 * This  script   search tables  of db and  create sql to run a mysql repair of table.
 *
 * @version 1.6 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 * 
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

if  ( !is_array($tableToRepair && isset($currentCourseDbNameGlu)) ) $tableToRepair = claro_sql_get_course_tbl($currentCourseDbNameGlu);
if  ( is_array($tableToRepair) )  $sqlForUpdate[] = "REPAIR TABLE  `".implode($tableToRepair,'`, `')."`";

?>