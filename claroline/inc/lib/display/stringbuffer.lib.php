<?php

// $Id$

/**
 * String buffer
 *
 * @version Claroline 1.12 $Revision$
 * @copyright (c) 2001-2014 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package kernel.display
 * @author Frederic Minne <zefredz@claroline.net>
 * @todo move to Claroline kernel
 */

/**
 * String buffer class to easily convert Claroline page to Claroline webservice
 */
class Claro_StringBuffer implements Display
{
    private $out;
    
    /**
     * 
     * @param string $str optional base string
     */
    public function __construct( $str = '' )
    {
        $this->out = $str;
    }
    
    /**
     * Add string at the end of the buffer
     * @param string $str
     */
    public function appendContent( $str )
    {
        $this->out .= $str;
    }
    
    /**
     * Add string at the start of the buffer
     * @param string $str
     */
    public function prependContent( $str )
    {
        $this->out .= $str . $this->out;
    }
    
    /**
     * Render string buffer
     * @return string
     */
    public function render()
    {
        return $this->out;
    }
    
    public function __toString ()
    {
        return $this->render();
    }
    
}
