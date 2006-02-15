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
$nameTools = 'Build production language files';

$urlSDK = $rootAdminWeb . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> get_lang('Administration'));
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> get_lang('SDK'));
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> get_lang('TranslationTools'));

include($includePath."/claro_init_header.inc.php");

echo claro_disp_tool_title($nameTools);

// go to lang folder
$path_lang = $rootSys . "claroline/lang";
chdir ($path_lang);

// browse lang folder

$languagePathList = get_lang_path_list($path_lang);

// display select box

if ( sizeof($languagePathList) > 0)
{
    echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";
    echo "<select name=\"language\">";
    echo '<option value="all" selected="selected">' . get_lang('All') . '</option>'. "\n";
    foreach($languagePathList as $key => $languagePath)
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

    $languageToBuild = array();

    if ($_REQUEST['language'] == 'all')
    {
        foreach ($languagePathList as $language => $languagePath)
        {
            $languageToBuild[] = $language;
        }
    }
    else
    {
        $languageToBuild[] = $_REQUEST['language'];
    }


    foreach ($languageToBuild as $language)
    {
        // get language name and display it

        $languagePath = $languagePathList[$language];

        echo "<h4>in " . $language . "</h4>\n";

        // move in the language folder
        chdir ($languagePath);

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

            echo "<ol>\n";

            foreach ($languageVarList as $thisLanguageFilename => $thisLangVarList)
            {
                echo "<li>";
                // add extension to file
                $languageFile =  $thisLanguageFilename . '.lang.php';

                echo "Create file: " . $languagePath . "/" . $languageFile;

                // open in write access language file
                $fileHandle = fopen($languageFile, 'w') or die("FILE OPEN FAILED: ". __LINE__);

                if ($fileHandle && count($thisLangVarList))
                {
                    // write php header
                    fwrite($fileHandle, '<?php' . "\n");

                    foreach($thisLangVarList as $thisLangVar)
                    {
                        $string = build_translation_line_file($thisLangVar['name'],$thisLangVar['content']) ;
                        fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
                    }

                    // write php footer
                    fwrite($fileHandle, "?>");
                }
                fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);
                echo "</li>\n";
            }
            echo "</ol>\n";
        }
    }

}

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

// display footer

include($includePath."/claro_init_footer.inc.php");

?>
