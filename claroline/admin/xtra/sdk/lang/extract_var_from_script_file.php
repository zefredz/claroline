<?php
set_time_limit (0);

/*
 * This script scans and retrieves all the language variables of an existing Claroline
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

$includePath = $rootSys.'claroline/inc';

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';

// get start time

$starttime = get_time();

// Start content
echo "<html>
<head>
 <title>Extract variables from scripts</title>
</head>
<body>";

echo "<h1>Extract variables from scripts</h1>\n";

// drop table if exists 

$sql = "DROP TABLE IF EXISTS ". $tbl_used_lang ." ";
mysql_query ($sql) or die($problemMessage);

// create table 

$sql = "CREATE TABLE ". $tbl_used_lang ." (
 id INTEGER NOT NULL auto_increment,
 varName VARCHAR(250) BINARY NOT NULL,
 langFile VARCHAR(250) NOT NULL,
 sourceFile VARCHAR(250) NOT NULL,
 INDEX index_varName (varName),
 PRIMARY KEY(id))";

mysql_query ($sql) or die($problemMessage . __LINE__);

// Get Files and subfolders 

$scan=scan_dir ($rootSys,$recurse=TRUE);

$files = $scan['files'];

// *** OPTIMISATION *** //

$claro_init_global_vars = array();

// *** OPTIMISATION *** //

$total_var_count = 0;

foreach ($files as $file)
{

	echo "<h3>" . $file . "</h3>\n";
   
	// extract variables
    
    $scannedFileList = array(); // re init the scannedFileList for each new script
	
    $languageVarList = get_lang_vars_from_file($file);

	// display variables 

	$var_count = 0;

	foreach($languageVarList as $varName) ++$var_count;
    $total_var_count += $var_count;
    echo "Variables: " . $var_count;
	
    // update table
	store_lang_used_in_script($languageVarList,$file);
	
} // end foreach 

echo "<p>Total variables: " . $total_var_count . "</p>";

// end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

echo "</body>\n</html>\n";

?>
