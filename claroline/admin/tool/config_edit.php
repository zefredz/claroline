<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/**
 * This tool is write to edit setting  of  claroline.
 * In the old claroline, there was a central config file
 * in next release a conf repository was build  with conf files
 * To not owerwrite on the following release,
 * was rename  from .conf.inc.php to .conf.inc.php.dist
 * installer was eable to rename from .conf.inc.php.dist to .conf.inc.php

 * The actual config file is build to merge new and active setting.
 * The system as more change than previous evolution
 * Tool are released with a conf definition file
 * This file define for each property a name, a place but also
 * some control for define accepted content.
 * and finally some comment, explanation or info
 *
 * this version do not include
 * * trigered procedure (function called when a property
 *   is switch or set to a particular value)
 * * renaming or deletion of properties from config
 * * locking  of edit file (This tools can't really be
 *   in the active part of the day in prod. )
 *   I need to change that to let  admin sleep during the night
 *
 * To make transition,
 * * a section of tool continue to
 *   edit the main configuration (benoit's script)
 * * a section can parse old file to found old properties
 *   and his values.
 *   This script would be continue to generate a def conf file.
 *
 */

// LANGUAGE

$langConfiguration = "Configuration";
$lang_config_config = 'Édition des fichiers de configuration';
$lang_config_config_short = 'Configuration';
$lang_nothingToConfigHere='Il n\'y a pas de paramétrage pour <B>%s</B>';
$langBackToMenu = 'Retour au Menu';
$langShowConf        = 'Show Conf';
$langShowDef         = 'Show Def';

$langShowConf        = 'Afficher la configuration';
$langShowDef         = 'Afficher le fichier de définition';

$langNoPropertiesSet = 'Il n\'y a pas de propriétés proposées';
$langShowContentFile = 'Voir le contenu du fichier';
$langFile            = 'fichier';
$langApply           = 'Appliquer';
$langApplied         = 'Appliqué';
$langConfig          = 'Configuration';
$lang_p_defFileOf_S = 'Fichier de définition pour la configuration %s.';
$lang_the_active_config_has_manually_change='Version de production modifiée';
$langFirstDefOfThisValue = '!!! Nouvelle valeur !!!';
$lang_p_config_file_creation = 'création du fichier de configuration  :<BR> %s';
$lang_p_DefNameAndContentDisruptedOnConfigCode = 'Le fichier de définition est probablement un copier-coller  de %s. Et n\'a pas été achevé.';

$langEmpty =  'empty';
$lang_p_nothing_to_edit_in_S = 'nothing to edit in %s';
$lang_p_DefNameAndContentDisruptedOnConfigCode = 'The definition file for configuration is probably copypaste from %s';
$langFirstDefOfThisValue = '!!!First definition of this value!!!';
$langNoPropertiesSet = 'No properties set';
$langShowConf        = 'Show Config file';
$langShowDef         = 'Show Definition file';
$langShowContentFile = 'Show content file';
$langFile            = 'File';
$langApply           = 'Apply';
$langApplied         = 'Applied';
$langConfig          = 'Configuration';
$lang_p_defFileOf_S = 'Show defintion file of %s config.';
$lang_p_edit_S      = 'Editing %s config.';
$lang_p_edit_S      = 'Edition de %s.';
$lang_p_Properties_of_S_saved_in_buffer = 'Properties of %s saved in buffer.';
$lang_the_active_config_has_manually_change='The config in production has manually changed';
$lang_p_config_missing_S = 'Configuration is missing %s';
$lang_p_ErrorOnBuild_S_for_S= 'Error in building of <em>%s</em> for <B>%s</B>';
$lang_p_config_file_creation = 'Configuration  file creation:<BR> %s';
$lang_noSectionFoundInDefinitionFile = 'no section found in definition file';
$lang_p_PropForConfigCommited = 'Properties for %s (%s) are now effective on server.';
$langPropertiesNotIncludeInSections = 'Properties not include in sections';
$lang_unknowProperties = 'Properties not know in definition file';
// include init and library files

require '../../inc/claro_init_global.inc.php';

include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/course.lib.inc.php');
include($includePath.'/lib/config.lib.inc.php');

/* ************************************************************************** */
/*  INITIALISE VAR
/* ************************************************************************** */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_tool            = $tbl_mdb_names['tool'];
$tbl_config_property = $tbl_mdb_names['config_property'];
$tbl_config_file     = $tbl_mdb_names['config_file'];
$tbl_rel_tool_config = $tbl_mdb_names['rel_tool_config'];

$toolNameList = array('CLANN___' => $langAnnouncement,
                      'CLFRM___' => $langForums,
                      'CLCAL___' => $langAgenda,
                      'CLCHT___' => $langChat,
                      'CLDOC___' => $langDocument,
                      'CLDSC___' => $langDescriptionCours,
                      'CLGRP___' => $langGroups,
                      'CLLNP___' => $langLearningPath,
                      'CLQWZ___' => $langExercises,
                      'CLWRK___' => $langWork,
                      'CLUSR___' => $langUsers);

