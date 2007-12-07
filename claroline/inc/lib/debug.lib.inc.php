<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
   | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
   |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
   |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
   +----------------------------------------------------------------------+

 * debug functions
 * 
 * All this  function output only  if  debugClaro is on 
 *
 * function echoSessionValue()
 * function mySqlQueryShowError($sql,$db="###")
 * function debugIO($file="")
 */

/**
 * function echoSessionValue()
 *
 * @desc print out  content of session's variable
 *
 * @return 
 * @authot Christophe Gesché gesché@ipm.ucl.ac.be
 *
 */
function echoSessionValue()
{
	$infoResult = "";
	GLOBAL $statuts,$statut,$status,
	$HTTP_GET_VARS,
	$HTTP_SESSION_VARS,
	$HTTP_POST_VARS,
	$dbHost, $dbLogin, $dbPass, $is_admin;
	if (!isset($is_admin) || !$is_admin)
	{
		exit("pwet");
	}

		$infoResult .= "
	<HR>
	<a href=\"../claroline/admin/phpInfo.php\">phpInfo Claroline</a>
	<PRE>";
	$infoResult .= "<strong>PHP Version</strong> : ".phpversion()."
	<strong>nivo d'err</strong> : ".error_reporting(2039);
	if (isset($statuts))
	{
		$infoResult .= "
	<strong>statut</strong> : ";
	print_r($statuts);
	}
	if (isset($statut))
	{
		$infoResult .= "
	<strong>statut</strong> : ";
	print_r($statut);
	}
	if (isset($status))
	{
		$infoResult .= "
	<strong>status</strong> : ";
	print_r($status);
	}
	
	if (isset($dbHost) || isset($dbLogin))
	{
		$infoResult .= "
	<strong>mysql param</strong> :
	 Serveur : $dbHost
	 User    : $dbLogin";
	}
	if (isset($HTTP_SESSION_VARS))
	{
		$infoResult .= "
	<strong>session</strong> : ";
	print_r($HTTP_SESSION_VARS);
	}
	if (isset($HTTP_POST_VARS))
	{
		$infoResult .= "
	<strong>Post</strong> : ";
	print_r($HTTP_POST_VARS);
	}
	if (isset($HTTP_GET_VARS))
	{
		$infoResult .= "
	<strong>GET</strong> : ";
	print_r($HTTP_GET_VARS);
	}
	
	$infoResult .= "
	<strong>Contantes</strong> : ";
	print_r(get_defined_constants());
	get_current_user();
	$infoResult .= "
	<strong>Fichiers inclus</strong> : ";
	print_r(get_included_files());
	$infoResult .= "
	<strong>Magic quote gpc</strong> : ".get_magic_quotes_gpc()."
	<strong>Magig quote runtime</strong> : ".get_magic_quotes_runtime()."
	<strong>date de dernière modification de la page</strong> : ".date("j-m-Y",getlastmod());
	/*
	get_cfg_var -- Retourne la valeur d'une option de PHP
	getenv -- Retourne la valeur de la variable d'environnement.
	ini_alter -- Change la valeur d'une option de configuration
	ini_get -- Lit la valeur d'une option de configuration.
	ini_get_all -- Lit toutes les valeurs de configuration
	ini_restore -- Restaure la valeur de l'option de configuration
	ini_set -- Change la valeur d'une option de configuration
	putenv -- Fixe la valeur d'une variable d'environnement.
	set_magic_quotes_runtime --  Active/désactive l'option magic_quotes_runtime.
	set_time_limit -- Fixe le temps maximum d'exécution d'un script.
	*/
	$infoResult .= "
	<strong>Type d'interface utilisé entre le serveur web et PHP</strong> : ".php_sapi_name()."
	<strong>informations OS</strong> : ".php_uname()."
	<strong>Version courante du moteur Zend</strong> : ".zend_version()."
	<strong>GID du propriétaire du script</strong> : ".getmygid()."
	<strong>inode du script</strong> : ".getmyinode()."
	<strong>numéro de processus courant</strong> : ".getmypid()."
	<strong>UID du propriétaire du script actuel</strong> : ".getmyuid()."
	<strong>niveau d'utilisation des ressources</strong> : ";
	print_r(@getrusage());
	
		$infoResult .= "
	</PRE>
	<HR>
		";
	if (PRINT_DEBUG_INFO)
	echo $infoResult;
	return $infoResult;
}

