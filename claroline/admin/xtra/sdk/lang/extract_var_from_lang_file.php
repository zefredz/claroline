<?php

/*
 * This script retrieves all the existing translation of an existing Claroline
 * It scans all the files of the 'lang' directory and stored the $lang variables
 * content into a mySQL database.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

// get start time

$starttime = get_time();

// Start content

echo "<html>
<head>
 <title>Extract variables from language files</title>
</head>
<body>";

echo "<h1>Extract variables from language files</h1>\n";

$dbTableName = TABLE_TRANSLATION ;

// drop table if exists

$sql = "DROP TABLE IF EXISTS ". $dbTableName ." ";
mysql_query ($sql) or die($problemMessage);

// create table 

$sql = "CREATE TABLE ". $dbTableName ." (
 id INTEGER NOT NULL auto_increment,
 language VARCHAR(250) NOT NULL,
 varName VARCHAR(250) BINARY NOT NULL,
 varContent VARCHAR(250) NOT NULL,
 varFullContent TEXT NOT NULL,
 sourceFile VARCHAR(250) NOT NULL,
 used tinyint(4) default 0,
 INDEX index_language (language,varName),
 PRIMARY KEY(id))";

mysql_query ($sql) or die($problemMessage . __LINE__);

// go to & browse lang path

$path_lang = $rootSys . "claroline/lang";

chdir ($path_lang);

$handle = opendir($path_lang);

while ($element = readdir($handle) )
{
	if ( $element == "." || $element == ".." || $element == "CVS" 
        || strstr($element,"~") || strstr($element,"#") 
       )
	{
		continue; // skip current and parent directories
	}
	if ( is_dir($element) )
	{
		$langAttribute['path'] = $path_lang . '/' . $element;
		$langAttribute['name'] = reset( explode (".", $element) );
		$langList     []       = $langAttribute;
	}
}

if ( sizeof($langList) > 0)
{
	foreach($langList as $thisLangList)
	{
        echo "<h3>" . $thisLangList['name'] . "</h3>\n";
        glance_through_dir_lang($thisLangList['path'], $thisLangList['name']);
        echo "<hr />\n";
	}
}

// get and display end time

$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

echo "</body>\n</html>\n";

?>
