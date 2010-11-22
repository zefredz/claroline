<?php //$Id$

/**
 * CLAROLINE
 *
 * Use portlets to display informations (course list, calendar,
 * announces, ...) via connectors in user's desktop
 * or course home page.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2010, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */

abstract class Portlet implements Display
{
    // Render title
    abstract public function renderTitle();
    
    // Render content
    abstract public function renderContent();
    
    // Render all
    public function render()
    {
        return '<div class="claroBlock portlet">' . "\n"
             . '<div class="claroBlockHeader">' . "\n"
             . $this->renderTitle() . "\n"
             . '</div>' . "\n"
             . '<div class="claroBlockContent">' . "\n"
             . $this->renderContent()
             . '</div>' . "\n"
             . '</div>' . "\n\n";
    }
}