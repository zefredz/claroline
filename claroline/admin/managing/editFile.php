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

$langFile = "editFile";
$cidReset=TRUE;

include("../../inc/claro_init_global.inc.php");
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include("../../inc/lib/file.lib.inc.php");

$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin;

$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $lang_EditFile_AdministrationTools);
$interbredcrump[]	= array ("url"=>$PHP_SELF, "name"=> $lang_EditFile_EditFile);

//The name of the files
$NameFile=array("textzone_top.inc.html","textzone_left.inc.html");
//The path of the files
$EditFile=array($rootSys.$NameFile[0],$rootSys.$NameFile[1]);

if(!$is_allowedToAdmin)
{
	$display=FALSE;
	$controlMsg["error"][]=$lang_EditFile_NoAdmin;
}
else
{
	$display=TRUE;
	//If choose a file to modify
	if(isset($_REQUEST["file"]))
	{
		$TextFile=contentFile($_REQUEST["file"]);
	}

	//Modify a file
	if(isset($_REQUEST["modify"]))
	{
		$fp=fopen($_REQUEST["file"],"w+");
		$text=$_REQUEST["textFile"];


		if (get_magic_quotes_gpc())
		{
			$text = stripslashes($text);
		}

		fwrite($fp,$text);
		$controlMsg["info"][]=$lang_EditFile_ModifyOk;
		unset($TextFile);
	}
}

// END OF WORKS

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg);



//OUTPUT

if($display)
{
		echo  $lang_EditFile_ListFileEdit." : ";
	?>
		<br>
		<table border="0">
	<?php
		$i=0;
		foreach($EditFile as $one_file)
		{
	?>
			<tr>
				<td align="RIGHT" width="30"> -
				</td>
				<td><small>
				<a href="<?php echo $PHP_SELF."?file=".$one_file; ?>"> <?php echo $NameFile[$i]; ?> </a>
				</small>
				</td>
			</tr>
	<?php
			$i++;
		}
	?>
		</table>
	<?php

		echo "<br><br>";
		echo $lang_EditFile_ViewFile." : ";

	?>
		<br>

		<form action="<?php echo $PHP_SELF; ?>">
			<textarea name="textFile" cols="90" rows="20"> <?php echo $TextFile; ?> </textarea>
			<br><br> &nbsp;&nbsp;
			<input type="hidden" name="file" value="<?php echo $file; ?>">
			<input type="submit" name="modify" value="<?php echo $lang_EditFile_ButtonSubmit; ?>">
		</form>

	<?php
}

include($includePath."/claro_init_footer.inc.php");

?>
