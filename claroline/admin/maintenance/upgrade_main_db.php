<?php // $Id$

/**
 * try to create main database of claroline without remove existing content
 */

DEFINE("DISPLAY_WELCOME_PANEL", 1);
DEFINE("DISPLAY_RESULT_PANEL",  2);

$langFile = "admin";
include('../../inc/claro_init_global.inc.php');
$nameTools = $langAdministrationTools;

// Include lib for config files

include ($includePath."/installedVersion.inc.php");
include ($includePath."/lib/config.lib.inc.php");

$display = DISPLAY_WELCOME_PANEL;
if ($_REQUEST['cmd']=="run")
{
    if ($statsDbName=="") $statsDbName = $mainDbName;

    $sqlForUpdate[] = "USE ".$mainDbName;
    @include("createMainBase.sql.php");
    @include("repairTables.sql.php");
    $sqlForUpdate[] = "USE ".$mainDbName;
    @include("createPMAextBase.sql.php");
    @include("repairTables.sql.php");
	if ($is_trackingEnabled)
    {
		$sqlForUpdate[] = "USE ".$statsDbName;
		@include("createTrackingBase.sql.php");
		@include("repairTables.sql.php");
	}

    $langUpgradeDataBase = "Upgrading Main Database ";

    if (!function_exists(mysql_info)) {function mysql_info() {return "";}} // mysql_info is used in

    $db = mysql_connect("$dbHost", "$dbLogin", "$dbPass");
    mysql_select_db($mainDbName);
    $nameTools = $langUpgradeDataBase;

    if ($encrypt)
    {
    	$result=mysql_query(" SELECT * FROM user");
    	while ($myrow = mysql_fetch_array($result))
    	{
    		$id=$myrow[user_id];
    		$newpass=md5($myrow[password]);
    		$sqlForUpdate[] = " UPDATE user SET password = '$newpass' WHERE user_id = $id";
    	}
    }
    $display = DISPLAY_RESULT_PANEL;
} // if ($cmd=="run")

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<div id="header">
<?php
 echo "<h1>Claroline upgrade -- version " . $clarolineVersion . "</h1>";
?>
</div>

<div id="menu">
<p><a href="upgrade.php">Upgrade</a> - Main Database</p>
</div>


<div id="content">

<h2>Upgrade Claroline main database</h2>

<?php
switch ($display)
{
    case DISPLAY_WELCOME_PANEL :
	echo "<p><a href=\"" . $PHP_SELF . "?cmd=run\" >Launch main Claroline database (<code>" . $mainDbName . "</code>) upgrading</a></p>";
    	echo "<p class=\"help\">Notice: Updating main database (It may take some time).</p>";
    	echo "<p><small><a href=\"upgrade.php\"><< Back</a></small></p>";
        break;
    case DISPLAY_RESULT_PANEL :
        echo "<p>main Claroline database (<code>".$mainDbName."</code>) upgraded</p>\n";

        if ($verbose) {
        	echo "<p class=\"info\">Mode Verbose</p>\n";
        }

        echo "<ol>\n";

        $nbError = 0;
        while (list($key,$sqlTodo) = each($sqlForUpdate))
        {
        	if ($sqlTodo[0] == "#")
        	{
        		if ($verbose)
        		{
        			echo "<p class=\"comment\">Comment: $sqlTodo</p>\n";
        		}
        	}
        	else
        	{
        		$res = @mysql_query($sqlTodo);
        		if ($verbose)
        		{
        			echo "<li>\n";
        			echo "<p class=\"tt\">$sqlTodo</p>\n";
        			echo "<p>" . mysql_affected_rows() . " affected rows<br />" . mysql_info() . "</p>\n";
        		}
        		if (mysql_errno() > 0)
        		{
        			if (mysql_errno() == 1060 || mysql_errno() == 1062 || mysql_errno() == 1091 || mysql_errno() == 1054 )
        			{
        				if ($verbose)
        				{
        					echo "<p class=\"success\">". mysql_errno(). ": ".mysql_error()."</p>\n";
        				}
        			}
        			else
        			{
        				echo "<p class=\"error\">".(++$nbError)."<strong>n°".mysql_errno()."</strong>: ".mysql_error()."<br />\n";
        				echo "<code>".$sqlTodo."</code>";
        				echo "</p>\n";
        			}
        		}
        		if ($verbose) {
        			echo "</li>\n";
        		}
        	}
        }
        mysql_close();
        echo "</ol>\n";

        if ($nbError>0 )
        {
        	echo "<p class=\"error\">$nbError errors found</p>\n";
        	echo "<form action=\"".$PHP_SELF."\" >\n
        	<input type=\"hidden\" name=\"verbose\" id=\"verbose\" value=\"true\" />
        	<p>Retry with more detail. <input type=\"submit\" name=\"retry\" value=\"retry\" /></p>\n
        	</form>\n";
        }
        else
        {
           // update config file
           // set version db

           echo "<p class=\"success\">Main database upgraded</p>\n";

           if (replace_var_value_in_conf_file ("versionDb",$version_db_cvs,$includePath ."/conf/claro_main.conf.php"))
           {
        		echo "<p><a href=\"upgrade.php\">Next</a></p>\n";
           }
           else
           {
           	echo "<p class=\"error\">Can't save success in config file</p>\n";
           }
        }
        break;
    default : die("display unknow")         ;
}
?>
</div>
</body>
</html>
