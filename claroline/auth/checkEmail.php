<?php  session_start();
 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$          |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
	  |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

include("../lang/english/registration.inc.php");
$nameTools = "Check email";
$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);
if (!isset($userMailCanBeEmpty))
{
	$userMailCanBeEmpty = true;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<link rel="stylesheet" href="../css/default.css" type="text/css">

<title>
	<?php echo "$nameTools - $langRegistration - $siteName - $clarolineVersion"; ?>
</title>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
	<tr>
		<td>
			<?php include('../include/claroline_header.php'); ?>
			
		</td>
	</TR>
	<tr valign="top">
		<td >
			<h4>
				<?php echo $nameTools ?>
				
			</h4>
			<br>
		</td>
	</tr>
<?

/*
		\n".$rootWeb."/claroline/auth/checkEmail.php?hash=".$hash"&email=".$email;
		}
		else
		{
			$hash = "ok";
		}
		$sqlIncriptUserHash = "
INSERT 
	INTO $mainDbName.userHash
		(user_id, hash) 
	VALUES 
		('$last_id', '$hash')";
		@mysql_query($sqlIncriptUserHash);
*/

?>
	<tr>
		<td>
			<?php
			
			echo "
			<br>
			hash : <font size=\"-3\">".$hash."</font>
			<br>
			email : ".$emailHash."
			<br>
			<br>";
			$sqlCheck = "
Select
	`user`.*, `hash`.* , `hash`.`user_id` `uid` 
From  
	`$mainDbName`.`userHash` `hash`, 
	`$mainDbName`.`user`  
WHERE
	`hash`.`user_id` = `user`.`user_id` and `email` = '".$emailHash."' and `hash` = '".$hash."';";
			$resHashFound  = @mysql_query($sqlCheck);
			if (mysql_errno())
			{
				echo "<br>
				-- ".mysql_errno()." : ".mysql_error()."<br>
				";
			}
			else 
			{
				$hashFound = mysql_fetch_array($resHashFound);
				if (	$hashFound["email"] == $emailHash 
					&& 	$hashFound["hash"] == $hash ) 
				{
					if ($hashFound["state"] != "VALID" )
					{
 						$sqlUpdateState = "
UPDATE
	userHash
SET  
	STATE =  'VALID'
WHERE
	user_id	= '".$hashFound["uid"]."' and hash = '".$hash."';";
						@mysql_query($sqlUpdateState);
						if (mysql_errno())
						{
							echo "<br>
							-- ".mysql_errno()." : ".mysql_error()."<br>
							";
						}
						echo "<br>",$emailHash," is now valid.";
					}
					else 
					{
						echo "<br>",$emailHash," is already valdided.";
					}
				}
			}
			?>
		</td>
	</tr>
</table>
<?php
	include($includePath."/claro_init_footer.inc.php");
?>