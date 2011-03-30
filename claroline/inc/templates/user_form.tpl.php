<!-- $Id$ -->

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
                    <?php if (!get_conf('userOfficialCodeCanBeEmpty')) : ?>
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
            
            <?php if (get_conf('allow_profile_picture') && in_array('picture', $this->editableFields) && !empty($this->data['user_id'])) : ?>
            <dt>
                <label for="picture">
                    <?php echo get_lang('User picture'); ?>
                </label>
            </dt>
            <?php if (!empty($this->pictureUrl)) : ?>
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
            <?php if (empty($this->data['user_id'])) : ?>
            <dt></dt>
            <dd>
                <p class="notice">
                    <?php echo get_lang('Choose now a username and a password for the user account'); ?><br />
                    <?php echo get_lang('Memorize them, you will use them the next time you will enter to this site.'); ?>
                </p>
            </dd>
            <?php endif; ?>
            <?php if (!empty($this->data['user_id']) && claro_is_platform_admin()) : ?>
            <dt>
                <?php echo get_lang('User id'); ?>
            </dt>
            <dd>
                <?php echo $this->data['user_id']; ?>
            </dd>
            <?php endif; ?>
            
            <?php if (!empty($this->data['username']) && !empty($this->data['authsource']) && !in_array(strtolower($this->data['authsource']), array('claroline', 'clarocrypt'))) : ?>
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
    
    
    
    <!-- FOURTH SECTION: permissions -->
    <fieldset>
        <legend>
            <?php echo get_lang('Permissions'); ?>
        </legend>
        
        <dl>
            <dt>
                <?php echo get_lang('Role'); ?>
            </dt>
            <dd>
                <?php if (get_conf('allowSelfRegProf')) : ?>
                <input name="isCourseCreator" id="follow" value="0" type="radio"<?php if (!$this->data['isCourseCreator']) : ?> checked="checked"<?php endif; ?> /><label for="follow"><?php echo get_lang('Follow courses'); ?></label><br />
                <input name="isCourseCreator" id="create" value="1" type="radio"<?php if ($this->data['isCourseCreator']) : ?> checked="checked"<?php endif; ?> /><label for="create"><?php echo get_lang('Create course'); ?></label>
                <?php endif; ?>
            </dd>
            <?php if (claro_is_platform_admin()) : ?>
            <dt>
                <?php echo get_lang('Is platform admin'); ?>
            </dt>
            <dd>
                <input type="radio" name="isAdmin" value="1" id="isAdmin"<?php if ($this->data['isPlatformAdmin']) : ?> checked="checked"<?php endif; ?> />
                <label for="isAdmin"><?php echo get_lang('Yes'); ?></label><br />
                <input type="radio" name="isAdmin" value="0"  id="isNotAdmin"<?php if (!$this->data['isPlatformAdmin']) : ?> checked="checked"<?php endif; ?> />
                <label for="isNotAdmin"><?php echo get_lang('No'); ?></label>
            </dd>
            <?php endif; ?>
            
            <?php if (claro_is_in_a_course()) : ?>
            <dt>
                <?php echo get_lang('Course tutor'); ?>
            </dt>
            <dd>
                <input type="radio" name="courseTutor" value="1" id="courseTutorYes"<?php if ($this->data['courseTutor']) : ?> checked="checked"<?php endif; ?> /><label for="courseTutorYes"><?php echo get_lang('Yes'); ?></label><br />
                <input type="radio" name="courseTutor" value="0" id="courseTutorNo"<?php if (!$this->data['courseTutor']) : ?> checked="checked"<?php endif; ?> /><label for="courseTutorNo"><?php echo get_lang('No'); ?></label>
            </dd>
            
            <dt>
                <?php echo get_lang('Course manager'); ?>
            </dt>
            <dd>
                <input type="radio" name="courseAdmin" value="1" id="courseAdminYes"<?php if ($this->data['courseAdmin']) : ?> checked="checked"<?php endif; ?> /><label for="courseAdminYes"><?php echo get_lang('Yes'); ?></label><br />
                <input type="radio" name="courseAdmin" value="0" id="courseAdminNo"<?php if (!$this->data['courseAdmin']) : ?> checked="checked"<?php endif; ?> /><label for="courseAdminNo"><?php echo get_lang('No'); ?></label>
            </dd>
            <?php endif; ?>
        </dl>
    </fieldset>
    
    <dl>
        <dt>
            <input type="submit" name="applyChange" id="applyChange" value="<?php echo get_lang('Ok'); ?>" />
            <?php if (claro_is_in_a_course()) : ?>
            <input type="submit" name="applySearch" id="applySearch" value="<?php echo get_lang('Search'); ?>" />
            <?php endif; ?>
            <?php echo claro_html_button($this->cancelUrl, get_lang('Cancel')); ?>
        </dt>
        <dd></dd>
    </dl>
</form>

<p class="notice">
    <?php echo get_lang('<span class="required">*</span> denotes required field'); ?>
</p>