<!-- $Id$ -->

<?php if (count($this->categoryList) > 0) : ?>

<form method="post" action="test.php">
    <label for="viewCategory">Category selection</label>
    <select id="viewCategory" name="viewCategory">
        <option value="">
            <?php echo get_lang('All categories'); ?>
        </option>

        <?php foreach ($this->categoryList as $category) : ?>
        <option value="<?php echo $category['id']; ?>"<?php if(isset($this->selectedViewCategory) && $this->selectedViewCategory == $category['id']) : ?> selected="selected"<?php endif; ?>>
            <?php echo $category['path']; ?>
        </option>

        <?php endforeach; ?>
    </select> 
    <input type="submit" value="<?php echo get_lang("filter"); ?>" />
</form><br /><br />

<div class="clearer"></div>

<?php endif; ?>


<?php if ($this->courseTreeRootNode->hasChildren()) : ?>

<dl class="courseList">
    
    <?php foreach ($this->courseTreeRootNode->getChildren() as $courseTreeNode) : ?>
    
        <?php if ($courseTreeNode->hasCourse()) : ?>
            
            <?php if ($courseTreeNode->getCourse()->isActivated()) : ?>
            
            <?php
                $childNodeView = new CourseTreeNodeView(
                    $courseTreeNode,
                    $this->courseUserPrivilegesList,
                    $this->notifiedCourseList);
                
                echo $childNodeView->render();
            ?>
            
            <?php else : ?>
            
            <?php
                $childNodeView = new CourseTreeNodeDesactivatedView(
                    $courseTreeNode,
                    $this->courseUserPrivilegesList,
                    $this->notifiedCourseList);
                
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
