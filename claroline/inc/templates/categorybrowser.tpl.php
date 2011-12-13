<!-- $Id$ -->

<!-- CURRENT CATEGORY (default: root category) -->
<?php if ($this->categoryBrowser->categoryId > 0) : ?>
<h3 id="categoryContent"><?php echo $this->currentCategory->name; ?></h3>

<?php
$backlinkUrlObj = Url::buildUrl(
                $_SERVER['PHP_SELF'].'#categoryContent',
                array('category' => $this->currentCategory->idParent),
                null);
if (isset($_REQUEST['cmd'])) 
{
    $backlinkUrlObj->addParam('cmd', $_REQUEST['cmd']);
}
?>

<p>
    <a class="backLink" href="<?php echo $backlinkUrlObj->toUrl(); ?>">
        <?php echo get_lang('Back to parent category'); ?>
    </a>
</p>

<?php else : ?>
<h3><?php echo get_lang('Root category'); ?></h3>

<?php endif; ?>



<!-- SUB CATEGORIES (with link to go deeper when possible) -->
<?php if ( count($this->categoryList) - 1 >= 0 ) : ?>

<h4><?php echo get_lang('Sub categories'); ?></h4>

<ul>
<?php foreach( $this->categoryList as $category ) : ?>
    
    <?php if (claroCategory::countAllCourses($category['id']) + claroCategory::countAllSubCategories($category['id']) > 0) : ?>
    <li>
        <?php 
        $urlObj = Url::buildUrl(
                        $_SERVER['PHP_SELF'].'#categoryContent',
                        array('category' => $category['id']),
                        null);
        if (isset($_REQUEST['cmd'])) 
        {
            $urlObj->addParam('cmd', $_REQUEST['cmd']);
        }
        ?>
        <a href="<?php echo $urlObj->toUrl(); ?>">
            <?php echo $category['name']; ?>
        </a>
    </li>
    
    <?php else : ?>
    <li><?php echo $category['name']; ?></li>
    
    <?php endif; ?>
    
<?php endforeach; ?>
</ul>

<?php endif; ?>



<!-- COURSES (belonging to the current category) -->
<h4><?php echo get_lang( 'Courses in this category' ); ?></h4>

<?php echo $this->courseTreeView->render(); ?>


<?php if ($this->categoryBrowser->categoryId > 0) : ?>
<p>
    <a class="backLink" href="<?php echo $backlinkUrlObj->toUrl(); ?>">
        <?php echo get_lang('Back to parent category'); ?>
    </a>
</p>
<?php endif; ?>
