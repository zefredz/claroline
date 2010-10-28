<!-- $Id$ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<table border="0" cellspacing="10" cellpadding="10" width="100%">
<tr>
<td valign="top" style="border-right: gray solid 1px;" width="220">
<?php if (is_array($this->toolLinkListSource)
    && !empty($this->toolLinkListSource)
    && is_array($this->toolLinkListSession)
    && !empty($this->toolLinkListSession) )
{
    echo '<div class="sourceToolPanel"><h3>' . get_lang('Course') . '</h3>';
    echo claro_html_menu_vertical_br($this->toolLinkListSource, array('id'=>'commonToolListSource'));
    echo '</div>';
    echo '<div class="sessionToolPanel"><h3>' . get_lang('Session') . '</h3>';
    echo claro_html_menu_vertical_br($this->toolLinkListSession, array('id'=>'commonToolListSession'));
    echo '</div>';
}
if (is_array($this->toolLinkListStandAlone))
{
    echo claro_html_menu_vertical_br($this->toolLinkListStandAlone, array('id'=>'commonToolListStandAlone'));
}
?>

<br />

<?php
if ( claro_is_allowed_to_edit() ) :
    echo claro_html_menu_vertical_br($this->courseManageToolLinkList,  array('id'=>'courseManageToolList'));
endif;
?>

<?php if ( claro_is_user_authenticated() ) : ?>
<br />
<span style='font-size:8pt'> 
<?php
    echo '<img class="iconDefinitionList" src="' . get_icon_url( 'hot' ) . '" alt="New items" />'
    	. get_lang('New items'). ' ('
    . '<a href="' . get_path('clarolineRepositoryWeb') . 'notification_date.php' . '" >' . get_lang('to another date') . '</a>';
            
    if (substr($_SESSION['last_action'],10) == '00:00:00' )
    {
        echo ' [' . claro_html_localised_date( get_locale('dateFormatNumeric'),
            strtotime($_SESSION['last_action'])) . ']';
    }
    
    echo ')' ;
?>
</span>

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