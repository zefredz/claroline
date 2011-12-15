<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php if ( claro_is_course_manager() && $this->course['status'] != 'enable' ): ?>
<?php
    $message = new DialogBox;
    
    $msgStr = get_lang('This course is deactivated') . '<br />';
    if ( $this->course['status'] == 'pending' ):
        $msgStr .= get_lang('You can reactive it from your course list');
    else:
        $msgStr .= get_lang('Contact the platform administrator');
    endif;
    
    $message->warning($msgStr);
    echo $message->render();
?>
<?php endif; ?>

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
<span style='font-size:8pt'>
<?php
    echo '<img class="iconDefinitionList" src="' . get_icon_url( 'hot' ) . '" alt="New items" />'
    	. get_lang('New items'). ' ('
    . '<a href="' . get_path('clarolineRepositoryWeb') . 'notification_date.php' . '" >' . get_lang('other date') . '</a>';
            
    if ($_SESSION['last_action'] != '1970-01-01 00:00:00')
    {
       $last_action =  $_SESSION['last_action'];
    }
    else
    {
        $last_action = date('Y-m-d H:i:s');
    }

    $nbChar = strlen($last_action);
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

<td valign="top">
<?php include( get_path('incRepositorySys') . '/introductionSection.inc.php' ); ?>
</td>

</tr>
</table>