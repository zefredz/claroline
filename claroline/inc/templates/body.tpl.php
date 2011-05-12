<!-- $Id$ -->

<?php  if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php if ( $this->claroBodyStart ): ?>

<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->
<div id="claroBody">
    
<?php endif;?>
    
<?php if ( claro_is_in_a_course() ): ?>
    
    <?php if (!empty($this->relatedCourses)) : ?>
    
    <ul class="coursesTabs">
        
        <?php foreach ($this->relatedCourses as $relatedCourse) : ?>
        
        <li<?php if ($relatedCourse['id'] == $this->course['id']) : ?> class="current"<?php endif; ?>>
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
    
    <?php endif; ?>

    <hr class="clearer" />

    <div class="tabbedCourse">
        
        <div class="courseInfos">
            <h2>
                <?php echo link_to_course($this->course['name'], $this->course['sysCode']); ?>
            </h2>
            <p>
                <b><?php echo $this->course['officialCode']; ?></b><br />
                <?php echo $this->course['titular']; ?>
            </p>
            <hr class="clearer" />
        </div>
        
        <hr class="clearer" />

        <div class="courseContent">
            
<?php endif; ?>

<!-- Page content -->
<?php echo $this->content;?>
<!-- End of Page Content -->

<?php if (claro_is_in_a_course() ): ?>

        </div> <!-- courseContent -->
    </div> <!-- tabedCourse -->

<?php endif; ?>

<?php if ( $this->claroBodyEnd ): ?>
    
    <div class="spacer"></div>
</div>
<!-- - - - - - - - - - - End of Claroline Body  - - - - - - - - - - -->

<?php endif;?>