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
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

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
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> get_lang('Administration'));
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> get_lang('SDK'));
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> get_lang('Translation Tools'));

include($includePath."/claro_init_header.inc.php");

echo claro_html_tool_title($nameTools);

// go to lang folder

$path_lang = $rootSys . "claroline/lang";
chdir ($path_lang);

// browse lang folder

$languagePathList = get_lang_path_list($path_lang);

// display select box

if ( sizeof($languagePathList) > 0)
{
    echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";
    echo "<select name=\"lang\">";
    echo '<option value="all" selected="selected">' . get_lang('All') . '</option>'. "\n";
    foreach($languagePathList as $key => $languagePath)
    {

        if (isset($_REQUEST['lang']) && $key == $_REQUEST['lang'] )
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

if (isset($_REQUEST['lang']))
{

    $languageToBuild = array();

    if ($_REQUEST['lang'] == 'all')
    {
        foreach ($languagePathList as $language => $languagePath)
        {
            $languageToBuild[] = $language;
        }
    }
    else
    {
        $languageToBuild[] = $_REQUEST['lang'];
    }

    echo "<ol>\n";

    foreach( $languageToBuild as $language )
    {

        $languagePath = $languagePathList[$language];

        // get language name and display it

        echo "<li><strong>" . $language . "</strong> ";

        // get the different variables

        $sql = " SELECT DISTINCT used.varName, trans.varFullContent
                FROM " . $tbl_used_lang . " used, " . $tbl_translation  . " trans
                WHERE trans.language = '$language'
                  AND used.varName = trans.varName
                ORDER BY used.varName, trans.varContent";

        $result = claro_sql_query($sql) or die ("QUERY FAILED: " .  __LINE__);

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

        chdir ($languagePath);

        echo "- Create file: " . $languagePath . "/" . LANG_COMPLETE_FILENAME;

        $fileHandle = fopen(LANG_COMPLETE_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);

        // build language files

        if ($fileHandle && count($languageVarList) > 0)
        {
            fwrite($fileHandle, "<?php \n");

            foreach($languageVarList as $thisLangVar)
            {
                $string = build_translation_line_file($thisLangVar['name'],$thisLangVar['content']) ;
                fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
            }

            fwrite($fileHandle, "?>");
        }

        fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);

        echo "</li>\n";

    }
    echo "</ol>\n";

} // end sizeof($languagePathList) > 0

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

// display footer

include($includePath."/claro_init_footer.inc.php");

?>
