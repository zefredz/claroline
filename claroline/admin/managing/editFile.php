<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.5.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------


$langEdit = "Edit";
$langPreview = "Preview";
$langFile = "editFile";
$langHomePageTextZone = "Home page text zones";

$cidReset=TRUE;

require '../../inc/claro_init_global.inc.php';
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include("../../inc/lib/file.lib.inc.php");



define("DISP_FILE_LIST",__LINE__);
define("DISP_EDIT_FILE",__LINE__);
define("DISP_PREVIEW_FILE",__LINE__);

$nameTools = $langHomePageTextZone;
$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin;

$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $lang_EditFile_AdministrationTools);

//The name of the files
$NameFile=array("textzone_top.inc.html","textzone_right.inc.html");
//The path of the files
$EditFile=array($rootSys.$NameFile[0],$rootSys.$NameFile[1]);

if(!$is_allowedToAdmin)
{
	$display=FALSE;
	$controlMsg["error"][]=$lang_EditFile_NoAdmin;
}
else
{
	$display=DISP_FILE_LIST;
	//If choose a file to modify
	//Modify a file
	if(isset($_REQUEST["modify"]))
	{
		$text=$_REQUEST["textFile"];
		if (get_magic_quotes_gpc())
		{
			$text = stripslashes($text);
		}

		$fp=fopen($EditFile[$_REQUEST["file"]],"w+");
		fwrite($fp,$text);
		$controlMsg["info"][]=$lang_EditFile_ModifyOk." <br>
		<strong>".basename($EditFile[$_REQUEST["file"]])."</strong>";
		$display=DISP_FILE_LIST;
	}

	if(isset($_REQUEST["file"]))
	{
		$TextFile=contentFile($EditFile[$_REQUEST["file"]]);

		if ($_REQUEST['cmd']=="edit")
		{
			$subtitle = 'Edit : '.basename($NameFile[$_REQUEST["file"]]);
			$display = DISP_EDIT_FILE;
		}
		else
		{
			if (trim(strip_tags($TextFile))=="")
				$TextFile = '<blockquote><font color="#808080">- <em>No Content</em> -</font><br></blockquote>
				';
			$subtitle = 'Preview : '.basename($NameFile[$_REQUEST["file"]]);
			$display = DISP_VIEW_FILE;
		}
	}


}

// END OF WORKS

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools	,
	'subTitle'=>$subtitle
	)
	);
claro_disp_msg_arr($controlMsg);



//OUTPUT

if($display==DISP_FILE_LIST
|| $display==DISP_EDIT_FILE || $display==DISP_VIEW_FILE // remove this  whe  display edit  prupose a link to back to list
)
{
	?>
		<p>
		Here you can modify the content of the text zones displayed on the platform home page.
		<br>See below the files you can edit from this tool.
		</p>

		<table cellspacing="2" cellpadding="2" border="0" class="claroTable">
<tr class="headerX">
    <th>File</th>
    <th ><?php echo $langEdit ?></th>
    <th ><?php echo $langPreview ?></th>
</tr>

	<?php
		foreach($NameFile as $idFile => $nameFile)
		{
	?>
<tr>
    <td ><TT><?php echo basename($nameFile); ?></TT> </td>
    <td align="center"><a href="<?php echo $PHP_SELF."?cmd=edit&amp;file=".$idFile; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/edit.gif" border="0" alt="<?php echo $langEdit ?>" ></a></td>
    <td align="center"><a href="<?php echo $PHP_SELF."?cmd=view&amp;file=".$idFile; ?>"><img src="<?php echo $clarolineRepositoryWeb ?>img/preview.gif" border="0" alt="<?php echo $langPreview ?>" ></a></td>
</tr>
	<?php
		}
	?>
		</table><br>
		
	<?php
}

if($display==DISP_EDIT_FILE)
{
		echo '<h4>'.basename($NameFile[$_REQUEST["file"]]).'</h4>';
		
	?>

		<form action="<?php echo $PHP_SELF; ?>">
<?php
		//	<textarea name="textFile" cols="90" rows="20"> <?php echo $TextFile; > </textarea>
			claro_disp_html_area('textFile', $TextFile);
?>
  
			
			<br><br> &nbsp;&nbsp;
			<input type="hidden" name="file" value="<?php echo $_REQUEST['file']; ?>">
			<input type="submit" class=\"claroButton\" name="modify" value=" <?php echo $langOk; ?>">
			<?php   claro_disp_button($PHP_SELF, 'Cancel'); ?>
		</form>
	<?php
}
elseif($display==DISP_VIEW_FILE)
{
		echo '<br>
		<h4>'.basename($NameFile[$_REQUEST["file"]]).'</h4>
		'.$TextFile.'<br>'; 

}

include($includePath."/claro_init_footer.inc.php");
?>
