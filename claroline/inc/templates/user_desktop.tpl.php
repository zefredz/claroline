<!-- $Id$ -->

<div id="rightSidebar">
    <?php echo $this->userProfileBox->render(); ?>
    
    <?php include_textzone('textzone_right.inc.html'); ?>
</div>

<div id="leftContent">
    <?php echo claro_html_tool_title(get_lang(get_lang('My desktop'))); ?>
    
    <?php echo $this->dialogBox->render(); ?>
    <div class="portlet collapsible collapsed">
        <h1>
            <?php echo get_lang('Presentation'); ?>
            <span class="separator">|</span>
            <a href="#" class="doCollapse"><?php echo get_lang('View all'); ?></a>
        </h1>
        <div class="content collapsible-wrapper">
            <?php include_textzone('textzone_top.authenticated.inc.html'); ?>
        </div>
    </div>
    
    <?php echo $this->outPortlet; ?>
</div>