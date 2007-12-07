<?php // $Id$
/**
 * CLAROLINE 
 *
 * This tool is write to edit setting  of  claroline.
 *
 * In the old claroline, there was a central config file
 * in next release a conf repository
 * was build with conf files.
 *
 * To not owerwrite on the following release,
 * was rename  from .conf.inc.php to .conf.inc.php.dist
 * installer was eable to rename from .conf.inc.php.dist
 * to .conf.inc.php
 *
 * The actual config file is build 
 * to merge new and active setting.
 *
 * The system as more change than previous evolution
 * Tool are released with a conf definition file.
 *
 * This file define for each property a name, 
 * a place but also some control for define accepted content.
 *
 * And finally some comment, explanation or info
 *
 * this version do not include
 * - trigered procedure (function called when a property
 *   is switch or set to a particular value)
 * - renaming or deletion of properties from config
 * - locking  of edit file (This tools can't really be
 *   in the active part of the day in prod. )
 *   I need to change that to let 
 *   admin sleep during the night
 *
 * To make transition,
 * - a section can parse old file to found old properties
 *   and his values.
 *   This script would be continue 
 *   to generate a def conf file.
 *
 * @version 1.7 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CONFIG
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

$cidReset=TRUE;
$gidReset=TRUE;

// include init and library files

require '../../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

/* ************************************************************************** */
/*  Initialise variables and include libraries
/* ************************************************************************** */

include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/course.lib.inc.php');
include($includePath.'/lib/config.lib.inc.php');

/* ************************************************************************** */
/* Process
/* ************************************************************************** */

$display_form = TRUE;

if ( !isset($_REQUEST['config_code']) )
{
    // no config_code
    // return to index
    $controlMsg['info'][] = 'No configuration code';
    $display_form = FALSE;
}
else
{
    // get config_code
    $config_code = trim($_REQUEST['config_code']);

    // Get info def and conf file (existing or not) for this config code.
    $def_file  = get_def_file($config_code);
    $conf_file = get_conf_file($config_code);

    if ( file_exists($def_file) )
    {
        $config_name = get_conf_name($config_code);

        if ( isset($_REQUEST['cmd']) && isset($_REQUEST['prop']) )
        {
            if ( $_REQUEST['cmd'] == 'save')
            {
                $okToSave = TRUE;

                // unset $conf_def & $conf_def_property_list array
                unset($conf_def,$conf_def_property_list);

                // get $conf_def & $conf_def_property_list array from definition files
                require($def_file);

                // validation of params posted by form
                if ( is_array($_REQUEST['prop']) )
                {
                    // validate form params
                    foreach ( $_REQUEST['prop'] as $propertyName => $propertyValue )
                    {
                        if (!validate_property($propertyValue, $conf_def_property_list[$propertyName]))
                        {
                            $controlMsg['info'][] = $propertyName . '(No valid)';
                            $okToSave = FALSE;
                        }
                    }

                    if ( $okToSave )
                    {
                        // save property in database
                        reset($_REQUEST['prop']);
                        foreach ( $_REQUEST['prop'] as $propertyName => $propertyValue )
                        {
                            $storedPropertyList[] = array('propName'=>$propertyName
                                                         ,'propValue'=>$propertyValue);
                                                         
                            // here was writing to DB.... Perhaps refactoring is needed
                        }
                    }
                    else
                    {
                        $controlMsg['info'][] = 'Save aborded';
                    }
                }
                else
                {
                    // No values posted by form
                    $okToSave = FALSE;
                    $controlMsg['info'][] = 'Save aborded';
                }

                if ( $okToSave )
                {
                    // build the conf file.

                    // 1° Get extra info from the def file.
                    require($def_file);

                    // 2° Perhaps it's the first creation
                    if ( !file_exists($conf_file) )
                    {
                        // create an empty file
                        if ( touch($conf_file) )
                        {
                            $controlMsg['info'][] = sprintf($lang_p_config_file_creation,$conf_file);
                        }
                        else
                        {
                            $controlMsg['info'][] = sprintf($lang_p_config_file_creation,$conf_file);
                        }
                    }

                    // here was reading from DB.... Perhaps refactoring is needed
                    if ( is_array($storedPropertyList) && count($storedPropertyList)>0 )
                    {

                        if ( write_conf_file( $conf_def
                                            , $conf_def_property_list
                                            , $storedPropertyList
                                            , $conf_file,realpath(__FILE__))
                                            )
                        {
                            // calculate hash of the config file
                            $conf_hash = md5_file($conf_file); // not in php 4.1
                            //$conf_hash = filemtime($conf_file);
                            if (save_config_hash_in_db($config_code,$conf_hash) )
                            {
                                $controlMsg['info'][] = sprintf( $lang_p_PropForConfigCommited
                                                               , $config_name
                                                               , $config_code
                                                               );
                                $controlMsg['debug'][] = 'File generated for <b>' . $config_name . '</b> is <em>'.$conf_file.'</em><br />' . "\n"
                                                       . 'Signature : <tt>' . $conf_hash . '</tt>';
                            }
                        }
                        else
                        {
                            $controlMsg['error'][] = sprintf( $lang_p_ErrorOnBuild_S_for_S
                                                            , $confFile
                                                            , $config_code);
                        }
                    }
                    else
                    {
                        $controlMsg['info'][] = 'No Properties for '.$config_name . ' (' . $config_code . '). <br />' . "\n"
                                              . '<em>'.$confFile.'</em>is not generated' ;
                    }
                }

            }

        }

        /*
         *  Get values from the configuration file.
         */

        // unset $conf_def & $conf_def_property_list array
        unset($conf_def,$conf_def_property_list);
        require($def_file);

        // Search for value  existing  in conf file 
        $currentConfContent = get_values_from_confFile($conf_file,$conf_def_property_list);

        unset($currentConfContent[$config_code.'GenDate']);

        if (is_array($conf_def['section']) )
        {
            foreach($conf_def['section'] as $sectionKey => $section)
            {
                // set force display off if section is hidden
                $force_display_off = (isset($section['display']) && !$section['display'] );

                if (is_array($section['properties']))
                {
                    foreach($section['properties'] as $propertyName )
                    {
                        $conf_def_property_list[$propertyName]['section']=$sectionKey;

                        // force display FALSE for properties in hidden section
                        if ($force_display_off) $conf_def_property_list[$propertyName]['display'] = FALSE;
                    }
                }
            }
        }
        
        if(!is_array($conf_def_property_list))
        {
             $controlMsg['info'][] = ''.$config_name . ' def file not valid (' . $config_code . '). <br />' . "\n"
                                   . '<em>$conf_def_property_list </em>is missing' ;
        }
        else
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
            $conf_def['section']['sectionmissing']['description'] =
            $langThisIsAnErrorInDefinitionFile . ' ' .
            $langRequestToTheCoderOfThisConfigToAddThesesProportiesInASectionOfTheDefinitionFile;
        }
    }
    else
    {
        // Definition file doesn't exists
        $controlMsg['info'][] = sprintf("This %s doesn't exist",$config_code.'.def.conf.php');
        $display_form = FALSE;
    }

    // verify is config file manually edit
    if ( is_conf_file_modified($_REQUEST['config_code']) )
    {
        $controlMsg['info'][] = 'Note. This configuration file has been manually changed. The system will try to retrieve all the configuration values, but it can not guarantee to retrieve additional settings manually inserted.<br>';
    }

}

