<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * SSO server
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2008 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     sso
     */

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses ('thirdparty/nusoap/nusoap.lib');
    
    class SsoService
    {
        const serviceName = 'SSO';
        
        protected $server;
        
        public function __construct()
        {
            $server = new nusoap_server();
    
            $server->register( 'SsoService.getUserInfoFromCookie',
               array('auth'   => 'xsd:string',
                     'cookie' => 'xsd:string',
                     'cid'    => 'xsd:string',
                     'gid'    => 'xsd:string' ) );
        }
        
        public function run()
        {     
            $server->service(file_get_contents("php://input"));
        }
        
        public function getUserInfoFromCookie( $auth, $cookie, $cid, $gid )
        {
        }
    }
?>