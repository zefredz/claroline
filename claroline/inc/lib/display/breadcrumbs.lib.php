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
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     CORE
     */
     
    class BreadCrumbs implements Display
    {
        private $breadCrumbs = array();
        
        public function render()
        {
            if ( $this->isEmpty() )
            {
                return '';
            }
            
            $lastNode = count( $this->breadCrumbs ) - 1;
            $currentNode = 0;

            $out = '';

            $nodeList = array();

            foreach ( $this->breadCrumbs as $node )
            {
                $nodeStr = '';

                if ( $currentNode == $lastNode )
                {
                    $nodeStr .= '<span class="lastBreadCrumbsNode">';
                }
                elseif ( $currentNode == 0 )
                {
                    $nodeStr .= '<span class="firstBreadCrumbsNode">';
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
        
        public function append( $name, $url = null )
        {
            $this->breadCrumbs[] = new BreadCrumbsNode( $name, $url );
        }

        public function prepend( $name, $url = null )
        {
            array_unshift ( $this->breadCrumbs, new BreadCrumbsNode( $name, $url ) );
        }

        public function appendNode( $node )
        {
            $this->breadCrumbs[] = $node;
        }

        public function prependNode( $node )
        {
            array_unshift ( $this->breadCrumbs, $node );
        }
        
        public function size()
        {
            return count( $this->breadCrumbs );
        }
        
        public function isEmpty()
        {
            return empty( $this->breadCrumbs );
        }
    }
    
    class BreadCrumbsNode
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

    class ClaroBreadCrumbs extends BreadCrumbs
    {
        private static $instance = false;
        // private $breadCrumbs = array();
        
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
            $this->init();
            
            return parent::render();
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
                
                $this->appendNode( new BreadCrumbsNode( $name, $url ) );
            }
        }
        
        private function autoPrepend()
        {
            if ( claro_is_in_a_group() )
            {
                $this->prependNode( new BreadCrumbsNode( claro_get_current_group_data('name')
                    , get_module_url('CLGRP') . '/group_space.php?cidReq='
                        . htmlspecialchars(claro_get_current_course_id())
                        .'&gidReq=' . (int) claro_get_current_group_id() ) );
                $this->prependNode( new BreadCrumbsNode( get_lang('Groups')
                    , get_module_url('CLGRP') . '/index.php?cidReq='
                        . htmlspecialchars(claro_get_current_course_id()) ) );
            }
            
            if ( claro_is_in_a_course() )
            {
                $this->prependNode( new BreadCrumbsNode( claro_get_current_course_data('officialCode')
                    , get_path('clarolineRepositoryWeb') . 'course/index.php?cid='
                        . htmlspecialchars(claro_get_current_course_id()) ) );
            }
                
            $this->prependNode( new BreadCrumbsNode( get_conf('siteName')
                , get_path('url') . '/index.php'
                , 'home.gif' ) );
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
            if ( ! ClaroBreadCrumbs::$instance )
            {
                ClaroBreadCrumbs::$instance = new ClaroBreadCrumbs;
            }

            return ClaroBreadCrumbs::$instance;
        }
    }
?>