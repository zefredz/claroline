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
function readValueFromTblConf($tool)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_config      = $tbl_mdb_names['config'];
    $sqlGetPropertyValues = 'SELECT `propName`, `propValue`, unix_timestamp(`lastChange`) `lastChange`
                             FROM `'.$tbl_config.'` 
                             WHERE claro_label = "'.$tool.'"';
    $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    return $valueFromTblConf;
};

function getToolList()
{
    global $toolNameList;
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

function countPropertyInDb($claro_label)
{
    global $includePath;
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_config      = $tbl_mdb_names['config'];
    $tbl_tool        = $tbl_mdb_names['tool'];
    
    $claro_label_db = str_pad($claro_label,8,'_');
    $sql = 'SELECT config_file
                FROM  `'.$tbl_tool.'` 
                WHERE claro_label = "'.$claro_label_db.'"';
    $_tool = claro_sql_query_fetch_all($sql);
    $confFile = realpath($includePath.'/conf/'.$_tool[0]['config_file']);
            
    if(file_exists($confFile))
    {
        @include($confFile); // J'ai du ajouter un @ que je n'aime pas et dont je ne comprend pas la nécéssité mais sans il rale parfois.
        $genDateVarName = $claro_label.'GenDate';
        $sqlGetPropertyValues = 'SELECT count(if(unix_timestamp(`lastChange`) > "'.$$genDateVarName.'",true,null)) qty_new_values, 
                                        count(id) qty_values
                                 FROM `'.$tbl_config.'` 
                                 WHERE `claro_label` = "'.$claro_label.'" 
                                 ';
        $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    }
    else
    {
        $sqlGetPropertyValues = 'SELECT 0 qty_new_values, 
                                        count(id) qty_values
                                 FROM `'.$tbl_config.'` 
                                 WHERE `claro_label` = "'.$claro_label.'" 
                                 ';
    }
    $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    return $valueFromTblConf[0];
}

function lastConfUpdate($claro_label)
{
    global $includePath;
    $_isUpToDate = TRUE;
    $confFile = realpath($includePath).claro_get_conf_file($claro_label);
    if(file_exists($confFile))
    {
        include ($confFile);
        $genDateVarName = $claro_label.'GenDate';
        $tbl_mdb_names   = claro_sql_get_main_tbl();
        $tbl_config      = $tbl_mdb_names['config'];
        $sqlGetPropertyValues = 'SELECT unix_timestamp(`lastChange`) `lastChange`
                                 FROM `'.$tbl_config.'` 
                                 WHERE `claro_label` = "'.$claro_label.'" 
                                   AND  unix_timestamp(`lastChange`) > "'.$$genDateVarName.'"';
        $valueFromTblConf = claro_sql_query_fetch_all($sqlGetPropertyValues);
    }

    return $valueFromTblConf[0]['lastChange'];
}

function config_checkToolProperty($propValue, $propertyDef)
{
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
    return true;
}

function claro_get_conf_file($claro_label)
{
   global $includePath;
   
   $tbl_mdb_names   = claro_sql_get_main_tbl();
   $tbl_tool        = $tbl_mdb_names['tool'];
    
   $claro_label_db = str_pad($claro_label,8,'_');
   $sql = 'SELECT config_file
                FROM  `'.$tbl_tool.'` 
                WHERE claro_label = "'.$claro_label_db.'"';
   $_tool = claro_sql_query_fetch_all($sql);
    
   if (isset($_tool[0]['config_file']))
   {
       $confFile = realpath($includePath.'/conf/').'/'.$_tool[0]['config_file'];
   }
   else
   {
       $confFile = realpath($includePath.'/conf/').'/'.$claro_label.'.conf.inc.php';
   }
   
   return $confFile;
   
}

function claro_create_conf_filename($claro_label)
{
   global $includePath;
   
   $tbl_mdb_names   = claro_sql_get_main_tbl();
   $tbl_tool        = $tbl_mdb_names['tool'];
    
   $claro_label_db = str_pad($claro_label,8,'_');
   $sql = 'UPDATE`'.$tbl_tool.'` 
           SET  config_file = "'.$claro_label.'.conf.inc.php"
           WHERE claro_label = "'.$claro_label_db.'"';
   claro_sql_query($sql);
   return $claro_label.'.conf.inc.php';
   
}

function get_tool_name($claro_label)
{
    GLOBAL $toolNameList;
    return (isset($toolNameList[$claro_label])?$toolNameList[$claro_label]:$claro_label);
}

function claro_get_def_file($name)
{
    global $includePath;

    $confDef = realpath($includePath.'/conf/def/'.$name.'.def.conf.inc.php');

    return $confDef;
}


?>
