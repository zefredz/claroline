<?php # $Id$
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

/*
// This tool is write to edit setting  of  claroline.
// In the old claroline, there was a central config file 
// in next release a conf repository was build  with conf files
// To not owerwrite on theF following release,  
//   was rename  from .conf.inc.php to .conf.inc.php.dist
// installer was eable to rename from .conf.inc.php.dist to .conf.inc.php

// the actual config file is build to merge new and active setting.
// The system as more change than previous evolution
// Tool are released with a conf definition file
// This file define for each property a name, a place but also
// some control for define accepted content.
// and finally some comment, explanation or info

// this version do not include 
// * trigered procedure (function called when a property 
//   is switch or set to a particular value)
// * renaming or deletion of properties from config
// * locking  of edit file (This tools can't really be
//   in the active part of the day in prod. )
//   I need to change that to let  admin sleep during the night

// To make transition, 
// * a section of tool continue to
//   edit the main configuration (benoit's script)
// * a section can parse old file to found old properties
//   and his values. 
//   This script would be continue to generate a def conf file.

// Commands

--- cmd==dispEditConfClass
Attempd an tool parameter
Read existing value set in db for this tool
Read the de file for this tool
Display the panel of generic edition (form build following def parameter)

--- isset(cmdSaveProperties
call by the DISP_EDIT_CONF_CLASS when user click on submit.
* check if value are right for control rules in def file
* store (insert/update) in properties in DB

--- cmd==generateConf
Attempd an tool parameter
Read existing value set in db for this tool
Write config file if all value needed are set

// Displays
 define("DISP_LIST_CONF",      __LINE__); Print out a lis of eable action.
 define("DISP_EDIT_CONF_CLASS",__LINE__);  Edit settings of a tool.
 define("DISP_GENERATE_CONF",  __LINE__);  Build conf file of a tool.
 define("DISP_SHOW_DEF_FILE",  __LINE__);  Display the definition file of a tool
 define("DISP_SHOW_CONF_FILE", __LINE__);  Display the Conf file of a tool

*/

///// CAUTION DEVS ////
///// This script use the PEAR package var_dump
// If you dont have pear, comment these lines 
// and replace Var_Dump::display by Var_Dump

define('CLARO_DEBUG_MODE',TRUE);
$langFile = "config";
$lang_config_config = 'Édition des fichiers de configuration';
$lang_nothingToConfigHere='Il n\'y a pas de paramétrage pour <B>%s</B>';
$langBackToMenu = 'Retour au Menu';
$langShowConf        = 'Show Conf';
$langShowDef         = 'Show Def';
$langNoPropertiesSet = 'No properties set';
$langShowContentFile = 'Voir le contenu du fichier';

define('DISP_LIST_CONF',        __LINE__);
define('DISP_EDIT_CONF_CLASS',  __LINE__);
define('DISP_GENERATE_CONF',    __LINE__);
define('DISP_SHOW_CONF',        __LINE__);
define('DISP_SHOW_DEF_FILE',    __LINE__);
define('DISP_SHOW_CONF_FILE',   __LINE__);

// include init and library files

require '../../inc/claro_init_global.inc.php';

include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/course.lib.inc.php');
include($includePath.'/lib/config.lib.inc.php');

// use Var_Dump PEAR package

require_once('Var_Dump.php');
Var_Dump::displayInit(array('display_mode' => 'HTML4_Text'));

// define

$nameTools 			= $lang_config_config;
$interbredcrump[]	= array ('url'=>$rootAdminWeb, 'name'=> $lang_config_AdministrationTools);
$noQUERY_STRING 	= TRUE;

