<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

require '../../../../inc/claro_init_global.inc.php';

// SECURITY CHECK

if (!$is_platformAdmin) claro_disp_auth_form();

/*
 * This script build the devel lang files for all languages.
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

$nameTools = "Build development language files";

$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$urlTranslation = $urlSDK . 'translation_index.php';
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> $langSDK);
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> $langTranslationTools);

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($nameTools);

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
    echo "<ol>\n";
	foreach($languageList as $thisLangList)
	{
	
	    $language = $thisLangList['name'];
	    echo "<li><strong>" . $language . "</strong> ";
	
		// get the different variables 
	
		$sql = " SELECT DISTINCT trans.varName, trans.varFullContent
	    		FROM " . $tbl_used_lang . " used, " . $tbl_translation  . " trans
	    		WHERE trans.language = '$language' 
                  AND used.varName = trans.varName
	    		ORDER BY trans.varName, trans.varContent";
	
		$result = mysql_query($sql) or die ("QUERY FAILED: " .  __LINE__);
	         
		if ($result) 
		{
		    $languageVarList = array();
	
		    while ($row=mysql_fetch_array($result))
		    {
		        $thisLangVar['name'   ] = $row['varName'       ];
		        $thisLangVar['content'] = $row['varFullContent'];
		
		        $languageVarList[] = $thisLangVar;
		    }
		}
	
		chdir ($thisLangList['path']);
	
		echo "- Create file: " . $thisLangList['path'] . "/" . LANG_COMPLETE_FILENAME;
	
		$fileHandle = fopen(LANG_COMPLETE_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);	
	
		// build language files
	
		if ($fileHandle && count($languageVarList) > 0)
		{
		    fwrite($fileHandle, "<?php \n");
		
		    foreach($languageVarList as $thisLangVar)
		    {
                $varContent = $thisLangVar['content'];

                // addslashes not back slashes double quote                
		        $varContent = preg_replace('/([^\\\\])"/', '\\1\\"', $varContent);

                // addslashes before $
                $varContent = preg_replace('/\$/','\\\$', $varContent);

		        $string = '$'.$thisLangVar['name'].' = "'.$varContent."\";\n";
	
		        fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
		    }
	
		    fwrite($fileHandle, "?>");
		}
	
		fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);
		
		echo "</li>\n";
	
	}    
    echo "</ol>\n";

} // end sizeof($languageList) > 0

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

// display footer

include($includePath."/claro_init_footer.inc.php");

?>
