<?php // $Id$
/**
 * CLAROLINE
 *
 * Sql query to update tracking tables
 *
 * @version  1.6 $Revision$
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

$sqlForUpdate[] = "# Start for tracking TABLES Queries";

// Update table track_e_default
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['track_e_default']  . "` CHANGE `default_user_id` `default_user_id` int(11) NOT NULL default '0'" ;

// Update table login_user_id
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $tbl_mdb_names['track_e_login']  . "` CHANGE `login_user_id` `login_user_id` int(11) NOT NULL default '0'" ;


?>