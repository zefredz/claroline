<?php // $Id$
/**
 * CLAROLINE
 *
 * $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package Desktop
 *
 * @author Claroline team <info@claroline.net>
 *
 */

// {{{ SCRIPT INITIALISATION

    // reset course and groupe
    $cidReset = TRUE;
    $gidReset = TRUE;
    $uidRequired = TRUE;

    // load Claroline kernel
    require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
    require_once get_path( 'includePath' ) . '/lib/user.lib.php';
    require_once dirname(__FILE__) . '/lib/portlet.lib.php';
    require_once dirname(__FILE__) . '/lib/portletRightMenu.lib.php';
    require_once dirname(__FILE__) . '/lib/porletInsertConfigDB.lib.php';

    // users authentified
    if( ! claro_is_user_authenticated() ) claro_disp_auth_form();

    $is_allowedToEdit = claro_is_allowed_to_edit();

    $dialogBox = new DialogBox();

    $acceptedCmdList = array(
    'rqAvatar',
    'exAvatar',
    'exDown',
    'exUp',
    'exVisible',
    'exInvisible'
    );

    if( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )
    {
        $cmd = $_REQUEST['cmd'];
    }
    else
    {
        $cmd = null;
    }

    if( isset($_REQUEST['label']) && !empty($_REQUEST['label']) )
    {
        $label = $_REQUEST['label'];
    }
    else
    {
        $label = NULL;
    }

    if( isset($_REQUEST['avatar']) && !empty($_REQUEST['avatar']) )
    {
        $avatar = $_REQUEST['avatar'];
    }
    else
    {
        $avatar = 'smile';
    }

// }}}

// {{{ MODEL

    $cssLoader = CssLoader::getInstance();
    $cssLoader->load('desktop','all');
/*
    $jsloader = JavascriptLoader::getInstance();
    $jsloader->load('jquery');

    $htmlHeaders = "\n"
    .   '<script type="text/javascript">' . "\n"
    .   '$(document).ready(function() {' . "\n"
    .   '$(".config legend").addClass("hideul");' . "\n"
    .   '$(".config").find("table").hide().end();' . "\n"
    .   '$(".config").find("legend").click(function() {' . "\n"
    .   '        var answer = $(this).next();' . "\n"
    .   '        if (answer.is(":visible")) {' . "\n"
    .   '            answer.slideUp("fast");' . "\n"
    .   '            $(this).removeClass("showul");' . "\n"
    .   '            $(this).addClass("hideul");' . "\n"
    .   '        } else {' . "\n"
    .   '            answer.slideDown("slow");' . "\n"
    .   '            $(this).removeClass("hideul");' . "\n"
    .   '            $(this).addClass("showul");' . "\n"
    .   '        }' . "\n"
    .   '    });' . "\n"
    .   '});' . "\n"
    .   '</script>' . "\n"
    ;

    $claroline->display->header->addHtmlHeader($htmlHeaders);
 */
// }}}

// {{{ CONTROLLER

    $PortletConfig = new PortletConfig();

    if( $cmd == 'exUp' )
    {
        $PortletConfig->move_portlet( $label, 'up' );
    }

    if( $cmd == 'exDown' )
    {
        $PortletConfig->move_portlet( $label, 'down' );
    }

    if( $cmd == 'exVisible' )
    {


        $PortletConfig->setVisible();

        $PortletConfig->saveVisibility($label);
    }

    if( $cmd == 'exInvisible' )
    {
        $PortletConfig->setInvisible();

        $PortletConfig->saveVisibility($label);
    }

    // class porletInsertConfigDB
    $porletInsertConfigDB = new porletInsertConfigDB();
    $portletList = $porletInsertConfigDB->loadAll();

    // Configuration des portlets
    $outPortlet = '';
    $outPortlet .= '<div class="config">' . "\n";
    $outPortlet .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
    .    '<thead>' . "\n"
    .    '<tr class="headerX" align="center" valign="top">' . "\n"
    .    '<th>' . get_lang('Title') . '</th>' . "\n"
    .    '<th>' . get_lang('Visibility') . '</th>' . "\n"
    .    '<th colspan="2">' . get_lang('Order') . '</th>' . "\n"
    .    '</tr>' . "\n"
    .    '</thead>' . "\n"
    .    '<tbody>' . "\n"
    ;

    if( is_array($portletList) && !empty($portletList) )
    {
        $portletListSize = count($portletList);
        $i = 0;
        
        foreach ( $portletList as $portlet )
        {
            $i++;
            $outPortlet .= "\n"
            .      '<tr>' . "\n"
            .      '<td>' . $portlet['name'] . '</td>' . "\n"
            ;
    
            if( $portlet['visibility'] == 'visible' )
            {
                $outPortlet .= "\n"
                .    '<td align="center">' . "\n"
                .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exInvisible&amp;label=' . $portlet['label'] . '">'
                .    claro_html_icon('visible')
                .    '</a>' . "\n"
                .    '</td>' . "\n"
                ;
            }
            else
            {
                $outPortlet .= "\n"
                .    '<td align="center">' . "\n"
                .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exVisible&amp;label=' . $portlet['label'] . '">'
                .    claro_html_icon('invisible')
                .    '</a>' . "\n"
                .    '</td>' . "\n"
                ;
            }
    
            if( $i > 1 )
            {
                $outPortlet .= '<td><a href="' . $_SERVER['PHP_SELF'] . '?label=' . $portlet['label'] . '&amp;cmd=exUp"><img src="' . get_icon_url('up') . '" alt="' . get_lang('up') . '" /></a></td>' . "\n";
            }
            else
            {
                $outPortlet .= '<td>&nbsp;</td>' . "\n";
            }
            
            if( $i < $portletListSize )
            {
                $outPortlet .= '<td><a href="' . $_SERVER['PHP_SELF'] . '?label=' . $portlet['label'] . '&amp;cmd=exDown"><img src="' . get_icon_url('down') . '" alt="' . get_lang('down') . '" /></a></td>' . "\n";
            }
            else
            {
                $outPortlet .= '<td>&nbsp;</td>' . "\n";
            }
            
            $outPortlet .= '</tr>' . "\n";
            
        }
    }

    $outPortlet .= "\n"
    .    '</tbody>' . "\n"
    .    '</table>' . "\n"
    .    '</div>' . "\n\n"
    ;
// }}}

// {{{ VIEW

    $output = '';

    $moduleName = get_lang('My Desktop');
    ClaroBreadCrumbs::getInstance()->append( $moduleName, './index.php' );
    ClaroBreadCrumbs::getInstance()->append( get_lang('Configuration') );

    $output .= claro_html_tool_title($moduleName);

    $output .= $dialogBox->render();

    $portletrightmenu = new portletrightmenu();

    $output .= $portletrightmenu->render();

    //$output .= '<div class="portlet"><div class="portletTitle">Configuration des portlets</div><div class="portletContent">' . $outPortlet . '</div></div>';
    $output .= $outPortlet;

    $output .= '<div style="clear:both"></div>';

    $claroline->display->body->appendContent($output);

    echo $claroline->display->render();

// }}}
?>