<?php // $Id$
/**
 * @version CLAROLINE 1.6
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license GENERAL PUBLIC LICENSE (GPL)
 * 
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * 
 *  Sql query to update tracking tables
 *
 */

$sqlForUpdate[] = "# Start for tracking TABLES Queries";

// Update table track_e_default
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $statsTblPrefix . "track_e_default` CHANGE `default_user_id` `default_user_id` int(11) NOT NULL default '0'" ;

// Update table login_user_id
$sqlForUpdate[] = "ALTER IGNORE TABLE `" . $statsTblPrefix . "track_e_login` CHANGE `login_user_id` `login_user_id` int(11) NOT NULL default '0'" ;


?>