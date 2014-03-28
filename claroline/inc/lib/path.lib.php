<?php // $Id$

/**
 * CLAROLINE
 *
 * Built url and system paths.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      see 'credits' file
 * @since       Claroline 1.8.3
 * @package     kernel.core
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
 * @author Christophe Gesche <moosh@claroline.net>
 * @return path
 */
function get_path($pathKey)
{
    static $pathList = array() ;

    if ( count($pathList) == 0 )
    {
        $rootPath = dirname(dirname(dirname(__DIR__)));

        // root path
        $pathList['rootSys'] =  $rootPath . '/' ;
        $pathList['includePath'] =  $rootPath . '/claroline/inc' ;
        $pathList['incRepositorySys'] =  $rootPath . '/claroline/inc' ;

        // root url
        $pathList['url'] =  get_conf('urlAppend');
        $pathList['rootWeb'] =  get_conf('rootWeb') ;

        // append path
        $pathList['imgRepositoryAppend'] =  'web/img/';
        $pathList['coursesRepositoryAppend'] =  get_conf('coursesRepositoryAppend','courses/');

        // root path + append path
        $pathList['clarolineRepositorySys'] =  $rootPath . '/claroline/' ;
        $pathList['coursesRepositorySys'] =  $rootPath . '/' . $pathList['coursesRepositoryAppend'] ;
        $pathList['rootAdminSys'] =  $rootPath . '/claroline/admin/' ;
        $pathList['imgRepositorySys'] =  $rootPath . '/' . $pathList['imgRepositoryAppend'];

        // root url + append path
        $pathList['coursesRepositoryWeb'] =  $pathList['url'] . '/' . $pathList['coursesRepositoryAppend'];
        $pathList['imgRepositoryWeb'] = $pathList['url']  . '/' . $pathList['imgRepositoryAppend'];
        $pathList['clarolineRepositoryWeb'] =  $pathList['url'] . '/claroline/';
        $pathList['rootAdminWeb'] =  $pathList['url'] . '/claroline/admin/';

        // path special case
        $pathList['garbageRepositorySys'] =  get_conf('garbageRepositorySys');
        $pathList['mysqlRepositorySys'] =  get_conf('mysqlRepositorySys');
        
        // user folder
        $pathList['userRepositorySys'] = $pathList['rootSys'].'platform/users/';
        $pathList['userRepositoryWeb'] = $pathList['url'].'/platform/users/';
    }

    if ( array_key_exists( $pathKey, $pathList ) )
    {
        return $pathList[$pathKey];
    }
    else
    {
        trigger_error('Claroline : Unknown path name "' . $pathKey . '" passed to get_path function' , E_USER_NOTICE);
        return false;
    }

}

/**
 * Get platform path url : return get_path('url') if not empty, 
 *  '/' if get_path('url') is empty.
 * Use this instead of in get_path('url') in claro_redirect
 * @return string platform base url without domain, port...
 * @since Claroline 1.11.0
 */
function get_platform_base_url()
{
    $url = get_path('url');
    
    if ( empty( $url ) )
    {
        return '/';
    }
    else
    {
        return $url;
    }
}
