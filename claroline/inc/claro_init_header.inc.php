<?php # $Id$

/*----------------------------------------
              HEADERS SECTION
  --------------------------------------*/

/*
 * HTTP HEADER
 */

//header('Content-Type: text/html; charset='. $charset)
//	or die ("WARNING : it remains some characters before &lt;?php bracket or after ?&gt end");

header('Content-Type: text/html; charset='. $charset);

if ($httpHeadXtra)
{
	foreach($httpHeadXtra as $thisHttpHead)
	{
		header($thisHttpHead);
	}
}

/*
 * HTML HEADER
 */

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php

$titlePage = "";

if(!empty($nameTools))
{
	$titlePage .= $nameTools.' - ';
}

if(!empty($_course['officialCode']))
{
	$titlePage .= $_course['officialCode'].' - ';
}

$titlePage .= $siteName; 

?>

<title><? echo $titlePage ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $clarolineRepositoryWeb ?>css/<?php echo $claro_stylesheet ?>">
<style type="text/css">@import url(<?php echo $clarolineRepositoryWeb ?>css/<?php echo $claro_stylesheet ?>);</style>
<link rel="top" href="<?php echo $rootWeb ?>index.php" title="" >
<link rel="courses" href="<?php echo $clarolineRepositoryWeb ?>auth/courses.php" title="<?php echo $langOtherCourses ?>" >
<link rel="profil" href="<?php echo $clarolineRepositoryWeb ?>auth/profile.php" title="<?php echo $langModifyProfile ?>" >
<link href="http://www.claroline.net/documentation.htm" rel="Help" >
<link href="http://www.claroline.net/credits.htm" rel="Author" >
<link href="http://www.claroline.net" rel="Copyright" >
<script language="javascript">document.cookie="javascriptEnabled=true";</script>
<?php
if ($htmlHeadXtra)
{
	foreach($htmlHeadXtra as $thisHtmlHead)
	{
		echo($thisHtmlHead);
	}
}
?>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<?php
//  Banner
include($includePath."/claro_init_banner.inc.php");

?>