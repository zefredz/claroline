<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
* CLAROLINE
*
* User desktop index
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

// {{{ SCRIPT INITIALISATION

    // reset course and groupe
    $cidReset = TRUE;
    $gidReset = TRUE;
    $uidRequired = TRUE;

    // load Claroline kernel
    require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
    
    if( ! claro_is_user_authenticated() ) claro_disp_auth_form();
    
    // load libraries
    require_once dirname(__FILE__) . '/lib/portlet.lib.php';
    require_once dirname(__FILE__) . '/lib/portletRightMenu.lib.php';
    require_once dirname(__FILE__) . '/lib/portletInsertConfigDB.lib.php';
    
    uses('user.lib', 'utils/finder.lib');

    $dialogBox = new DialogBox();

// }}}

// {{{ MODEL

    $cssLoader = CssLoader::getInstance();
    $cssLoader->load('desktop','all');

// }}}

// {{{ CONTROLLER

    $i = 1;

    $outPortlet = '';

    $allowedExtensions = array('.php');

    $path = dirname( __FILE__ ) . '/lib/portlet';

    try
    {
        $fileFinder = new Claro_FileFinder_Extension( $path, '.class.php', false );

        foreach ( $fileFinder as $file )
        {
            $fileName = $file->getFilename();
            $filePath = $file->getRealPath();

            // add elt to array
            require_once $filePath;

            // add className to array
            $pos = strpos($fileName, '.');
            $className = substr($fileName, '0', $pos);

            $portletInsertConfigDB = new PortletInsertConfigDB();

            // load db
            $portletInDB = $portletInsertConfigDB->load($className);

            // si present en db on passe
            if( !$portletInDB )
            {
                if( class_exists($className) )
                {
                    // insert db
                    $portletInsertConfigDB->setLabel($className);
                    $portletInsertConfigDB->setName($className);
                    $portletInsertConfigDB->setRank($i);
                    $portletInsertConfigDB->save();
                }
            }

            $i++;
        }
    }
    catch (Exception $e)
    {
        $dialogBox->error( get_lang('Cannot load portlets') );
        pushClaroMessage($e->__toString());
    }

    $portletList = $portletInsertConfigDB->loadAll( true );

    foreach ( $portletList as $portlet )
    {
        // load portlet
        if( !class_exists($portlet['label']) ) continue;
        $portlet = new $portlet['label']();

        if( !method_exists($portlet, 'render') ) continue;
        $outPortlet .= $portlet->render();
    }


// }}}

// {{{ VIEW

    $output = '';

    $nameTools = get_lang('My Desktop');

    $output .= claro_html_tool_title($nameTools);

    $output .= $dialogBox->render();

    $portletrightmenu = new portletrightmenu();

    $output .= $portletrightmenu->render();

    $output .= $outPortlet;

    $claroline->display->body->appendContent($output);

    echo $claroline->display->render();

// }}}
?>