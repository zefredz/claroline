<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    require '../inc/claro_init_global.inc.php';

    $nameTools = $langWiki;
    $hide_banner=TRUE;
    
    require_once $includePath."/claro_init_header.inc.php";
    
    echo $langWikiHelpContent;
    
    $hide_footer = true;
    require_once $includePath."/claro_init_footer.inc.php";
    
?>