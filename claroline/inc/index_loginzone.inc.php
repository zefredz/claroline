<?php # -$Id$

// Prevent direct reference to this script
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die();

if ( $claro_CasEnabled ) // CAS is a special case of external authentication
{
?>
<!-- CAS login hyperlink -->
<div align="center">
<a href="<?php echo $clarolineRepositoryWeb ?>auth/login.php?authModeReq=CAS">
<?php echo $claro_CasLoginString ?>
</a>
</div>
<?php
}

if( $claro_displayLocalAuthForm )
{
?>
<!-- Authentication Form -->
<form class="claroLoginForm"
      action ="<?php echo $clarolineRepositoryWeb . 'auth/login.php' ?>" 
      method="post">
<fieldset style="padding: 7px;">
<legend><?php echo get_lang('Authentication') ?> : </legend>
<label for="login">
<small><?php echo get_lang('UserName'); ?></small><br />
<input type="text" name="login" id="login" size="12"><br />
</label>

<label for="password" >
<small><?php echo get_lang('Password') ?></small><br />
<input type="password" name="password" id="password" size="12"><br />
</label>
<input type="submit" value="<?php echo get_lang('Enter') ?>" name="submitAuth">
</fieldset>
</form>

<!-- 'Lost Password' hyperlink -->
<p>
<a href="claroline/auth/lostPassword.php"><?php echo get_lang('LostPassword') ?></a>
</p>

<?php
    if( $allowSelfReg )
    {
    ?>
    <!-- 'Create user Account' hyperlink -->
    <p>
    <a href="claroline/auth/inscription.php"><?php echo get_lang('CreateUserAccount') ?></a>
    </p>
    <?php
    }
} // end else if claro_displayLocalAuthForm


?>