/* ************************************************************************** */
/*  SECURITY CHECKS
/* ************************************************************************** */

$is_allowedToAdmin  = $is_platformAdmin;

if(!$is_allowedToAdmin)
{
    claro_disp_auth_form(); // display auth form and terminate script
}

// define bredcrump

$nameTools = $langConfiguration;

$interbredcrump[] = array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
$interbredcrump[] = array ('url'=>$rootAdminWeb.'tool/config_list.php', 'name'=> $langConfiguration);

/**
 * Process
 */

$display_form = TRUE;

if ( !isset($_REQUEST['config_code']) )
{
	// no config_code
	// return to index
	$controlMsg['info'][] = "No configuration code";
	$display_form = FALSE;
}
else
{
    // get config_code
	$config_code = trim($_REQUEST['config_code']);

    // Get info def and conf file (existing or not) for this config code.
    $confDef  = claro_get_def_file($config_code);
    $confFile = claro_get_conf_file($config_code);

	if ( file_exists($confDef) )
    {

        // define bredcrump
        $nameTools = get_config_name($config_code);
        $QUERY_STRING = 'config_code='.$config_code;

        if ( isset($_REQUEST['cmd']) && isset($_REQUEST['prop']) )
        {
            if ( $_REQUEST['cmd'] = 'save')
			{                
                $okToSave = TRUE;

				// Save configuration
                unset($conf_def,$conf_def_property_list);
        
                require($confDef);

                if ( is_array($_REQUEST['prop']) )
                {

                    // Validate form params
                    foreach ( $_REQUEST['prop'] as $propName => $propValue )
                    {
                        if (!config_checkToolProperty($propValue, $conf_def_property_list[$propName]))
                        {
                            $okToSave = FALSE;
                        }
                    }

                    if ( $okToSave )
                    {
                        // Save form param in buffer
                        reset($_REQUEST['prop']);
                        foreach ( $_REQUEST['prop'] as $propName => $propValue )
                        {
                            save_param_value_in_buffer($propName,$propValue, $config_code);
                        }
                    }
                    else
                    {
                        $controlMsg['info'][] = 'Save aborded';
                    }
                }
                else
                {
                    $okToSave = FALSE;
                }
        
                if ( $okToSave )
                {
                    // OK to build the conf file.
        
                    // 1° Get extra info from the def file.
                    require($confDef);
        
                    // 2° Perhaps it's the first creation
                    if ( !$confFile )
                    {
                        $confFile = claro_create_conf_filename($config_code);
                        $controlMsg['info'][] = sprintf($lang_p_config_file_creation
                                                       ,$confFile);
                        $confFile = claro_get_conf_file($config_code);
                    }
        
                    $storedPropertyList = read_param_value_in_buffer($config_code);
        
                    if ( is_array($storedPropertyList) && count($storedPropertyList)>0 )
                    {
                        if ( write_conf_file($conf_def,$conf_def_property_list,$storedPropertyList,$confFile, realpath(__FILE__)) )
                        {
                            set_hash_confFile($confFile,$config_code);
                            $hashConf = md5_file($confFile);
                            $controlMsg['info'][] =  sprintf($lang_p_PropForConfigCommited,$nameTools,$config_code);
                            $controlMsg['debug'][] = 'file generated for <B>'.$config_code.'</B> is <em>'.$confFile.'</em>'.'<br>Signature : <TT>'.$hashConf.'</tt>';
                        }
                        else
                        {
                            $controlMsg['error'][] = sprintf($lang_p_ErrorOnBuild_S_for_S,$confFile,$config_code);
                        }
                    }
                    else
                    {
                        $controlMsg['info'][] = 'No Properties for '.$nameTools
                                               .' ('.$config_code.').<BR><em>'.$confFile.'</em> is not generated';
        
                    }
                }
				
			}

		}
		
		/**
         *  Display configuration form
         */

		require($confDef);

        $nameTools = get_config_name($config_code);
        $conf_info = get_conf_info($config_code);

        // read value from buffer (database)
        $storedPropertyList = read_param_value_in_buffer($config_code);

        if ( is_array($storedPropertyList) )
        {
            foreach ( $storedPropertyList as $storedProperty )
            {
                $conf_def_property_list[$storedProperty['propName']]['actualValue'] = $storedProperty['propValue'];
            }
        }

        /* Search for value  existing  in conf file but not in def file, or inverse */
        $currentConfContent = parse_config_file(basename(claro_get_conf_file($config_code)));
        unset($currentConfContent[$config_code.'GenDate']);
        
        $currentConfContentKeyList = is_array($currentConfContent)?array_keys($currentConfContent):array();
        $conf_def_property_listKeyList = is_array($conf_def_property_list)?array_keys($conf_def_property_list):array();
        $unknowValueInConfigFileList = array_diff($currentConfContentKeyList,$conf_def_property_listKeyList);
        $newValueInDefFile = array_diff($conf_def_property_listKeyList,$currentConfContentKeyList);

        if (is_array($conf_def['section']) )
        {
            foreach($conf_def['section'] as $sectionKey => $section)
            {
                if (is_array($section['properties']))
                {
                    foreach($section['properties'] as $propertyName )
                    {
                        $conf_def_property_list[$propertyName]['section']=$sectionKey;
                    }
                }
            }
        }

        foreach ($conf_def_property_list as $_propName => $_propDescriptorList)
        {
            if (!isset($_propDescriptorList['section']))
            {
                $conf_def_property_list['section']='missingSection';
                $conf_def['section']['sectionmissing']['properties'][]=$_propName;
            }
        }

       if (isset($conf_def['section']['sectionmissing']))
        {
            $conf_def['section']['sectionmissing']['label'] = $langPropertiesNotIncludeInSections;
            $conf_def['section']['sectionmissing']['description'] = 'This is an error in definition file. Request to the coder of this config to add theses proporties in a section of the definition file.';

        }	
	}
	else 
	{
		// Definition file doesn't exists
		$controlMsg['info'][] = sprintf("This %s doesn't exist",$config_code.'.def.conf.php');
		$display_form = FALSE;
	}

}

