<?php
// $Id$
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

$langFile = "registration";
include("../inc/claro_init_global.inc.php");
$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);
include("../inc/claro_init_header.inc.php");
$nameTools = "1";

define ("STUDENT",5);
define ("COURSEMANAGER",1);


// Forbidden to self-register Claroline 1.4.0
if(!$allowSelfReg and isset($allowSelfReg))
{
	echo "<BR><BR>You are not allowed here<BR><BR><BR><BR>";
}
else
{

?>

<h3>
	<?php echo $langRegistration ?>
</h3>

<form action="inscription_second.php" method="post">
<table cellpadding="3" cellspacing="0" border="0">

	<tr>
		<td align="right">
			<LABEL for="name">
				<?php echo $langName ?>
			</LABEL>
			&nbsp; :
		</td>
		<td>
			<input type="text" id="name" name="nom">
		</td>
	</tr>

	<tr>
		<td align="right">
			<LABEL for="surname">
				<?php echo $langSurname;?>
			</LABEL>
			&nbsp; :
		</td>
		<td>
			<input type="text" name="prenom" id="surname">
		</td>
	</tr>

	<tr>
		<td align="right">
			<LABEL for="username">
				<?php echo $langUsername ?>
			</LABEL>
			&nbsp;:
		</td>
		<td>
			<input type="text" name="uname" id="username" >
		</td>
	</tr>

	<tr>
		<td  align="right">
			<LABEL for="pass1">
				<?php echo $langPass ?>
			</LABEL>
			&nbsp;:
		</td>
		<td>
			<input type="password" name="password1" id="pass1" >
		</td>
	</tr>

	<tr>
		<td align="right">
			<LABEL for="pass2">
				<?php echo $langPass ?>
			</LABEL> :
			<br>
			<small>
				(<?php echo $langConfirmation ?>)
			</small>
		</td>
		<td>
			<input type="password" name="password" id="pass2">
		</td>
	</tr>

	<tr>
		<td align="right">
			<LABEL for="email">
				<?php echo $langEmail;?>
			</LABEL> :
		</td>
		<td>
			<input type="text" name="email" id="email">
		</td>
	</tr>

	<tr>
		<td align="right">
			<LABEL for="language">
				<?php echo $langStatus ?>
			</LABEL>
			:
		</td>
		<td>
		
<?php
// Deactivate Teacher Self-registration if $allowSelfRegProf=FALSE

if ($is_platformAdmin OR $allowSelfRegProf)
	{
	echo "
		<select name=\"statut\" id=\"language\">
		<option value=\"".STUDENT."\">$langRegStudent</option>
		<option value=\"".COURSEMANAGER."\">$langRegAdmin</option>
		</select>";
	}
else
	{
	echo "
		<input type=\"hidden\" name=\"statut\" id=\"language\" value=\"STUDENT\">
		$langRegStudent";
}
?>

</td></tr><tr>
<td>
	<input type="hidden" name="submitRegistration" value="true">
</td>
<td><input type="submit" value="<?php echo $langOk;?>" ></td>
</tr>

</table>

</form>

<?php
}	// END else == $allowSelfReg
?>
<?php include ("../inc/claro_init_footer.inc.php");
