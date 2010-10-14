<?php
/**
 * CLAROLINE
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 */

require_once dirname(__FILE__) . '/inc/claro_init_global.inc.php';

if ( claro_is_user_authenticated() )
{

    require_once get_path('incRepositorySys') . '/lib/form.lib.php';
    
    $dialogBox = new DialogBox();
    
    $display_form = true;
    
    if (((isset($_REQUEST['fday']) && is_int((int)$_REQUEST['fday'])))
        && ((isset($_REQUEST['fmonth']) && is_int((int)$_REQUEST['fmonth']))) 
        && ((isset($_REQUEST['fyear']) && is_int((int)$_REQUEST['fyear']))))
    {
        $_SESSION['last_action'] = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'] . '00:00:00';
        claro_redirect('index.php');
    }
    
    /**
     *     DISPLAY SECTION
     *
     */
    
    $output = '';
    
    $output .= claro_html_title(get_lang('Change notification date'),2);
    $output .= $dialogBox->render();
    
    if ($display_form)
    {
        $output .= '<form method="get" action="' . htmlspecialchars( $_SERVER['PHP_SELF'] ) . '">'
        . claro_form_relay_context()
        . '<fieldset>' . "\n"
        . '<dd>'
        . claro_html_date_form('fday', 'fmonth', 'fyear', 0 , 'long' ) . ' '
        . '</dd>' . "\n"
            . '</dl>'
        . '</fieldset>'
        . '<input type="submit" class="claroButton" name="notificationDate" value="' . get_lang('Ok') . '" />' . "\n"
        . '</form>' . "\n";
    }
    Claroline::getDisplay()->body->appendContent( $output );
    
    echo Claroline::getDisplay()->render();

}   
else claro_redirect('index.php');
?>