/* ************************************************************************** */
/* Display
/* ************************************************************************** */

if ( !isset($config_name) )
{
    $nameTools = $langConfiguration;
}
else
{
    // tool name and url to edit config file
    $nameTools = $config_name; // the name of the configuration page
    $_SERVER['QUERY_STRING'] = 'config_code=' . $config_code;
}

// define bredcrumb
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => $langAdministration);
$interbredcrump[] = array ('url' => $rootAdminWeb . 'tool/config_list.php', 'name' => $langConfiguration);

// display claroline header
include($includePath . '/claro_init_header.inc.php');

// display tool title
echo claro_disp_tool_title(array('mainTitle'=>$langConfiguration,'subTitle'=>$nameTools));

// display message
if ( isset($controlMsg['debug']) ) unset($controlMsg['debug']);

if ( !empty($controlMsg) )
{
    claro_disp_msg_arr($controlMsg);
}

// Display edition form
if ( $display_form )
{
    if ( is_array($conf_def) )
    {

        // display description of configuration
        if ( !empty($conf_def['description']) )
        {
            echo '<p>'.$conf_def['description'].'</p>' . "\n";
        }

        // start edition form
        echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?config_code=' . $config_code . '" name="editConfClass" >' . "\n";
        echo '<input type="hidden" name="config_code" value="' . $config_code . '" >' . "\n";
        echo '<input type="hidden" name="cmd" value="save" >' . "\n";

        if (is_array($conf_def['section']) )
        {

            echo '<table class="claroTable"  border="0" cellpadding="5" width="100%">' . "\n";

            // display each section of properties
            foreach($conf_def['section'] as $section)
            {
                if (!(isset($section['display'])) || $section['display'] )
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
                }

                // The default value is show in input or preselected value if there is no value set.
                // If a value is already set the default value is show as sample.
                if ( is_array($section['properties']) )
                {
                    // display each property of the section
                    foreach ( $section['properties'] as $property_name )
                    {
                        if ( is_array($conf_def_property_list[$property_name]) )
                        {
                            // PHP 4.3 - $debugMsg['val'][]=var_export($conf_def_property_list[$property_name],1);

                            if ( isset($_REQUEST['prop'])  )
                            {
                                claroconf_disp_editbox_of_a_value($conf_def_property_list[$property_name], $property_name, $_REQUEST['prop'][$property_name]);
                            }
                            else
                            {
                                claroconf_disp_editbox_of_a_value($conf_def_property_list[$property_name], $property_name, $currentConfContent[$property_name]);
                            }
                        }
                        else
                        {
                            echo 'Def corrupted: property ' . $property_name . ' is not defined';
                        }
                    }
                }

            }
            echo '</table>' . "\n";

            echo '<input type="submit" value="Save" >' . "\n";
        }
        else
        {
            echo 'No section found in definition file';
        }
        echo '</form>'."\n";
    }
    else
    {
        $controlMsg['info'][] = sprintf($lang_p_nothing_to_edit_in_S ,get_config_name($config_code));
        echo claro_disp_message_box($controlMsg);
    }
}

// display footer
include($includePath . '/claro_init_footer.inc.php' );

?>
