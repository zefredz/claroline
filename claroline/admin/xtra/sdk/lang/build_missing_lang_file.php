<?php

/*
 * This script build the lang files with var without translation for all languages.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

// get start time

$starttime = get_time();

// start html content

echo "<html>
<head>
 <title>Build missing language files</title>
</head>
<body>";

echo "<h1>Build missing language files</h1>\n";

// go to lang folder 

$path_lang = $rootSys . "claroline/lang";
chdir ($path_lang);

// browse lang folder 

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
		$languageAttribute['path'] = $path_lang . '/' . $element;
		$languageAttribute['name'] = reset( explode (".", $element) );
		$languageList     []       = $languageAttribute;
	}
}

if ( sizeof($languageList) > 0)
{
	foreach($languageList as $thisLangList)
	{
	
	    $language = $thisLangList['name'];
	    echo "<h3>" . $language . "</h3>\n";
	
		// get the different variables 

    	$sql = " SELECT DISTINCT u.varName
	         FROM ". TABLE_USED_LANG_VAR . " u 
	         LEFT JOIN " . TABLE_TRANSLATION . " t ON 
	         (
	            u.varName = t.varName 
	            AND t.language=\"" . $language . "\"
	         ) 
	         WHERE t.varContent is NULL
             ORDER BY u.varName";
	
		$result = mysql_query($sql) or die ("QUERY FAILED: " .  __LINE__);
	         
		if ($result) 
		{
		    $languageVarList = array();
	
		    while ($row=mysql_fetch_array($result))
		    {
		        $thisLangVar['name'   ] = $row['varName'       ];
		        $languageVarList[] = $thisLangVar;
		    }
		}
	
		chdir ($thisLangList['path']);
	
		echo "<p>Create file: " . $thisLangList['path'] . "/" . LANG_MISSING_FILENAME . "</p>\n";
	
		$fileHandle = fopen(LANG_MISSING_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);	
	
		// build language files
	
		if ($fileHandle && count($languageVarList) > 0)
		{
		    fwrite($fileHandle, "<?php \n");
		
		    foreach($languageVarList as $thisLangVar)
		    {
                $varContent = "";
		        $varContent = preg_replace('/([^\\\\])"/', '\\1\\"', $varContent);
		        $string = '$'.$thisLangVar['name'].' = "'.$varContent."\";\n";
	
		        fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
		    }
	
		    fwrite($fileHandle, "?>");
		}
	
		fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);
		
		echo "<hr />\n";
	
	}    

} // end sizeof($languageList) > 0

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

echo "</body>\n</html>\n";

?>
