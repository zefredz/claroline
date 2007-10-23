<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Class used to configure and display the page body
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     DISPLAY
     */
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    class ClaroBody implements Display
    {
        private static $instance = false;
        
        private $content = '';
        private $claroBodyHidden = false;
        private $inPopup = false;
        
        private $template;
        
        private function __construct()
        {
            $file = new ClaroTemplateLoader('body.tpl');
            $this->template = $file->load();
        }
        
        public static function getInstance()
        {
            if ( ! ClaroBody::$instance )
            {
                ClaroBody::$instance = new ClaroBody;
            }

            return ClaroBody::$instance;
        }
        
        /**
         * Hide the claroBody div in the body
         */
        public function hideClaroBody()
        {
            $this->claroBodyHidden = true;
        }
        
        /**
         * Display the claroBody div in the body
         */
        public function showClaroBody()
        {
            $this->claroBodyHidden = false;
        }
        
        /**
         * Show 'Close window' buttons
         */
        public function popupMode()
        {
            $this->inPopup = true;
        }
        
        /**
         * Set the content of the page
         * @param   string content
         */
        public function setContent( $content)
        {
            $this->content = $content;
        }
        
        /**
         * Append a string to the content of the page
         * @param   string str
         */
        public function appendContent( $str )
        {
            $this->content .= $str;
        }
        
        /**
         * Clear the content of the paget
         */
        public function clearContent()
        {
            $this->setContent('');
        }
        
        /**
         * Return the content of the page
         * @return  string  pagecontent
         */
        public function getContent()
        {
            return $this->content;
        }
        
        /**
         * Render the page body
         * @return  string
         */
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