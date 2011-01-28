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
<span style="font-size:8pt;">

<?php
    echo '<img class="iconDefinitionList" src="' . get_icon_url( 'hot' ) . '" alt="New items" />'
        . get_lang('New items'). ' ('
        . '<a href="' . get_path('clarolineRepositoryWeb') . 'notification_date.php' . '" >'
        . get_lang('to another date') . '</a>';
            
    $nbChar = strlen($_SESSION['last_action']);
    if (substr($_SESSION['last_action'],$nbChar - 8) == '00:00:00' )
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

<td class="coursePortletList" valign="top">
<?php
    echo $this->dialogBox->render();
?>

<?php
    if ( claro_is_allowed_to_edit() ) :
        echo '<div class="claroBlock">'."\n"
           . '<a href="'
           . htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
           . '?portletCmd=rqAdd')).'">'
           . '<img src="'.get_icon_url('default_new').'" alt="'.get_lang('Add a new portlet').'" /> '
           . get_lang('Add a portlet to your course homepage').'</a>'."\n"
           . '</div>';
    endif;
    
    if ($this->portletIterator->count() > 0)
    {
        foreach ($this->portletIterator as $portlet)
        {
            if ($portlet->getVisible() || !$portlet->getVisible() && claro_is_allowed_to_edit())
            {
                echo $portlet->render();
            }
        }
    }
    elseif ($this->portletIterator->count() == 0 && claro_is_allowed_to_edit())
    {
        echo get_lang('There is nothing to display on your course home page right now.  Use the "Add course portlet" link above to fill it.');
    }
?>
</td>

</tr>
</table>