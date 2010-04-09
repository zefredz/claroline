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

require_once dirname(__FILE__) . '/input.lib.php';

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
        
        claro_utf8_encode_array( $response );
        
        return json_encode( $response );
    }
}

class Json_Error extends Json_Response
{
    public function __construct( $error )
    {
        parent::__construct( $error, Json_Response::ERROR );
    }
}

class Json_Exception extends Json_Error
{
    public function __construct( $e )
    {
        $errorArr = array(
            'errno' => $e->getCode(),
            'error' => $e->getMessage()
        );
        
        if ( claro_debug_mode() )
        {
            $errorArr['trace'] = $e->getTraceAsString();
            $errorArr['file'] = $e->getFile();
            $errorArr['line'] = $e->getLine();
        }
        
        parent::__construct( $errorArr );
    }
}

class Ajax_Request
{
    protected $klass, $method, $params;
    
    public function __construct( $class, $method, $params = array() )
    {
        $this->klass = $class; 
        $this->method = $method;
        $this->params = $params;
    }
    
    public function getClass()
    {
        return $this->klass;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getParameters()
    {
        return $this->params;
    }

    public function  __toString()
    {
        return $this->getClass().'::'.$this->getMethod().'('.implode(',',$this->getParameters()).')';
    }

    public static function getRequest( Claro_Input $userInput )
    {
        $request = new self(
            $userInput->getMandatory('class'),
            $userInput->getMandatory('method'),
            $userInput->get('parameters', array())
        );

        return $request;
    }
}

interface Ajax_Remote_Service
{

}

class Ajax_Remote_Service_Broker
{
    protected $register = array();

    public function register( $className, Ajax_Remote_Service $object, $methods = null, $overwrite = false )
    {
        if ( ! isset($this->register[$className]) || $overwrite === true )
        {
            $this->register[$className] = array(
                'object' => $object,
                'methods' => $methods
            );
        }
        else
        {
            throw new Exception ("Service Error : try to overwrite class {$className}");
        }
    }

    public function handle( Ajax_Request $request )
    {
        try
        {
            if ( isset ($this->register[$request->getClass()]) )
            {
                if (
                    in_array( $request->getMethod(), $this->register[$request->getClass()]['methods'] )
                    || is_null($this->register[$request->getClass()]['methods'])
                )
                {
                    if ( is_callable( array(
                            $this->register[$request->getClass()]['object'],
                            $request->getMethod() )
                        )
                    )
                    {
                        $response = call_user_method_array(
                            $request->getMethod(),
                            $this->register[$request->getClass()]['object'],
                            $request->getParameters() );

                        return new Json_Response(array(
                            'class' => $request->getClass(),
                            'method' => $request->getMethod(),
                            'params' => $request->getParameters(),
                            'reponse' => $response
                        ));
                    }
                    else
                    {
                        throw new Exception( "Method not callable {$request->getMethod()} in class {$request->getClass()}" );
                    }
                }
                else
                {
                    throw new Exception( "Method not found {$request->getMethod()} in class {$request->getClass()}" );
                }
            }
            else
            {
                throw new Exception( "Class not found {$request->getClass()}" );
            }
        }
        catch ( Exception $e )
        {
            return new Json_Exception($e);
        }
    }
}
