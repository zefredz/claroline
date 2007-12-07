<?php // $Id$
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

// Include claro_init_global
require '../inc/claro_init_global.inc.php';

// include profile library
include($includePath."/conf/user_profile.conf.php");

// Redirect before first output
if( ! isset($allowSelfReg) || $allowSelfReg == FALSE)
{
	header("Location: ".$rootWeb);
    exit;
}

// Initialise request variable from form 

if ( isset($_REQUEST['lastname']) ) $lastname = $_REQUEST['lastname'];
else $lastname = "";
if ( isset($_REQUEST['firstname']) ) $firstname = $_REQUEST['firstname'];
else $firstname = "";
if ( isset($_REQUEST['officialCode']) ) $officialCode = $_REQUEST['officialCode'];
else $officialCode = "";
if ( isset($_REQUEST['username']) ) $username = $_REQUEST['username'];
else $username = "";
if ( isset($_REQUEST['email']) ) $email = $_REQUEST['email'];
else $email = "";
if ( isset($_REQUEST['phone']) ) $phone = $_REQUEST['phone'];
else $phone = "";
if ( isset($_REQUEST['status']) ) $status = $_REQUEST['status'];
else $status = "";

$display_status_selector = (bool) ($is_platformAdmin OR $allowSelfRegProf);

// NAMING STATUS VALUES FOR THE PROFILES SCRIPTS

define ("STUDENT",      5);
define ("COURSEMANAGER",1);

// Display banner

$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);
include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($langRegistration);

?>
<form action="inscription_second.php" method="post">
<table cellpadding="3" cellspacing="0" border="0">

    <tr>
        <td align="right">
            <label for="lastname"><?php echo $langLastname;?>&nbsp;:</label>
        </td>
        <td>
            <input type="text" size="40" name="lastname" id="lastname" value="<?php echo htmlspecialchars($lastname);?>">
        </td>
    </tr>

    <tr>
		<td align="right">
			<label for="firstname"><?php echo $langFirstname ?>&nbsp;:</label>
		</td>
		<td>
			<input type="text" size="40" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname);?>">
		</td>
	</tr>
<?php
if (CONFVAL_ASK_FOR_OFFICIAL_CODE)
{
?>
    <tr>
        <td align="right">
            <label for="officialCode"><?php echo $langOfficialCode ?>&nbsp;:</label>
        </td>
        <td>
            <input type="text" size="40" id="offcialCode" name="officialCode" value="<?php echo htmlspecialchars($officialCode);?>">
        </td>
    </tr>
<?php
}
?>
    <tr>
        <td >
        </td>
        <td>
          <br>
        </td>
    </tr>

	<tr>
		<td align="right">
			<label for="username"><?php echo $langUserName ?>&nbsp;:</label>
		</td>
		<td>
			<input type="text" size="40" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
		</td>
	</tr>

	<tr>
		<td align="right">
			<label for="password"><?php echo $langPassword ?>&nbsp;:</label>
		</td>
		<td>
			<input type="password" size="40" id="password" name="password">
		</td>
	</tr>

	<tr>
		<td align="right">
			<label for="password_conf"><?php echo $langPassword ?>&nbsp;:<br>
			<small>(<?php echo $langConfirmation ?>)</small>
            </label>
		</td>
		<td>
			<input type="password" size="40" id="password_conf" name="password_conf">
		</td>
	</tr>

    <tr>
        <td >
        </td>
        <td>
          <br>
        </td>
    </tr>

	<tr>
		<td align="right">
			<label for="email"><?php echo $langEmail;?>&nbsp;:</label>
        </td>
		<td>
			<input type="text" size="40" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
		</td>
	</tr>

    <tr>
        <td align="right">
            <label for="phone"><?php echo $langPhone;?>&nbsp;:</label>
        </td>
        <td>
            <input type="text" size="40" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
        </td>
    </tr>

<?php

// Deactivate Teacher Self-registration if $allowSelfRegProf=FALSE

if ($display_status_selector)
{
?>
	<tr>
		<td align="right">
			<label for="status"><?php echo $langAction ?>&nbsp;:</label>
		</td>
		<td>
			<select id="status" name="status">
				<option value="<?php echo STUDENT ?>">
                <?php echo $langRegStudent; ?>
                </option>
				<option value="<?php echo COURSEMANAGER ?>" <?php echo $status == COURSEMANAGER ? 'selected' : ''?>>
                <?php echo $langRegAdmin; ?>
                </option>
			</select>
		</td></tr>
<?php
}
?>
<tr>
	<td>
		<input type="hidden" name="submitRegistration" value="true">
	</td>
	<td>
		<input type="submit" value="<?php echo $langRegister;?>" >
	</td>
</tr>

</table>

</form>

<?php
include ("../inc/claro_init_footer.inc.php");
?>
