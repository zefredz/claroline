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


if  ( !is_array($tableToRepair) )
{

	if ( isset($singleDbEnabled) && $singleDbEnabled)
    {
	    $sql =  "SHOW TABLES like '" . $currentCourseDbName ."%' "; 
    }
    else
    {
    	$sql = "SHOW TABLES FROM " . $currentCourseDbName ;
    }

	$res_getTablesNames = mysql_query($sql);

	if( mysql_num_rows($res_getTablesNames) )
	{
		while ($getTablesNames = mysql_fetch_array($res_getTablesNames)) 
		{
			$tableToRepair[] = $getTablesNames[0];
		}		
	}
}

if  ( is_array($tableToRepair) )
{
	reset($tableToRepair);
	$sqlRepair = "REPAIR TABLE  ";

	for ($i=0;$i<count($tableToRepair);$i++)
	{
		if ($i < (count($tableToRepair) -1)) 
		{
			$sqlRepair .= "`".$currentCourseDbNameGlu.$tableToRepair[$i]."`, ";
		}
		else
		{
			$sqlRepair .= "`".$currentCourseDbNameGlu.$tableToRepair[$i];
		}
	}
	$sqlForUpdate[] = $sqlRepair;
}

?>