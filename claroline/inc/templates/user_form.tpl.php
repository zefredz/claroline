<!-- $Id$ -->

<?php echo claro_html_tool_title($this->formTitle); ?>

<?php echo $this->dialogBox->render(); ?>

<form action="<?php echo $this->formAction; ?>" method="post" enctype="multipart/form-data">
    <?php echo $this->relayContext ?>
    <input type="hidden" id="cmd" name="cmd" value="registration" />
    <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
    
    <?php if (claro_is_user_authenticated()) : ?>
    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
    <?php endif; ?>
    
    <?php if (isset($this->data['confirmUserCreate'])) : ?>
    <input type="hidden" id="confirmUserCreate" name="confirmUserCreate" value="<?php echo ($this->data['confirmUserCreate'] ? 1 : 0); ?>" />
    <?php endif; ?>
    
    <?php if (!empty($this->data['user_id'])) : ?>
    <input type="hidden" id="uidToEdit" name="uidToEdit" value="<?php echo $this->data['user_id']; ?>" />
    <?php endif; ?>
    
    
    
    <!-- FIRST SECTION: personal informations -->
    <fieldset>
        <legend>
            <?php echo get_lang('Personal informations'); ?>
        </legend>
        
        <dl>
            <dt>
                <label for="lastname">
                    <?php echo get_lang('Last name'); ?>
                    <span class="required">*</span>
                </label>
            </dt>
            <dd>
                <?php if (in_array('name', $this->editableFields)) : ?>
                <input type="text" size="40" id="lastname" name="lastname" value="<?php echo $this->data['lastname']; ?>" />
                <?php else : ?>
                <?php echo $this->data['lastname']; ?>
                <?php endif; ?>
            </dd>
            <dt>
                <label for="firstname">
                    <?php echo get_lang('First name'); ?>
                    <span class="required">*</span>
                </label>
            </dt>
            <dd>
                <?php if (in_array('name', $this->editableFields)) : ?>
                <input type="text" size="40" id="firstname" name="firstname" value="<?php echo $this->data['firstname']; ?>" />
                <?php else : ?>
                <?php echo $this->data['firstname']; ?>
                <?php endif; ?>
            </dd>
            
            <?php if (get_conf('ask_for_official_code')) : ?>
            <dt>
                <label for="officialCode">
                    <?php echo get_lang('Administrative code'); ?>
                    <?php if (get_conf('userOfficialCodeCanBeEmpty')) : ?>
                    <span class="required">*</span>
                    <?php endif; ?>
                </label>
            </dt>
            <?php if (in_array('official_code', $this->editableFields)) : ?>
            <dd>
                <input type="text" size="40" id="officialCode" name="officialCode" value="<?php echo $this->data['officialCode']; ?>" />
            </dd>
            <?php else : ?>
            <dd>
                <?php echo $this->data['officialCode']; ?>
            </dd>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!empty($this->languages)) : ?>
            <dt>
                <label for="language_selector">
                    <?php echo get_lang('Language'); ?>
                </label>
            </dt>
            <dd>
                <?php echo $this->languages ?>
            </dd>
            <?php endif; ?>
            
            <?php if (get_conf('allow_profile_picture') && !empty($this->data['user_id'])) : ?>
            <dt>
                <label for="picture">
                    <?php echo get_lang('User picture'); ?>
                </label>
            </dt>
            <?php if (in_array('picture', $this->editableFields)) : ?>
            <dd>
                <img class="userPicture" src="<?php echo $this->pictureUrl; ?>" alt="<?php echo get_lang('User picture'); ?>" />
                <br />
                <input type="checkbox" name="delPicture" id="delPicture" value="true" />
                <label for="delPicture"><?php echo get_lang('Delete picture'); ?></label>
            </dd>
            <?php else : ?>
            <dd>
                <input type="file" name="picture" id="picture" /><br />
                <span class="notice">
                    <?php echo get_lang("max size %width%x%height%, %size% bytes", array(
                            '%width%' => get_conf('maxUserPictureWidth', 150),
                            '%height%' => get_conf('maxUserPictureHeight', 200),
                            '%size%' => get_conf('maxUserPictureHeight', 100*1024)));
                    ?>
                </span>
            </dd>
            <?php endif; ?>
            <?php endif; ?>
        </dl>
    </fieldset>
    
    
    
    <!-- SECOND SECTION: platform's account -->
    <fieldset>
        <legend>
            <?php echo get_lang('Platform\'s account'); ?>
        </legend>
        
        <dl>
            <?php if (!empty($this->data['user_id']) && claro_is_platform_admin()) : ?>
            <dt>
                <?php echo get_lang('User id'); ?>
            </dt>
            <dd>
                <?php echo $this->data['user_id']; ?>
            </dd>
            <?php endif; ?>
            <dt>
                <label for="email">
                    <?php echo get_lang('Email'); ?>
                </label>
            </dt>
            <dd>
                <?php if (in_array('email', $this->editableFields)) : ?>
                <input type="text" name="email" id="email" size="40" value="<?php echo htmlspecialchars($this->data['email']); ?>" />
                <?php else : ?>
                <?php echo htmlspecialchars($this->data['email']); ?>
                <?php endif; ?>
            </dd>
            
            <?php if (!empty($this->data['username']) && !in_array(strtolower($this->data['authsource']), array('claroline', 'clarocrypt'))) : ?>
            <dt>
                <?php echo get_lang('Username'); ?>
                <span class="required">*</span>
            </dt>
            <dd>
                <?php echo htmlspecialchars($this->data['username']); ?>
            </dd>
            
            <?php else : ?>
            <dt>
                <label for="username">
                    <?php echo get_lang('Username'); ?>
                    <span class="required">*</span>
                </label>
            </dt>
            <dd>
                <?php if (in_array('login', $this->editableFields)) : ?>
                <input type="text" name="username" id="username" size="40" value="<?php echo htmlspecialchars($this->data['username']); ?>" />
                <?php else : ?>
                <?php echo htmlspecialchars($this->data['username']); ?>
                <?php endif; ?>
            </dd>
            <?php if (in_array('password', $this->editableFields)) : ?>
            <?php if (!empty($this->data['user_id']) && !claro_is_platform_admin()) : ?>
            <dt>
                <label for="old_password">
                    <?php echo get_lang('Old password'); ?>
                </label>
            </dt>
            <dd>
                <input type="password" autocomplete="off" name="old_password" id="old_password" size="40" />
            </dd>
            <?php endif; ?>
            
            <dt>
                <label for="password">
                    <?php if (!empty($this->data['user_id'])) : ?>
                    <?php echo get_lang('New password'); ?>
                    <?php else : ?>
                    <?php echo get_lang('Password'); ?>
                    <?php endif; ?>
                    <span class="required">*</span>
                </label>
            </dt>
            <dd>
                <input type="password" autocomplete="off" name="password" id="password" size="40" />
            </dd>
            <dt>
                <label for="password_conf">
                    <?php if (!empty($this->data['user_id'])) : ?>
                    <?php echo get_lang('New password'); ?>
                    <?php else : ?>
                    <?php echo get_lang('Password'); ?>
                    <?php endif; ?>
                    (<?php echo get_lang('Confirmation'); ?>)
                    <span class="required">*</span>
                </label>
            </dt>
            <dd>
                <input type="password" autocomplete="off" name="password_conf" id="password_conf" size="40" />
            </dd>
            
            <?php endif; ?>
            
            <?php endif; ?>
        </dl>
    </fieldset>
    
    
    
    <!-- THIRD SECTION: others informations -->
    <fieldset>
        <legend>
            <?php echo get_lang('Other informations'); ?>
        </legend>
        
        <dl>
            <dt>
                <label for="phone">
                    <?php echo get_lang('Phone'); ?>
                </label>
            </dt>
            <dd>
                <?php if (in_array('phone', $this->editableFields)) : ?>
                <input type="text" value="<?php echo $this->data['phone']; ?>" name="phone" id="phone" size="40" />
                <?php else : ?>
                <?php echo $this->data['phone']; ?>
                <?php endif; ?>
            </dd>
            <dt>
                <label for="skype">
                    <?php echo get_lang('Skype account'); ?>
                </label>
            </dt>
            <dd>
                <?php if (in_array('skype', $this->editableFields)) : ?>
                <input type="text" value="<?php echo $this->data['skype']; ?>" name="skype" id="skype" size="40" />
                <?php else : ?>
                <?php echo $this->data['skype']; ?>
                <?php endif; ?>
            </dd>
        </dl>
    </fieldset>
</form>