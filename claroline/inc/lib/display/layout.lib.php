<?php // $Id$

/**
 * Claroline Layout library.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.display
 */

/**
 * Define the layout interface
 */
interface Layout extends Display {};

/**
 * Two column abstract layout
 */
abstract class TwoColumnsLayout implements Layout
{
    protected $left = '';
    protected $right = '';
    
    /**
     * Prepend contents to the left column
     * @param string $str
     */
    public function prependToLeft( $str )
    {
        $this->left = $str . $this->left;
    }
    
    /**
     * Prepend contents to the right column
     * @param string $str
     */
    public function prependToRight( $str )
    {
        $this->right = $str . $this->right;
    }
    
    /**
     * Append contents to the left column
     * @param string $str
     */
    public function appendToLeft( $str )
    {
        $this->left .= $str;
    }
    
    /**
     * Append contents to the right column
     * @param string $str
     */
    public function appendToRight( $str )
    {
        $this->right .= $str;
    }
    
    /**
     * Render the left column as HTML string
     * @return string
     */
    abstract public function renderLeft();
    
    /**
     * Render the right column as HTML string
     * @return
     */
    abstract public function renderRight();
    
    /**
     * Render the layout as HTML string
     * @see Display
     * @return string
     */
    public function render()
    {
        return $this->renderLeft() . $this->renderRight()
            . '<div class="spacer"></div>' . "\n"
            ;
    }
}


/**
 * Layout with a menu on the left
 */
class LeftMenuLayout extends TwoColumnsLayout
{
    /**
     * @see TwoColumnsLayout
     * @return string
     */
    public function renderLeft()
    {
        return '<div id="leftSidebar">' . "\n" . $this->left . '</div>' . "\n";
    }
    
    /**
     * @see TwoColumnsLayout
     * @return string
     */
    public function renderRight()
    {
        return '<div id="rightContent">' . "\n" . $this->right . '</div>' . "\n";
    }
}

/**
 * Layout with a menu on the right
 */
class RightMenuLayout extends TwoColumnsLayout
{
    /**
     * @see TwoColumnsLayout
     * @return string
     */
    public function renderLeft()
    {
        return '<div id="leftContent">' . "\n" . $this->left . '</div>' . "\n";
    }
    
    /**
     * @see TwoColumnsLayout
     * @return string
     */
    public function renderRight()
    {
        return '<div id="rightSidebar">' . "\n" . $this->right . '</div>' . "\n";
    }
}
