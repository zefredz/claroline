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

// get start time

$starttime = get_time();

// start html content

$nameTools = "Convert language file 1.7 to 1.8";

$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$urlTranslation = $urlSDK . 'translation_index.php';
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> get_lang('Administration'));
$interbredcrump[] = array ("url"=>$urlSDK, "name"=> get_lang('SDK'));
$interbredcrump[] = array ("url"=>$urlTranslation, "name"=> get_lang('TranslationTools'));

include($includePath."/claro_init_header.inc.php");

echo claro_disp_tool_title($nameTools);

// go to lang folder 

$path_lang = $rootSys . "claroline/lang";

// browse lang folder 

$languagePathList = get_lang_path_list($path_lang);

// display select box

if ( sizeof($languagePathList) > 0)
{
    echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";
    echo "<select name=\"language\">";
//    echo '<option value="all" selected="selected">' . get_lang('All') . '</option>'. "\n";
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
    echo get_lang('No language folder');
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
        
    // open conversion file
        
    if ( file_exists('conversion_17_to_18.xml') )
    {
        $xml = simplexml_load_file('conversion_17_to_18.xml');
    } 
    else 
    {
        exit('Echec lors de l\'ouverture du fichier conversion.xml.');
    }
    
    echo '<ol>' . "\n";

	foreach( $languageToBuild as $language )
	{
        $nb_update = 0;
        
        echo '<li>' . $language ;
        
        // open and parse old language file
        $language_list = load_array_translation($language);
        
        // convert file
        echo '<ol>' . "\n";
        
        foreach ($xml->varname as $varname)
        {
            switch ( (string) $varname['conversion'] )
            {
                case 'rename':
                    $old_value = (string) $varname->old;
                    $new_value = (string) $varname->new;
        
                    if ( isset($language_list[$old_value]) )
                    {
                        $language_list[$new_value] = $language_list[$old_value];
                        unset($language_list[$old_value]);
                        echo '<li>Rename ' . $old_value . ' to ' . $new_value . '</li>' . "\n";
                        $nb_update++;
                    }            
                    break;
                case 'delete':
                    $delete_value = (string) $varname->name;
                    if ( isset($language_list[$delete_value]) )
                    {
                        unset($language_list[$delete_value]);
                        echo '<li>Delete ' . $delete_value . '</li>' . "\n";
                        $nb_update++;
                    }
                    break;
            }
        }
        
        echo '</ol>' . "\n";
        
        // Write new file
        
        if ( $nb_update > 0 ) 
        {
                
                echo '<ul>';
               
                echo '<li>Save file : ' . $path_lang . "/" . $language . "/" . LANG_COMPLETE_FILENAME . '</li>';
 
                $fileHandle = fopen($path_lang . "/" . $language . "/" . LANG_COMPLETE_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);

        		// build language files
	
        		if ($fileHandle && count($language_list) > 0)
		        {
        		    fwrite($fileHandle, "<?php \n");

                    ksort($language_list);		

                    foreach ( $language_list as $varName => $varContent )
                    {
                        // addslashes not back slashes double quote                
        		        $varContent = preg_replace('/([^\\\\])"/', '\\1\\"', $varContent);

                        // addslashes before $
                        $varContent = preg_replace('/\$/','\\\$', $varContent);
		                $string = '$_lang[\''. $varName .'\'] = "'.$varContent."\";\n";
		                fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
        		    }
	
		            fwrite($fileHandle, "?>");
		        }
        		fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);
                
                echo '</ul>';

        }

        echo '</li>' . "\n" ;

    } // end foreach languages

    echo '</ul>';

} // if isset($language)

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n";

// display footer

include($includePath."/claro_init_footer.inc.php");

?>