// Function developped by Christophe Gesché at Claroline
// to detect errors in Mysql Queries
function mySqlQueryShowError($sql,$db="###")
{
	echo "<div>this function is deprecated, replace mySqlQueryShowError() by mysql_query_dbg()</div>";
    if ($db=="###")
	{
		$val =  @mysql_query($sql);
	}
	else
	{
		$val =  @mysql_query($sql,$db);
	}
	if (mysql_errno())
	{
		echo "<HR>".mysql_errno().": ".mysql_error()."<br><PRE>$sql</PRE><HR>";
	}
    else
	{
		echo "<!-- \n$sql\n-->";
	}


	return $val;
}


// Function developped by Christophe Gesché at Claroline
// to detect errors in Mysql Queries
function mysql_query_dbg($sql,$db="###")
{
	if ($sql=="")
	{
		exit ("sql vide");
	}
	if ($db=="###")
	{
		$val =  @mysql_query($sql);
	}
	else
	{
		$val =  @mysql_query($sql,$db);
	}
	if (mysql_errno())
	{
		echo "<HR>".mysql_errno().": ".mysql_error()."<br><PRE>$sql</PRE><HR>";
	}
    else
	{

	}


	return $val;
}


/**
 * function debugIO($file="")
 *
 * @desc io file
 * @return 
 * @author Christophe Gesché gesché@ipm.ucl.ac.be
 *
 */

function debugIO($file="")
{
	 GLOBAL $SERVER_SOFTWARE;	

	$infoResult = "
[Script :  ".$_SERVER['PHP_SELF']."]
[Server :  ".$SERVER_SOFTWARE."]
[Php :  ".phpversion()."]
[sys :  ".php_uname()."]
[My uid : ".getmyuid()."]
[current_user : ".get_current_user()."]
[my gid : ".getmygid()."]
[my inode : ".getmyinode()."]
[my pid : ".getmypid()."]
[space  : - free -  : ".disk_free_space ('..')."
 - total - : ".disk_total_space('..')."
]";

	if  ($file != "")
	{
	$infoResult .= "<HR> <strong>".$file."</strong> -
		[<strong>o</strong>:".fileowner($file)." <strong>g</strong>:".filegroup($file)." ".display_perms(fileperms($file))."]";
		if (is_dir($file)) $infoResult .=  "-Dir-";
		if (is_file($file)) $infoResult .=  "-File-";
		if (is_link($file)) $infoResult .=  "-Lnk-";
		if (is_executable($file)) $infoResult .=  "-X-";
		if (is_readable($file)) $infoResult .=  "-R-";
		if (is_writeable($file)) $infoResult .=  "-W-";
	}
	
	$file = ".";
	$infoResult .=  "<HR> <strong>".$file."</strong> -
	[<strong>o</strong>:".fileowner($file)." <strong>g</strong>:".filegroup($file)." ".display_perms(fileperms($file))."]";
	if (is_dir($file)) $infoResult .=  "-Dir-";
	if (is_file($file)) $infoResult .=  "-File-";
	if (is_link($file)) echo "-Lnk-";
	if (is_executable($file)) echo "-X-";
	if (is_readable($file)) echo "-R-";
	if (is_writeable($file)) echo "-W-";

	$file = "..";
	echo "<HR> <strong>".$file."</strong> -
	[<strong>o</strong>:".fileowner($file)." <strong>g</strong>:".filegroup($file)." ".display_perms(fileperms($file))."]";
	if (is_dir($file)) $infoResult .=  "-Dir-";
	if (is_file($file)) $infoResult .=  "-File-";
	if (is_link($file)) $infoResult .=  "-Lnk-";
	if (is_executable($file)) $infoResult .=  "-X-";
	if (is_readable($file)) $infoResult .=  "-R-";
	if (is_writeable($file)) $infoResult .=  "-W-";

	if (PRINT_DEBUG_INFO)
	echo $infoResult;
	return $infoResult;

}
function display_perms( $mode )
  {
     /* Determine Type */
     if( $mode & 0x1000 )
        $type='p'; /* FIFO pipe */
     else if( $mode & 0x2000 )
        $type='c'; /* Character special */
     else if( $mode & 0x4000 )
        $type='d'; /* Directory */
     else if( $mode & 0x6000 )
      $type='b'; /* Block special */
     else if( $mode & 0x8000 )
        $type='-'; /* Regular */
     else if( $mode & 0xA000 )
        $type='l'; /* Symbolic Link */
     else if( $mode & 0xC000 )
        $type='s'; /* Socket */
    else
        $type='u'; /* UNKNOWN */

     /* Determine permissions */
     $owner["read"]    = ($mode & 00400) ? 'r' : '-';
     $owner["write"]   = ($mode & 00200) ? 'w' : '-';
     $owner["execute"] = ($mode & 00100) ? 'x' : '-';
     $group["read"]    = ($mode & 00040) ? 'r' : '-';
     $group["write"]   = ($mode & 00020) ? 'w' : '-';
     $group["execute"] = ($mode & 00010) ? 'x' : '-';
     $world["read"]    = ($mode & 00004) ? 'r' : '-';
     $world["write"]   = ($mode & 00002) ? 'w' : '-';
     $world["execute"] = ($mode & 00001) ? 'x' : '-';

     /* Adjust for SUID, SGID and sticky bit */
   if( $mode & 0x800 )
        $owner["execute"] = ($owner[execute]=='x') ? 's' : 'S';
     if( $mode & 0x400 )
       $group["execute"] = ($group[execute]=='x') ? 's' : 'S';
     if( $mode & 0x200 )
        $world["execute"] = ($world[execute]=='x') ? 't' : 'T';

     $strPerms = 
	 "<strong>t</strong>:".$type
	 ."<strong>o</strong>:".$owner[read].$owner[write].$owner[execute]
	 ."<strong>g</strong>:".$group[read].$group[write].$group[execute]
	 ."<strong>w</strong>:".$world[read].$world[write].$world[execute];
	 return $strPerms;
  }

