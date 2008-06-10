<?php // $Id: pluginRegistry.lib.php 373 2007-12-12 13:12:04Z mlaurent $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * $Revision: 373 $
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLTRACK
 *
 * @author Claroline team <info@claroline.net>
 *
 */
    // vim: expandtab sw=4 ts=4 sts=4 foldmethod=marker:

    class TrackingRendererRegistry
    {
        private static $instance = false;

        private $courseRendererList;
        private $userRendererList;

        public function __construct()
        {
            $this->courseRendererList = array();
            $this->userRendererList = array();

            $this->loadAll();
        }

        public function registerCourse( $className )
        {
            $this->courseRendererList[] = $className;
        }
        
        public function registerUser( $className )
        {
            $this->userRendererList[] = $className;
        }


        public function loadAll($cidReq = null)
        {
            $this->loadDefaultRenderer();
            
            $this->loadModuleRenderer($cidReq);
        }

        private function loadDefaultRenderer()
        {
            $file = dirname(__FILE__) . '/defaultTrackingRenderer.class.php';
                    
            if( file_exists( $file ) )
            {
                require_once $file;
                if ( claro_debug_mode() ) pushClaroMessage('Tracking : default tracking renderers loaded', 'debug');
            }
            else
            {
                if ( claro_debug_mode() ) pushClaroMessage('Tracking : cannot find default tracking renderers (file : ' . $file . ')', 'error');
            }
        }
        
        private function loadModuleRenderer($cidReq = null)
        {
            if( !is_null($cidReq) )
            {
                $toolList = claro_get_course_tool_list($cidReq);
            }
            else
            {
                $toolList = claro_get_main_course_tool_list();
            }
            
            foreach( $toolList as $tool )
            {
                if( !is_null($tool['label']) )
                {
                    $file = get_module_path($tool['label']) . '/connector/tracking.cnr.php';
                    
                    if( file_exists( $file ) )
                    {
                        require_once $file;
                        if ( claro_debug_mode() ) pushClaroMessage('Tracking : '.$tool['label'].' tracking renderers loaded', 'debug');
                    }
                }
            }
        }

        public function getCourseRendererList()
        {
            return $this->courseRendererList;
        }
        
        public function getUserRendererList()
        {
            return $this->userRendererList;
        }

        public static function getInstance()
        {
            if ( ! TrackingRendererRegistry::$instance )
            {
                TrackingRendererRegistry::$instance = new TrackingRendererRegistry;
            }

            return TrackingRendererRegistry::$instance;
        }
    }

?>