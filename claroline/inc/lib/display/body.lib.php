<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    class ClaroBody extends Display
    {
        public $banner, $footer;
        
        private $content = '';
        private $claroBodyHidden = false;
        private $inPopup = false;
        
        private $template;
        
        public function __construct()
        {
            $file = new ClaroTemplateLoader('body.tpl');
            $this->template = $file->load();
        }
        
        public function hideClaroBody()
        {
            $this->claroBodyHidden = true;
        }
        
        public function showClaroBody()
        {
            $this->claroBodyHidden = false;
        }
        
        public function popupMode()
        {
            $this->inPopup = true;
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
        
        public function render()
        {
            if ( ! $this->claroBodyHidden )
            {
                $this->template->setBlockDisplay('claroBodyStart', true);
                $this->template->setBlockDisplay('claroBodyEnd', true);
            }
            else
            {
                $this->template->setBlockDisplay('claroBodyStart', false);
                $this->template->setBlockDisplay('claroBodyEnd', false);
            }
            
            $this->template->addReplacement('content', $this->getContent() );
            
            $output = $this->template->render();
            
            if ( $this->inPopup )
            {
                $output = PopupWindowHelper::popupEmbed($output);
            }
                
            return $output;
        }
    }
?>