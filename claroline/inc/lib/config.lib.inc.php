<?php # $Id$ 
/**
 * config lib contain function to manage conf file
 */

/**
 * function claro_undist_file ($file)
 * @return wether the success
 * @param $file string path to file
 * @desc find config file if not exist get the '.dist' file and rename it
 * @author Benoit
 */
function claro_undist_file ($file) 
{
	if ( !file_exists($file)) 
	{
		if ( file_exists($file.".dist"))
		{
			$perms = fileperms($file.".dist");
			$group = filegroup($file.".dist");
			// $perms|bindec(110000) <- preserve perms but force rw right on group
			@copy($file.".dist",$file) && chmod ($file,$perms|bindec(110000)) && chgrp($file,$group);
			if (file_exists($file))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

/**
 * function trueFalse($booleanState)
 * @return the boolean value as string
 * @param $booleanState boolean
 * @author Moosh
 */
function trueFalse($booleanState)
{
	return ($booleanState?'TRUE':'FALSE');
}

/**
 * function replace_var_value_in_conf_file ($varName,$value,$file)
 * @return wether the success
 * @param $varName name of the variable
 * @param $value new value of the variable
 * @param $file string path to file
 * @desc replace value of variable in file
 * @author Benoit
*/

function replace_var_value_in_conf_file ($varName,$value,$file)
{

 $replace = false;

 // Quote regular expression characters of varName 
 
 if ($varName != "")
 {
 	// build regexp 
 	$regVarName = preg_quote($varName);
 	$regExp = '~(\$(' . $regVarName . '))[[:space:]]*=[[:space:]]*(.*);~U';
 }
 else
 {
 	return false;
 }

 if(file_exists($file))
 {
   //Open config file 
	if($fp = @fopen($file,"r"))
	{
		// take all lines in the file
		while(!feof($fp))
		{
			// length param in fgets is required before PHP 4.2.0
			$line=fgets($fp,1024);
			trim($line);
			
			unset($find);
			$find = preg_match_all($regExp,$line,$result);

			if($find)
			{
				// $result[0] the variable and the value
				// $result[1] the name of the variable
				// $result[2] the value
								
				// replace the variable with the new value
				$line = str_replace($result[3]," \"".$value."\"",$line);
				$replace = true;
			}
			//Create a table with correct ligne to create de new file config
			$newLines[]= $line;
		}
		fclose($fp);
	}
	else
	{
		// can't open file in read 
		return false;
	}
 }
 else 
 {
  // file doesn't exists 
  return false;
 }

 if ($replace)
 {
 
	// rewrite file
	if($nf=@fopen($file,"w+"))
	{
		if(isset($newLines))
		{
			foreach($newLines as $line)
			{
				fwrite($nf,$line);
			}
		}
		fclose($nf);
 	}
 	else
 	{
		// can't open file in write 
		return false;
 	}
 }

 return true;
 
}


/// these functions are  use to manage free strings.
// function cleanvalue($string) this function remove tags, ; , top and terminal blank
// this function is called by two others

// function cleanoutputvalue($string) protect html entities before an output (in html page)
// function cleanwritevalue($string) protect befor write it in a file between " ";


function cleanvalue($string)
{ 
	return trim(str_replace(';',':',strip_tags(stripslashes($string))));
}

function cleanoutputvalue($string)
{ 
	return trim(htmlspecialchars(cleanvalue($string)));
}

function cleanwritevalue($string)
{ 
	return trim(str_replace('"','\"',cleanvalue($string)));
}


// - - - - - - - - - - - -- - 

/**
 * read_param_value_in_buffer()
 * 
 * @author moosh moosh@claroline.net
 * @param $config_code id of def file correcponding to data to found
 * @return an array containning name and value of properties.
 **/
function read_param_value_in_buffer($config_code)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_config_property      = $tbl_mdb_names['config_property'];
    $sqlGetPropertyValues = 'SELECT `propName`, `propValue`, unix_timestamp(`lastChange`) `lastChange`
                             FROM `'.$tbl_config_property.'` 
                             WHERE config_code = "'.$config_code.'"';
    $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    return $valueFromTblConf;
};

/**
 * function getToolList()
 * 
 * @author moosh moosh@claroline.net
 * @param none
 * @return an array containing name and value of properties.
 **/
function getToolList()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_tool      = $tbl_mdb_names['tool'];
    $sqlGetToolList = 'select claro_label from `'.$tbl_tool.'`';
    $toolList = claro_sql_query_fetch_all($sqlGetToolList);
    $t=array();
    foreach($toolList as $tool)
    {
        $claro_label = str_replace( '_', '',$tool['claro_label']);
        $t[] = array( 'value' => $claro_label
                     ,'name'  => get_tool_name($claro_label)
                    );
    }
    return $t;
}

/**
 * function countPropertyInDb()
 * 
 * @author moosh moosh@claroline.net
 * @param $config_code string id of config to count properties
 * @return interger qty of property stored in db for this config
 **/
function countPropertyInDb($config_code)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_config_property  = $tbl_mdb_names['config_property'];
    $confFile = claro_get_conf_file($config_code);
    if(file_exists($confFile))
    {
        include($confFile);
        $genDateVarName = $config_code.'GenDate';
        $sqlGetPropertyValues = 'SELECT count(if((unix_timestamp(`lastChange`) > "'.$$genDateVarName.'"),1,null)) qty_new_values, 
                                        count(id_property) qty_values
                                 FROM `'.$tbl_config_property.'` 
                                 WHERE `config_code` = "'.$config_code.'" 
                                 ';
        $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    }
    else
    {
        $sqlGetPropertyValues = 'SELECT 0 qty_new_values, 
                                        count(id_property) qty_values
                                 FROM `'.$tbl_config_property.'` 
                                 WHERE `config_code` = "'.$config_code.'" 
                                 ';
    }
    $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    return $valueFromTblConf[0];
}

