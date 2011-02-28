<!-- $Id$ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php echo claro_html_tool_title(get_lang(get_lang('My desktop'))); ?>

<?php echo $this->dialogBox->render(); ?>

<div id="rightSidebar">
    <?php echo $this->userProfileBox->render(); ?>
</div>

<div id="leftContent">
    <?php echo $this->outPortlet; ?>
</div>