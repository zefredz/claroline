<!-- $Id: file.tpl.php 13260 2011-06-15 13:32:01Z abourguignon $ -->

<h3><label for="keyword"><?php echo get_lang( 'Search from keyword' ); ?></label></h3>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="cmd" value="search" />
    <input type="text" name="keyword" id="keyword" class="inputSearch" />
    <input type="submit" value="<?php echo get_lang('Search'); ?>" />
</form>

<?php if (!empty($this->keyword)) : ?>

<h3><?php echo get_lang('Search results for <i>"%keyword"</i>', array('%keyword' => htmlentities(strip_tags($this->keyword)))); ?></h3>

<?php if (!empty($this->courseList)) : ?>
<?php echo $this->courseList; ?>
<?php else : ?>
<p><?php echo get_lang('Your search did not match any courses'); ?></p>
<?php endif; ?>

<?php endif; ?>