function printVar($var, $varName="@")
{
	GLOBAL $DEBUG;
	if ($DEBUG)
	{
		echo "<blockquote>\n";
		echo "<b>[$varName]</b>";
		echo "<hr noshade size=\"1\" style=\"color:blue\">";
		echo "<pre style=\"color:red\">\n";
		var_export($var);
		echo "</pre>\n";
		echo "<hr noshade size=\"1\" style=\"color:blue\">";
		echo "</blockquote>\n";
	}
	else
	{
		echo "<!-- DEBUG is OFF -->";
		echo "DEBUG is OFF";
	}
}

function printInit($selection="*")
{
	GLOBAL
$uidReset,	$cidReset,	$gidReset, $tidReset,
$uidReq,	$cidReq, 	$gidReq,   $tidReq, $tlabelReq,
$_uid,   	$_cid,   	$_gid,	   $_tid,
$_user,		$_course,	$_group,   
$_groupProperties,
$_groupUser,
$_courseTool,
$is_platformAdmin,
$is_allowedCreateCourse,
$is_courseMember,
$is_courseAdmin,
$is_courseAllowed,
$is_courseTutor,
$is_toolAllowed,
$HTTP_SESSION_VARS,
$_claro_local_run,

$is_groupMember,
$is_groupTutor,
$is_groupAllowed;

	if ($_claro_local_run)
	{
		echo "local init runned";
	}
	else
	{
		echo "<font color=\"red\">local init never runned during this script</font>";
	}
	echo '
<table width="100%" border="1" cellspacing="4" cellpadding="1" bordercolor="#808080" bgcolor="#C0C0C0" lang="en">
	<TR>';
	if($selection == "*" or strstr($selection,"u"))
	{
		echo '
		<TD valign="top" >
			<strong>User</strong> :
			(uid) 			: '.var_export($uid,1).' |
			(_uid) 			: '.var_export($_uid,1).' |
			(session[_uid]) : '.var_export($HTTP_SESSION_VARS["_uid"],1).'
			<BR>
			reset = '.var_export($uidReset,1).' | 
			req = '.var_export($uidReq,1).'<br>
			_user : <pre>'.var_export($_user,1).'</pre>
			<br>is_platformAdmin			:'.var_export($is_platformAdmin,1).'
			<br>is_allowedCreateCourse	:'.var_export($is_allowedCreateCourse,1).'
		</TD>';
	}
	if($selection == "*" or strstr($selection,"c"))
	{
		echo "
		<TD valign=\"top\" >
			<strong>Course</strong> : (_cid)".var_export($_cid,1)."
			<br>
			reset = ".var_export($cidReset,1)." | req = ".var_export($cidReq,1)."
			<br>
			_course : <pre>".var_export($_course,1)."</pre>
			<br>
			_groupProperties : 
			<PRE>
				".var_export($_groupProperties,1)."
			</PRE>
		</TD>";
	}
	echo '
	</TR>
	<TR>';
	if($selection == "*" or strstr($selection,"g"))
	{
		echo "<TD valign=\"top\" ><strong>Group</strong> : (_gid) ".var_export($_gid,1)."<br>
		reset = ".var_export($gidReset,1)." | req = ".var_export($gidReq,1)."<br>
		_group :<pre>".var_export($_group,1).
		"</pre></TD>";
	}
	if($selection == "*" or strstr($selection,"t"))
	{
		echo '<TD valign="top" ><strong>Tool</strong> : (_tid)'.var_export($_tid,1).'<br>
		reset = '.var_export($tidReset,1).' | 
		req = '.var_export($tidReq,1).'| 
		req = '.var_export($tlabelReq,1).'
		<br>
		_tool :'.
		var_export($_tool,1).
		"</TD>";
	}
	echo "</TR>";
	if($selection == "*" or (strstr($selection,"u")&&strstr($selection,"c")))
	{
		echo '<TR><TD valign="top" colspan="2"><strong>Course-User</strong>';
		if ($_uid) echo '<br><strong>User</strong> :'.var_export($_uid,1);
		if ($_cid) echo ' in '.var_export($_cid,1).'<BR>';
		if ($_uid && $_cid) 
		echo '_courseUser			: <pre>'.var_export($_courseUser,1).'</pre>';
		echo '<br>is_courseMember	: '.var_export($is_courseMember,1);
		echo '<br>is_courseAdmin	: '.var_export($is_courseAdmin,1);
		echo '<br>is_courseAllowed	: '.var_export($is_courseAllowed,1);
		echo '<br>is_courseTutor	: '.var_export($is_courseTutor,1);
		echo '</TD></TR>';
	}
	echo "";
	if($selection == "*" or (strstr($selection,"u")&&strstr($selection,"g")))
	{

		echo '<TR><TD valign="top"  colspan="2"><strong>Course-Group-User</strong>';
		if ($_uid) echo '<br><strong>User</strong> :'.var_export($_uid,1);
		if ($_gid) echo ' in '.var_export($_gid,1);
		if ($_gid) echo "<br>_groupUser:".var_export($_groupUser,1);
		echo "<br>is_groupMember:".var_export($is_groupMember,1);
		echo "<br>is_groupTutor:".var_export($is_groupTutor,1);
		echo "<br>
		is_groupAllowed:";
		var_export($is_groupAllowed);
		echo "</TD>
		</tr>";
	}
	if($selection == "*" or (strstr($selection,"c")&&strstr($selection,"t")))
	{

		echo '<tr>
		<TD valign="top" colspan="2" ><strong>Course-Tool</strong><br>';
		if ($_tid) echo 'Tool :'.$_tid;
		if ($_cid) echo ' in '.$_cid.'<br>';
		
		if ($_tid) echo "_courseTool	: <pre>".var_export($_courseTool,1).'</pre><br>';
		echo 'is_toolAllowed : '.var_export($is_toolAllowed,1);
		echo "</TD>";
	}
	echo "</TR></TABLE>";
}

