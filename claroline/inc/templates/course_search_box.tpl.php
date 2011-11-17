<!-- $Id$ -->

<h3><label for="keyword"><?php echo get_lang( 'Search from keyword' ); ?></label></h3>
<form method="post" action="<?php echo $this->formAction; ?>">
    <input type="text" name="coursesearchbox_keyword" id="coursesearchbox_keyword" class="inputSearch" />
    <button type="submit"><?php echo get_lang('Search'); ?></button>
</form>

<?php if (!empty($this->keyword)) : ?>

<h3>
    <?php echo get_lang('Search results for <i>"%keyword"</i>', array('%keyword' => htmlentities(strip_tags($this->keyword)))); ?>
</h3>

<?php echo $this->courseTree->render(); ?>

<?php endif; ?>
