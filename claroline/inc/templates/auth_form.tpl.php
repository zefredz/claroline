<!-- $Id$ -->

<table align="center">
  <tr>
    <td>
        <?php echo claro_html_tool_title(get_lang('Authentication Required')); ?>
        <?php echo $this->dialogBox->render(); ?>
        <form class="claroLoginForm" action ="<?php echo $this->formAction; ?>" method="post">
            <fieldset>
                <?php echo $this->sourceUrlFormField; ?>
                <?php echo $this->cidRequiredFormField; ?>
                <?php echo $this->sourceCidFormField; ?>
                <?php echo $this->sourceGidFormField; ?>
                
                <label for="login"><?php echo get_lang('Username'); ?></label><br />
                <input type="text" name="login" id="login" size="15" tabindex="1" value="<?php echo htmlspecialchars_decode($this->defaultLoginValue); ?>"/><br />
                <br />
                <label for="password"><?php echo get_lang('Password'); ?></label><br />
                <input type="password" name="password" id="password" size="15" tabindex="2" autocomplete="off"/><br />
                <br />
                <input type="submit" value="<?php echo get_lang('Ok'); ?>" />&nbsp;
                <?php echo claro_html_button(get_path('clarolineRepositoryWeb'), get_lang('Cancel')); ?>
            </fieldset>
        </form>
        
        <?php if (get_conf('claro_CasEnabled', false)) : ?>
        <a href="login.php?<?php echo ($this->sourceUrl ? 'sourceUrl='.urlencode($this->sourceUrl) : ''); ?>&authModeReq=CAS">
            <?php echo ('' != trim(get_conf('claro_CasLoginString', '')) ? get_conf('claro_CasLoginString') : get_lang('Login')); ?>
        </a>
        <?php endif; ?>
    </td>
  </tr>
</table>