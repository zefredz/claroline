<!-- $Id$ -->

<?php if ($this->courseTreeRootNode->hasChildren()) : ?>
<dl class="courseList">
    <?php foreach ($this->courseTreeRootNode->getChildren() as $courseTreeNode) : ?>
    <dt>
        <img
            class="access qtip"
            src="<?php echo get_course_access_icon($courseTreeNode->getCourse()->access); ?>"
            alt="<?php echo htmlspecialchars(get_course_access_mode_caption($courseTreeNode->getCourse()->access)); ?>" />
        
        <a href="<?php echo htmlspecialchars(get_path('url')
            .'/claroline/course/index.php?cid='.$courseTreeNode->getCourse()->sysCode); ?>">
            <?php echo htmlspecialchars($courseTreeNode->getCourse()->officialCode); ?>
            &ndash;
            <?php echo htmlspecialchars($courseTreeNode->getCourse()->name); ?>
        </a>
        
        <span class=role>
        <?php if ($this->cupList->getCoursePrivileges($courseTreeNode->getCourse()->courseId)->isCourseManager()) : ?>
        [Manager]
        <?php elseif ($this->cupList->getCoursePrivileges($courseTreeNode->getCourse()->courseId)->isCourseTutor()) : ?>
        [Tutor]
        <?php elseif ($this->cupList->getCoursePrivileges($courseTreeNode->getCourse()->courseId)->isCourseMember()) : ?>
        [Member]
        <?php elseif ($this->cupList->getCoursePrivileges($courseTreeNode->getCourse()->courseId)->isEnrolmentPending()) : ?>
        [Pending]
        
        <?php endif; ?>
        </span>
    </dt>
    <dd>
        <?php if (isset($courseTreeNode->getCourse()->email) && claro_is_user_authenticated()) : ?>
        <a href="mailto:<?php echo $courseTreeNode->getCourse()->email; ?>">
            <?php echo htmlspecialchars($courseTreeNode->getCourse()->titular); ?>
        </a>
        
        <?php else : ?>
        <?php echo htmlspecialchars($courseTreeNode->getCourse()->titular); ?>
        
        <?php endif; ?>
        
        -
        
        <?php echo get_course_locale_lang($courseTreeNode->getCourse()->language); ?>
        
        <?php if ($courseTreeNode->hasChildren()) : ?>
        <dl>
        <?php foreach ($courseTreeNode->getChildren() as $courseTreeNodeChild) : ?>
            <dt>
                <img
                    class="access qtip"
                    src="<?php echo get_course_access_icon($courseTreeNodeChild->getCourse()->access); ?>"
                    alt="<?php echo htmlspecialchars(get_course_access_mode_caption($courseTreeNodeChild->getCourse()->access)); ?>" />
                
                <a href="<?php echo htmlspecialchars(get_path('url')
                    .'/claroline/course/index.php?cid='.$courseTreeNodeChild->getCourse()->sysCode); ?>">
                    <?php echo htmlspecialchars($courseTreeNodeChild->getCourse()->officialCode); ?>
                    &ndash;
                    <?php echo htmlspecialchars($courseTreeNodeChild->getCourse()->name); ?>
                </a>
                
                <span class=role>
                <?php if ($this->cupList->getCoursePrivileges($courseTreeNodeChild->getCourse()->courseId)->isCourseManager()) : ?>
                [Manager]
                <?php elseif ($this->cupList->getCoursePrivileges($courseTreeNodeChild->getCourse()->courseId)->isCourseTutor()) : ?>
                [Tutor]
                <?php elseif ($this->cupList->getCoursePrivileges($courseTreeNodeChild->getCourse()->courseId)->isCourseMember()) : ?>
                [Member]
                <?php elseif ($this->cupList->getCoursePrivileges($courseTreeNodeChild->getCourse()->courseId)->isEnrolmentPending()) : ?>
                [Pending]
                
                <?php endif; ?>
                </span>
            </dt>
            <dd>
                <?php if (isset($courseTreeNodeChild->getCourse()->email) && claro_is_user_authenticated()) : ?>
                <a href="mailto:<?php echo $courseTreeNodeChild->getCourse()->email; ?>">
                    <?php echo htmlspecialchars($courseTreeNodeChild->getCourse()->titular); ?>
                </a>
                
                <?php else : ?>
                <?php echo htmlspecialchars($courseTreeNodeChild->getCourse()->titular); ?>
                
                <?php endif; ?>
                
                -
                
                <?php echo get_course_locale_lang($courseTreeNodeChild->getCourse()->language); ?>
            </dd>
            
        <?php endforeach; ?>
        </dl>
        
        <?php endif; ?>
    </dd>
    <?php endforeach; ?>
</dl>

<?php else : ?>
<p>
    <?php echo get_lang('No courses here.'); ?>
</p>

<?php endif; ?>
