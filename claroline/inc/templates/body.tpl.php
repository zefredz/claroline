<!-- $Id$ -->

<?php if ( $this->claroBodyStart || !$this->inPopup ): ?>

<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->
<div id="claroBody">
    
<?php endif;?>
    
<?php if ( claro_is_in_a_course() && $this->courseTitleAndTools ): ?>
    
    <?php if (!empty($this->relatedUserCourses)) : ?>
    
    <ul class="coursesTabs">
        <?php foreach ($this->relatedUserCourses as $relatedCourse) : ?>
        
        <li class="<?php echo $relatedCourse['id']; ?>
            <?php if ($relatedCourse['isSourceCourse']) : ?> sourceCourse<?php endif; ?>
            <?php if ($relatedCourse['id'] == $this->course['id']) : ?> current<?php endif; ?>">
            <a class="qtip"
               href="<?php echo htmlspecialchars(Url::Contextualize(
                   get_path('clarolineRepositoryWeb') . 'course/index.php',
                   array('cid'=>$relatedCourse['sysCode']))); ?>"
                   title="<?php echo $relatedCourse['title']; ?>">
                <?php echo $relatedCourse['officialCode']; ?>
            </a>
        </li>
        
        <?php endforeach; ?>
        
        <li class="more"><a href="#more">&raquo;</a></li>
    </ul>
    
    <?php endif; ?> <!-- related course -->
    
    <div class="clearer"></div>
    
    <div class="tabbedCourse<?php if ($this->course['isSourceCourse']) : ?> sourceCourse<?php endif; ?>">
        
        <div class="courseInfos">
            <h2>
                <?php echo link_to_course($this->course['name'], $this->course['sysCode']); ?>
            </h2>
            <p>
                <b><?php echo $this->course['officialCode']; ?></b><br />
                <?php echo $this->course['titular']; ?>
            </p>
            
            <?php if ( claro_is_in_a_group() ): ?>
            
            <div class="clearer"></div>
            <div class="groupInfos">
            <h3>
                <a
                    href="<?php echo htmlspecialchars(Url::contextualize(
                        get_module_url('CLGRP').'/group_space.php')); ?>">
                <?php echo htmlspecialchars($this->group['name']); ?>
                </a>
            </h3>
                
            <?php if ( basename($_SERVER['PHP_SELF']) != 'group_space.php' ): ?>
            
            <p>
                <?php echo get_group_tool_menu(
                    claro_get_current_group_id(),
                    claro_get_current_course_id() ); ?>
            </p>
            
            <?php endif; ?> <!-- basename -->
            
            </div>
            
            <script type="text/javascript">
                $( function() {
                    (getLeftMenuToggleFunction())();
                });
            </script>
            
            <?php endif; ?> <!-- in a group -->
            
            <div class="clearer"></div>
        </div>
        
        <div class="clearer"></div>
        
        <div class="courseContent">
            
            <div id="courseLeftSidebar">
                <div class="toolList">
                    <a href="#" id="toggleLeftMenu" class="qtip hide" title="<?php echo get_lang("Display/hide course tool list"); ?>"> </a>
                    <?php echo $this->courseToolList->render(); ?>
                </div>
            </div>
            
            <div id="courseRightContent">
<?php endif; ?>

<!-- Page content -->
<?php echo $this->content;?>
<!-- End of Page Content -->

<?php if (claro_is_in_a_course() && $this->courseTitleAndTools ): ?>

            </div> <!-- rightContent -->
            <div class="clearer"></div>
        </div> <!-- courseContent -->
    </div> <!-- tabedCourse -->

<?php endif; ?>

<?php if ( $this->claroBodyEnd ): ?>
    
    <div class="spacer"></div>
</div>
<!-- - - - - - - - - - - End of Claroline Body  - - - - - - - - - - -->

<?php endif;?>