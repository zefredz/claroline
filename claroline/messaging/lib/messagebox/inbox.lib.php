<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * in box  class (helper)
 *
 * @version     1.9 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


//load receivedmessagebox class
require_once dirname(__FILE__) . '/receivedmessagebox.lib.php';
//load inboxstrategy class
require_once dirname(__FILE__) . '/../selectorstrategy/inboxstartegy.lib.php';

class InBox extends ReceivedMessageBox
{
    /**
     * create a new Inbox
     *
     * @param int $userId user identification
     * if it not defined it use the current user id
     * @param MessageFilter $messageFilter
     * if it not defined it used the default value (deleted, read or unread)
     */
    public function __construct($userId = NULL, $messageFilter = NULL)
    {
        if (is_null($messageFilter))
        {
            $messageFilter = new InBoxStrategy();
        }
        
        parent::__construct($messageFilter,$userId);
    }
}
