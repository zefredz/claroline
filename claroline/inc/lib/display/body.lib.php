<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    class ClaroBody extends Display
    {
        public $banner, $footer;
        
        private $jsBodyOnload = array();
        private $content = '';
        private $bannerHidden = false;
        private $footerHidden = false;
        private $claroBodyHidden = false;
        private $bannerAtEnd = false;
        
        public function __construct()
        {
            $this->banner = new ClaroBanner;
            $this->footer = new ClaroFooter;
        }
        
        public function addBodyOnload( $function )
        {
            $this->jsBodyOnload[] = $function;
        }
        
        public function brailleMode()
        {
            $this->bannerAtEnd = true;
        }
        
        public function popupMode()
        {
            $this->hideBanner();
            $this->hideFooter();
        }
        
        public function hideBanner()
        {
            $this->banner->hide();
        }
        
        public function showBanner()
        {
            $this->banner->show();
        }

        public function hideFooter()
        {
            $this->footer->hide();
        }
        
        public function showFooter()
        {
            $this->footer->show();
        }
        
        public function hideClaroBody()
        {
            $this->claroBodyHidden = true;
        }
        
        public function showClaroBody()
        {
            $this->claroBodyHidden = false;
        }
        
        public function setContent( $content)
        {
            $this->content = $content;
        }
        
        public function appendContent( $str )
        {
            $this->content .= $str;
        }
        
        public function clearContent()
        {
            $this->setContent('');
        }
        
        public function getContent()
        {
            return $this->content;
        }
        
        private function _globalVarsCompat()
        {
            if ( isset( $GLOBALS['claroBodyOnload'] ) && !empty($GLOBALS['claroBodyOnload']) )
            {
                $this->jsBodyOnload = array_merge( $this->jsBodyOnload, $GLOBALS['claroBodyOnload'] );
            }
        }
        
        public function render()
        {
            $output = '';
            
            if ( true === get_conf( 'warnSessionLost', true ) && claro_get_current_user_id() )
            {
                $this->jsBodyOnload[] = 'claro_session_loss_countdown(' . ini_get('session.gc_maxlifetime') . ');';
            }
            
            $output .= '<body dir="' . get_locale('text_dir') . '"'
                .    ( !empty( $this->jsBodyOnload ) ? ' onload="' . implode('', $claroBodyOnload ) . '" ':'')
                .    '>' . "\n"
                ;
                
            if ( ! $this->bannerAtEnd )
            {
                $output .= $this->banner->render() . "\n";
            }
            
            if ( ! $this->claroBodyHidden )
            {
                // need body div
                    $output .= "\n"
                        . '<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->' . "\n"
                        . '<div id="claroBody">' . "\n"
                        ;
            }
            
            $output .= $this->getContent();
            
            if ( ! $this->claroBodyHidden )
            {
                // need body div
                $output .= "\n" . '</div>' . "\n"
                    . '<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->' . "\n"
                    ;
            }
            
            if ( $this->bannerAtEnd )
            {
                $output .= $this->banner->render() . "\n";
            }
            
            $output .= $this->footer->render() . "\n";
            
            if ( claro_debug_mode() )
            {
                $output .= claro_disp_debug_banner();
            }
            
            $output .= '</body>' . "\n";
                
            return $output;
        }
    }
?>