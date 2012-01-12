<!-- $Id$ -->

<?php if (count($this->categoryList) > 0) : ?>

<!-- Select box of courses categories -->
<form id="courseListCategorySelector" method="post" 
    action="<?php echo $_SERVER['PHP_SELF']; ?>#courseListCategorySelector">
    <label for="viewCategory">Category selection</label>
    <select id="viewCategory" name="viewCategory">
        <option value="">
            <?php echo get_lang('All categories'); ?>
        </option>
        
        <?php foreach ($this->categoryList as $category) : ?>
        <option value="<?php echo $category['id']; ?>"<?php if(isset($this->selectedViewCategory) && $this->selectedViewCategory == $category['id']) : ?> selected="selected"<?php endif; ?>>
            <?php echo $category['name']; ?>
        </option>
        
        <?php endforeach; ?>
    </select>
    <input type="submit" value="<?php echo get_lang("filter"); ?>" />
</form><br />

<div class="clearer"></div>

<?php endif; ?>


<?php if ($this->courseTreeRootNode->hasChildren()) : ?>

<!-- Render the base of the course tree -->
<dl class="courseList">
    
    <?php foreach ($this->courseTreeRootNode->getChildren() as $courseTreeNode) : ?>
        
        <?php if ($courseTreeNode->hasCourse()) : ?>
            
            <?php if ($courseTreeNode->getCourse()->isActivated()) : ?>
            
            <!-- Render the course and its children -->
            <?php
                $childNodeView = new CourseTreeNodeView(
                    $courseTreeNode,
                    $this->courseUserPrivilegesList,
                    $this->notifiedCourseList,
                    $this->viewOptions);
                
                echo $childNodeView->render();
            ?>
            
            <?php else : ?>
            
            <!-- Render the course (deactivated) and its children -->
            <?php
                $childNodeView = new CourseTreeNodeDeactivatedView(
                    $courseTreeNode,
                    $this->courseUserPrivilegesList,
                    $this->notifiedCourseList,
                    $this->viewOptions);
                
                echo $childNodeView->render();
            ?>
            
            <?php endif; ?>
            
        <?php else : ?>
        
        <!-- Render the course (adoptive) and its orphan children -->
        <?php
            $nodeView = new CourseTreeNodeAnonymousView(
                $courseTreeNode,
                $this->courseUserPrivilegesList, 
                null,
                $this->viewOptions);
            
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
