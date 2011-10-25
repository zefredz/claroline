<!-- $Id$ -->

<?php if ($this->courseTreeRootNode->hasChildren()) : ?>

<dl class="courseList">
    
    <?php foreach ($this->courseTreeRootNode->getChildren() as $courseTreeNode) : ?>
    
        <?php if ($courseTreeNode->hasCourse()) : ?>
    
            <?php if ($courseTreeNode->getCourse()->isActivated()) : ?>
    
            <?php
                $childNodeView = new CourseTreeNodeView(
                    $courseTreeNode, 
                    $this->courseUserPrivilegesList);
                
                echo $childNodeView->render();
            ?>
            
            <?php else : ?>
    
            <?php
                $childNodeView = new CourseTreeNodeDesactivatedView(
                    $courseTreeNode, 
                    $this->courseUserPrivilegesList);
                
                echo $childNodeView->render();
            ?>
            
            <?php endif; ?>
        
        <?php else : ?>
    
        <?php
            $nodeView = new CourseTreeNodeAnonymousView(
                $courseTreeNode, 
                $this->courseUserPrivilegesList);
            
            echo $nodeView->render();
        ?>
        
        <?php endif; ?>
    
    <?php endforeach; ?>
    
</dl>

<?php else : ?>

<p>
    <?php echo get_lang('No courses here.'); ?>
</p>

<?php endif; ?>
