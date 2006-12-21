<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * built url and system paths
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author see 'credits' file
 *
 * @since claroline 1.8.3
 *
 * @package KERNEL
 *
 */


/**
Http://www.domain.tld/whereisMyCampus/claroline/blah

$rootWeb    = Http://www.domain.tld/whereisMyCampus/claroline/blah
$hostWeb    = Http://www.domain.tld
$urlAppend  = /whereisMyCampus/claroline/blah
$clarolineRepositorySys = Http://www.domain.tld/whereisMyCampus/claroline

*/
/**
 * Return a common path of claroline
 *
 * @param string $pathKey key name of the path ( varname in previous version of claroline)
 * @author Christophe Gesché <moosh@claroline.net>
 * @return path
 */
function get_path($pathKey)
{
    switch ($pathKey)
    {
        case 'includePath'            : return dirname( dirname(__FILE__) );
        case 'incRepositorySys'       : return dirname( dirname(__FILE__) );

        case 'rootSys' : return get_conf('rootSys') ;
        case 'rootWeb' : return get_conf('rootWeb') ;

        // private translation / Dont use theses paths
        case 'imgRepositoryAppend'       : return 'img/'; // <-this line would be editable in claroline 1.7
        case 'clarolineRepositoryAppend' : return get_conf('clarolineRepositoryAppend','claroline/');
        case 'coursesRepositoryAppend'   : return get_conf('coursesRepositoryAppend','courses/');
        case 'rootAdminAppend'           : return get_conf('rootAdminAppend','admin/');


        case 'clarolineRepositorySys' : return get_conf('rootSys') . get_conf('clarolineRepositoryAppend','claroline/');
        case 'clarolineRepositoryWeb' : return get_conf('urlAppend') . '/' . get_conf('clarolineRepositoryAppend','claroline/');
        case 'userImageRepositorySys' : return get_conf('rootSys') . get_conf('userImageRepositoryAppend','platform/img/users/');
        case 'userImageRepositoryWeb' : return get_conf('urlAppend') . '/' . get_conf('userImageRepositoryAppend','platform/img/users/');
        case 'coursesRepositorySys'   : return get_conf('rootSys') . get_conf('coursesRepositoryAppend','courses/');
        case 'coursesRepositoryWeb'   : return get_conf('urlAppend') . '/' . get_conf('coursesRepositoryAppend','courses/');
        case 'rootAdminSys'           : return get_conf('clarolineRepositorySys') . get_conf('rootAdminAppend','admin/');
        case 'rootAdminWeb'           : return get_conf('clarolineRepositoryWeb') . get_conf('rootAdminAppend','admin/');
        case 'imgRepositorySys'       : return get_conf('clarolineRepositorySys') . get_conf('imgRepositoryAppend');
        case 'imgRepositoryWeb'       : return get_conf('clarolineRepositoryWeb') . get_conf('imgRepositoryAppend');
        case 'url'                    : return get_conf('urlAppend');

        default : trigger_error('Claroline : Unknown path name "' . $pathKey . '" passed to get_path function' , E_USER_NOTICE);

        return false;
    }

}

?>
