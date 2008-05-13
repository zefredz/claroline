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
    uses('utils/finder.lib.php');

    // users authentified 
    if( ! claro_is_user_authenticated() ) claro_disp_auth_form();
    
	$is_allowedToEdit = claro_is_allowed_to_edit();

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
    
    // avatar par defaut
    //$porletConfigAvatar = new porletConfigAvatar();
    //$porletConfigAvatar->save();
    
    // affichage des portlets
    
    $portletList = $porletInsertConfigDB->loadAll( true );
    
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