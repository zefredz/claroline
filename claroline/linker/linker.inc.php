<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$ 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @author claroline Team <cvs@claroline.net>
 * @author Renaud Fallier <captren@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */
    // include for the linker 
    require_once dirname(__FILE__).'/resolver.lib.php';
    require_once dirname(__FILE__).'/linker_sql.lib.php';
    require_once dirname(__FILE__).'/CRLTool.php';
    require_once dirname(__FILE__).'/linker.lib.php';
    require_once dirname(__FILE__).'/jpspan.lib.php'; 

    //$jpspanAllowed -> config variable
    $jpspanEnabled = get_conf('jpspanAllowed') && claro_is_jpspan_enabled();
    
    //for debugging : disabled jpspan 
    //$jpspanEnabled = false; 
    if( $jpspanEnabled )
    {
        require_once dirname(__FILE__)."/linker_jpspan.lib.php";
    }
    else
    {
        require_once dirname(__FILE__)."/linker_popup.lib.php";
    }

?>