function lastConfUpdate($config_code)
{
    global $includePath;
    $confFile = realpath($includePath).claro_get_conf_file($config_code);
    if(file_exists($confFile))
    {
        include ($confFile);
        $genDateVarName = $config_code.'GenDate';
        $tbl_mdb_names   = claro_sql_get_main_tbl();
        $tbl_config_property      = $tbl_mdb_names['config_property'];
        $sqlGetPropertyValues = 'SELECT unix_timestamp(`lastChange`) `lastChange`
                                 FROM `'.$tbl_config_property.'` 
                                 WHERE `config_code` = "'.$config_code.'" 
                                   AND  unix_timestamp(`lastChange`) > "'.$$genDateVarName.'"';
        $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    }

    return $valueFromTblConf[0]['lastChange'];
}

/** config_checkToolProperty($propValue, $propertyDef)
 *
 * @param    $propValue mixed value to check with condition of definition bloc
 * @param    $propertyDef array containing rules to validate a propertyValue.
 * @return   boolean State of validity 
 * @author   moosh
 * @internal $is_validValue boolean flag to record stat of validity
 * @version  claroline 1.6
 * @desc     check the validity of a config value.
 */
function config_checkToolProperty($propValue, $propertyDef)
{
    global $controlMsg; 
    $acceptedValue = $propertyDef['acceptedValue'];
    $propName      = $propertyDef['label'];
    $validator     = $propertyDef['type'];
    $is_validValue = TRUE;
    if(is_array($propertyDef))
    {
        switch($validator)
        {
            case 'boolean' : 
                if (!($propValue=='TRUE'||$propValue=='FALSE') )
                {
                    $controlMsg['error'][] = $propName.' would be boolean';
                    $is_validValue = FALSE;
                }   
                break;
            case 'integer' : 
                // $propValue = (int) $propValue;
                if (eregi("[^0-9]",$propValue))
                {
                    $controlMsg['error'][] = $propName.' would be integer';
                    $is_validValue = FALSE;
                }
                elseif (isset($acceptedValue['max'])&& $acceptedValue['max']<$propValue)
                {
                    $controlMsg['error'][] = $propName.' would be integer inferior or equal to '.$acceptedValue['max'];
                    $is_validValue = FALSE;
                }   
                elseif (isset($acceptedValue['min'])&& $acceptedValue['min']>$propValue)
                {
                    $controlMsg['error'][] = $propName.' would be integer superior or equal to '.$acceptedValue['min'];
                    $is_validValue = FALSE;
                }   

                break;
            case 'enum' : 
                if (!in_array($propValue,array_keys($acceptedValue))) 
                {
                    $controlMsg['error'][] = $propName.' would be in enum list';
                    $is_validValue = FALSE;
                }   
                break;
            case 'relpath' :
            case 'syspath' :
            case 'wwwpath' :
                if (empty($propValue))
                {
                    $controlMsg['error'][] = $propName.' is empty';
                    $is_validValue = FALSE;
                }   
                break;
            case 'regexp' :
                if (isset($acceptedValue) && !eregi( $acceptedValue, $propValue )) 
                {
                    $controlMsg['error'][] = $propName.' would be valid for '.$acceptedValue;
                    $is_validValue = FALSE;
                }   
                break;
            case 'string' :
            default :
        }
    }
    else
    {
        trigger_error('propertyDef is not an array, coding error',E_USER_ERROR);
        return false;
    }
    //$controlMsg['debug'][] = 'check : '.$propName.' : '.$propValue.' is '.$validator.' : '.var_export($is_validValue,1);
    return $is_validValue;
}

