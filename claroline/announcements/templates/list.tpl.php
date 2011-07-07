<!-- $Id$ -->

<?php if (count($this->announcementList) > 0) : ?>

<?php foreach ($this->announcementList as $announcement) : ?>

<div class="item<?php if (!$announcement['visible']) : ?> hidden<?php endif; ?>">
    <h1<?php if ($announcement['hot']) : ?> class="hot"<?php endif; ?> id="item<?php echo $announcement['id']; ?>">
        <img src="<?php echo get_icon_url('announcement'); ?>" alt="<?php echo get_lang('Announcement'); ?>" />
        <?php echo get_lang('Published on'); ?>:
        <?php echo claro_html_localised_date( get_locale('dateFormatLong'), strtotime($this->lastPostDate)); ?>
    </h1>
    
    <div class="content">
        <?php if (!empty($announcement['title'])) : ?><h2><?php echo htmlspecialchars($announcement['title']); ?></h2><?php endif; ?>
        <?php if (!empty($announcement['content'])) : ?><?php echo claro_parse_user_text($announcement['content']); ?><?php endif; ?>
    </div>
    
    <?php echo ResourceLinker::renderLinkList($announcement['currentLocator']); ?>
    
    <?php if (claro_is_course_manager()) : ?>
    <div class="manageTools">
        <a href="<?php echo htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;id=' . $announcement['id'])); ?>">
            <img src="<?php echo get_icon_url('edit'); ?>" alt="<?php echo get_lang('Modify'); ?>" />
        </a>
        
        <a href="<?php echo htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $announcement['id'])); ?>"
         onclick="javascript:if(!confirm('<?php echo clean_str_for_javascript(get_lang('Are you sure to delete "%title" ?', array('%title' => $announcement['title']))); ?>')) return false;">
            <img src="<?php echo get_icon_url('delete'); ?>" alt="<?php echo get_lang('Delete'); ?>" />
        </a>
        
        <?php if ($announcement['visible']) : ?>
        <a href="<?php echo htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $announcement['id'])); ?>">
            <img src="<?php echo get_icon_url('visible'); ?>" alt="<?php echo get_lang('Make invisible'); ?>" />
        </a>
        <?php else : ?>
        <a href="<?php echo htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $announcement['id'])); ?>">
            <img src="<?php echo get_icon_url('invisible'); ?>" alt="<?php echo get_lang('Make visible'); ?>" />
        </a>
        <?php endif; ?>
        
        <a href="<?php echo htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?cmd=exMvUp&amp;id=' . $announcement['id'])); ?>">
            <img src="<?php echo get_icon_url('move_up'); ?>" alt="<?php echo get_lang('Move up'); ?>" />
        </a>
        
        <a href="<?php echo htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?cmd=exMvDown&amp;id=' . $announcement['id'])); ?>">
            <img src="<?php echo get_icon_url('move_down'); ?>" alt="<?php echo get_lang('Move down'); ?>" />
        </a>
    </div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

<?php else : ?>
<p><?php echo get_lang('No announcement'); ?></p>
<?php endif; ?>