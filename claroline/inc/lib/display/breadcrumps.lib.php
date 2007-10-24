<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * Breadcrumps
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     CORE
     */

    class ClaroBreadCrumps implements Display
    {
        private static $instance = false;
        private $breadCrumps = array();
        
        private function __construct()
        {
        }
        
        public function init()
        {
            $this->_compatVars();
            $this->autoPrepend();
            $this->autoAppend();
        }
        
        public function render()
        {
            $lastNode = count( $this->breadCrumps ) - 1;
            $currentNode = 0;
            
            $out = '';
            
            $nodeList = array();

            foreach ( $this->breadCrumps as $node )
            {
                $nodeStr = '';
                
                if ( $currentNode == $lastNode )
                {
                    $nodeStr .= '<span class="lastBCNode">';
                }
                elseif ( $currentNode == 0 )
                {
                    $nodeStr .= '<span class="firstBCNode">';
                }
                
                // var_dump( $node );

                $nodeStr .= $node->render();
                
                if ( $currentNode == $lastNode
                    || $currentNode == 0 )
                {
                    $nodeStr .= '</span>';
                }
                
                $nodeList[] = $nodeStr;
                
                $currentNode++;
            }
            
            $out .= implode ( "&nbsp;&gt;&nbsp;", $nodeList );
            
            $out .= "\n";
            
            return $out;
        }
        
        private function autoAppend()
        {
            if ( array_key_exists( 'nameTools', $GLOBALS ) )
            {
                $name = $GLOBALS['nameTools'];
                
                if ( array_key_exists( 'noPHP_SELF', $GLOBALS )
                    && $GLOBALS['noPHP_SELF'] )
                {
                    $url = null;
                }
                elseif ( array_key_exists( 'noQUERY_STRING', $GLOBALS )
                    && $GLOBALS['noQUERY_STRING'] )
                {
                    $url = $_SERVER['PHP_SELF'];
                }
                else
                {
                    if ( ! array_key_exists( 'noQUERY_STRING', $_SERVER ) )
                    {
                        $url = $_SERVER['PHP_SELF'];
                    }
                    else
                    {
                        $url  = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
                    }
                }
                
                $this->appendNode( new BCNode( $name, $url ) );
            }
        }
        
        private function autoPrepend()
        {
            if ( claro_is_in_a_group() )
            {
                $this->prependNode( new BCNode( claro_get_current_group_data('name')
                    , get_module_url('CLGRP') . '/group_space.php?cidReq='
                        . htmlspecialchars(claro_get_current_course_id())
                        .'&gidReq=' . (int) claro_get_current_group_id() ) );
                $this->prependNode( new BCNode( get_lang('Groups')
                    , get_module_url('CLGRP') . '/index.php?cidReq='
                        . htmlspecialchars(claro_get_current_course_id()) ) );
            }
            
            if ( claro_is_in_a_course() )
            {
                $this->prependNode( new BCNode( claro_get_current_course_data('officialCode')
                    , get_path('clarolineRepositoryWeb') . 'course/index.php?cid='
                        . htmlspecialchars(claro_get_current_course_id()) ) );
            }
                
            $this->prependNode( new BCNode( get_conf('siteName')
                , get_path('url') . '/index.php'
                , 'home.gif' ) );
        }
        
        public function append( $name, $url = null )
        {
            $this->breadCrumps[] = new BCNode( $name, $url );
        }

        public function prepend( $name, $url = null )
        {
            array_unshift ( $this->breadCrumps, new BCNode( $name, $url ) );
        }
        
        public function appendNode( $node )
        {
            $this->breadCrumps[] = $node;
        }
        
        public function prependNode( $node )
        {
            array_unshift ( $this->breadCrumps, $node );
        }
        
        private function _compatVars()
        {
            if ( array_key_exists( 'interbredcrump', $GLOBALS )
                && is_array( $GLOBALS['interbredcrump'] ) )
            {
                // var_dump( $GLOBALS['interbredcrump'] );
                foreach ( $GLOBALS['interbredcrump'] as $node )
                {
                    $this->append( $node['name'], $node['url'] );
                }
            }
        }
        
        public static function getInstance()
        {
            if ( ! ClaroBreadCrumps::$instance )
            {
                ClaroBreadCrumps::$instance = new ClaroBreadCrumps;
            }

            return ClaroBreadCrumps::$instance;
        }
    }
    
    class BCNode
    {
        private $name, $url, $icon;
        
        public function __construct( $name, $url = null, $icon = null )
        {
            $this->icon = $icon;
            $this->name = $name;
            $this->url = $url;
        }
        
        public function render()
        {
            $nodeHtml = '';
            
            if ( ! empty( $this->url ) )
            {
                $nodeHtml .= '<a href="'.$this->url.'"  target="_top">';
            }
            
            if ( ! empty( $this->icon ) )
            {
                $nodeHtml .= claro_html_icon( 'home', null, null );
            }
            
            $nodeHtml .= htmlspecialchars( $this->name );
            
            // var_dump( $this->name );
            
            if ( ! empty( $this->url ) )
            {
                $nodeHtml .= '</a>';
            }
            
            return $nodeHtml;
        }
    }
?>