function claro_get_conf_file($config_code)
{
   global $includePath;
   unset($conf_def);
   $confDefFile = claro_get_def_file(config_code);
   if(file_exists($confDefFile)) include $confDefFile;
   
   if (isset($conf_def['config_file']))
   {
       $confFile = realpath($includePath.'/conf/').'/'.$conf_def['config_file'];
   }
   else
   // ici il faut voir si cela a du sens
   // cela veut dire que le fichier de déf ne défini pas le fichier de config.
   // ca ne pose pas de problème à priori puisque 
   // 1 config_code = 1 def_file
   // 1 def_file    = 1 conf_file
   {
       $confFile = realpath($includePath.'/conf/').'/'.$config_code.'.conf.inc.php';
   }
   return $confFile;
}

function claro_create_conf_filename($config_code)
{
   return touch(claro_get_conf_file($config_code));
}

function get_tool_name($claro_label)
{
    GLOBAL $toolNameList;
    return (isset($toolNameList[$claro_label])?$toolNameList[$claro_label]:$claro_label);
}

function get_config_name($config_code)
{
    unset($conf_def);
    @include(claro_get_def_file($config_code));
    return (isset($conf_def['config_name'])
            ? $conf_def['config_name']
            : ( isset($conf_def['config_file'])
              ? $conf_def['config_file']
              : $config_code));
}


function get_conf_info($config_code)
{
    $tbl_mdb_names       = claro_sql_get_main_tbl();
    $tbl_tool            = $tbl_mdb_names['tool'];
    $tbl_config_file     = $tbl_mdb_names['config_file'];
    $tbl_rel_tool_config = $tbl_mdb_names['rel_tool_config'];

    $sql_get_conf_info = 'SELECT `cfg`.`config_code` `config_code`, 
                                 `cfg`.`config_hash` `config_hash`,  
                                 `r_t_cfg`.*, 
                                 `r_t_cfg`.`claro_label` `claro_label`, 
                                 `t`.`icon` `icon`
                                 
                          FROM `'.$tbl_config_file.'` `cfg`
                          LEFT JOIN `'.$tbl_rel_tool_config.'` `r_t_cfg`

                           ON `cfg`.`config_code` = `r_t_cfg`.`config_code` 
                          LEFT JOIN `'.$tbl_tool.'` `t`
                           ON `t`.`claro_label`  = `r_t_cfg`.`claro_label`
                           
                           WHERE `cfg`.config_code = "'.$config_code.'"';    
    
    $conf_info = claro_sql_query_fetch_all($sql_get_conf_info);
    $conf_info[0]['manual_edit'] = (bool) (file_exists(claro_get_conf_file($config_code))&&$conf_info[0]['config_hash'] != md5_file(claro_get_conf_file($config_code)));
    return $conf_info[0];
}

function claro_get_def_file($config_code)
{
    global $includePath;

    $confDefFilename = realpath($includePath.'/conf/def/'.$config_code.'.def.conf.inc.php');
    return $confDefFilename;
}

function get_def_list()
{
    global $includePath ;
    if ($handle = opendir($includePath.'/conf/def')) 
    {
       $defConfFileList = array();
       while (FALSE !== ($file = readdir($handle))) 
       {
           if ($file != "." && $file != ".." && substr($file, -17)=='.def.conf.inc.php')
           {
                $config_code = str_replace('.def.conf.inc.php','',$file);
                $defConfFileList[$config_code] = 
                 array( 'def'         => file_exists(claro_get_def_file($config_code))
                      , 'conf'        => file_exists(claro_get_conf_file($config_code))
                      , 'config_code' => $config_code
                      , 'name'        => get_config_name($config_code)
                      , 'propQtyInDb' => countPropertyInDb($config_code)
                      );                       
           }
       }
       closedir($handle);
    }
    return $defConfFileList;
}

