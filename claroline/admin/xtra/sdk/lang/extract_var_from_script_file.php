<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

require '../../../../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

set_time_limit (0);

/*
 * This script scans and retrieves all the language variables of an existing Claroline
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

require_once get_path('incRepositorySys') . '/lib/config.lib.inc.php';

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';

// get start time

$starttime = get_time();

// Start content

$nameTools = 'Extract variables from scripts';

$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );


include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

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

$scan=scan_dir (get_path('rootSys'),$recurse=TRUE);

$files = $scan['files'];

$total_var_count = 0;

foreach ($files as $file)
{

    echo "<h4>" . $file . "</h4>\n";

    // extract variables

    $scannedFileList = array(); // re init the scannedFileList for each new script

    $languageVarList = get_lang_vars_from_file($file);

    echo 'Found ' . count($languageVarList) . ' Variables' . "\n";
    // display variables

    $var_count = 0;

    foreach($languageVarList as $varName) ++$var_count;
    $total_var_count += $var_count;
    echo 'Variables: ' . $var_count;

    // update table
    store_lang_used_in_script($languageVarList,$file);

} // end foreach

$defCodeList = get_config_code_list();
foreach ($defCodeList as $configCode)
{
    $file = claro_get_conf_def_file($configCode). '/' . $configCode . '.def.conf.inc.php';
    echo '<h4>DEF ' . $file . '</h4>' . "\n";


    $languageVarList = get_lang_vars_from_deffile($file);

    echo 'Found ' . count($languageVarList) . ' Variables' . "\n";

    // display variables

    $var_count = 0;

    foreach($languageVarList as $varName) ++$var_count;
    $total_var_count += $var_count;
    echo "Variables: " . $var_count;

    // update table
    $file = realpath(dirname(__FILE__) . '/../../../' .  '/tool/config_edit.php');
    $file = str_replace('\\','/',$file);

    store_lang_used_in_script($languageVarList,$file);

} // end foreach

echo "<p>Total variables: " . $total_var_count . "</p>";

// end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

// display footer

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>