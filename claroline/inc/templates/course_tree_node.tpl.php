<!-- $Id$ -->

<dt>
    <img
        class="access qtip"
        src="<?php echo get_course_access_icon($this->node->getCourse()->access); ?>"
        alt="<?php echo htmlspecialchars(get_course_access_mode_caption($this->node->getCourse()->access)); ?>" />
    
    <a href="<?php echo htmlspecialchars(get_path('url')
        .'/claroline/course/index.php?cid='.$this->node->getCourse()->sysCode); ?>">
        <?php echo htmlspecialchars($this->node->getCourse()->officialCode); ?>
        &ndash;
        <?php echo htmlspecialchars($this->node->getCourse()->name); ?>
    </a>
    
    <span class="role">
    <?php if ($this->courseUserPrivilegesList->getCoursePrivileges($this->node->getCourse()->courseId)->isCourseManager()) : ?>
    [Manager]
    <?php elseif ($this->courseUserPrivilegesList->getCoursePrivileges($this->node->getCourse()->courseId)->isCourseTutor()) : ?>
    [Tutor]
    <?php elseif ($this->courseUserPrivilegesList->getCoursePrivileges($this->node->getCourse()->courseId)->isCourseMember()) : ?>
    [Member]
    <?php elseif ($this->courseUserPrivilegesList->getCoursePrivileges($this->node->getCourse()->courseId)->isEnrolmentPending()) : ?>
    [Pending]
    
    <?php endif; ?>
    </span>
</dt>
<dd>
    <?php if (isset($this->node->getCourse()->email) && claro_is_user_authenticated()) : ?>
    <a href="mailto:<?php echo $this->node->getCourse()->email; ?>">
        <?php echo htmlspecialchars($this->node->getCourse()->titular); ?>
    </a>
    
    <?php else : ?>
    <?php echo htmlspecialchars($this->node->getCourse()->titular); ?>
    
    <?php endif; ?>
    
    -
    
    <?php echo get_course_locale_lang($this->node->getCourse()->language); ?>
    
    <?php if ($this->node->hasChildren()) : ?>
    <dl>
    <?php foreach ($this->node->getChildren() as $childNode) : ?>
        <?php if ($childNode->getCourse()->isActivated()) : ?>
        <?php
            $childNodeView = new CourseTreeNodeView($childNode, $this->courseUserPrivilegesList);
            echo $childNodeView->render();
        ?>
        
        <?php else : ?>
        <?php
            $childNodeView = new CourseTreeNodeDesactivatedView($childNode, $this->courseUserPrivilegesList);
            echo $childNodeView->render();
        ?>
        
        <?php endif; ?>
        
    <?php endforeach; ?>
    </dl>
    
    <?php endif; ?>
</dd>
