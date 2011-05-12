<!-- $Id$ -->

<?php echo $this->dialogBox->render(); ?>

<?php if ( $this->displayRegistrationLink ): ?>

<p>
    <?php
        echo claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
            $_SERVER['PHP_SELF'] . '?registration=1' ))
            , '<img src="' . get_icon_url('enroll') . '"'
            .     ' alt="' . get_lang("Add me to this group") . '" />'
            . get_lang("Add me to this group")
        );
    ?>
</p>

<?php elseif ( $this->displayUnregistrationLink ): ?>

<p>
    <?php
        echo claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
            $_SERVER['PHP_SELF'] . '?unregistration=1' ))
            , '<img src="' . get_icon_url('unenroll') . '"'
            .     ' alt="' . get_lang("Remove me from this group") . '" />'
            . get_lang("Remove me from this group")
        );
    ?>
</p>
<?php endif; ?>

<div id="leftSidebar" class="toolList">
    <?php
        if ( is_array($this->toolLinkList ) ):

            echo claro_html_list( $this->toolLinkList, array( 'id'=> 'groupToolList' ) );

        endif;
    ?>
    
    <br />

    <?php
        if ( claro_is_allowed_to_edit() ) :
            echo claro_html_cmd_link ( htmlspecialchars(Url::Contextualize('group_edit.php'))
                , '<img src="' . get_icon_url('edit') . '"'
                .     ' alt="' . get_lang("Edit this group") . '" />'
                .    get_lang("Edit this group")
            );
        endif;
    ?>
    
    <?php
        if ( current_user_is_allowed_to_send_message_to_current_group() ):
        
                echo '<br />'
                    . claro_html_cmd_link ( htmlspecialchars(Url::Contextualize(
                        '../messaging/sendmessage.php?cmd=rqMessageToGroup&amp;' ))
                        , '<img src="' . get_icon_url('mail_send') . '" alt="" />' . get_lang("Send a message to group")
                    );
        endif;
    ?>
</div>
<div id="rightContent" class="groupSpaceContents">
    <fieldset>
        <dl>
            <dt><?php echo get_lang("Description"); ?></dt>
            <dd><?php echo htmlspecialchars( $this->groupDescription ); ?></dd>
            <dt><?php echo get_lang("Group Tutor"); ?></dt>
            <dd>
                <?php if ( count($this->tutorDataList) > 0 ): ?>
                
                    <?php foreach( $this->tutorDataList as $thisTutor ): ?>
                        
                    <span class="item">
                        <?php echo htmlspecialchars( $thisTutor['lastName'] . ' ' . $thisTutor['firstName'] ); ?>

                        <?php if ( current_user_is_allowed_to_send_message_to_user( $thisTutor['id'] ) ): ?>

                            - 
                            <a 
                                href="<?php echo htmlspecialchars(Url::Contextualize(
                                    '../messaging/sendmessage.php?cmd=rqMessageToUser&amp;userId=' 
                                    . (int)$thisTutor['id'] )); ?>">
                                <?php echo get_lang('Send a message'); ?>
                            </a>

                        <?php endif; ?>

                    </span>
                
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php echo get_lang("(none)"); ?>
                <?php endif; ?>
            </dd>
            <dt><?php echo get_lang("Group members"); ?></dt>
            <dd>
                <?php if ( count( $this->groupMemberList ) > 0 ): ?>
                    
                    <?php foreach ( $this->groupMemberList as $thisGroupMember ): ?>
                        
                    <a 
                        href="<?php echo htmlspecialchars(Url::Contextualize(
                                '../user/userInfo.php?uInfo=' . $thisGroupMember['id'], 
                                $this->urlContext  ) ); ?>" 
                        class="item">
                        
                        <?php echo htmlspecialchars( $thisGroupMember['lastName'] . ' ' . $thisGroupMember['firstName'] ); ?>
                    </a>
        
                    <?php if(current_user_is_allowed_to_send_message_to_user($thisGroupMember['id'])): ?>

                    - <a 
                        href="<?php echo htmlspecialchars(Url::Contextualize(
                            '../messaging/sendmessage.php?cmd=rqMessageToUser&amp;userId=' . (int) $thisGroupMember['id'] )); ?>">
                        <?php echo get_lang('Send a message'); ?>
                    </a>
                    
                    <?php endif; ?>
        
                    <br />
                    
                    <?php endforeach; ?>
                
                <?php else: ?>
                    <?php echo get_lang("(none)"); ?>
                <?php endif; ?>
            </dd>
        </dl>
    </fieldset>
</div>
<hr class="clearer" />