$htmlHeadXtra[] = '<style>
label    {

}
fieldset    {
	background-color: #FFFFCF;
}
.firstDefine{
	color: #CC3333;

}
.toolDesc    {
	border: 1px solid Gray;
	background-color: #FFDAB9;
	margin-left: 5%;
	padding-left: 2%;
	padding-right: 2%;
}
.sectionDesc {
	border: 1px solid Gray;
	background-color: #00FA9A;
	margin-left: 5%;
	padding-left: 2%;
	padding-right: 2%;
}
.propDesc    {
	border: 1px solid Gray;
	background-color: #AFEEEE;
	margin-left: 5%;
	padding-left: 2%;
	padding-right: 2%;
}
</style>
';

/* ************************************************************************** */
/*  
/* ************************************************************************** */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_tool      = $tbl_mdb_names['tool'];
$tbl_config    = $tbl_mdb_names['config'];
$tbl_rel_tool_config    = $tbl_mdb_names['rel_tool_config'];

$toolNameList = array('CLANN' => $langAnnouncement,
                      'CLFRM' => $langForums,
                      'CLCAL' => $langAgenda,
                      'CLCHT' => $langChat,
                      'CLDOC' => $langDocument,
                      'CLDSC' => $langDescriptionCours,
                      'CLGRP' => $langGroups,
                      'CLLNP' => $langLearnPath,
                      'CLQWZ' => $langExercises,
                      'CLWRK' => $langWork,
                      'CLUSR' => $langUsers);

/* ************************************************************************** */
/*  SECURITY CHECKS
/* ************************************************************************** */

$is_allowedToAdmin 	= $is_platformAdmin;

if(!$is_allowedToAdmin)
{
	claro_disp_auth_form(); // display auth form and terminate script
} 

/* ************************************************************************** */
/*  REQUESTS
/* ************************************************************************** */

// Default display

$panel = DISP_LIST_CONF;

