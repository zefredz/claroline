<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * View mode block
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     PACKAGE_NAME
     */

    class ClaroViewMode implements Display
    {
        private static $instance = false;

        private function __construct()
        {
        }
        
        public function render()
        {
            $out = '';
            
            if ( !claro_is_user_authenticated() )
            {
                $out .= $this->renderLoginLink();
            }
            elseif ( ( !claro_is_platform_admin() )
                && ( claro_is_in_a_course() && !claro_is_course_member() )
                && claro_get_current_course_data('registrationAllowed') )
            {
                $out .= $this->renderRegistrationLink();
            }
            elseif ( claro_is_display_mode_available() )
            {
                $out .= $this->renderViewModeSwitch();
            }
            else
            {
            }
            
            return $out;
        }
        
        private function renderViewModeSwitch()
        {
            $out = '';

            if ( isset($_REQUEST['View mode']) )
            {
                $out .= claro_html_tool_view_option($_REQUEST['View mode']);
            }
            else
            {
                $out .= claro_html_tool_view_option();
            }

            if ( claro_is_platform_admin() && ! claro_is_course_member() )
            {
                $out .= ' | <a href="' . get_path('clarolineRepositoryWeb')
                    . 'auth/courses.php?cmd=exReg&course='
                    . claro_get_current_course_id().'">'
                    . claro_html_icon( 'enroll.gif' )
                    . '<b>' . get_lang('Enrolment') . '</b>'
                    . '</a>'
                    ;
            }

            $out .= "\n";
            
            return $out;
        }
        
        private function renderRegistrationLink()
        {
            return '<div id="toolViewOption">'
                . '<a href="'
                . get_path('clarolineRepositoryWeb')
                . 'auth/courses.php?cmd=exReg&course='.claro_get_current_course_id()
                . '">'
                . claro_html_icon( 'enroll.gif' )
                . '<b>' . get_lang('Enrolment') . '</b>'
                . '</a>'
                . '</div>' . "\n"
                ;
        }
        
        private function renderLoginLink()
        {
            return "\n".'<div id="toolViewOption" style="padding-right:10px">'
                . '<a href="' . get_path('clarolineRepositoryWeb') . 'auth/login.php'
                . '?sourceUrl='
                . urlencode( base64_encode(
                    ( isset( $_SERVER['HTTPS'])
                        && ($_SERVER['HTTPS']=='on'||$_SERVER['HTTPS']==1)
                        ? 'https://'
                        : 'http://' )
                    . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) )
                . '" target="_top">'
                . get_lang('Login')
                . '</a>'
                . '</div>'."\n"
                ;
        }

        public static function getInstance()
        {
            if ( ! ClaroViewMode::$instance )
            {
                ClaroViewMode::$instance = new ClaroViewMode;
            }

            return ClaroViewMode::$instance;
        }
    }
?>