<?php

/*
 * This script build production lang files for all languages.
 */

// include configuration and library file
include ('language.conf.php');
include ('language.lib.php');

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';
$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

// get start time
$starttime = get_time();

// start html content
echo "<html>
<head>
 <title>Build production language files</title>
</head>
<body>";

echo "<h1>Build production language files</h1>\n";

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
		$path = $path_lang . '/' . $element;
		$name = reset( explode (".", $element) );
		$languagePath[$name] = $path;
	}
}

// display select box

if ( sizeof($languagePath) > 0)
{
    echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";
    echo "<select name=\"language\">";
	foreach($languagePath as $key => $thisLangPath)
	{
        if (isset($_REQUEST['language']) && $key == $_REQUEST['language'] )
        {
            echo "<option value=\"" . $key . "\" selected=\"selected\">" . $key . "</option>";
        }
        else 
        {
            echo "<option value=\"" . $key ."\">" . $key . "</option>";
        }
    }
    echo "</select>";
    echo "<p><input type=\"submit\" value=\"OK\" /></p>";
    echo "</form>";
}
else
{
    echo "No language folder";
}

// if select language and laguage exists

if (isset($_REQUEST['language']))
{
	
    // get language name and display it
    $language = $_REQUEST['language'];
    $path = $languagePath[$language];

    echo "<h3>in " . $language . "</h3>\n";
    echo "<p>\n";
    
    // move in the language folder
    chdir ($path);	

	// get the different variables 
	$sql = " SELECT DISTINCT used.langFile, 
                             used.varName,
                             translation.varFullContent
    		FROM ". $tbl_used_lang . " used,
                 ". $tbl_translation  . " translation
    		WHERE translation.language = '$language' 
                  AND used.varName = translation.varName
            GROUP BY used.langFile, used.varName
    		ORDER BY used.langFile, used.varName";

	$result = mysql_query($sql) or die ("QUERY FAILED: " .  __LINE__);
         
	if ($result) 
	{
	    $languageVarList = array();

	    while ($row=mysql_fetch_array($result))
	    {
            // get source file from query
            $languageFileName = $row['langFile'];
            
            // get name & content of the varibales
	        $thisLangVar['name'   ] = $row['varName'       ];
	        $thisLangVar['content'] = $row['varFullContent'];

            // put language variable 
	        $languageVarList[$languageFileName][] = $thisLangVar;
	    }
	}	

	// build language files

	if (count($languageVarList) > 0)
	{
	
        foreach ($languageVarList as $thisLanguageFilename => $thisLangVarList)
        {

            // add extension to file
            $languageFile =  $thisLanguageFilename . '.lang.php';

            echo "Create file: " . $path . "/" . $languageFile . "<br />\n";

            // open in write access language file
	        $fileHandle = fopen($languageFile, 'w') or die("FILE OPEN FAILED: ". __LINE__);	

            if ($fileHandle && count($thisLangVarList))
            {
                // write php header
	    	    fwrite($fileHandle, '<?php' . "\n");
	
    	        foreach($thisLangVarList as $thisLangVar)
	            {
                    // addslashes not back slashes double quote
	                $varContent = preg_replace('/([^\\\\])"/', '\\1\\"', $thisLangVar['content']);
		            $string = '$'.$thisLangVar['name'].' = "'.$varContent."\";\n";
                    // write the language variable
        	        fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
	            }
            
                // write php footer
		        fwrite($fileHandle, "?>");
            }
    		fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);
        }
	}

	echo "</p>";
}    


// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

echo "</body>\n</html>\n";

?>
