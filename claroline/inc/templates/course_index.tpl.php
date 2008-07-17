<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<table border="0" cellspacing="10" cellpadding="10" width="100%">
<tr>
<td valign="top" style="border-right: gray solid 1px;" width="220">
<?php echo claro_html_menu_vertical_br($this->toolLinkList, array('id'=>'commonToolList')); ?>
<br />

<?php 
if ( claro_is_allowed_to_edit() ) :
    echo claro_html_menu_vertical_br($this->courseManageToolLinkList,  array('id'=>'courseManageToolList'));
endif;
?>

<?php if ( claro_is_user_authenticated() ) : ?>
<br />
<small>
<span class="item hot">
<?php echo get_lang('denotes new items'); ?>
</span>
</small>
<?php endif; ?>

</td>

<td width="20">
&nbsp;
</td>

<td valign="top">
<?php include( get_path('incRepositorySys') . '/introductionSection.inc.php' ); ?>
</td>

</tr>
</table>