/**
 * Display
 */

// display claroline header


include($includePath."/claro_init_header.inc.php");

// display tool title

claro_disp_tool_title(array('mainTitle'=>$langConfiguration,'subTitle'=>$nameTools));

// Verify integrity md5sum

// read value from buffer (database)
$conf_info = get_conf_info($_REQUEST['config_code']);

if ( $conf_info['manual_edit'] == TRUE )
{
    $controlMsg['info'][] = 'The config file has manually change.<br>'
           .'<br>'
           .'Actually the script prefill with values found in the current conf, '
           .'and overwrite values set in the database'
           ;
    
}

// display message

if ( is_array($controlMsg['debug']) ) unset($controlMsg['debug']);

if ( !empty($controlMsg) ) 
{
	claro_disp_msg_arr($controlMsg);
}

// Display form

if ( $display_form )
{
    if ( is_array($conf_def) )
    {
        if ( !empty($conf_def['description']) )
        {
            echo '<p>'.$conf_def['description'].'</p>' . "\n";
        }
    
        // display form
        echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '" name="editConfClass" >' . "\n";
        echo '<input type="hidden" name="config_code" value="' . $config_code . '" >' . "\n";
        echo '<input type="hidden" name="cmd" value="save" >' . "\n";
    
        if (is_array($conf_def['section']) )
        {
    
            echo '<table class="claroTable"  border="0" cellpadding="5" width="100%">' . "\n";
    
            foreach($conf_def['section'] as $section)
            {
    
                // display fieldset with the label of the section
                echo '<tr>'
                    .'<th class="superHeader" colspan="3">' . $section['label'] . '</th>'
                    .'</tr>' . "\n";
    
                // display description of the section
                if ( !empty($section['description']) )
                {
                    echo '<tr><th class="headerX" colspan="3">' . $section['description'] . '</th></tr>' . "\n";
                }
                else
                {
                    echo '<tr><th class="headerX" colspan="3">&nbsp;</th></tr>' . "\n";
                }
    
                // The default value is show in input or preselected value if there is no value set.
                // If a value is already set the default value is show as sample.
                if ( is_array($section['properties']) )
                {
    
                    // display properties
                    foreach( $section['properties'] as $property )
                    {
                        if (is_array($conf_def_property_list[$property]))
                        {
                            if ( isset($_REQUEST['prop'])  )
                            {
                                claroconf_disp_editbox_of_a_value($conf_def_property_list[$property], $property, $prop[$property]);
                            }
                            else
                            {
                                claroconf_disp_editbox_of_a_value($conf_def_property_list[$property], $property, $currentConfContent[$property]);
                            }
                        }
                        else
                        {
                            echo 'Def corrupted: property '.$property.' is not defined';
                        }
                    }
                }
    
            }
            echo '</table>' . "\n";
    
            echo '<input type="submit" value="Save" >' . "\n";
        }
        else
        {
            echo 'no section found in definition file';
        }

        if (sizeof($unknowValueInConfigFileList)>0)
        {
            echo '<table class="claroTable"  border="0" cellpadding="5" width="100%">' . "\n"
                .'<tr><th class="superHeader" colspan="3">'.$lang_unknowProperties.'</th></tr>'
                ;
            foreach ($unknowValueInConfigFileList as $key => $unknowValueInConfigFile)
            {
                $htmlPropLabel = $unknowValueInConfigFile;
                echo '<tr style="vertical-align: top">' 
		           . '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' . "\n"
                   . '<td nowrap="nowrap" colspan="2">' . "\n"
                   . var_export($currentConfContent[$unknowValueInConfigFile],1)
                   .'</td></tr>' . "\n"
                   ;
            }
            echo '</table>';
        }
        echo '</form>'."\n";
    }
    else
    {
        $controlMsg['info'][] = sprintf($lang_p_nothing_to_edit_in_S ,get_config_name($config_code));
        claro_disp_message_box($controlMsg);
    }
}

// display footer

include($includePath."/claro_init_footer.inc.php");

?>