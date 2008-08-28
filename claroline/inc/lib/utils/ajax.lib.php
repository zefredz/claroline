<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Ajax utility functions and classes
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     core.linker
 */

class Json_Response
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    
    protected $type, $body;
    
    public function __construct( $body, $type = self::SUCCESS )
    {
        $this->body = $body;
        $this->type = $type;
    }
    
    public function toJson()
    {
        $response = $response = array(
            'responseType' => $this->type,
            'responseBody' => $this->body
        );
        
        return claro_utf8_encode( json_encode( $response ) );
    }
}

class Json_Error extends Json_Response
{
    public function __construct( $error )
    {
        $this->body = $error;
        $this->type = Json_Response::ERROR;
    }
}