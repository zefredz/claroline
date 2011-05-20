<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Display a tool's title with a toolbox and help link.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     DISPLAY
 */

class ToolTitle implements Display
{
    public $superTitle;
    public $mainTitle;
    public $subTitle;
    
    /**
     * Array of array('name' => $name, 'url' => $url)
     */
    public $toolList;
    
    /**
     * String url
     */
    public $helpUrl;
    
    public function __construct($titleParts, $helpUrl = null, $toolList = array())
    {
        if (is_array($titleParts))
        {
            if (!empty($titleParts['superTitle']))
            {
                $this->superTitle = $titleParts['superTitle'];
            }
            if (!empty($titleParts['mainTitle']))
            {
                $this->mainTitle = $titleParts['mainTitle'];
            }
            if (!empty($titleParts['subTitle']))
            {
                $this->subTitle = $titleParts['subTitle'];
            }
        }
        else
        {
            $this->mainTitle = $titleParts;
        }
        
        if (!empty($helpUrl))
        {
            $this->helpUrl = $helpUrl;
        }
        
        if (!empty($toolList))
        {
            $this->toolList = $toolList;
        }
    }
    
    public function render()
    {
        // Tool list and help
        if (!empty($this->toolList))
        {
            $toolList = '<ul class="toolList">'."\n";
            
            if (!empty($this->helpUrl))
            {
                $toolList .= '<li><a class="help" href="'.$this->helpUrl.'">&nbsp;</a></li>'."\n";
            }
            
            foreach ($this->toolList as $tool)
            {
                if (!empty($tool['img']))
                {
                    $style= ' style="background-image: url('.get_icon_url($tool['img']).'); background-repeat: no-repeat; background-position: left center; padding-left: 20px;"';
                }
                else
                {
                    $style = '';
                }
                
                $toolList .= '<li><a'.$style.' href="'.$tool['url'].'">'
                      . $tool['name'].'</a></li>'."\n";
            }
            
            $toolList .= '</ul>'."\n";
        }
        else
        {
            $toolList = '';
        }
        
        $out = '<div class="toolTitle">';
        
        // Title parts
        if (!empty($this->superTitle))
        {
            $out .= '<span class="superTitle">'.$this->superTitle.'</span><hr class="clearer" />'."\n";
        }
        
        $out .= '<h3 class="mainTitle">'.$this->mainTitle.'</h3>'."\n"
              . $toolList;
        
        if (!empty($this->superTitle))
        {
            $out .= '<hr class="clearer" /><span class="subTitle">'.$this->subTitle.'</span>'."\n";
        }
        
        // Help link
        if (!empty($this->helpUrl))
        {
            $out .= '<a class="help" href="'.$this->helpUrl.'"></a>'."\n";
        }
        
        $out .= '</div>'
              . '<hr class="clearer" />'."\n";
        
        return $out;
    }
}