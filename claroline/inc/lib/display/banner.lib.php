<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Class used to configure and display the page banners
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2008 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     display
     */
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses ( 'display/breadcrumbs.lib', 'display/viewmode.lib' );
    
    class ClaroBanner extends CoreTemplate
    {
        protected static $instance = false;
        
        protected $hidden = false;
        public $breadcrumbs;
        public $viewmode;

        public function __construct()
        {
            $this->breadcrumbs = ClaroBreadCrumbs::getInstance();
            $this->viewmode = ClaroViewMode::getInstance();
            parent::__construct('banner.tpl.php');
            
            $this->breadcrumbLine = true;
        }
        
        public static function getInstance()
        {
            if ( ! self::$instance )
            {
                self::$instance = new ClaroBanner;
            }

            return self::$instance;
        }
        
        /**
         * Hide the banners
         */
        public function hide()
        {
            $this->hidden = true;
        }
        
        /**
         * Show the banners
         */
        public function show()
        {
            $this->hidden = false;
        }
        
        /**
         * Hide breadcrump line
         */
        public function hideBreadcrumbLine()
        {
            $this->breadcrumbLine = false;  
        }
        
        /**
         * Render the banners
         * @return  string
         */
        public function render()
        {
            if ( $this->hidden )
            {
                return '<!-- banner hidden -->' . "\n";
            }
            
            $this->_prepareCampusBanner();
            $this->_prepareUserBanner();
            $this->_prepareCourseBanner();
            
            return parent::render();
        }
        
        private function _prepareCourseBanner()
        {
            if ( claro_is_in_a_course() )
            {
                $_courseToolList = claro_get_current_course_tool_list_data();
                
                if (is_array($_courseToolList) && claro_is_course_allowed())
                {
                    $toolNameList = claro_get_tool_name_list();
                    
                    foreach($_courseToolList as $_courseToolKey => $_courseToolDatas)
                    {

                        if (isset($_courseToolDatas['name']) && !is_null($_courseToolDatas['name']) && isset($_courseToolDatas['label']))
                        {
                            $_courseToolList[ $_courseToolKey ] [ 'name' ] = $toolNameList[ $_courseToolDatas['label'] ];
                        }
                        else
                        {
                            $external_name = $_courseToolList[ $_courseToolKey ] [ 'external_name' ] ;
                            $_courseToolList[ $_courseToolKey ] [ 'name' ] = get_lang($external_name);
                        }
                        // now recheck to be sure the value is really filled before going further
                        if ($_courseToolList[ $_courseToolKey ] [ 'name' ] =='')
                        $_courseToolList[ $_courseToolKey ] [ 'name' ] = get_lang('No name');
                    }
                    
                    $courseToolSelector = '<form action="'.get_path('clarolineRepositoryWeb').'redirector.php" name="redirector" method="post">' . "\n"
                    . '<select name="url" size="1" onchange="top.location=redirector.url.options[selectedIndex].value" >' . "\n\n";

                    $courseToolSelector .= '<option value="' . get_path('clarolineRepositoryWeb') . 'course/index.php?cid=' . htmlspecialchars(claro_get_current_course_id()) .'" style="padding-left:22px;background:url(' . get_path('imgRepositoryWeb') . '/course.gif) no-repeat">' . get_lang('Course Home') . '</option>' . "\n";

                    if (is_array($_courseToolList))
                    {
                        foreach($_courseToolList as $_courseToolKey => $_courseToolData)
                        {
                            //find correct url to access current tool

                            if (isset($_courseToolData['url']))
                            {
                                if (!empty($_courseToolData['label']))
                                $_courseToolData['url'] = get_module_url($_courseToolData['label']) . '/' . $_courseToolData['url'];
                                // reset group to access course tool

                                if (claro_is_in_a_group() && !$_courseToolData['external'])
                                $_toolDataUrl = strpos($_courseToolData['url'], '?') !== false
                                ? $_courseToolData['url'] . '&amp;gidReset=1'
                                : $_courseToolData['url'] . '?gidReset=1'
                                ;
                                else $_toolDataUrl = $_courseToolData['url'];

                            }

                            //find correct url for icon of the tool

                            if (isset($_courseToolData['icon']))
                            {
                                $_toolIconUrl = get_module_url($_courseToolData['label']).'/'.$_courseToolData['icon'];
                            }

                            // select "groups" in group context instead of tool
                            if ( claro_is_in_a_group() )
                            {
                                $toolSelected = $_courseToolData['label'] == 'CLGRP___' ? 'selected="selected"' : '';
                            }
                            else
                            {
                                $toolSelected = $_courseToolData['id'] == claro_get_current_tool_id() ? 'selected="selected"' : '';
                            }

                            $_courseToolDataName = $_courseToolData['name'];
                            $courseToolSelector .= '<option value="' . $_toolDataUrl . '" '
                            .   $toolSelected
                            .   'style="padding-left:22px;background:url('.$_toolIconUrl.') no-repeat">'
                            .    get_lang($_courseToolDataName)
                            .    '</option>'."\n"
                            ;
                        }
                    } // end if is_array _courseToolList
                    $courseToolSelector .= "\n"
                    . '</select>' . "\n"
                    . '<noscript>' . "\n"
                    . '<input type="submit" name="gotool" value="go" />' . "\n"
                    . '</noscript>' . "\n"
                    . '</form>' . "\n\n";
                    
                    $this->assign('courseToolSelector', $courseToolSelector );
                }
                
                $this->showBlock('courseBanner');
            }
            else
            {
                $this->hideBlock('courseBanner');
            }
        }
        
        /**
         * Prepare the user banner
         */
        private function _prepareUserBanner()
        {
            if( claro_is_user_authenticated() )
            {
                $userToolUrlList = array();
                
                $userToolUrlList[]  = '<a href="'
                    . get_path('clarolineRepositoryWeb')
                    . 'desktop/index.php" target="_top">'
                    . get_lang('My Desktop').'</a>'
                    ;
                
                $userToolUrlList[]= '<a href="'.  get_path('url')
                    . '/index.php" target="_top">'
                    . get_lang('My course list').'</a>'
                    ;
                /*$userToolList = claro_get_user_tool_list();
                
                foreach ($userToolList as $userTool)
                {
                    $userToolUrlList[] = '<a href="'. get_module_url('CLCAL')
                        . '/' . $userTool['entry'] . '" target="_top">'
                        . get_lang('My calendar').'</a>'
                        ;
                }*/
                
                $userToolUrlList[]  = '<a href="'
                    . get_path('clarolineRepositoryWeb')
                    . 'auth/profile.php" target="_top">'
                    . get_lang('My User Account').'</a>'
                    ;

                $userToolUrlList[]  = '<a href="'
                    . get_path('clarolineRepositoryWeb')
                    . 'messaging" target="_top">'
                    . get_lang('My Messages').'</a>'
                    ;    
                
                if(claro_is_platform_admin())
                {
                    $userToolUrlList[] = '<a href="'
                        . get_path('clarolineRepositoryWeb')
                        .'admin/" target="_top">'
                        . get_lang('Platform Administration'). '</a>'
                        ;
                }

                $userToolUrlList[] = '<a href="'.  get_path('url')
                    . '/index.php?logout=true" target="_top">'
                    . get_lang('Logout').'</a>'
                    ;

                $this->assign('userToolList'
                    , claro_html_menu_horizontal($userToolUrlList));
                    
                $this->showBlock('userBanner');
            }
            else
            {
                $this->hideBlock('userBanner');
            }
        }
        
        /**
         * Prepare the campus banner
         */
        private function _prepareCampusBanner()
        {
            $campus = array();
            
            $campus['siteName'] =  get_conf('siteLogo') != ''
                ? '<img src="' . get_conf('siteLogo') . '" alt="'.get_conf('siteName').'"  />'
                : get_conf('siteName')
                ;

            $institutionNameOutput = '';

            $bannerInstitutionName = (get_conf('institutionLogo') != '')
                ? '<img src="' . get_conf('institutionLogo')
                    . '" alt="' . get_conf('institution_name') . '" />'
                : get_conf('institution_name')
                ;

            if( !empty($bannerInstitutionName) )
            {
                if( get_conf('institution_url') != '' )
                {
                    $institutionNameOutput .= '<a href="'
                        . get_conf('institution_url').'" target="_top">'
                        . $bannerInstitutionName.'</a>'
                        ;
                }
                else
                {
                    $institutionNameOutput .= $bannerInstitutionName;
                }
            }

            /* --- External Link Section --- */
            if( claro_get_current_course_data('extLinkName') != '' )
            {
                $institutionNameOutput .= get_conf('institution_url') != ''
                    ? ' / '
                    : ' '
                    ;

                if( claro_get_current_course_data('extLinkUrl') != '' )
                {
                    $institutionNameOutput .= '<a href="'
                        . claro_get_current_course_data('extLinkUrl')
                        . '" target="_top">'
                        . claro_get_current_course_data('extLinkName')
                        . '</a>'
                        ;
                }
                else
                {
                    $institutionNameOutput .= claro_get_current_course_data('extLinkName');
                }
            }
            
            $campus['institution'] = $institutionNameOutput;

            $this->assign( 'campus', $campus );
        }
    }
?>