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

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('NotAllowed'));

/*
 * This script retrieves all the existing translation of an existing Claroline
 * It scans all the files of the 'lang' directory and stored the get_lang(' variables')
 * content into a mySQL database.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

// table

$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

// get start time

$starttime = get_time();

// Start content

$nameTools = 'Extract variables from language files';

$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$urlTranslation = $urlSDK . 'translation_index.php';
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> get_lang('Administration'));
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> get_lang('SDK'));
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> get_lang('TranslationTools'));

include($includePath."/claro_init_header.inc.php");

echo claro_disp_tool_title($nameTools);

// drop table if exists

$sql = "DROP TABLE IF EXISTS ". $tbl_translation ." ";

claro_sql_query ($sql);

// create table 

$sql = "CREATE TABLE ". $tbl_translation ." (
 id INTEGER NOT NULL auto_increment,
 language VARCHAR(250) NOT NULL,
 varName VARCHAR(250) BINARY NOT NULL,
 varContent VARCHAR(250) NOT NULL,
 varFullContent TEXT NOT NULL,
 sourceFile VARCHAR(250) NOT NULL,
 used tinyint(4) default 0,
 INDEX index_language (language,varName),
 INDEX index_content  (language,varContent),
 PRIMARY KEY(id))";

claro_sql_query($sql);

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
		$languageAttribute['path'] = $path_lang . '/' . $element;
		$languageAttribute['name'] = reset( explode (".", $element) );
		$languageList     []       = $languageAttribute;
	}
}

if ( sizeof($languageList) > 0)
{
	foreach($languageList as $thisLangList)
	{
        echo "<h4>" . $thisLangList['name'] . "</h4>\n";
        glance_through_dir_lang($thisLangList['path'], $thisLangList['name']);
        echo "<hr />\n";
	}
}

// get and display end time

$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

// display footer

include($includePath."/claro_init_footer.inc.php");

?>
