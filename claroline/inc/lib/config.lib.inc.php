<?php # $Id$ 
/**
 * config lib contain function to manage conf file
 */

/**
 * function claro_undist_file ($file)
 * @return wether the success
 * @param $file string path to file
 * @desc find config file if not exist get the '.dist' file and rename it
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
 *
 */
function trueFalse($booleanState)
{
	if ($booleanState)
		$booleanState = "TRUE";
	else
		$booleanState = "FALSE";
	return $booleanState;
}

/**
 * function replace_var_value_in_conf_file ($varName,$value,$file)
 * @return wether the success
 * @param $varName name of the variable
 * @param $value new value of the variable
 * @param $file string path to file
 * @desc replace value of variable in file
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
 * readValueFromTblConf()
 * 
 * @author moosh moosh@claroline.net
 * @param $tool tlabel of tool to get proerties
 * @return an array containning name and value of properties.
 **/
function readValueFromTblConf($config_code)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_config_property      = $tbl_mdb_names['config_property'];
    $sqlGetPropertyValues = 'SELECT `propName`, `propValue`, unix_timestamp(`lastChange`) `lastChange`
                             FROM `'.$tbl_config_property.'` 
                             WHERE config_code = "'.$config_code.'"';
    $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
  //var_dump::display($sqlGetPropertyValues);
  //var_dump::display($valueFromTblConf);
    return $valueFromTblConf;
};

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
    return $toolList;
}

function countPropertyInDb($config_code)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_config_property      = $tbl_mdb_names['config_property'];
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

function config_checkToolProperty($propValue, $propertyDef)
{
    global $acceptedValue;
    $allVarOk = TRUE;
    if(is_array($propertyDef))
    {
        switch($propertyDef['type'])
        {
            case 'boolean' : 
                if (!($propValue=='TRUE'||$propValue=='FALSE') )
                {
                    $allVarOk = FALSE;
                }   
                break;
            case 'integer' : 
                if (!is_integer($propValue)) 
                {
                    $allVarOk = FALSE;
                }   
    
                break;
            case 'enum'    : 
                if (!is_array($propValue,$acceptedValue)) 
                {
                    $allVarOk = FALSE;
                }   
                break;
            case 'regexp' : 
                if (!eregi( $acceptedValue, $propValue )) 
                {
                    $allVarOk = FALSE;
                }   
                break;
        }
    }
    else
    {
        trigger_error('propertyDef is not an array, coding error',E_USER_ERROR);
        return false;
    }
    return $allVarOk;
}

function claro_get_conf_file($config_code)
{
   global $includePath;
    
   $confDef = claro_get_def_file(config_code);
   if(file_exists($confDef)) include $confDef;
   
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
   return $config_code.'.conf.inc.php';
}

function get_tool_name($claro_label)
{
    GLOBAL $toolNameList;
    return (isset($toolNameList[$claro_label])?$toolNameList[$claro_label]:$claro_label);
}

function get_config_name($config_code)
{
    GLOBAL $toolNameList;
    return (isset($toolNameList[$config_code])?$toolNameList[$config_code]:$config_code);
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
                      , 'propQtyInDb' => countPropertyInDb($config_code)
                      , 'name'        => get_tool_name($config_code)
                      );                       
           }
       }
       closedir($handle);
    }
    return $defConfFileList;
}



function get_conf_list()
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_config_file = $tbl_mdb_names['config_file'];

    $sql_get_conf_list = 'SELECT `config_code` `config_code`, `config_hash` `config_hash` FROM `'.$tbl_config_file.'`';    
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
            if ($conf_def_property_list[$storedProperty['propName']]['type']!='boolean') 
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

?>