function get_conf_list()
{
    $tbl_mdb_names       = claro_sql_get_main_tbl();
    $tbl_tool            = $tbl_mdb_names['tool'];
    $tbl_config_file     = $tbl_mdb_names['config_file'];
    $tbl_rel_tool_config = $tbl_mdb_names['rel_tool_config'];

    $sql_get_conf_list = 'SELECT `cfg`.`config_code` `config_code`, 
                                 `cfg`.`config_hash` `config_hash`,  
                                 `r_t_cfg`.*, 
                                 `r_t_cfg`.`claro_label` `claro_label`, 
                                 `t`.`icon` `icon`
                                 
                          FROM `'.$tbl_config_file.'` `cfg`
                          LEFT JOIN `'.$tbl_rel_tool_config.'` `r_t_cfg`

                           ON `cfg`.`config_code` = `r_t_cfg`.`config_code` 
                          LEFT JOIN `'.$tbl_tool.'` `t`
                           ON `t`.`claro_label`  = `r_t_cfg`.`claro_label`';    

    
    $result_conf_list = claro_sql_query_fetch_all($sql_get_conf_list);
    if (is_array($result_conf_list))
    foreach ($result_conf_list as $config)
        $conf_list[$config['config_code']] = $config;
    return  $conf_list;
}

