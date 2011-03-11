<form
    name="groupedit"
    method="post"
    action="<?php echo htmlspecialchars(
        Url::Contextualize(
            $_SERVER['PHP_SELF']
            . '?edit=yes&amp;gidReq='
            . claro_get_current_group_id()
        )
    );?>">

    <?php echo claro_form_relay_context(); ?>

    <table border="0" cellspacing="3" cellpadding="5">
        <tr valign="top">
            <td align="right">
                <label for="name" >
                    <?php echo get_lang("Group name");?>
                </label> :
            </td>
            <td colspan="2">
                <input
                    type="text"
                    name="name"
                    id="name"
                    size="40"
                    value="<?php echo htmlspecialchars($this->groupName);?>"
                 />
            </td>
            <td>
                <a href="<?php echo htmlspecialchars(Url::Contextualize('group_space.php?gidReq=' . claro_get_current_group_id()));?>">
                    <img src="<?php echo get_icon_url('group'); ?>" alt="" />
                    &nbsp;
                    <?php echo get_lang("Area for this group"); ?>
                </a>
            </td>
        </tr>
        <tr valign="top">
            <td align="right">
                <label for="description">
                    <?php echo get_lang("Description")
                        . ' '
                        . get_lang("(optional)");
                    ?>
                </label> :
            </td>
            <td colspan="3">
                <textarea name="description" id="description" rows="4 "cols="70"><?php echo htmlspecialchars($this->groupDescription); ?></textarea>
            </td>
        </tr>
        <tr valign="top">
            <td align="right">
                <label for="tutor">
                    <?php echo get_lang("Group Tutor");?>
                </label> :
            </td>
            <td colspan="2">
                <?php
                    echo claro_html_form_select(
                        'tutor',
                        $this->tutorList,
                        $this->groupTutorId,
                        array('id'=>'tutor')
                    );
                ?>
                &nbsp;&nbsp;
                <small>
                    <a href="<?php echo htmlspecialchars(Url::Contextualize('../user/user.php?gidReset=true'));?>">
                        <?php echo get_lang("User list"); ?>
                    </a>
                </small>
            </td>
            <td>
                <label for="maxMember">
                    <?php echo get_lang("Max."); ?>
                </label>
                <input
                    type="text"
                    name="maxMember"
                    id="maxMember"
                    size="2"
                    value="<?php echo htmlspecialchars($this->groupMaxMember); ?>"
                 />
                <?php echo get_lang("seats (optional)"); ?>
            </td>
        </tr>
        <!-- ################### STUDENTS IN AND OUT GROUPS ####################### -->
        <tr valign="top">
            <td align="right">
                <?php echo get_lang('Users');?> :
            </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr valign="top">
            <td align="right">
                &nbsp;
            </td>
            <td>
                <label for="ingroup">
                    <?php echo get_lang("Group members");?>
                </label> :
                <br />
                <?php
                    echo claro_html_form_select(
                        'ingroup[]',
                        $this->usersInGroupList,
                        '',
                        array('id'=>'ingroup', 'size'=>'8', 'multiple'=>'multiple'),
                        true
                    );
                ?>
                <br />
                <input
                    type="submit"
                    value="<?php echo get_lang("Ok"); ?>"
                    name="modify"
                    onclick="selectAll(this.form.elements['ingroup'],true)"
                 />
            </td>
            <td>
                <!--
                WATCH OUT ! form elements are called by numbers "form.element[3]"...
                because select name contains "[]" causing a javascript element name problem
                 -->
                <br />
                <br />
                <input
                    type="button"
                    onclick="move(this.form.elements['ingroup'],this.form.elements['nogroup'])"
                    value="   >>   "
                 />
                <br />
                <input
                    type="button"
                    onclick="move(this.form.elements['nogroup'],this.form.elements['ingroup'])"
                    value="   <<   "
                 />
            </td>
            <td align="right">
                <label for="nogroup">
                    <?php if ( get_conf('multiGroupAllowed') ): ?>
                        <?php echo get_lang("Users not in this group");?>
                    <?php else: ?>
                        <?php echo get_lang("Unassigned students");?>
                    <?php endif; ?>
                </label> :
                <br />
                <?php
                    echo claro_html_form_select(
                        'nogroup[]',
                        $this->usersNotInGroupList,
                        '',
                        array( 'id'=>'nogroup', 'size'=>'8', 'multiple'=>'multiple' ),
                        true
                    );
                ?>
            </td>
        </tr>
        <tr valign="top">
            <td colspan="4">&nbsp;</td>
        </tr>
    </table>
</form>