if ( isset($_REQUEST['config_code']) && isset($_REQUEST['cmd']) )
{
    $config_code = $_REQUEST['config_code'];
    $confDef  = claro_get_def_file($config_code);
    $confFile = claro_get_conf_file($config_code); 

    if( $_REQUEST['cmd']=='dispEditConfClass' )
    {
        // Edit settings of a config_code 
 
        if(file_exists($confDef))
        {
            $panel = DISP_EDIT_CONF_CLASS;
        }
        else
        {
    		$controlMsg['error'][]=sprintf($lang_nothingToConfigHere,get_config_name($config_code));
            $panel = DISP_LIST_CONF;
        }
    }
    elseif( $_REQUEST['cmd']=='showConf' )
    {
        // Show Configuration

        if(file_exists($confFile))
        {
            @require($confDef);
            @require($confFile);
            $interbredcrump[] = array ('url'=>$_SERVER['PHP_SELF'], 'name'=> $nameTools);
            $nameTools = get_config_name($config_code);
            $panel = DISP_SHOW_CONF;
        }
        else
        {
    		$controlMsg['error'][]=sprintf($lang_nothingToConfigHere,get_config_code_name($config_code));
            $panel = DISP_LIST_CONF;
        }
    }
    elseif( $_REQUEST['cmd']=='showDefFile' )
    {
        // Show Definition File
        
        if(file_exists($confDef))
        {
            $interbredcrump[] = array ('url'=>$_SERVER['PHP_SELF'], 'name'=> $nameTools);
            $nameTools = get_config_name($config_code);
            $panel = DISP_SHOW_DEF_FILE;
        }
        else
        {
    		$controlMsg['error'][]=sprintf($lang_nothingToConfigHere,get_config_name($config_code));
            $panel = DISP_LIST_CONF;
        }
    }
    elseif($_REQUEST['cmd']=='showConfFile')
    {
        // Show configuration file

        if(file_exists($confFile))
        {
            $interbredcrump[] = array ('url'=>$_SERVER['PHP_SELF'], 'name'=> $nameTools);
            $nameTools = get_config_name($config_code);
            $panel = DISP_SHOW_CONF_FILE;
        }
        else
        {
    		$controlMsg['error'][]=sprintf($lang_nothingToConfigHere,get_config_name($config_code));
            $panel = DISP_LIST_CONF;
        }
    }
    elseif(isset($_REQUEST['cmdSaveProperties']))
    {
        if(file_exists($confDef))
        {
            require($confDef);
            $interbredcrump[] = array ('url'=>$_SERVER['PHP_SELF'], 'name'=> $nameTools);
            $nameTools = get_config_name($config_code);
        }

        //  var_dump::display($_REQUEST['prop']);
        $okToSave = TRUE;
        if ($config_code != $toolConf['config_code'])
        {
            $okToSave = TRUE;
            $controlMsg['error'][] = $toolConf['config_code'].' != '.$config_code.' <br>
            The definition file for configuration is probably copypaste from '.basename($confDef);
    
        }
        if (is_array($_REQUEST['prop']) )
        {
            foreach($_REQUEST['prop'] as $propName => $propValue )
            {
                $validator     = $toolConfProperties[$propName]['type'];
                $acceptedValue = $toolConfProperties[$propName]['acceptedValue'];
                $container     = $toolConfProperties[$propName]['container'];
                //      config_checkToolProperty($propValue, $toolConfProperties[$propName]);
                //      $controlMsg['log'][] = $propName.' '.$validator.' '.var_export($acceptedValue,1);
                switch($validator)
                {
                    case 'boolean' : 
                        if (!($propValue=='TRUE'||$propValue=='FALSE') )
                        {
                            $controlMsg['error'][] = $propName.' would be boolean';
                            $okToSave = FALSE;
                        }   
                        break;
                    case 'integer' : 
                        $propValue = (int) $propValue;
                        if (!is_integer($propValue)) 
                        {
                            $controlMsg['error'][] = $propName.' would be integer';
                            $okToSave = FALSE;
                        }
                        elseif (isset($acceptedValue['max'])&& $acceptedValue['max']<$propValue)
                        {
                            $controlMsg['error'][] = $propName.' would be integer inferior or equal to '.$acceptedValue['max'];
                            $okToSave = FALSE;
                        }   
                        elseif (isset($acceptedValue['min'])&& $acceptedValue['min']>$propValue)
                        {
                            $controlMsg['error'][] = $propName.' would be integer superior or equal to '.$acceptedValue['min'];
                            $okToSave = FALSE;
                        }   
                        break;
                    case 'enum' : 
                        if (!in_array($propValue,array_keys($acceptedValue))) 
                        {
                            $controlMsg['error'][] = $propName.' would be in enum list';
                            $okToSave = FALSE;
                        }   
                        break;
                    case 'relpath' :
                    case 'syspath' :
                    case 'wwwpath' :
                        if (empty($propValue))
                        {
                            $controlMsg['error'][] = $propName.' is empty';
                            $okToSave = FALSE;
                        }   
                        break;
                    case 'regexp' :
                        if (!eregi( $acceptedValue, $propValue )) 
                        {
                            $controlMsg['error'][] = $propName.' would be valid';
                            $controlMsg['error'][] = $acceptedValue.' '.$propValue;
                            $okToSave = FALSE;
                        }   
                        break;
                    default :
                    
                }
    
                if ($okToSave) 
                {
                    $sqlParamExist = 'SELECT count(id_property) nbline
                                      FROM `'.$tbl_config.'` 
                                      WHERE propName    ="'.$propName.'" 
                                        AND config_code ="'.$config_code.'"';
    
                    $exist = claro_sql_query_fetch_all($sqlParamExist);
    
                    if ($exist[0]['nbline']==0) 
                    {
                        $sql ='INSERT 
                               INTO `'.$tbl_config.'` 
                               SET propName    = "'.$propName.'", 
                                   propValue   = "'.$propValue.'", 
                                   lastChange  = now(), 
                                   config_code = "'.$config_code.'"';
                    }
                    else
                    {
                        $sql ='UPDATE 
                                `'.$tbl_config.'` 
                               SET propName    ="'.$propName.'", 
                                   propValue   ="'.$propValue.'", 
                                   lastChange  = now(), 
                                   config_code ="'.$tool.'"
                               WHERE propName    ="'.$propName.'" 
                                 AND config_code ="'.$config_code.'"
                                 AND not (propValue   ="'.$propValue.'") # do not update if same value 
                                 ';
                    }
    //              $controlMsg['info'][] = Var_dump::display($sql,1);
                    
                    claro_sql_query($sql);                 
                }
                else
                {
                    $controlMsg['info'][] = 'Save aborded';
                    $panel = DISP_EDIT_CONF_CLASS;
                }
            }
        }
                else
        {
            $okToSave = FALSE;
        }            
        $controlMsg['info'][] = 'Properties saved in DB. Generate file to apply on your platform';        
    }
    elseif($_REQUEST['cmd']=='generateConf')
    {
        if(file_exists($confDef))
        {
            require($confDef);
            $panel = DISP_GENERATE_CONF;
            $interbredcrump[] = array ("url"=>$_SERVER['PHP_SELF'], "name"=> $nameTools);
            $nameTools = get_config_name($config_code);
        }
        else
        {
    		$controlMsg['error'][]=sprintf($lang_nothingToConfigHere,get_tool_name($tool));
            $panel = DISP_LIST_CONF;    
        }
            
        if (!$confFile)
        {
            $confFile = claro_create_conf_filename($config_code);
            $controlMsg['info'][] = sprintf('création du fichier de configuration :<BR> %s'
                                           ,$confFile);
            $confFile = claro_get_conf_file($config_code);
        }
        
        if(file_exists($confDef))
        {
            require($confDef);
            $panel = DISP_EDIT_CONF_CLASS;
            $interbredcrump[] = array ("url"=>$_SERVER['PHP_SELF'], "name"=> $nameTools);
            $nameTools = get_config_name($config_code);
        }
        
        $storedPropertyList = readValueFromTblConf($config_code);
        
        $generatorFile = realpath(__FILE__);
        if (strlen($generatorFile)>50) 
        {
            $generatorFile = str_replace("\\","/",$generatorFile);
            $generatorFile = "\n\t\t".str_replace("/","\n\t\t/",$generatorFile);
        }
        $fileHeader = '<?php '."\n"
                    . '/* '
                    . 'DONT EDIT THIS FILE - NE MODIFIEZ PAS CE FICHIER '."\n"      
                    . '-------------------------------------------------'."\n"      
                    . 'Generated by '.$generatorFile.' '."\n"      
                    . 'User UID:'.$_uid.' '.str_replace("array (","",str_replace("'","",str_replace('=>',"\t",var_export($_user,1))))."\n"      
                    . 'Date '.claro_disp_localised_date($dateTimeFormatLong)."\n"      
                    . '-------------------------------------------------'."\n"      
                    . 'DONT EDIT THIS FILE - NE MODIFIEZ PAS CE FICHIER '."\n"      
                    . ' */'."\n\n"
                    . '$'.$config_code.'GenDate = "'.time().'";'."\n\n"
                    . (isset($toolConf['technicalInfo'])
                    ? '/*'
                    . str_replace('*/', '* /', $toolConf['technicalInfo'])
                    . '*/'
                    : '')
                    ;
    
        $handleFileConf = fopen($confFile,'w');
        fwrite($handleFileConf,$fileHeader);
        
        
        foreach($storedPropertyList as $storedProperty)
        {
            $valueToWrite  = $storedProperty['propValue']; 
            $container     = $toolConfProperties[$storedProperty['propName']]['container'];
            $description   = $toolConfProperties[$storedProperty['propName']]['$description'];
            if ($toolConfProperties[$storedProperty['propName']]['type']!='boolean') 
            {
                $valueToWrite = "'".$valueToWrite."'";   
            }
            if(strtoupper($container)=='CONST')
                $propertyLine = 'define("'.$storedProperty['propName'].'",'.$valueToWrite.');'."\n";
            else
                $propertyLine = '$'.$storedProperty['propName'].' = '.$valueToWrite.';'."\n";
            $propertyDesc = (isset($description)?'/* '.$storedProperty['propName'].' : '.str_replace("\n","",$description).' */'."\n":
            (isset($toolConfProperties[$storedProperty['propName']]['label'])?'/* '.$storedProperty['propName'].' : '.str_replace("\n","",$toolConfProperties[$storedProperty['propName']]['label']).' */'."\n":''));
            $propertyDesc .= ( isset($toolConfProperties[$storedProperty['propName']]['technicalInfo'])
                    ? '/*'."\n"
                    . str_replace('*/', '* /', $toolConfProperties[$storedProperty['propName']]['technicalInfo'])
                    . '*/'."\n"
                    : '' )
                    ;
    
            $propertyGenComment = '// Update on '
                                 .claro_disp_localised_date($dateTimeFormatLong,$storedProperty['lastChange'])
                                 ."\n"."\n"
                                 ;
    
            fwrite($handleFileConf,$propertyLine);
            fwrite($handleFileConf,$propertyDesc);
            fwrite($handleFileConf,$propertyGenComment);
    
        }
        fwrite($handleFileConf,"\n".'?>');
        fclose($handleFileConf);
        $controlMsg['info'][] = 'Properties for '.$nameTools.' ('.$config_code.') are now effective on server.<br />file generated is <em>'.$confFile.'</em>';        
        $panel = DISP_LIST_CONF;
    
    }

}

