<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * trashbox strategy
 *
 * @version     1.9 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


//load receivedmessagestrategy class
require_once __DIR__.'/receivedmessagestrategy.lib.php';

class TrashBoxStrategy extends ReceivedMessageStrategy 
{
    /**
     * create a default trashbox strategy 
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDeletedStrategy(parent::ONLY_DELETED);
        $this->setReadStrategy(parent::NO_FILTER);
    }
}
