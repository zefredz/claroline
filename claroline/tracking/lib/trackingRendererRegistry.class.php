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
 * @package CLPAGES
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

		public function registerCourse( $label, $className )
		{
			$this->courseRendererList[$label] = $className;
		}
		
		public function registerUser( $label, $className )
        {
            $this->userRendererList[$label] = $className;
        }


		public function loadAll($cidReq = null)
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
		            $file = get_module_path($tool['label']) . '/connector/trackingRenderer.class.php';
		            
		            if( file_exists( $file ) )
		            {
		                require_once $file;
		            }
		        }
		    }
		}

		public function getPluginClass( $type )
		{
			$type = strtolower($type);

			if( isset($this->plugins[$type]['className']) )
			{
				return $this->plugins[$type]['className'];
			}
			else
			{
				return '';
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