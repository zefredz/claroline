<?php // $Id$

/**
 * check extention and  write  if exist  in a  <LI></LI>
 *
 * @params string	$extentionName 		name  of  php extention to be checked
 * @params boolean	$echoWhenOk			true => show ok when  extention exist
 * @author Christophe Gesch�
 * @desc check extention and  write  if exist  in a  <LI></LI>
 *
 */

function warnIfExtNotLoaded($extentionName,$echoWhenOk=false)
{
	if (extension_loaded ($extentionName))
	{
		if ($echoWhenOk)
			echo "
				<LI> $extentionName - ok </LI> ";
	}
	else
	{
		echo '
				<LI>
					<font color="red">Warning !</font> 
					'.$extentionName.' is missing.</font>
				<br>
				Configure php to use this extention
				(see <a href="http://www.php.net/'.$extentionName.'">'.$extentionName.' manual</a>).
				</LI>';
	}
}

/**
 * function toprightPath()
 * @desc search read and write access from the given directory to root
 * @param path string path where begin the scan
 * @return array with 2 fields "topWritablePath" and "topReadablePath"
 * @author Christophe Gesch�
 *
 * $serchtop log is only use for debug
 */

function topRightPath($path=".")
{
	$whereIam = getcwd();
	chdir($path);
	$pathToCheck = realpath(".");
	$previousPath=$pathToCheck."*****";

	$search_top_log = "top right Path<dl>";
	while(!empty($pathToCheck))
	{
		$pathToCheck = realpath(".");
		if (is_writable($pathToCheck))
			$topWritablePath = $pathToCheck;
		if (is_readable($pathToCheck))
			$topReadablePath = $pathToCheck;
		$search_top_log .= "<dt>".$pathToCheck."</dt><dd>write:".(is_writable($pathToCheck)?"open":"close")." read:".(is_readable($pathToCheck)?"open":"close")."</dd>";
		if ($pathToCheck!="/" && $pathToCheck!=$previousPath &&(is_writable($pathToCheck)||is_readable($pathToCheck)))
		{
			chdir("..") ;
			$previousPath=$pathToCheck;
		}
		else
		{
			$pathToCheck ="";
		}

	}
	$search_top_log .= "</dl>
 	topWritablePath = ".$topWritablePath."<br>
	topReadablePath = ".$topReadablePath;

	//echo $search_top_log;
	chdir($whereIam);
	return array("topWritablePath" => $topWritablePath, "topReadablePath" => $topReadablePath);
};

function check_if_db_exist($db_name,$db=null)
{
	
	// I HATE THIS SOLUTION . 
	// It's would be better to have a SHOW DATABASE case insensitive
	if (PHP_OS!="WIN32"&&PHP_OS!="WINNT")
	{
		$sql = "SHOW DATABASES LIKE '".$db_name."'";
	}
	else 
	{
		$sql = "SHOW DATABASES LIKE '".strtolower($db_name)."'";
	}
	
	if ($db)
	{
		$res = claro_sql_query($sql,$db);
	}
	else 
	{
		$res = claro_sql_query($sql);
	}
	$foundDbName = mysql_fetch_array($res, MYSQL_NUM);
	return $foundDbName;
}

function check_claro_table_in_db_exist($dbType,$db=null)
{
	GLOBAL $dbName;
	switch ($dbType)
	{
		case 'main' :
			
			break;
		case 'stat' :
			break;
		default :
			die('error in check_claro_table_in_db_exist function called with an unknow type : "'.$dbType.'"');
	}
	return false;
}


?>
