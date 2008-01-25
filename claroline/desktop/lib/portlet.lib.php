<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * $Revision$
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

    
    class portlet 
    {
    
        private $title = '';
        private $content = '';
                
        // render title
        public function renderTitle()
        {
            return $this->title;
        }
        
        // render content
        public function renderContent()
        {
            return $this->content;
        }

        // render all
        public function render()
        {
            return '<div class="portlet"><div class="portletTitle">' . $this->renderTitle() . '</div><div class="portletContent">' . $this->renderContent() . '</div></div>';
        }

    }

?>