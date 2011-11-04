<!-- $Id$ -->

<dt class="desactivated<?php if (!empty($this->modifiedCourseList) 
    && in_array($this->node->getCourse()->courseId, $this->modifiedCourseList)) : 
    ?> hot<?php endif; ?>">
    <img
        class="access qtip"
        src="<?php echo get_course_access_icon(
            $this->node->getCourse()->access); ?>"
        alt="<?php echo htmlspecialchars(
            get_course_access_mode_caption(
                $this->node->getCourse()->access)); ?>" />
    
    <?php if (!empty($this->courseUserPrivilegesList)) : ?>
    
    <?php if ( $this->courseUserPrivilegesList->getCoursePrivileges(
        $this->node->getCourse()->courseId)->isCourseManager() ) : ?>
    
        <img class="role qtip" src="<?php echo get_icon_url('manager'); ?>" alt="<?php echo get_lang('You are manager of this course'); ?>" />
    
    <?php elseif ( $this->courseUserPrivilegesList->getCoursePrivileges(
        $this->node->getCourse()->courseId)->isCourseTutor() ) : ?>
    
        <span class="role">[Tutor]</span>
    
    <?php elseif ( $this->courseUserPrivilegesList->getCoursePrivileges(
        $this->node->getCourse()->courseId)->isCourseMember() ) : ?>
    
        <img class="role qtip" src="<?php echo get_icon_url('user'); ?>" alt="<?php echo get_lang('You are user of this course'); ?>" />
    
    <?php elseif ( $this->courseUserPrivilegesList->getCoursePrivileges(
        $this->node->getCourse()->courseId)->isEnrolmentPending() ) : ?>
    
        <span class="role">[Pending]</span>
    
    <?php endif; ?>
    
    <?php endif; ?>
    
    <?php if ( $this->courseUserPrivilegesList->getCoursePrivileges(
        $this->node->getCourse()->courseId )->isCourseManager()
        || claro_is_platform_admin() ) : ?>
    
    <a <?php if (!empty($this->modifiedCourseList) 
        && in_array($this->node->getCourse()->courseId, $this->modifiedCourseList)) : 
        ?>class="hot"<?php endif; ?>
        href="<?php echo htmlspecialchars(get_path('url')
        .'/claroline/course/index.php?cid='.$this->node->getCourse()->sysCode); ?>">
        
        <?php echo htmlspecialchars($this->node->getCourse()->officialCode); ?>
        &ndash;
        <?php echo htmlspecialchars($this->node->getCourse()->name); ?>
        
    </a>
    
    <?php else : ?>
        
    <?php echo htmlspecialchars($this->node->getCourse()->officialCode); ?>
    &ndash;
    <?php echo htmlspecialchars($this->node->getCourse()->name); ?>
    
    <?php endif; ?>
</dt>
<dd>
    <span>
    <?php if ( isset($this->node->getCourse()->email )
        && claro_is_user_authenticated() ) : ?>
    
    <a href="mailto:<?php echo $this->node->getCourse()->email; ?>">
        <?php echo htmlspecialchars($this->node->getCourse()->titular); ?>
    </a>
    
    <?php else : ?>
    
    <?php echo htmlspecialchars( $this->node->getCourse()->titular ); ?>
    
    <?php endif; ?>
    
    -
    
    <?php echo get_course_locale_lang( $this->node->getCourse()->language ); ?>
    
    <?php if ($this->node->hasChildren()) : ?>
    
    <dl>
        
    <?php foreach ( $this->node->getChildren() as $childNode ) : ?>
        
        <?php
            $childNodeView = new CourseTreeNodeView(
                $childNode,
                $this->courseUserPrivilegesList);
            
            echo $childNodeView->render();
        ?>
    
    <?php endforeach; ?>
        
    </dl>
    
    <?php endif; ?>
    </span>
</dd>
