<!-- $Id$ -->

<dt>
    <?php echo get_lang('Session courses without source courses'); ?>
</dt>

<dd>
    
    <?php if ( $this->node->hasChildren() ) : ?>
    
    <dl>
        
    <?php foreach ( $this->node->getChildren() as $childNode ) : ?>
        
        <?php
            $childNodeView = new CourseTreeNodeView(
                $childNode, 
                $this->courseUserPrivilegesList,
                $this->notifiedCourseList,
                $this->viewOptions);
            
            echo $childNodeView->render();
        ?>
    
    <?php endforeach; ?>
        
    </dl>
    
    <?php endif; ?>
    
</dd>
