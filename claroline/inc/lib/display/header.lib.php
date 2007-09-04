<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses( 'core/loader.lib' );

    class ClaroHeader extends Display
    {
        private $_htmlXtraHeaders;
        private $_httpXtraHeaders;
        private $_template;
        
        public function __construct( $pageTitle = '' )
        {
            $file = new ClaroTemplateLoader('header.tpl');
            $this->_template = $file->load();
            $this->_htmlXtraHeaders = array();
            $this->_httpXtraHeaders = array();
        }
        
        /**
         * Add extra HTML header elements
         *
         * @access  public
         * @param   string content, page content
         */
        public function addHtmlHeader( $header )
        {
            $this->_htmlXtraHeaders[] = $header;
        }

        /**
         * Add extra HTTP header elements
         *
         * @access  public
         * @param   string content, page content
         */
        public function addHttpHeader( $header )
        {
            $this->_httpXtraHeaders[] = $header;
        }
        
        public function sendHttpHeaders()
        {
            if (! is_null(get_locale('charset')) )
            {
                header('Content-Type: text/html; charset='. get_locale('charset'));
            }

            if ( !empty($this->_httpXtraHeaders) )
            {
                foreach( $this->_httpXtraHeaders as $httpHeader )
                {
                    header( $httpHeader );
                }
            }
        }
        
        private function _globalVarsCompat()
        {
            if ( isset( $GLOBALS['htmlHeadXtra'] ) && !empty($GLOBALS['htmlHeadXtra']) )
            {
                $this->_htmlXtraHeaders = array_merge($this->_htmlXtraHeaders, $GLOBALS['htmlHeadXtra'] );
            }
            
            if ( isset( $GLOBALS['httpHeadXtra'] ) && !empty($GLOBALS['httpHeadXtra']) )
            {
                $this->_httpXtraHeaders = array_merge($this->_httpXtraHeaders, $GLOBALS['httpHeadXtra'] );
            }
        }
        
        public function render()
        {
            $this->_globalVarsCompat();
            
            $titlePage = '';

            if(!empty($nameTools))
            {
                $titlePage .= $nameTools . ' - ';
            }

            if(claro_is_in_a_course() && claro_get_current_course_data('officialCode') != '')
            {
                $titlePage .= claro_get_current_course_data('officialCode') . ' - ';
            }

            $titlePage .= get_conf('siteName');
            
            $this->_template->addReplacement( 'pageTitle', $titlePage );
            
            if ( true === get_conf( 'warnSessionLost', true ) && claro_get_current_user_id() )
            {
                $this->_template->addReplacement( 'warnSessionLost',
"function claro_session_loss_countdown(sessionLifeTime){
    var chrono = setTimeout('claro_warn_of_session_loss()', sessionLifeTime * 1000);
}

function claro_warn_of_session_loss() {
    alert('" . clean_str_for_javascript (get_lang('WARNING ! You have just lost your session on the server.') . "\n"
             . get_lang('Copy any text you are currently writing and paste it outside the browser')) . "');
}
" );
            }
            else
            {
                $this->_template->addReplacement( 'warnSessionLost', '' );
            }
            
            $jsloader = JavascriptLoader::getInstance();
            
            $this->addHtmlHeader($jsloader->toHtml());
            
            $cssloader = CssLoader::getInstance();

            $this->addHtmlHeader($cssloader->toHtml());
            
            if ( !empty( $this->_htmlXtraHeaders ) )
            {
                $this->_template->addReplacement( 'htmlScriptDefinedHeaders',
                    implode ( "\n", $this->_htmlXtraHeaders ) );
            }
            else
            {
                $this->_template->addReplacement( 'htmlScriptDefinedHeaders', '' );
            }
            
            return $this->_template->render() . "\n";
        }
    }
?>