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

/**
 * How to use ?
 * ============
 *
 * 1st option: create a new ToolTitle object and render it.
 * --------------------------------------------------------
 *
 * $toolTitle = new ToolTitle(
 *      array('superTitle' => 'My Section',
 *            'mainTitle' => 'My page',
 *            'subTitle' => 'List items of this page'
 *      ),
 *
 *      'www.help.tld',
 *
 *      array(array(
 *            'img' => 'new_item',
 *            'name' => 'Add a new item',
 *            'url' => './add.php'),
 *            array(
 *            'name' => 'List the 5 last items',
 *            'url' => './list5.php'),
 *            array(
 *            'img' => 'delete',
 *            'name' => 'Delete all the items',
 *            'url' => './delete.php',
 *            'params' => array('class' => 'caution')
 *      ),
 *
 *      3
 * );
 *
 * echo $toolTitle->render();
 *
 *
 * 2nd option: use the helper claro_html_tool_title().
 * ---------------------------------------------------
 *
 * echo claro_html_tool_title(sames params than in 1st option);
 *
 *
 * Note: put tooltips on your commands.
 * ------------------------------------
 *
 * If you wish to give more information about a command, you can simply
 * put it in the "title" attribute of the command (use the "params" entry
 * of the assoc array).  This title will be rendered in a tooltip
 * when the mouse is over the command.
 */



class ToolTitle implements Display
{
    public $superTitle;
    public $mainTitle;
    public $subTitle;
    
    /**
     * Array of array('img' => $iconUrl, 'name' => $name, 'url' => $url, 'params' => $param) of commands
     */
    public $commandList;
    
    /**
     * int $showCommands number of displayed commands
     */
    public $showCommands;
    
    /**
     * String $helpUrl
     */
    public $helpUrl;
    
    public function __construct($titleParts, $helpUrl = null, $commandList = array(), $showCommands = null)
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
        
        if (!empty($commandList))
        {
            $this->commandList = $commandList;
        }
        
        if (!empty($showCommands) && is_int($showCommands))
        {
            $this->showCommands = $showCommands;
        }
        else
        {
            $showCommands = null;
        }
    }
    
    /**
     * TODO: move it into a template
     */
    public function render()
    {
        // We'll need some js
        JavascriptLoader::getInstance()->load('tooltitle');
        
        // Command list and help
        $commandList = '';
        if (!empty($this->commandList))
        {
            $help = '';
            if (!empty($this->helpUrl))
            {
                $help .= '<li><a class="help" href="#" '
                       . "onclick=\"MyWindow=window.open('". get_path('clarolineRepositoryWeb') . "help/" . $this->helpUrl . "',"
                       . "'MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;\">"
                       . '&nbsp;</a></li>'."\n";
            }
            
            $commands = '';
            $i = 0;
            foreach ($this->commandList as $command)
            {
                $styleA = '';
                if (!empty($command['img']))
                {
                    $styleA = ' style="background-image: url('.get_icon_url($command['img']).'); background-repeat: no-repeat; background-position: left center; padding-left: 20px;"';
                }
                
                $styleLi = '';
                if (!empty($this->showCommands) && $i >= $this->showCommands)
                {
                    $styleLi = ' class="hidden"';
                }
                
                $params = '';
                if (!empty($command['params']))
                {
                    foreach($command['params'] as $key => $value)
                    {
                        $params .= ' '.$key.'="'.$value.'"';
                    }
                }
                
                $commands .= '<li'.$styleLi.'>'
                           . '<a'.$styleA.$params.' href="'.$command['url'].'">'
                           . $command['name'].'</a></li>'."\n";
                
                $i++;
            }
            
            $more = '';
            if (!empty($this->showCommands) && count($this->commandList) > $this->showCommands)
            {
                $more = '<li><a class="more" href="#">&raquo;</a></li>';
            }
            
            $commandList .= '<ul class="commandList">'."\n"
                          . $help
                          . $commands
                          . $more
                          . '</ul>'."\n";
        }
        
        $out = '<div class="toolTitleBlock">';
        
        // Title parts
        if (!empty($this->superTitle))
        {
            $out .= '<span class="toolTitle superTitle">'.$this->superTitle.'</span>'."\n";
        }
        
        if (empty($this->commandList))
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
              . $commandList
              . '</td></tr></table>';
        
        if (!empty($this->subTitle))
        {
            $out .= '<span class="toolTitle subTitle">'.$this->subTitle.'</span>'."\n";
        }
        
        $out .= '</div>'."\n";
        
        return $out;
    }
}