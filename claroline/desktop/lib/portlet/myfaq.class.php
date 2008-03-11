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


class myfaq extends portlet
{
    function __construct()
    {
    }
    
    function renderContent()
    {
        $this->content = 'Le contenu de mon portlet';
        return $this->content;
    }
    
    function renderTitle()
    {
        $this->title = get_lang('My F.A.Q.');
        return $this->title;
    }
}

?>