function printConfig()
{
	GLOBAL $dbHost, $dbLogin, $dbPass, $mainDbName, $clarolineVersion, $versionDb, $rootWeb, $urlAppend, $serverAddress, $checkEmailByHAshSent 			, $ShowEmailnotcheckedToStudent 	, $userMailCanBeEmpty 			, $userPasswordCrypted 			, $userPasswordCrypted			, $platformLanguage 	, $siteName			, $rootWeb			, $rootSys			, $clarolineRepositoryAppend  , $coursesRepositoryAppend	, $rootAdminAppend			, $clarolineRepositoryWeb 	, $clarolineRepositorySys		, $coursesRepositoryWeb		, $coursesRepositorySys		, $rootAdminSys				, $rootAdminWeb;
	echo "<table width=\"100%\" border=\"1\" cellspacing=\"1\" cellpadding=\"1\" bordercolor=\"#808080\" bgcolor=\"#C0C0C0\" lang=\"en\"><TR>";
		echo "
	<tr><td colspan=2><strong>Mysql</strong></td></tr>
	<tr><td>dbHost</TD><TD>$dbHost 			</td></tr>
	<tr><td>dbLogin 	</TD><TD>$dbLogin 			</td></tr>
	<tr><td>dbPass	</TD><TD>".str_repeat("*",strlen($dbPass))."</td></tr>
	<tr><td>mainDbName		</TD><TD>$mainDbName			</td></tr>
	<tr><td>clarolineVersion	</TD><TD>$clarolineVersion</td></tr>
	<tr><td>versionDb 			</TD><TD>$versionDb </td></tr>
    <tr><td>rootWeb</TD><TD>$rootWeb</td></tr>
	<tr><td>urlAppend </TD><TD>$urlAppend</td></tr>
	<tr><td>serverAddress </TD><TD>$serverAddress</td></tr>
	<tr><td colspan=2><HR></td></tr>
	<tr><td colspan=2><strong>param for new and future features</strong></td></tr>
	<tr><td>checkEmailByHashSent 			</TD><TD>$checkEmailByHAshSent 			</td></tr>
	<tr><td>ShowEmailnotcheckedToStudent 	</TD><TD>$ShowEmailnotcheckedToStudent 	</td></tr>
	<tr><td>userMailCanBeEmpty 			</TD><TD>$userMailCanBeEmpty 			</td></tr>
	<tr><td>userPasswordCrypted 			</TD><TD>$userPasswordCrypted 			</td></tr>
	<tr><td colspan=2></td></tr>
	<tr><td>platformLanguage 	</TD><TD>$platformLanguage 	</td></tr>
	<tr><td>siteName			</TD><TD>$siteName			</td></tr>
	<tr><td>rootWeb			</TD><TD>$rootWeb			</td></tr>
	<tr><td>rootSys			</TD><TD>$rootSys			</td></tr>
	<tr><td colspan=2></td></tr>
	<tr><td>clarolineRepository<strong>Append</strong>  	</TD><TD>$clarolineRepositoryAppend </td></tr>
	<tr><td>coursesRepository<strong>Append</strong>		</TD><TD>$coursesRepositoryAppend	</td></tr>
	<tr><td>rootAdmin<strong>Append</strong>				</TD><TD>$rootAdminAppend			</td></tr>
	<tr><td colspan=2></td></tr>
	<tr><td>clarolineRepository<strong>Web</strong>	</TD><TD>$clarolineRepositoryWeb 	</td></tr>
	<tr><td>clarolineRepository<strong>Sys</strong>	</TD><TD>$clarolineRepositorySys		</td></tr>
	<tr><td>coursesRepository<strong>Web</strong>	</TD><TD>$coursesRepositoryWeb		</td></tr>
	<tr><td>coursesRepository<strong>Sys</strong>	</TD><TD>$coursesRepositorySys		</td></tr>
	<tr><td>rootAdmin<strong>Sys</strong>			</TD><TD>$rootAdminSys				</td></tr>
	<tr><td>rootAdmin<strong>Web</strong>			</TD><TD>$rootAdminWeb				</td></tr>
				";
	echo "</TABLE>";
}

?>
