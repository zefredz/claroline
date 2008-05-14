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

require_once get_path( 'clarolineRepositorySys' ) . '/messaging/lib/tools.lib.php';
require_once get_path( 'clarolineRepositorySys' ) . '/messaging/lib/messagebox/inbox.lib.php';

class MyBox extends Portlet
{
    protected $inbox;

    function __construct()
    {
        $this->inbox = new InBox;
        $this->inbox->getMessageStrategy()->setNumberOfMessagePerPage( get_conf('myboxNumberOfMessage',5) );
    }

    function renderContent()
    {
        $output = getBarMessageBox( claro_get_current_user_id(), $displayMenuAdmin = false );

        $output .= '<br /><br />';

        $output .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
        .    '<thead>' . "\n"
        .      '<tr class="headerX" align="center" valign="top">' . "\n"
        .        '<th>&nbsp;</th>' . "\n"
        .        '<th>' . get_lang('De') . '</th>' . "\n"
        .        '<th>' . get_lang('Sujet') . '</th>' . "\n"
        .        '<th>' . get_lang('Reçu') . '</th>' . "\n"
        .      '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        ;

        if( $this->inbox->getNumberOfMessage() > 0 )
        {
            foreach( $this->inbox as $message )
            {
                if ($message->getRecipient() == 0)
                {
                    $classMessage = 'class="plateformMessage"';
                    $iconMessage = '<img src="' . get_icon_url('important') . '" alt="' . get_lang('important') . '" />';
                }
                else
                {
                    ( $message->isRead() ? $classMessage = 'class="readMessage"' : $classMessage = 'class="unreadMessage"' );
                    ( $message->isRead() ? $iconMessage = '<img src="' . get_icon_url('emaillu') . '" alt="' . get_lang('email') . '" />' : $iconMessage = '<img src="' . get_icon_url('email') . '" alt="' . get_lang('email') . '" />' );
                }

                $output .= "\n"
                .      '<tr ' . $classMessage . '>' . "\n"
                .       '<td>' . $iconMessage . '</td>' . "\n"
                .       '<td>' . htmlspecialchars( $message->getSenderLastName() ) . '&nbsp;' . htmlspecialchars( $message->getSenderFirstName() ) . '</td>' . "\n"
                .       '<td>'
                .       '<a href="' . get_path( 'clarolineRepositoryWeb' ) . 'messaging/readmessage.php?messageId=' . $message->getId() . '&amp;type=received">'
                .       htmlspecialchars( $message->getSubject() )
                .       '</a>' . "\n"
                .       '</td>' . "\n"
                .       '<td align="center">' . claro_html_localised_date( get_locale( 'dateFormatLong' ), strtotime( $message->getSendTime() ) ) . '</td>' . "\n"
                .      '</tr>' . "\n"
                ;
            }
        }
        else
        {
                $output .= "\n"
                .      '<tr>' . "\n"
                .       '<td colspan="4" align="center">' . get_lang('Empty') . '</td>' . "\n"
                .      '</tr>' . "\n"
                ;
        }

        $output .= "\n"
        .    '</tbody>' . "\n"
        .    '</table>' . "\n"
        ;

        return $output;
    }

    function renderTitle()
    {
        $this->title = get_lang('My MailBox');
        return $this->title;
    }
}
?>