function write_conf_file($conf_def,$conf_def_property_list,$storedPropertyList,$confFile,$generatorFile=__FILE__)
{
    global $_uid,$_user,$dateTimeFormatLong;
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
                    . '$'.$conf_def['config_code'].'GenDate = "'.time().'";'."\n\n"
                    . (isset($conf_def['technicalInfo'])
                    ? '/*'
                    . str_replace('*/', '* /', $conf_def['technicalInfo'])
                    . '*/'
                    : '')
                    ;
    
        $handleFileConf = fopen($confFile,'w');
        fwrite($handleFileConf,$fileHeader);
                
        foreach($storedPropertyList as $storedProperty)
        {
            $valueToWrite  = $storedProperty['propValue']; 
            $container     = $conf_def_property_list[$storedProperty['propName']]['container'];
            $description   = $conf_def_property_list[$storedProperty['propName']]['$description'];
            if (strtolower($conf_def_property_list[$storedProperty['propName']]['type'])!='boolean') 
            {
                $valueToWrite = "'".$valueToWrite."'";   
            }
            if(strtoupper($container)=='CONST')
                $propertyLine = 'define("'.$storedProperty['propName'].'",'.$valueToWrite.');'."\n";
            else
                $propertyLine = '$'.$storedProperty['propName'].' = '.$valueToWrite.';'."\n";
            $propertyDesc = (isset($description)?'/* '.$storedProperty['propName'].' : '.str_replace("\n","",$description).' */'."\n":
            (isset($conf_def_property_list[$storedProperty['propName']]['label'])?'/* '.$storedProperty['propName'].' : '.str_replace("\n","",$conf_def_property_list[$storedProperty['propName']]['label']).' */'."\n":''));
            $propertyDesc .= ( isset($conf_def_property_list[$storedProperty['propName']]['technicalInfo'])
                    ? '/*'."\n"
                    . str_replace('*/', '* /', $conf_def_property_list[$storedProperty['propName']]['technicalInfo'])
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
        return true;
}

function set_hash_confFile($confFile,$config_code)
{
    $mainTbl = claro_sql_get_main_tbl();
    $hashConf = md5_file($confFile);
    $sql =' UPDATE `'.$mainTbl['config_file'].'`          '
         .' SET config_hash = "'.$hashConf.'"      '
         .' WHERE config_code = "'.$config_code.'" ';

    if (!claro_sql_query_affected_rows($sql))
    {
        $sql =' INSERT  INTO `'.$mainTbl['config_file'].'`          '
             .' SET config_hash = "'.$hashConf.'"      '
             .' , config_code = "'.$config_code.'" ';
        return claro_sql_query($sql);
    }
    else 
    {
        return true;
    }
}

function parse_config_file($confFileName)
{
    GLOBAL $includePath;
    $code = file_get_contents($includePath."/conf/".$confFileName);
    $tokens = token_get_all($code);
    @include($includePath."/conf/".$confFileName);
    $vars = array();
    for($i=0; $i < count($tokens); $i++)
    {
        if (($tokens[$i][0] == T_VARIABLE ))
        {
            $possibleVar = substr($tokens[$i][1], 1);
            if (  $tokens[$i+1][0] == T_WHITESPACE
                && $tokens[$i+2] == '='
                )
            {
                $i += 2;
                if ($tokens[$i+1][0] == T_WHITESPACE) $i++;
                $vars[$possibleVar] = '';
                while ($i++)    
                {
                    if ($tokens[$i] == ';') break;
                    if (is_array($tokens[$i]))
                        $val = $tokens[$i][1];
                    else
                        $val = $tokens[$i];
                    $vars[$possibleVar] .= $val;
                }
            }
            $propList[$possibleVar] =  $$possibleVar;
        }
        elseif (($tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING ))
        {
            $tokens[$i][1] = str_replace('"','',$tokens[$i][1]);
            unset($value);
            @eval('$value = '.$tokens[$i][1].';');
            $propList[$tokens[$i][1]] =  $value;
        }
    }
    return  $propList;
}



function  claroconf_disp_editbox_of_a_value($conf_def_property_list, $property, $currentValue=NULL)
{
    global $langFirstDefOfThisValue;
    $currentValue = (is_bool($currentValue)?($currentValue?'TRUE':'FALSE'):$currentValue);
    $htmlPropDesc = ($conf_def_property_list['description']?'<div class="propDesc">'.nl2br(htmlentities($conf_def_property_list['description'])).'</div><br />':'');
    $htmlPropName = 'prop['.($property).']';
    $htmlPropLabel = (isset($conf_def_property_list['label'])?htmlentities($conf_def_property_list['label']):$htmlPropName);
    
    if (isset($currentValue)&&$currentValue!=$conf_def_property_list['actualValue']) 
    {
        $htmlPropValue = $currentValue;
        $htmlPropDefault = (isset($conf_def_property_list['actualValue'])
                           ?'<span class="buffer"> In buffer : '
                            .$conf_def_property_list['actualValue']
                            .'</span><br />'
                           :''
                           ).(isset($conf_def_property_list['actualValue'])
                            ?'<span class="default"> Default : '
                             .$conf_def_property_list['default']
                             .'</span><br />'
                            :'<span class="firstDefine">'
                             .$langFirstDefOfThisValue
                             .'</span>'
                             .'<br>'
                            )
                          ;
    }
    else 
    {
        $htmlPropValue = isset($conf_def_property_list['actualValue'])?$conf_def_property_list['actualValue']:$conf_def_property_list['default'];
        $htmlPropDefault = isset($conf_def_property_list['actualValue'])
                           ?'<span class="default">'
                          .'Default : '
                           .$conf_def_property_list['default']
                           .'</span><br />'
                           :'<span class="firstDefine">'
                           .$langFirstDefOfThisValue
                           .'</span>'
                           .'<br>'
                           ;
    }
    $size = (int) strlen($htmlPropValue);
    $size = 2+(($size > 90)?90:(($size < 15)?15:$size));
    $htmlUnit = (isset($conf_def_property_list['unit'])?''.htmlentities($conf_def_property_list['unit']).' ':'');
    
    if (isset($conf_def_property_list['display']) 
           &&!$conf_def_property_list['display']) 
    {
        echo '<input type="hidden" value="'.$htmlPropValue.'" name="'.$htmlPropName.'">'."\n";
    } 
    elseif ($conf_def_property_list['readonly']) 
    {
        echo '<H2>'
            .$htmlPropLabel
            .'</H2>'."\n"
            .$htmlPropDesc."\n"
            .'<span>'
            ;
        switch($conf_def_property_list['type'])
        {
       	    case 'boolean' : 
   	        case 'enum' : 
                echo (isset($conf_def_property_list['acceptedValue'][$htmlPropValue])?$conf_def_property_list['acceptedValue'][$htmlPropValue]:$htmlPropValue);
        		break;
       	    case 'integer' : 
   	        case 'string' : 
         	default:
            	// probably a string or integer
                echo $conf_def_property_list['default'];
    } // switch
    echo '</span>'."\n"
        .'<input type="hidden" value="'.$htmlPropValue.'" name="'.$htmlPropName.'">'."\n";
    } 
    else
    // Prupose a form following the type 
    switch($conf_def_property_list['type'])
    {
   	    case 'boolean' : 
            echo '<H2>'
                .$htmlPropLabel
                .'</H2>'."\n"
                .$htmlPropDesc."\n"
                .$htmlPropDefault."\n"
                .'<span>'
                .'<input id="'.$property.'_TRUE"  type="radio" name="'.$htmlPropName.'" value="TRUE"  '.($htmlPropValue=='TRUE'?' checked="checked" ':' ').' >'
                .'<label for="'.$property.'_TRUE"  >'
                .($conf_def_property_list['acceptedValue']['TRUE' ]?$conf_def_property_list['acceptedValue']['TRUE' ]:'TRUE' )
                .'</label>'
                .'</span>'."\n"
                .'<span>'
                .'<input id="'.$property.'_FALSE" type="radio" name="'.$htmlPropName.'" value="FALSE" '.($htmlPropValue=='TRUE'?' ':' checked="checked" ').' ><label for="'.$property.'_FALSE" >'.($conf_def_property_list['acceptedValue']['FALSE']?$conf_def_property_list['acceptedValue']['FALSE']:'FALSE').'</label></span>'."\n"
                ;
    		break;
   	    case 'enum' : 
            echo '<H2>'
                .$htmlPropLabel
                .'</H2>'."\n"
                .$htmlPropDesc."\n"
                .$htmlPropDefault."\n";
            foreach($conf_def_property_list['acceptedValue'] as  $keyVal => $labelVal)
            {
                echo '<span>'
                    .'<input id="'.$property.'_'.$keyVal.'"  type="radio" name="'.$htmlPropName.'" value="'.$keyVal.'"  '.($htmlPropValue==$keyVal?' checked="checked" ':' ').' >'
                    .'<label for="'.$property.'_'.$keyVal.'"  >'.($labelVal?$labelVal:$keyVal ).'</label>'
                    .'<span class="propUnit">'.$htmlUnit.'</span>'
                    .'</span>'
                    .'<br>'."\n";
            }   
    		break;
    		
//TYPE : integer, an integer is attempt
    	case 'integer' : 
            echo '<H2>'
                .'<label for="'.$property.'">'
                .$conf_def_property_list['label']
                .'</label>'
                .'</H2>'."\n"
                .'<br>'."\n"
                .$htmlPropDesc."\n"
                .$htmlPropDefault."\n"
                .'<input size="'.$size.'"  align="right" id="'.$property.'" type="text" name="'.$htmlPropName.'" value="'.$htmlPropValue.'"> '."\n"
                .'<span class="propUnit">'.$htmlUnit.'</span>'
                .'<span class="propType">('.$conf_def_property_list['type'].')</span>'."\n"
                .'<br>'
                ;
    		;
    		break;
    	default:
    	// probably a string
            echo '<h2>'."\n"
                .'<label for="'.$property.'">'
                .$conf_def_property_list['label']
                .'</label>'."\n"
                .'</h2>'."\n"
                .$htmlPropDesc."\n"
                .$htmlPropDefault."\n"
                .'<input size="'.$size.'"  id="'.$property.'" type="text" name="'.$htmlPropName.'" value="'.$htmlPropValue.'"> '
                .'<span class="propUnit">'.$htmlUnit.'</span>'
                .'<span class="propType">('.$conf_def_property_list['type'].')</span>'."\n"
                .'<br>'."\n"
                ;
    		;
    } // switch
}

function save_param_value_in_buffer($propName,$propValue,$config_code)
{

    $mainTblName = claro_sql_get_main_tbl();
    $tbl_config_property = $mainTblName['config_property'];

    $sql ='UPDATE 
            `'.$tbl_config_property.'` 
           SET propName    ="'.$propName.'", 
               propValue   ="'.$propValue.'", 
               lastChange  = now()
           WHERE propName    ="'.$propName.'" 
             AND config_code ="'.$config_code.'"
             ';
    if (!claro_sql_query_affected_rows($sql))
    {
        $sql ='INSERT 
                   INTO `'.$tbl_config_property.'` 
                   SET propName    = "'.$propName.'", 
                       propValue   = "'.$propValue.'", 
                       lastChange  = now(), 
                       config_code = "'.$config_code.'"';
        return claro_sql_query($sql);
    }
    else 
        return true;
}

/***
 * function 
 * @desc
 * @author Christophe Gesché moosh@claroline.net
 * @param 
 * @return 
 * 
 */

?>