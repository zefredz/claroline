<?php // $Id$


/*
 * This  script   search tables  of db and  create sql to run a mysql repair of table.
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
