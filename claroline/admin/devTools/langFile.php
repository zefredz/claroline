<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$lang_no_access_here ="Pas d'accès ";
$langFile = "trad4all";
//require '../../inc/claro_init_global.inc.php';
$nameTools = $langCheckDatabase;
$interbredcrump[]= array ("url"=>"../index.php", "name"=> $langAdmin);


$is_allowedToEdit 	= $is_platformAdmin || $PHP_AUTH_USER;
if (!$is_allowedToEdit)
{
	header("Location:.");
	exit();
}


if ($is_allowedToEdit)
{
	//include($includePath.'/claro_init_header.inc.php');
/*	claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);
	claro_disp_msg_arr($controlMsg);
*/
	$nomOutil  = $siteName;
	$nomPage = $langNomPageCheckLAng;
	$llangDir = "../../lang/";
	$resOfScan = list_dir($llangDir);
	$llanguuesDispo = $resOfScan["langues"];

if ($file=="" || !isset($file))
{
    $files = $resOfScan["files"];
    echo "
<form action=\"$PHP_SELF\">
		<select name=\"file\" size=\"1\">";
	while (list($key,$contenu)= each($files))
	{ 
		echo "
			<option value=\"".$key."\" >".$key."</option>";
	}
	echo "
		</select>
		";
	while (list($key,$contenu)= each($llanguuesDispo))
	{ 
		echo "
			<br>
			<input type=\"checkbox\" name=\"llanguesAtester[]\" value=\"$contenu\" id=\"$contenu\" > <label for=\"$contenu\">$contenu</label>";
	}
		echo "<hr>

<input type=\"radio\" name=\"show\" value=\"complete\"> complete
|
<input type=\"radio\" name=\"show\" value=\"digest\"> résumé
|
<input type=\"radio\" name=\"show\" value=\"both\" checked> both
			<br>
			<input type=\"submit\">
</form>";

 include "../barre.inc.php";
 
exit();
}
$llanguesAtester = $HTTP_GET_VARS["llanguesAtester"];
var_export($llanguesAtester);
$nbLangues = sizeof($llanguesAtester);

for ($pntrLang = 0 ;  $pntrLang< $nbLangues; $pntrLang++)
{
	echo "
<Br>
[".($pntrLang+1)."/".$nbLangues."] ";
	$llangueDir = $llanguesAtester[$pntrLang];

	$fileOfLang = "../../lang/".$llangueDir."/".$file;
	echo "traitement ".$llangueDir." - (".$fileOfLang.")";
	if(!@include($fileOfLang))
		echo ("<font color=\"red\"> *** missing *** </font>");

    echo $llangModify;
    $auurr = get_defined_vars();
	while (list($key2,$contenu)= each($auurr))
	{ 
		if ("lang" == substr($key2,0,4))
		{
			$llangContent[$key2][$llangueDir] = $contenu;
			$$key2 ="";
			unset($$key2);
		}
	}
}


ksort($llangContent);
reset($llangContent);

while (list($key,$contenu)= each($llangContent))
{ 
if ($show == "complete" || $show =="both")
	echo "
<HR>
variable 
<strong>
	$".$key."
</strong>
<BR>
<DL>";

	$nbLangues = sizeof($llanguesAtester);
	reset($llanguesAtester);
	for ($pntrLang = 0 ;  $pntrLang< $nbLangues; $pntrLang++)
	{ 
	    if ($contenu[$llanguesAtester[$pntrLang]]=="") 
		{
			$contenu[$llanguesAtester[$pntrLang]] ="
<font color=\"#008000\">
	--empty--
</font>";
			$empty[$key][$llanguesAtester[$pntrLang]] = $contenu[$llanguesAtester[$pntrLang]];
			$empty[$key]['english'] = $contenu['english'];
			$empty[$key]['french']  = $contenu['french'];
		}
if ($show == "complete" || $show =="both")
		echo "
<DT>
	".$llanguesAtester[$pntrLang]."
	<DD>
		".$contenu[$llanguesAtester[$pntrLang]]."";
	}
if ($show == "complete" || $show =="both")
	echo "
</DL>";

}

if ($show =="both")
{
	echo "
<p style=\"page-break-after: always;\"></p>";
	include "../barre.inc.php";
}

//print_r($empty);
if (is_array($empty) && ($show =="digest" or $show =="both"))
{
	echo "
	contenu de empty
	<TABLE>";
	while (list($aaa,$bbb) = each($empty))
	{ 
		echo "
	<TR>
		<TD>
			$aaa
		</TD>
		<TD>";	
		echo "
			<TABLE bgcolor=\"#00ffff\" border=\"1\">";
		while (list($ddd,$eee) = each($bbb))
		{ 
			if ($ddd =="english" && $eee=="
<font color=\"#008000\">
	--empty--
</font>")
			   echo "
				<TR bgcolor=\"#ffff00\" bordercolor=\"#0000ff\" >";
			else
				echo "
				<TR>";
			echo "
					<TD>
						$ddd;
					</TD>
					<TD>
						$eee
					</TD>
				</TR>";	
			}
			echo "
			</TABLE>
		</TD>
	</TR>";	
		}
		echo "
</TABLE>";
	}
	include $rootAdminSys."barre.inc.php"; 
}
else
{
	echo $lang_no_access_here;
}

include($includePath."/claro_init_footer.inc.php");





function list_dir($dirname)
{
	if($dirname[strlen($dirname)-1]!='/')
		$dirname.='/';
	$handle=opendir($dirname);
	while ($entries = readdir($handle))
	{
		if (	$entries=='.'
			||	$entries=='..'
			||	$entries=='CVS'
			||	$entries=='Repository'
			||	$entries=='Entries'
			||	$entries=='root')
			continue;
		if (is_dir($dirname.$entries))
		{
			$llangues[] = $entries;
			$handlein=opendir($dirname.$entries);
			while ($entriesin = readdir($handlein))
			{   
				if ($entriesin=='.'||$entriesin=='..'||	$entriesin=='CVS'
			||	$entriesin=='Repository'
			||	$entriesin=='Entries'
			||	$entriesin=='root')
					continue;
				if (is_dir($dirname.$entriesin))
				{
					echo "
				<font size=\"-1\">
					<br> 
					-> ".$entriesin." in $dirname : abnormal location 
					-> unchecked
				</font>"; 
				}
				else
					$filesInLang[$entriesin]="*";
			}
			closedir($handlein);
		}
	}	
	closedir($handle);
	$dirScanned["files"]= $filesInLang;
	$dirScanned["langues"]= $llangues;
    return $dirScanned;
}

?>