/* ************************************************************************** */
//    PREPARE VIEW   
/* ************************************************************************** */

if ($panel == DISP_LIST_CONF)
{
    $helpSection = 'help_config_menu.php';
    $toolList = get_def_list();
}
elseif ($panel == DISP_EDIT_CONF_CLASS)
{
    require($confDef);
    $interbredcrump[] = array ("url"=>$_SERVER['PHP_SELF'], "name"=> $lang_config_config);
    $nameTools = get_config_name($config_code);
    
    $storedPropertyList = readValueFromTblConf($config_code);
    if (is_array($storedPropertyList))
    {
        foreach($storedPropertyList as $storedProperty)
        {
            $toolConfProperties[$storedProperty['propName']]['actualValue'] = $storedProperty['propValue']; 
        }
    }
}

/* ************************************************************************** */
// OUTPUT VIEW   
/* ************************************************************************** */

// display claroline header

include($includePath."/claro_init_header.inc.php");

// display tool title

claro_disp_tool_title(array('mainTitle'=>$nameTools),(isset($helpSection)?$helpSection:false));

// display control message

if (!empty($controlMsg))
{
    claro_disp_msg_arr($controlMsg);
}

// OUTPUT

switch ($panel)
{
    case DISP_LIST_CONF : 
    ?>
    
<table class="claroTable" cellspacing="4" >
    <thead>
    <tr class="headerX" >
        <th>Fichier</th>
        <th>Éditer</th>
        <th>Appliquer</th>
    </tr>
    </thead>
<?php
        foreach($toolList as $config_code => $tool)
        {
            echo '<tr>'
                .'<td>'
                .($tool['conf']
                    ?'<a href="'.$_SERVER['PHP_SELF'].'?cmd=showConf&amp;config_code='.$config_code.'" >'.$tool['name'].'</a>'
                    : $tool['name']
                 )
                .'</td>'
                ;
            
            if (!$tool['def'])
            {
                echo '<td colspan="2" >'
                    .'<strike>éditer</strike>'
                    .'</td>'
                    ;
            }
            else 
            {
                echo '<td>'
                    .'<a href="'.$_SERVER['PHP_SELF']
                    .'?cmd=dispEditConfClass&amp;config_code='.$config_code.'" >'
                    .'<img src="'.$clarolineRepositoryWeb.'img/edit.gif" border="0" alt="'.$langEdit.'">'
                    .'</a>'
                    .'</td>'
                    .'<td>'
                    . ( $tool['propQtyInDb']['qty_values']>0
                      ? '<a href="'.$_SERVER['PHP_SELF']
                      .'?cmd=generateConf&amp;config_code='.$config_code.'" >'
                      .'<img src="'.$clarolineRepositoryWeb.'img/download.gif" border="0" alt="'.$langSave.'">'
                     . ($tool['propQtyInDb']['qty_new_values']>0
                       ? '<br>(<small>'.$tool['propQtyInDb']['qty_new_values'].' new values</small>)'
                       : 'Up to date'
                       )
                       .'</a>'
                     : '<small>'
                       .$langNoPropertiesSet
                       .'</small>'
                     )
                   .'</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        break;
    case DISP_EDIT_CONF_CLASS : 

        if (is_array($toolConf))
        {
            echo (isset($toolConf['description'])?'<div class="toolDesc">'.$toolConf['description'].'</div><br />':'')                
                .'<em><small><small>'.$confDef.'</small></small></em>'
                .'<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="editConfClass">'."\n"
                //.'<input type="hidden" value="dispEditConfClass" name="cmd">'."\n"
                .'<input type="hidden" value="'.$config_code.'" name="config_code">'."\n"
                ;
            if (is_array($toolConf['section']) ) 
            {
                foreach($toolConf['section'] as $section)
                {
                    echo '<fieldset>'."\n"
                        .'<legend>'.$section['label'].'</legend>'."\n"
                        .($section['description']
                         ?'<div class="sectionDesc">'.$section['description'].'</div><br/>'
                         :'')
                        ."\n"
                        ;
//                  The default value is show in input or preselected value if there is no value set.
//                  If a value is already set the default value is show as sample.
                    if (is_array($section['properties']))
                    foreach($section['properties'] as $property )
                    {
//                      var_dump::display($property);
                        $htmlPropLabel = htmlentities($toolConfProperties[$property]['label']);
                        $htmlPropDesc = ($toolConfProperties[$property]['description']?'<div class="propDesc">'.nl2br(htmlentities($toolConfProperties[$property]['description'])).'</div><br />':'');
                        $htmlPropName = 'prop['.($property).']';
                        $htmlPropValue = isset($toolConfProperties[$property]['actualValue'])?$toolConfProperties[$property]['actualValue']:$toolConfProperties[$property]['default'];
                        $size = (int) strlen($htmlPropValue);
                        $size = 2+(($size > 90)?90:(($size < 8)?8:$size));
                        $htmlPropDefault = isset($toolConfProperties[$property]['actualValue'])?'<span class="default"> Default : '.$toolConfProperties[$property]['default'].'</span><br />':'<span class="firstDefine">!!!First definition of this value!!!</span><br />';
                        if ($toolConfProperties[$property]['readonly']) 
                        {
                            echo '<H2>'
                                .$htmlPropLabel
                                .'</H2>'."\n"
                                .$htmlPropDesc."\n"
                                .$htmlPropValue.'<br>'."\n"
                                ;
                        } 
                        else
                        // Prupose a form following the type 
                        switch($toolConfProperties[$property]['type'])
                        {
                       	    case 'boolean' : 
                                $htmlPropDefault = isset($toolConfProperties[$property]['actualValue'])
                                                   ?'<span class="default"> Default : '
                                                   .($toolConfProperties[$property]['acceptedValue'][$toolConfProperties[$property]['default']]?$toolConfProperties[$property]['acceptedValue'][$toolConfProperties[$property]['default']]:$toolConfProperties[$property]['default'] )
                                                   .'</span><br />'
                                                   :'<span class="firstDefine">!!!First definition of this value!!!</span><br />'
                                                   ;
                                echo '<H2>'
                                    .$htmlPropLabel
                                    .'</H2>'."\n"
                                    .$htmlPropDesc."\n"
                                    .$htmlPropDefault."\n"
                                    .'<span>'
                                    .'<input id="'.$property.'_TRUE"  type="radio" name="'.$htmlPropName.'" value="TRUE"  '.($htmlPropValue=='TRUE'?' checked="checked" ':' ').' >'
                                    .'<label for="'.$property.'_TRUE"  >'
                                    .($toolConfProperties[$property]['acceptedValue']['TRUE' ]?$toolConfProperties[$property]['acceptedValue']['TRUE' ]:'TRUE' )
                                    .'</label>'
                                    .'</span>'."\n"
                                    .'<span>'
                                    .'<input id="'.$property.'_FALSE" type="radio" name="'.$htmlPropName.'" value="FALSE" '.($htmlPropValue=='TRUE'?' ':' checked="checked" ').' ><label for="'.$property.'_FALSE" >'.($toolConfProperties[$property]['acceptedValue']['FALSE']?$toolConfProperties[$property]['acceptedValue']['FALSE']:'FALSE').'</label></span>'."\n"
                                    ;
                        		break;
                       	    case 'enum' : 
                                $htmlPropDefault = isset($toolConfProperties[$property]['actualValue'])
                                                   ?'<span class="default"> Default : '
                                                   .($toolConfProperties[$property]['acceptedValue'][$toolConfProperties[$property]['default']]?$toolConfProperties[$property]['acceptedValue'][$toolConfProperties[$property]['default']]:$toolConfProperties[$property]['default'] )
                                                   .'</span><br />'
                                                   :'<span class="firstDefine">!!!First definition of this value!!!</span><br />'
                                                   ;
                                echo '<H2>'
                                    .$htmlPropLabel
                                    .'</H2>'."\n"
                                    .$htmlPropDesc."\n"
                                    .$htmlPropDefault."\n";
                                foreach($toolConfProperties[$property]['acceptedValue'] as  $keyVal => $labelVal)
                                {
                                    echo '<span>'
                                        .'<input id="'.$property.'_'.$keyVal.'"  type="radio" name="'.$htmlPropName.'" value="'.$keyVal.'"  '.($htmlPropValue==$keyVal?' checked="checked" ':' ').' >'
                                        .'<label for="'.$property.'_'.$keyVal.'"  >'.($labelVal?$labelVal:$keyVal ).'</label>'
                                        .'</span>'
                                        .'<br>'."\n";
                                }   
                        		break;
                        		
//TYPE : integer, an integer is attempt
                        	case 'integer' : 
                                $htmlPropDefault = isset($toolConfProperties[$property]['actualValue'])?'<span class="default"> Default : '.$toolConfProperties[$property]['default'].'</span><br />':'<span class="firstDefine">!!!First definition of this value!!!</span><br />';
                                echo '<H2>'
                                    .'<label for="'.$property.'">'
                                    .$toolConfProperties[$property]['label']
                                    .'</label>'
                                    .'</H2>'."\n"
                                    .'<br>'."\n"
                                    .$htmlPropDesc."\n"
                                    .$htmlPropDefault."\n"
                                    .'<input size="'.$size.'"  align="right" id="'.$property.'" type="text" name="'.$htmlPropName.'" value="'.$htmlPropValue.'"> '.$toolConfProperties[$property]['type']."\n"
                                    .'<br>'
                                    ;
                        		;
                        		break;
                        	default:
                        	// probably a string
                                $htmlPropDefault = isset($toolConfProperties[$property]['actualValue'])?'<span class="default"> Default : '.$toolConfProperties[$property]['default'].'</span><br />':'<span class="firstDefine">!!!First definition of this value!!!</span><br />';
                                echo '<h2>'."\n"
                                    .'<label for="'.$property.'">'
                                    .$toolConfProperties[$property]['label']
                                    .'</label>'."\n"
                                    .'</h2>'."\n"
                                    .$htmlPropDesc."\n"
                                    .$htmlPropDefault."\n"
                                    .'<input size="'.$size.'"  id="'.$property.'" type="text" name="'.$htmlPropName.'" value="'.$htmlPropValue.'"> '.$toolConfProperties[$property]['type']."\n"
                                    .'<br>'."\n"
                                    ;
                        		;
                        } // switch
                    } 
                echo '</fieldset>';
                }
                echo '<input type="hidden" name="cmd" value="cmdSaveProperties" >';
                echo '<input type="submit" name="cmdSaveProperties" value="Save" >'
                    .'</form>'."\n"
                    ;
                   
            }
            else
            {
                echo 'no section found in definition file';                                
            }
    }
    else
    {
        echo '<div >nothing to edit in '.$tool. '</div>';
    }

        break;

    case DISP_SHOW_CONF : 
        echo '<div>'
            .'[<a href="'.$_SERVER['PHP_SELF'].'?cmd=showConfFile&amp;config_code='.$config_code.'" >'.$langShowContentFile.'</a>]'
            .'[<a href="'.$_SERVER['PHP_SELF'].'" >'
            .$langBackToMenu
            .'</a>]</div>'
            ;
        if (is_array($toolConf))
        {
            echo (isset($toolConf['description'])?'<div class="toolDesc">'.$toolConf['description'].'</div><br />':'')                
                .'<em><small><small>'.$confDef.'</small></small></em>'
                ;
            if (is_array($toolConf['section']) ) 
            {
                foreach($toolConf['section'] as $section)
                {
                    echo '<FIELDSET>'
                        .'<LEGEND>'.$section['label'].'</LEGEND>'."\n"
                        .($section['description']?'<div class="sectionDesc">'.$section['description'].'</div><br />':'')."\n"
                        ;
                    if (is_array($section['properties']))
                    foreach($section['properties'] as $property )
                    {
                        $htmlPropLabel = htmlentities($toolConfProperties[$property]['label']);
                        $htmlPropDesc = ($toolConfProperties[$property]['description']?'<div class="propDesc">'.nl2br(htmlentities($toolConfProperties[$property]['description'])).'</div><br />':'');
                        if ($toolConfProperties[$property]['container']=='CONST')
                             eval('$htmlPropValue = '.$property.';');
                        else eval('$htmlPropValue = $'.$property.';');
                        $htmlUnit = ($toolConfProperties[$property]['unit']?''.htmlentities($toolConfProperties[$property]['unit']):'');
                        echo '<H2>'
                            .$htmlPropLabel 
                            .'('.$toolConfProperties[$property]['type'].')'
                            .'</H2>'."\n"
                            .$htmlPropDesc."\n"
                            .'<em>'.$property.'</em>: '
                            .'<strong>'.var_export($htmlPropValue,1).'</strong> '.$htmlUnit.'<br>'."\n"
                            ;
                    } // foreach($section['properties'] as $property )
                    echo '</FIELDSET><br>'."\n";
                } //foreach($toolConf['section'] as $section)
            }
            else
            {
                echo 'no section found in definition file';                                
            }
    }
        break;

    case DISP_SHOW_CONF_FILE : 
        echo '<div class="links">'
            .'<a href="'.$_SERVER['PHP_SELF'].'" >'
            .$langBackToMenu
            .'</a>'
            .'</div>'
            .'<br />'."\n"
            ;
        highlight_file($confFile);

        break;

    case DISP_SHOW_DEF_FILE : 
        echo '<div class="links">'
            .'<a href="'.$_SERVER['PHP_SELF'].'" >'
            .$langBackToMenu
            .'</a>'
            .'</div>'
            .'<br />'."\n"
            ;
        highlight_file($confDef);
        break;

    default : echo 'error : panel not defined';

}

var_dump::display($_REQUEST);
var_dump::display($toolConf);
var_dump::display($toolConfProperties);
include($includePath."/claro_init_footer.inc.php");
?>