<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

require '../../inc/claro_init_global.inc.php';

$nameTools 			= $langConfiguration;
$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $lang_config_AdministrationTools);
$noQUERY_STRING 	= TRUE;


include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/course.lib.inc.php");
include($includePath."/lib/config.lib.inc.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

$dateNow 			= claro_disp_localised_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin;

if(!$is_allowedToAdmin)
{
	$controlMsg["error"][]=$lang_config_NoAdmin;
}
else
{

	if(isset($_REQUEST["change"]))
	{
		$siteName                       = trim($_REQUEST["siteName"]);
		$administrator_name             = trim($_REQUEST["nameAdministrator"]);
		$administrator_phone            = trim($_REQUEST["phoneAdministrator"]);
		$administrator_email            = trim($_REQUEST["emailAdministrator"]);
		$institution_name               = trim($_REQUEST["nameInstitution"]);
		$institution_url                = trim($_REQUEST["urlInstitution"]);
		$platformLanguage               = trim($_REQUEST["platformLanguage"]);
		$rootWeb                        = trim($_REQUEST["rootWeb"]);
		$urlAppend                      = trim($_REQUEST["urlAppend"]);
		$dbHost                         = trim($_REQUEST["dbHost"]);
		$dbLogin                        = trim($_REQUEST["dbLogin"]);
		$dbPass                         = trim($_REQUEST["dbPass"]);
		$dbNamePrefix                   = trim($_REQUEST["dbNamePrefix"]);

		//CHECK EMAIL SYNTAX
		$emailRegex = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
		$syntaxOk=true;

		if(!empty($administrator_email) && !eregi( $emailRegex, $administrator_email) )
		{
			$syntaxOk = false;
			$controlMsg['error'][]=$lang_config_ErrorEmailAdmin;
		}
	}

	if(isset($_REQUEST["change"]) && !empty($rootWeb) && !empty($dbHost) && !empty($dbLogin)  && $syntaxOk && $link=@mysql_connect($dbHost,$dbLogin,$dbPass))
	{
		mysql_close($link);

		//Open file claro_main.conf.php
		if(file_exists("../../inc/conf/claro_main.conf.php"))
		{
			if($fp = @fopen("../../inc/conf/claro_main.conf.php","r"))
			{
				//Create 2 tables, one with the variable name and one with de new value of this variable
				$replace=array("\$siteName","\$administrator_name","\$administrator_phone","\$administrator_email",
								"\$institution_name","\$institution_url","\$platformLanguage","\$rootWeb","\$urlAppend",
								"\$dbLogin","\$dbPass","\$dbNamePrefix");

				$newVal = array ( cleanwritevalue($siteName)
                                , cleanwritevalue($administrator_name)
                                , cleanwritevalue($administrator_phone)
                                , cleanwritevalue($administrator_email)
                                , cleanwritevalue($institution_name)
                                , $institution_url
                                , $platformLanguage
                                , $rootWeb
                                , $urlAppend
                                , $dbLogin
                                , $dbPass
                                , $dbNamePrefix
                                );

				//Take all variables in the file claro_main.conf.php
				while(!feof($fp))
				{
					$ligne=fgets($fp,255);
					trim($ligne);

					unset($find);

					$find=preg_match_all('~(\$([a-zA-Z0-9_\[\]\'\'\"\"]+))[[:space:]]*=[[:space:]]*(.*);~U',$ligne,$result);

						unset($var);
						unset($begin);
						unset($end);

						if($find>0)
						{
							//Take the variables and their values
							foreach($result[0] as $v)
							{
								$var[]=$v;
							}
							//Take the name of the variables
							foreach($result[1] as $b)
							{
								$begin[]=$b;
							}
							//Take the values
							foreach($result[3] as $e)
							{
								$end[]=$e;
							}

							//Replace the variables with their new values
							$i=0;
							while($var[$i])
							{
								for($j=0;$j<count($replace);$j++)
								{
									if(!strcmp($begin[$i],$replace[$j]))
									{
										$newVar=str_replace($end,"\"".$newVal[$j]."\"",$var[$i]);
										$ligne=str_replace($var[$i],$newVar,$ligne);
									}
								}

								$i++;
							}
						}
						//Create a table with correct ligne to create de new file config
						$fileCorrect[]=$ligne;
				}
				fclose($fp);
			}
			else
				$controlMsg['error'][]=$lang_config_ErrorOpenFile;
		}



		//Create de new configuration file

		if($nf=@fopen("../../inc/conf/claro_main.conf.php","w+"))
		{
			if(isset($fileCorrect))
			{
				foreach($fileCorrect as $ligne)
				{
					fwrite($nf,$ligne);
				}
			}
			fclose($nf);
		}
		else
			$controlMsg['error'][]=$lang_config_ErrorOpenFile;

	}	//else if the are a error in the values in the form
	elseif(isset($_REQUEST["change"]))
	{
		if(!$link=@mysql_connect($dbHost,$dbLogin,$dbPass))
		{
			$controlMsg['error'][]=$lang_config_ErrorConnectMySQL;
		}
		else
			mysql_close($link);

		if(empty($dbHost))
		{
			$controlMsg['error'][]=$lang_config_ErrorHostEmpty;
		}
		if(empty($rootWeb))
		{
			$controlMsg['error'][]=$lang_config_ErrorRootWebEmpty;
		}
		if(empty($dbLogin))
		{
			$controlMsg['error'][]=$lang_config_ErrorLoginBDEmpty;
		}
	}
}

//update css used

if (!empty($_REQUEST['CSSUsed']))
{
    replace_var_value_in_conf_file ("claro_stylesheet",$_REQUEST['CSSUsed'],$includePath ."/conf/claro_main.conf.php");
    $claro_stylesheet = $_REQUEST['CSSUsed'];
}

//find available styles in the /css directory
       
if ($handle = opendir('../../css')) 
{
   $styles = array();
   while (false !== ($file = readdir($handle))) 
   {
       $ext = strrchr($file, '.');       
       if ($file != "." && $file != ".." && (strtolower($ext)==".css"))
       {
           $styles[] = $file;
       }
   }
   closedir($handle);
}

// END OF WORKS


include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools	
	)
	);
