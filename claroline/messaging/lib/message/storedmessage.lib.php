<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * stored message class
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


//load internalmessage class
require_once dirname(__FILE__) . '/internalmessage.lib.php';

abstract class StoredMessage extends InternalMessage
{
    protected $messageId;
    protected $sendTime;
    
    
    protected $senderFirstName;
    protected $senderLastName;

    /**
     * set the fields of the current message
     *
     * @param array $messageData
     * $messageData['message_id'] = message identification
     * $messageData['subject'] = subject of the message
     * $messageData['message'] = content of the message
     * $messageData['sender'] = itendification of the sender
     * $messageData['send_time'] = send time of the message
     * $messageData['tools'] = send time of the message
     * $messageData['group'] = send time of the message
     * $messageData['course'] = send time of the message
     */
    protected function setFromArray($messageData)
    {
        if (isset($messageData['message_id']) && !is_null($messageData['message_id']))
        {
            $this->messageId = (int)$messageData['message_id'];
        }
        else
        {
            throw new Exception("\$messageData['message_id'] is not defined: All data must be defined");
        }

        if (isset($messageData['subject']) && !is_null($messageData['subject']))
        {
            $this->subject = $messageData['subject'];
        }
        else
        {
            if (!isset($messageData['subject'])) echo "is not set";
            if (is_null($messageData['subject'])) echo "is null";
            throw new Exception("\$messageData['subject'] is not defined: All data must be defined");
        }

        if (isset($messageData['message']) && !is_null($messageData['message']))
        {
            $this->message = $messageData['message'];
        }
        else
        {
            throw new Exception("\$messageData['message'] is not defined: All data must be defined");
        }

        if (isset($messageData['sender']) && !is_null($messageData['sender']))
        {
            $this->sender = (int)$messageData['sender'];
        }
        else
        {
            throw new Exception("\$messageData['sender'] is not defined: All data must be defined");
        }

        if (isset($messageData['send_time']) && !is_null($messageData['send_time']))
        {
            $this->sendTime = $messageData['send_time'];
        }
        else
        {
            throw new Exception("\$messageData['send_time'] is not defined: All data must be defined");
        }

        if (array_key_exists("course", $messageData))//could be  null
        {
            $this->course = $messageData['course'];
        }
        else
        {
            throw new Exception("\$messageData['course'] is not defined: All data must be defined");
        }

        if (array_key_exists("group", $messageData))//could be  null
        {
            $this->group = $messageData['group'];
        }
        else
        {
            throw new Exception("\$messageData['group'] is not defined: All data must be defined");
        }

        if (array_key_exists("tools", $messageData))//could be  null
        {
            $this->tools = $messageData['tools'];
        }
        else
        {
            throw new Exception("\$messageData['tools'] is not defined: All data must be defined");
        }
    }

    /**
     * return the identification of the current message
     *
     * @return int message identification
     */
    public function getId()
    {
        return $this->messageId;
    }

    /**
     * return the send time of the message
     *
     * @return string send time of the message
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    public function setSenderFirstName($firstName)
    {
        $this->senderFirstName= $firstName;
    }
    
    public function setSenderLastName($lastName)
    {
        $this->senderLastName = $lastName;
    }
    
    public function getSenderFirstName()
    {
        return $this->senderFirstName;
    }
    
    public function getSenderLastName()
    {
        return $this->senderLastName;
    }

    public function isPlateformMessage()
    {
        $tableName = get_module_main_tbl(array('im_recipient'));
        
        $sql = "SELECT DISTINCT sent_to \n"
            ." FROM `".$tableName['im_recipient']."` \n"
            ." WHERE message_id = ".$this->getId()
            ;
        $sentto = claro_sql_query_fetch_single_value($sql);
        
        if ($sentto == 'toAll')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
