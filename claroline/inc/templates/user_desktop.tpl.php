<!-- $Id$ -->

<?php echo claro_html_tool_title(get_lang(get_lang('My desktop'))); ?>

<?php echo $this->dialogBox->render(); ?>

<div id="rightSidebar">
    <?php echo $this->userProfileBox->render(); ?>
    
    <?php include_textzone('textzone_right.inc.html'); ?>
</div>

<div id="leftContent">
    <div class="claroBlock portlet collapsible collapsed">
        <h3 class="blockHeader">
            <?php echo get_lang('Presentation'); ?>
            <span class="separator">|</span>
            <a href="#" class="doCollapse"><?php echo get_lang('View all'); ?></a>
        </h3>
        <div class="claroBlockContent collapsible-wrapper">
            <?php include_textzone('textzone_top.authenticated.inc.html'); ?>
        </div>
    </div>
    
    <?php echo $this->outPortlet; ?>
</div>