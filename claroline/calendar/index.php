<?php // $Id$
/**
 * CLAROLINE 
 *
 * select the good agenda waiting that two scripts are merged.
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 */

$tlabelReq = 'CLANN';

require '../inc/claro_init_global.inc.php';

if (isset($_cid))
{
    claro_redirect('./agenda.php');
}
else
{
    claro_redirect('./myagenda.php');
}
exit();

?>
