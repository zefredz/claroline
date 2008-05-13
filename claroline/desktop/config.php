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

// }}}

// {{{ CONTROLLER

    $PortletConfig = new PortletConfig();
    $porletConfigAvatar = new porletConfigAvatar();
    
    if( $cmd == 'exAvatar' )
    {
        if( $porletConfigAvatar->update( $avatar ) )
        {
            $dialogBox->success( get_lang('Avatar changed !') );
        }
        else
        {
            $dialogBox->error( get_lang('Avatar not changed !') );
        }
    }

    if( $cmd == 'rqAvatar' )
    {
        $htmlConfirmDelete = get_lang('Are you sure to change avatar ?')
        .     '<br /><br />'
        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exAvatar&amp;avatar='.$_REQUEST['selectAvatar'].'">' . get_lang('Yes') . '</a>'
        .    '&nbsp;|&nbsp;'
        .    '<a href="' . $_SERVER['PHP_SELF'] . '">' . get_lang('No') . '</a>'
        ;

        $dialogBox->question( $htmlConfirmDelete );
    }
    
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



/*
    $i = 1;
    
    $outPortlet = '';
    
    $allowedExtensions = array('.php');

    $path = dirname( __FILE__ ) . '/lib/portlet';
    
    
    try
    {
        $fileFinder = new ExtensionFileFinder( $path, '.class.php', false );

        foreach ( $fileFinder as $file )
        {
            // l'objet $file est de class SplFileInfo
            // pour la doc voir : http://www.php.net/~helly/php/ext/spl/ 
            
            $fileName = $file->getFilename();
            $filePath = $file->getRealPath();

            // add elt to array
            require_once $filePath;
            
            // add className to array
            $pos = strpos($fileName, '.');
            $className = substr($fileName, '0', $pos);
            
            // class porletInsertConfigDB
            $porletInsertConfigDB = new porletInsertConfigDB();
            
            // load db
            $portletInDB = $porletInsertConfigDB->load($className);

            // si present en db on passe
            if( !$portletInDB )
            {
                if( class_exists($className) )
                {
                    // insert db
                    $porletInsertConfigDB->setLabel($className);
                    $porletInsertConfigDB->setName($className);
                    $porletInsertConfigDB->setRank($i);
                    $porletInsertConfigDB->setActivated(true);
                    $porletInsertConfigDB->save();
                }
            }
                        
            $i++;
        }     
    }
    catch (Exception $e)
    {
        $dialogBox->error( get_lang('Error to load portlet') );
        pushClaroMessage($e->__toString());
    }
*/
    
    // class porletInsertConfigDB
    $porletInsertConfigDB = new porletInsertConfigDB();
    $portletList = $porletInsertConfigDB->loadAll();
    
    // Configuration des portlets
    $outPortlet = '';    
    $outPortlet .= '<fieldset class="config">';
    $outPortlet .= '<legend>' . get_lang('Configuration des portlets') . '</legend>';
    
    $outPortlet .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
    .    '<thead>' . "\n"
    .      '<tr class="headerX" align="center" valign="top">' . "\n"
    .        '<th>' . get_lang('Nom') . '</th>' . "\n"
    .       '<th>' . get_lang('Visibility') . '</th>' . "\n"
    .       '<th colspan="2">' . get_lang('Ordre') . '</th>' . "\n"
    .      '</tr>' . "\n"
    .    '</thead>' . "\n"
    .    '<tbody>' . "\n"
    ;
    
    foreach ( $portletList as $portlet )
    {
        $outPortlet .= "\n"
        .      '<tr>' . "\n"
        .       '<td>' . $portlet['name'] . '</td>' . "\n"
        ;
        
            if( $portlet['visibility'] == 'VISIBLE' )
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
        
        $outPortlet .= "\n"
        .       '<td><a href="' . $_SERVER['PHP_SELF'] . '?label=' . $portlet['label'] . '&amp;cmd=exUp"><img src="' . get_icon_url('up') . '" alt="' . get_lang('up') . '" /></a></td>' . "\n"
        .       '<td><a href="' . $_SERVER['PHP_SELF'] . '?label=' . $portlet['label'] . '&amp;cmd=exDown"><img src="' . get_icon_url('down') . '" alt="' . get_lang('down') . '" /></a></td>' . "\n"
        .      '</tr>' . "\n"
        ;
    }
    
    $outPortlet .= "\n"
    .    '</tbody>' . "\n"
    .    '</table>' . "\n"
    ;

    $outPortlet .= '</fieldset>';
    $outPortlet .= '</form>' . "\n";
    
    // Configuration des avatars
    $outPortlet .= '<form action="' . $_SERVER['PHP_SELF'] . '">' . "\n";
    
    $outPortlet .= '<input type="hidden" name="cmd" value="rqAvatar" />' . "\n";
    
    $outPortlet .= '<fieldset class="config avatar">';
    $outPortlet .= '<legend>' . get_lang('Configuration des avatars') . '</legend>';

    $outPortlet .= "\n"
    .    '<table class="claroTable" width="100%" border="0" cellspacing="2">' . "\n"
    .    '<tbody>' . "\n"
   
    .      '<tr>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-angel') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="angel" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-crying') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="crying" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-devilish') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="devilish" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-glasses') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="glasses" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-grin') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="grin" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-kiss') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="kiss" />' . "\n"
    .      '</td>' . "\n"
    .      '</tr>' . "\n"
    
    .      '<tr>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-monkey') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="monkey" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-sad') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="sad" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-smile') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="smile" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-smile-big') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="smile-big" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-surprise') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="surprise" />' . "\n"
    .      '</td>' . "\n"
    .      '<td>' . "\n"
    .       '<img src="' . get_icon_url('Avatar-wink') . '" alt="' . get_lang('avatar') . '" />' . "\n"
    .       '<input type="radio" name="selectAvatar" value="wink" />' . "\n"
    .      '</td>' . "\n"
    .      '</tr>' . "\n"
    
    .      '<tr>' . "\n"
    .      '<td colspan="6">' . "\n"
    .       '<input type="submit" value="' . get_lang('Save') . '" />' . "\n"
    .      '</td>' . "\n"
    .      '</tr>' . "\n"
    
    
    .    '</tbody>' . "\n"
    .    '</table>' . "\n"
    ;
    
    $outPortlet .= '</fieldset>';

// }}}

// {{{ VIEW    

    $output = '';
    
    $moduleName = get_lang('My Desktop');
    $interbredcrump[]= array ('url' => './index.php', 'name' => $moduleName);
    $interbredcrump[]= array ('url' => NULL, 'name' => get_lang('Configuration'));

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