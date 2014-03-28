<?php //$Id$

/**
 * CLAROLINE
 *
 * Use portlets to display informations (course list, calendar,
 * announces, ...) via connectors in user's desktop
 * or course home page.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */

/**
 * Abstract portlet to be implemented to provide blocks to enhance the user desktop 
 */
abstract class Portlet implements Display
{
    private $label;
    
    /**
     * Constructor
     * @param type $label label of the portlet
     */
    public function __construct($label)
    {
        $this->label = $label;
    }
    
    // Render title
    /**
     * Generate the title of the portlet
     * @return string
     */
    abstract public function renderTitle();
    
    // Render content
    /**
     * Generate the contents of the portlet
     * @return string
     */
    abstract public function renderContent();
    
    // Render all
    /**
     * Render the portlet as HTML
     * @return string
     * @see Display
     */
    public function render()
    {
        return '<div class="portlet'.(!empty($this->label)?' '.$this->label:'').'">' . "\n"
             . '<h1>' . "\n"
             . $this->renderTitle() . "\n"
             . '</h1>' . "\n"
             . '<div class="content">' . "\n"
             . $this->renderContent()
             . '</div>' . "\n"
             . '</div>' . "\n\n";
    }
}
