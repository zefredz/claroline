<?php

/*
 * This script build the devel lang files for all languages.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

// get start time

$starttime = get_time();

// start html content

echo "<html>
<head>
 <title>Build development language files</title>
</head>
<body>";

echo "<h1>Build development language files</h1>\n";

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
		$langAttribute['path'] = $path_lang . '/' . $element;
		$langAttribute['name'] = reset( explode (".", $element) );
		$langList     []       = $langAttribute;
	}
}

if ( sizeof($langList) > 0)
{
	foreach($langList as $thisLangList)
	{
	
	    $language = $thisLangList['name'];
	    echo "<h3>" . $language . "</h3>\n";
	
		// get the different variables 
	
		$sql = " SELECT DISTINCT trans.varName, trans.varFullContent
	    		FROM " . TABLE_USED_LANG_VAR . " used, " .TABLE_TRANSLATION. " trans
	    		WHERE trans.language = '$language' 
                  AND used.varName = trans.varName
	    		ORDER BY trans.varName, trans.varContent";
	
		$result = mysql_query($sql) or die ("QUERY FAILED: " .  __LINE__);
	         
		if ($result) 
		{
		    $langVarList = array();
	
		    while ($row=mysql_fetch_array($result))
		    {
		        $thisLangVar['name'   ] = $row['varName'       ];
		        $thisLangVar['content'] = $row['varFullContent'];
		
		        $langVarList[] = $thisLangVar;
		    }
		}
	
		chdir ($thisLangList['path']);
	
		echo "<p>Create file: " . $thisLangList['path'] . "/" . LANG_COMPLETE_FILENAME . "</p>\n";
	
		$fileHandle = fopen(LANG_COMPLETE_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);	
	
		// build language files
	
		if ($fileHandle && count($langVarList) > 0)
		{
		    fwrite($fileHandle, "<?php \n");
		
		    foreach($langVarList as $thisLangVar)
		    {
                $varContent = $thisLangVar['content'];
		        $varContent = preg_replace('/([^\\\\])"/', '\\1\\"', $varContent);
		        $string = '$'.$thisLangVar['name'].' = "'.$varContent."\";\n";
	
		        fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
		    }
	
		    fwrite($fileHandle, "?>");
		}
	
		fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);
		
		echo "<hr />\n";
	
	}    

} // end sizeof($langList) > 0

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

echo "</body>\n</html>\n";

?>
