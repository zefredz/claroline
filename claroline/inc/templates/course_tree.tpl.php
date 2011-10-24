<!-- $Id$ -->

<?php if ($this->courseTreeRootNode->hasChildren()) : ?>
<dl class="courseList">
    <?php foreach ($this->courseTreeRootNode->getChildren() as $courseTreeNode) : ?>
        <?php if ($courseTreeNode->hasCourse()) : ?>
        <?php
            $nodeView = new CourseTreeNodeView($courseTreeNode, $this->courseUserPrivilegesList);
            echo $nodeView->render();
        ?>
        
        <?php else : ?>
        <?php
            $nodeView = new CourseTreeNodeAnonymousView($courseTreeNode, $this->courseUserPrivilegesList);
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