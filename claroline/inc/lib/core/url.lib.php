<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Url manipulation library
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     KERNEL
 */

/**
 * Class to manipulate Urls
 */
class Url
{
    protected $url = array(
        'scheme' => '',
        'host' => '',
        'port' => '',
        'user' => '',
        'pass' => '',
        'path' => '',
        'query' => array(),
        'fragment' => ''
    );
    

    /**
     * Constructor
     * @param   string url base url (use PHP_SELF if missing)
     */
    public function __construct( $url = '' )
    {
        $url = empty($url)
            ? $_SERVER['PHP_SELF']
            : $url
            ;
            
        $url = htmlspecialchars_decode( $url );
        
        $urlArr = @parse_url( $url );
        
        $queryArr = array();
        
        if ( !empty($urlArr['query']) )
        {
            @parse_str($urlArr['query'], $queryArr );
        }
        
        unset ($urlArr['suery']);
        
        $this->url = array_merge( $this->url, $urlArr );
        $this->url['query'] = $queryArr;
    }
    
    public function __get( $name )
    {
        if ( isset( $this->url[$name] ) )
        {
            return $this->url[$name];
        }
        else
        {
            return null;
        }
    }
    
    public function __set( $name, $value )
    {
        if ( isset( $this->url[$name] ) )
        {
            $this->url[$name] = $value;
        }
    }

    /**
     * Relay Claroline current Url context in urls
     */
    public function relayCurrentContext()
    {
        /* $context = Claro_Context::getCurrentUrlContext();
        
        if ( array_key_exists( 'cid', $context )
            && ! array_key_exists( 'cidReq', $context ) )
        {
            $context['cidReq'] = $context['cid'];
            unset( $context['cid'] );
        }

        if ( array_key_exists( 'gid', $context )
            && ! array_key_exists( 'gidReq', $context ) )
        {
            $context['gidReq'] = $context['gid'];
            unset( $context['gid'] );
        } */
        
        $this->addParamList( Claro_Context::getCurrentUrlContext() );
    }
    
    /**
     * Relay given Url context in urls
     * @param   array context
     */
    public function relayContext( $context )
    {
        $this->addParamList( $context );
    }

    /**
     * Add a list of parameters to the current url
     * @param   array paramList associative array of parameters name=>value
     */
    public function addParamList( $paramList, $overwrite = false )
    {
        if ( !empty( $paramList ) && is_array( $paramList ) )
        {
            foreach ( $paramList as $name => $value )
            {
                if ( !$overwrite && !empty( $value ) )
                {
                    $this->addParam( $name, $value );
                }
                elseif ( $overwrite )
                {
                    $this->replaceParam( $name, $value, true );
                }
            }
        }
    }

    /**
     * Add one parameter to the current url
     * @param   string name parameter name
     * @param   string value parameter value
     */
    public function addParam( $name, $value )
    {
        if ( !array_key_exists($name, $this->url['query'] ) )
        {
            $this->url['query'][$name] = $value;
        }
    }

    /**
     * Replace the value of the given parameter with the given value
     * @param   string name parameter name
     * @param   string value parameter value
     * @param   boolean addIfMissing add the parameter if missing (default false)
     * @return  boolean true if replaced or added, else false
     */
    public function replaceParam( $name, $value, $addIfMissing = false )
    {
        if ( $addIfMissing || array_key_exists( $name, $this->url['query'] ) )
        {
            $this->addParam( $name, $value );
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Remove the given parameter
     * @param   string name parameter name
     * @return  boolean true if removed, else false
     */
    public function removeParam( $name )
    {
        if ( array_key_exists( $name, $this->url['query'] ) )
        {
            unset( $this->url['query'] );
            return true;
        }
        else
        {
            return false;
        }
    }

    public function toUrl()
    {
        $url = '';
        
        if ( !empty($this->url['scheme']) )
        {
            if ( $this->url['scheme'] != 'mailto' )
            {
                $url .= $this->url['scheme'] . '://';
            }
            else
            {
                $url .= $this->url['scheme'] . ':';
            }
        }
        
        if ( !empty( $this->url['user'] ) )
        {
            $url .= $this->url['user'];
            
            if ( !empty( $this->url['pass'] ) )
            {
                $url .= ":{$this->url['pass']}";
            }
            
            $url .= '@';
        }
        
        if ( !empty ( $this->url['host']))
        {
            $url .= $this->url['host'];
        }
        
        if ( !empty ( $this->url['port']))
        {
            $url .= ':'.$this->url['port'];
        }
        
        if ( !empty ( $this->url['path']))
        {
            $url .= $this->url['path'];
        }
        
        if ( !empty($this->url['query']) )
        {
            $url .= '?' . http_build_query( $this->url['query'] );
        }
        
        if ( !empty ( $this->url['fragment']))
        {
            $url .= '#' . $this->url['fragment'];
        }
        
        return $url;
    }
    
    public static function Contextualize( $url, $context = null )
    {
        $urlObj = new self($url);
        
        if ( empty( $context ) )
        {
            $urlObj->relayCurrentContext();
        }
        else
        {
            $urlObj->relayContext( $context );
        }
        
        return $urlObj->toUrl();
    }
}

/*$url = new Url('http://www.claroline.net?forum_id=3&plop=gnome#4');
$url->addParam('path','/img/blue.gif');

$url->port=3306;

var_Dump($url->toUrl());*/