claro_disp_msg_arr($controlMsg);



// OUTPUT
?>

<form action="<?php echo $_SERVEUR['PHP_SELF'] ?>" method="POST" target="_self">
	<table border="0" >
	<tbody>
		<tr>
			<td align="right"> 
				<label for="siteName"><?php echo $lang_config_siteName ?></label> 
			</td>
			<td> 
				<input type="text" name="siteName" id="siteName" size="30" value="<?php echo cleanoutputvalue($siteName) ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><h4><?php echo $lang_config_TitleAdministrator ?></h4></td>
		</tr>
		<tr>
			<td align="right">  
				<label for="nameAdministrator"><?php echo  $lang_config_name ?></label>
			</td>
			<td> 
				<input type="text" name="nameAdministrator" id="nameAdministrator" size="30" value="<?php echo cleanoutputvalue($administrator_name)?>" />
			</td>
		</tr>
		<tr>
			<td align="right">  
				<label for="phoneAdministrator"><?php echo $lang_config_phone ?></label>
			</td>
			<td> 
				<input type="text" name="phoneAdministrator" id="phoneAdministrator" size="30" value="<?php echo cleanoutputvalue($administrator_phone) ?>" />
			</td>
		</tr>
		<tr>
			<td align="right"> 
				<label for="emailAdministrator"><?php echo $lang_config_email ?></label>
			</td>
			<td> 
				<input type="text" name="emailAdministrator" id="emailAdministrator" size="30" value="<?php echo cleanoutputvalue($administrator_email) ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><h4><?php echo $lang_config_TitleInstitution ?></h4></td>
		</tr>
		<tr>
			<td align="right"> 
				<label for="nameInstitution"><?php echo  $lang_config_name ?></label>
			</td>
			<td> 
				<input type="text" name="nameInstitution" id="nameInstitution" size="30" value="<?php echo cleanoutputvalue($institution_name) ?>" />
			</td>
		</tr>
		<tr>
			<td align="right"> 
				<label for="urlInstitution"><?php echo $lang_config_urlInstitution ?></label>
			</td>
			<td> 
				<input type="text" name="urlInstitution"  id="urlInstitution" size="50" value="<?php echo $institution_url ?>"  >
			</td>
		</tr>
		<tr>
			<td colspan="2"><h4><?php echo $lang_config_TitleProperty ?></h4></td>
		</tr>
		<tr>
			<td align="right"> 
				<label for="platformLanguage"><?php echo $lang_config_language ?></label>
			</td>
			<td> 
				<select name="platformLanguage" id="platformLanguage">
				<?php
					echo createSelectBoxLanguage($platformLanguage);
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">  
				<label for="rootWeb"><?php echo $lang_config_rootWeb;?></label> 
				<font color="gray">(*)</font> :
			</td>
			<td> 
				<input type="text" name="rootWeb" id="rootWeb" size="50" value="<?php echo $rootWeb ?>"  >
			</td>
		</tr>
		<tr>
			<td align="right"> 
				<label for="urlAppend"><?php echo $lang_config_urlAppend;?></label> :
			</td>
			<td> 
				<input type="text" name="urlAppend"  id="urlAppend" size="50" value="<?php echo $urlAppend ?>"  >
			</td>
		</tr>
		<tr>
			<td colspan="2"><h4><?php echo $lang_config_TitleBD; ?></h4></td>
		<tr>
			<td align="right">  
				<label for="dbHost"><?php echo $lang_config_dbHost; ?></label> <font color="gray">(*)</font> :
			</td>
			<td> 
				<input type="text" name="dbHost" id="dbHost" size="30" value="<?php echo $dbHost ?>"  >
			</td>
		</tr>
		<tr>
			<td align="right">  
				<label for="dbLogin"><?php echo $lang_config_dbLogin; ?></label> <font color="gray">(*)</font> :
			</td>
			<td> 
				<input type="text" name="dbLogin" id="dbLogin" size="30" value="<?php echo $dbLogin ?>"  >
			</td>
		</tr>
		<tr>
			<td align="right">  
				<label for="dbPass"><?php echo $lang_config_dbPass; ?></label>
			</td>
			<td> 
				<input type="text" name="dbPass" id="dbPass" size="30" value="<?php echo $dbPass ?>"  >
			</td>
		</tr>
		<tr>
			<td align="right">  
				<label for="dbNamePrefix"><?php echo $lang_config_dbNamePrefix; ?></label>
			</td>
			<td>
				<input type="text" name="dbNamePrefix" id="dbNamePrefix" size="30" value="<?php echo $dbNamePrefix ?>"  >
			</td>
		</tr>
		<tr>
		<td colspan="2"><h4><?php echo $lang_config_layout; ?></h4>
		</td>
		<tr>
			<td align="right">  
				<label for="CSSchange"><?php echo $langChangeCSS; ?> :</label>
			</td>
			<td>
				<select name="CSSUsed">
				<?php
				foreach ($styles as $TheStyle)
				{
				    echo "<option  value=\"$TheStyle\" ";
				    if ($claro_stylesheet == $TheStyle)
				    {
				        echo "selected = \"selected\" ";
				    }
				    echo " >$TheStyle</option>";
				}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<br>
				<input type="submit" value="<?php echo $lang_config_ButtonSend; ?>" name="change">
			</td>
		</tr>
	</tbody>
	</table>

	<h5><font color="gray"><?php echo $lang_config_Info; ?></font></h5>

</form>
<?php

include($includePath."/claro_init_footer.inc.php");

?>
