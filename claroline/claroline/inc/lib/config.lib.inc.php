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
			$line=fgets($fp);
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


?>
