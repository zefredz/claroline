<?php // $Id: chatMsgList.class.php 415 2008-03-31 13:32:19Z fragile_be $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 415 $
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLTRACK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

abstract class TrackingRenderer
{
    public function __contruct(){}
    
    public function render()
    {
        $html = '<div class="statBlock">' . "\n"
        .    ' <div class="blockHeader">' . "\n"
        .    $this->renderHeader()
        .    ' </div>' . "\n"
        .    ' <div class="blockContent">' . "\n"
        .    $this->renderContent()
        .    ' </div>' . "\n"
        .    ' <div class="blockFooter">' . "\n"
        .    $this->renderFooter()
        .    ' </div>' . "\n"
        .    '</div>' . "\n";

        return $html;
    }
    
    abstract protected function renderHeader();
    abstract protected function renderContent();
    abstract protected function renderFooter();
}

?>