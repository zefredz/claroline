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

$cidReset=true;
$gidReset=true;

require '../../../inc/claro_init_global.inc.php';
include($includePath."/lib/debug.lib.inc.php");
include ('lang/language.conf.php');

$nameTools = $langTranslationTools;
$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$table_exists = TRUE;

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';
$tbl_used_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

$sql1 = " select count(*) from " . $tbl_used_lang;
$sql2 = " select count(*) from " . $tbl_used_translation;

mysql_query($sql1);
if ( mysql_errno() == 1146 ) $table_exists = FALSE;

mysql_query($sql2);
if ( mysql_errno() == 1146 ) $table_exists = FALSE;

// DISPLAY

// Deal with interbredcrumps  and title variable

$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => $langAdministration);
$interbredcrump[] = array ('url' => $urlSDK, 'name' => $langSDK);

include $includePath . '/claro_init_header.inc.php';

// echo claro_disp_tool_title('<img src="lang/language.png" style="vertical-align: middle;" alt="" /> '.$nameTools);
echo claro_disp_tool_title($nameTools);
?>
<h4><?php echo $langExtractLangVariable?></h4>
<ul>
<li><a href="lang/extract_var_from_lang_file.php"><?php echo $langExtractFromLangFile?></a></li>
<li><a href="lang/extract_var_from_script_file.php"><?php echo $langExtractFromScriptFile?></a></li>
</ul>

<?php
if ( $table_exists == TRUE )
{
?>

<h4><?php echo $langBuildLangFile?></h4>
<ul>
<li><a href="lang/build_devel_lang_file.php"><?php echo $langBuildCompleteLangFile?></a></li>
<li><a href="lang/build_prod_lang_file.php"><?php echo $langBuildProductionLangFile?></a></li>
<li><a href="lang/build_missing_lang_file.php"><?php echo $langBuildMissingLangFile?></a></li>
<li><a href="lang/build_empty_lang_file.php"><?php echo $langBuildEmptyLangFile?></a></li>
</ul>

<h4><?php echo $langFindDoubledVariable?></h4>
<ul>
<li><a href="lang/display_var_diff.php"><?php echo $langFindVarWithSameNameAndDifferentContent?></a></li>
<li><a href="lang/display_content_diff.php"><?php echo $langFindVarWithSameContentAndDifferentName?></a></li>
</ul>

<h4><?php echo $langTranslationStatistics?></h4>
<ul>
<li><a href="lang/progression_translation.php"><?php echo $langTranslationStatistics?></a></li>
</ul>
<?
}
?>

<?php
include $includePath . '/claro_init_footer.inc.php';
?>
