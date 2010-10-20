<!-- $Id$ -->

<?php if ($this->categoryBrowser->categoryId > 0) : ?>
    <h3><?php echo $this->currentCategory->name; ?></h3>
    <p>
        <small>
        <a href="
        <?php echo $_SERVER['PHP_SELF'] . "?category=" . urlencode( $this->currentCategory->idParent ); ?>
        #categoryContent">
        &larr;
        <?php echo get_lang( 'previous level' ); ?>
        </a>
        </small>
    </p>
<?php else : ?>
    <h3><?php echo get_lang('Root category'); ?></h3>
<?php endif; ?>

<?php if ( ( count($this->categoriesList ) - 1) >= 0 ) : ?>
    
    <?php echo claro_html_title( get_lang( 'Categories' ), 4 ); ?>
    
    <ul>
    <?php foreach( $this->categoriesList as $category ) : ?>
        <li>
        
        <?php if (claroCategory::countAllCourses($category['id']) + claroCategory::countAllSubCategories($category['id']) > 0) : ?>
           <?php echo '<a href="' . $_SERVER['PHP_SELF'] . "?category="
            . urlencode( $category['id'] ) . '#categoryContent">'
            . $category['name'] . '</a>'; ?>
        <?php else : ?>
            <?php echo $category['name']; ?>
        <?php endif; ?>
        
        </li>
    <?php endforeach; ?>
    </ul>
    
<?php endif; ?>

<?php if ( count($this->coursesList) > 0 ) : ?>
    <?php if ( ( count($this->categoriesList) - 1 ) > 0 ) : ?>
        <hr size="1" noshade="noshade" />
    <?php endif; ?>
        
    <h4><?php echo get_lang( 'Course list' ); ?></h4>
    <dl class="userCourseList">
        <?php foreach( $this->coursesList as $course ) : ?>
            <?php echo render_course_dt_in_dd_list( $course, false ); ?>
        <?php endforeach; ?>
    </dl>
<?php else : ?>
    <?php if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] = 'search') : ?>
        <p>
            <?php get_lang( 'Your search did not match any courses' ); ?>
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php if ($this->categoryBrowser->categoryId > 0) : ?>
<p>
    <small>
    <a href="
    <?php echo $_SERVER['PHP_SELF'] . "?category=" . urlencode( $this->currentCategory->idParent ); ?>
    #categoryContent">
    &larr;
    <?php echo get_lang( 'previous level' ); ?>
    </a>
    </small>
</p>
<?php endif; ?>