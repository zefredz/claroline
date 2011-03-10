<!-- $Id$ -->

<?php echo claro_html_tool_title(get_lang('Administration')); ?>

<?php echo $this->dialogBox->render(); ?>

<table style="white-space:nowrap; vertical-align: top;" cellspacing="5" align="center">
<tr>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('user') . '" alt="" />&nbsp;'.get_lang('Users')); ?>
        <?php echo claro_html_list($this->menu['AdminUser'], array('class' => 'adminUser')); ?>
    </td>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('course') . '" alt="" />&nbsp;'.get_lang('Courses')); ?>
        <?php echo claro_html_list($this->menu['AdminCourse'], array('class' => 'adminCourse')); ?>
    </td>
</tr>

<tr>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('settings') . '" alt="" />&nbsp;'.get_lang('Platform')); ?>
        <?php echo claro_html_list($this->menu['AdminPlatform'], array('class' => 'adminPlatform')); ?>
    </td>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('claroline') . '" alt="" />&nbsp;Claroline.net'); ?>
        <?php echo claro_html_list($this->menu['AdminClaroline'], array('class' => 'adminClaroline')); ?>
    </td>
</tr>

<tr>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('exe') . '" alt="" />&nbsp;' . get_lang('Tools')); ?>
        <?php echo claro_html_list($this->menu['AdminTechnical'], array('class' => 'adminTechnical')); ?>
    </td>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('mail_close') . '" alt="" />&nbsp;'.get_lang('Communication')); ?>
        <?php echo claro_html_list($this->menu['Communication'], array('class' => 'adminCommunication')); ?>
    </td>
</tr>'


<?php if (!empty($this->menu['ExtraTools'])) : ?>
<tr>
    <td>
        <?php echo claro_html_tool_title('<img src="' . get_icon_url('exe') . '" alt="" />&nbsp;' . get_lang('Administration tools')); ?>
        <?php echo claro_html_list($this->menu['ExtraTools'], array('class' => 'adminExtraTools')); ?>
    </td>
    <td>
        &nbsp;
    </td>
</tr>
<?php endif; ?>

</table>