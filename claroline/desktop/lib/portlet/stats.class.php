<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package Auth
 *
 */


class stats extends portlet
{
    function __construct()
    {
        $this->title = get_lang('My statistics');
        $this->content = 'le contenu de mon portlet';
    }
    
    function renderContent()
    {
        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title;
    }
}

?>