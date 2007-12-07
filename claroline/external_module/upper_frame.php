<?php  session_start();
include('../include/config.php');
include('../include/lang.php');
?>
<HTML>
  <HEAD>
     <TITLE>framenavp.htm</TITLE>
  </HEAD>
  <body bgcolor=#6666FF>
  <table width="596" align=center border="0" cellpadding="0" cellspacing="0">
    <tr>
		<td valign="top">
			<font color=#ffffff>
<?
// Connect to DB
$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);

// Header settings
$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
mysql_select_db($mysqlMainDb,$db);
$result = mysql_query("SELECT intitule FROM cours WHERE code='$currentCourseID'",$db);
$myrow = mysql_fetch_array($result);
$intitule=$myrow[0];

echo "<small>Vous &ecirc;tes sorti du cours $currentCourseID $intitule.<br>
      Vous pouvez y revenir &agrave; tout moment en cliquant sur
      <a href=\"../$currentCourseID/index.php\" target=_top>
      <font color=#FFFF99>Retour</font></a>
      </small></td>";
?>
    </tr>
  </table>
  </BODY>
  </HTML>