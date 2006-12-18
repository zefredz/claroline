<?php // $Id$
    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * CLAROLINE
     *
     * @version 1.8 $Revision$
     *
     * @copyright 2001-2006 Universite catholique de Louvain (UCL)
     *
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
    
    require '../inc/claro_init_global.inc.php';

    $nameTools = get_lang("Wiki");
    $hide_banner=TRUE;
    
    $htmlHeadXtra[] =
        '<style type="text/css">
            dt{font-weight:bold;margin-top:5px;}
        </style>';
    
    require_once get_path('incRepositorySys')."/claro_init_header.inc.php";
    
    $help = ( isset( $_REQUEST['help'] ) ) ? $_REQUEST['help'] : 'syntax';
    
    echo '<center><a href="#" onclick="window.close()">'.get_lang("Close window").'</a></center>' . "\n";
    
    switch( $help )
    {
        case 'syntax':
        {
            echo get_block('blockWikiHelpSyntaxContent');
            break;
        }
        case 'admin':
        {
            echo get_block('blockWikiHelpAdminContent');
            break;
        }
        default:
        {
            echo '<center><h1>'.get_lang('Wrong parameters').'</h1></center>';
        }
    }
    
    echo '<center><a href="#" onclick="window.close()">'.get_lang("Close window").'</a></center>' . "\n";
    
    $hide_footer = true;
    require_once get_path('incRepositorySys')."/claro_init_footer.inc.php";
    
?>