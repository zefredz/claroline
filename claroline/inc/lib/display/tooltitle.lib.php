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
     * Array of array('img' => $iconUrl, 'name' => $name, 'url' => $url, 'params' => $param) of tools
     */
    public $toolList;
    
    /**
     * int $showTools number of displayed tools
     */
    public $showTools;
    
    /**
     * String $helpUrl
     */
    public $helpUrl;
    
    public function __construct($titleParts, $helpUrl = null, $toolList = array(), $showTools = null)
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
        
        if (!empty($showTools) && is_int($showTools))
        {
            $this->showTools = $showTools;
        }
        else
        {
            $showTools = null;
        }
    }
    
    public function render()
    {
        // Tool list and help
        $toolList = '';
        if (!empty($this->toolList))
        {
            $help = '';
            if (!empty($this->helpUrl))
            {
                $help .= '<li><a class="help" href="#" '
                       . "onclick=\"MyWindow=window.open('". get_path('clarolineRepositoryWeb') . "help/" . $this->helpUrl . "',"
                       . "'MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;\">"
                       . '&nbsp;</a></li>'."\n";
            }
            
            $tools = '';
            $i = 0;
            foreach ($this->toolList as $tool)
            {
                $styleA = '';
                if (!empty($tool['img']))
                {
                    $styleA = ' style="background-image: url('.get_icon_url($tool['img']).'); background-repeat: no-repeat; background-position: left center; padding-left: 20px;"';
                }
                
                $styleLi = '';
                if (!empty($this->showTools) && $i >= $this->showTools)
                {
                    $styleLi = ' class="hidden"';
                }
                
                $params = '';
                if (!empty($tool['params']))
                {
                    foreach($tool['params'] as $key => $value)
                    {
                        $params .= ' '.$key.'="'.$value.'"';
                    }
                }
                
                $tools .= '<li'.$styleLi.'><a'.$styleA.$params.' href="'.$tool['url'].'">'
                      . $tool['name'].'</a></li>'."\n";
                
                $i++;
            }
            
            $more = '';
            if (!empty($this->showTools) && count($this->toolList) > $this->showTools)
            {
                $more = '<li><a class="more" href="#">&raquo;</a></li>';
            }
            
            $toolList .= '<ul class="toolList">'."\n"
                       . $help
                       . $tools
                       . $more
                       . '</ul>'."\n";
        }
        
        $out = '<div class="toolTitleBlock">';
        
        // Title parts
        if (!empty($this->superTitle))
        {
            $out .= '<span class="toolTitle superTitle">'.$this->superTitle.'</span><hr class="clearer" />'."\n";
        }
        
        if (empty($this->toolList))
        {
            $style = ' style="border-right: 0"';
        }
        else
        {
            $style = '';
        }
        
        $out .= '<table><tr><td>'
              . '<h1 class="toolTitle mainTitle"'.$style.'>'.$this->mainTitle.'</h1>'."\n"
              . '</td><td>'
              . $toolList
              . '</td></tr></table>';
        
        if (!empty($this->superTitle))
        {
            $out .= '<hr class="clearer" /><span class="toolTitle subTitle">'.$this->subTitle.'</span>'."\n";
        }
        
        // Help link
        if (!empty($this->helpUrl))
        {
            $out .= '<a class="help" href="'.$this->helpUrl.'"></a>'."\n";
        }
        
        $out .= '</div>'."\n";
        
        return $out;
    }
}