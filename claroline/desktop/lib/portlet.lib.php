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

    abstract class Portlet
    {
        // render title
        abstract public function renderTitle();

        // render content
        abstract public function renderContent();

        // render all
        public function render()
        {
            return '<div class="portlet">' . "\n"
            .   '<div class="header">' . "\n"
            .   $this->renderTitle() . "\n"
            .   '</div>' . "\n"
            .   '<div class="portletContent">' . "\n"
            .   $this->renderContent()
            .   '</div>' . "\n" 
            .   '</div>' . "\n\n";
        }
    }
?>