<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php if ( get_conf('claro_CasEnabled') ) : // CAS is a special case of external authentication ?>
<!-- CAS login hyperlink -->
<div align="center">
<a href="' . get_path('clarolineRepositoryWeb') . 'auth/login.php?authModeReq=CAS">
<?php echo get_conf('claro_CasLoginString'); ?>
</a>
</div>
<?php endif; ?>


<?php if( get_conf('claro_displayLocalAuthForm') ) : ?>

 <!-- Authentication Form -->
<form class="claroLoginForm" action ="<?php echo get_path('clarolineRepositoryWeb'); ?>auth/login.php" method="post">
<fieldset style="padding: 7px;">
<legend><?php echo get_lang('Authentication'); ?> : </legend>
<label for="login">
<?php echo get_lang('Username'); ?><br />
<input type="text" name="login" id="login" size="12" tabindex="1" /><br />
</label>
<label for="password" >
<?php echo get_lang('Password'); ?><br />
<input type="password" name="password" id="password" size="12" tabindex="2" /><br />
</label>
<input type="submit" value="<?php echo get_lang('Enter'); ?>" name="submitAuth" tabindex="3" />
</fieldset>
</form>

<?php   if( get_conf('claro_displayLostPasswordLink', true) ) : ?>
<!-- "Lost Password" -->
<p>
<a href="<?php echo get_path('clarolineRepositoryWeb'); ?>auth/lostPassword.php"><?php echo get_lang('Lost password'); ?></a>
</p>
<?php   endif; ?>

<?php   if( get_conf('allowSelfReg') ) : ?>
<!-- "Create user Account" -->
<p>
<a href="<?php echo get_path('clarolineRepositoryWeb'); ?>auth/inscription.php"><?php echo get_lang('Create user account'); ?></a>
</p>
<?php   endif; ?>

<?php endif; ?>