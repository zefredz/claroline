<!-- $Id$ -->

<h3><label for="keyword"><?php echo get_lang( 'Search from keyword' ); ?></label></h3>
<form method="post" action="<?php echo $this->formAction; ?>">
    <input type="text" name="coursesearchbox_keyword" id="coursesearchbox_keyword" class="inputSearch" />
    <button type="submit"><?php echo get_lang('Search'); ?></button>
</form>

<?php if (!empty($this->keyword)) : ?>

<h3><?php echo get_lang('Search results for <i>"%keyword"</i>', array('%keyword' => htmlentities(strip_tags($this->keyword)))); ?></h3>

<?php if (!empty($this->courseList)) : ?>
<dl class="courseList">
<?php foreach ($this->courseList as $course) : ?>
<?php echo render_course_in_dl_list($course); ?>

<?php endforeach; ?>
</dl>

<?php else : ?>
<p><?php echo get_lang('Your search did not match any courses'); ?></p>

<?php endif; ?>

<?php endif; ?>