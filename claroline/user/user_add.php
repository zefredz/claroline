<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool allow to add a user in his course (an din the platform)
 * @version 1.8 $Revision$
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 * @author Claro Team <cvs@claroline.net>
 * @package CLUSR
 */
/*=====================================================================
 Init Section
 =====================================================================*/

$tlabelReq = 'CLUSR';
$gidReset = true;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
$can_add_single_user     = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_add_single_user') )
                     || claro_is_platform_admin();
if ( ! $can_add_single_user ) claro_die(get_lang('Not allowed'));

// include configuration file
include claro_get_conf_repository() . 'user_profile.conf.php';

// include libraries
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/course_user.lib.php';
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

// Initialise variables
$nameTools        = get_lang('Add a user');
$interbredcrump[] = array ('url' => 'user.php', 'name' => get_lang('Users') );

$messageList = array();
$messageList['warning'] = array();
$messageList['error'] = array();

$platformRegSucceed = false;
$courseRegSucceed   = false;

/*=====================================================================
                                MAIN SECTION
 =====================================================================*/

// Initialise field variable from subscription form
$userData = user_initialise();

$cmd = isset($_REQUEST['cmd']) ? $cmd = $_REQUEST['cmd'] : null;

if ( (isset($_REQUEST['applySearch'] ) && ( $_REQUEST['applySearch'] != '' )))
{
    $cmd = 'applySearch';
}

$userData['lastname'     ] = isset($_REQUEST['lastname'        ]) ? strip_tags(trim($_REQUEST['lastname'    ])) : null;
$userData['firstname'    ] = isset($_REQUEST['firstname'       ]) ? strip_tags(trim($_REQUEST['firstname'   ])) : null;
$userData['officialCode' ] = isset($_REQUEST['officialCode'    ]) ? strip_tags(trim($_REQUEST['officialCode'])) : null;
$userData['username'     ] = isset($_REQUEST['username'        ]) ? strip_tags(trim($_REQUEST['username'    ])) : null;
$userData['email'        ] = isset($_REQUEST['email'           ]) ? strip_tags(trim($_REQUEST['email'       ])) : null;
$userData['phone'        ] = isset($_REQUEST['phone'           ]) ? strip_tags(trim($_REQUEST['phone'       ])) : null;
$userData['password'     ] = isset($_REQUEST['password'        ]) ? trim($_REQUEST['password'               ])  : null;
$userData['password_conf'] = isset($_REQUEST['password_conf'   ]) ? trim($_REQUEST['password_conf'          ])  : null;

$userData['status'     ] = isset($_REQUEST['status'     ]) ? (int)  $_REQUEST['status'     ] : null;
$userData['tutor'      ] = isset($_REQUEST['tutor'      ]) ? (bool) $_REQUEST['tutor'      ] : null;
$userData['courseAdmin'] = isset($_REQUEST['courseAdmin']) ? (bool) $_REQUEST['courseAdmin'] : null;

$userData['confirmUserCreate'] = isset($_REQUEST['confirmUserCreate']) ? $_REQUEST['confirmUserCreate'] : null;

$userId = isset($_REQUEST['userId']) ? (int) $_REQUEST['userId'] : null;

$displayResultTable = false;
$displayForm        = true;
$errorMsgList       = array();

if ( $cmd == 'registration' )
{
    /*
     * Two possible ways to enroll a user to a course :
     * Registration of a completly new user from $userData
     * Registration of an existing user form its $userId
     */

    if ( $userData && ! $userId)
    {
        $errorMsgList = user_validate_form_registration($userData);

        if ( count($errorMsgList) == 0 ) $validUserData = true;
        else                             $validUserData = false;

        if ( in_array(get_lang('This official code is already used by another user.'), $errorMsgList) ) // validation exception ...
        {
            $userList = user_search( array('officialCode' => $userData['officialCode']),
                                     claro_get_current_course_id(), false, true);

            $messageList['error'][] = get_lang('This official code is already used by another user.')
                           . '<br />' . get_lang('Take one of these options') . ' : '
                           . '<ul>'
                           . '<li>'
                           . '<a href="#resultTable" onclick="highlight(\'resultTable\');">'
                           . get_lang('Click on the enrollment command beside the concerned user')
                           . '</a>'
                           . '</li>'
                           . '<li>'
                           . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=cancel'. claro_url_relay_context('&amp;') . '">' . get_lang('Cancel the operation') . '</a>'
                           . '</li>'
                           . '</ul>';

             $displayResultTable = true;
        }
        elseif (    ! $userData['confirmUserCreate']
                 && ! ( empty($userData['lastname']) && empty($userData['email']) ) )
        {
            $userList = user_search( array('lastname' => $userData['lastname'    ],
                                           'email'    => $userData['email'       ]),
                                     claro_get_current_course_id(), false, true);
            if ( count($userList) > 0 )
            {
                 // PREPARE THE URL command TO CONFIRM THE USER CREATION
                 $confirmUserCreateUrl = array();
                 foreach($userData as $thisDataKey => $thisDataValue)
                 {
                    $confirmUserCreateUrl[] = $thisDataKey .'=' . urlencode($thisDataValue);
                 }

                 $confirmUserCreateUrl = $_SERVER['PHP_SELF']
                                       . '?cmd=registration&amp;'
                                       . implode('&amp;', $confirmUserCreateUrl)
                                       . '&amp;confirmUserCreate=1'
                                       . claro_url_relay_context('&amp;');


                 $messageList['warning'][] .= get_lang('Notice') . '. '
                . get_lang('Users with similar settings exist on the system yet')
                . '<br />' . get_lang('Take one of these options') . ' : '
                . '<ul>'
                . '<li>'
                . '<a href="#resultTable" onclick="highlight(\'resultTable\');">'
                . get_lang('Click on the enrollment command beside the concerned user')
                . '</a>'
                . '</li>'
                . '<li>'
                . '<a href="'.$confirmUserCreateUrl.'">'
                . get_lang('Confirm the creation of a new user')
                . '</a>'
                . '<br /><small>'
                . $userData['lastname'    ] . ' ' . $userData['firstname']
                . $userData['officialCode'] . ' ' . $userData['email']
                . '</small>'
                . '</li>'
                . '<li>'
                . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=cancel'. claro_url_relay_context('&amp;').'">' . get_lang('Cancel the operation') . '</a>'
                . '</li>'
                . '</ul>';

                $displayForm        = false;
                $displayResultTable = true;
            }
        }
        else
        {
            $userList = array();
        }

        if ( count($errorMsgList) > 0 && count($userList) == 0 )
        {
            if (array_key_exists('error', $messageList)) $messageList['error'] = array_merge($messageList['error'], $errorMsgList);
            else                                         $messageList['error'] = $errorMsgList;

        }
    }

    if ( ! $userId && $validUserData && count($userList) == 0 )
    {
        $userData['language'] = null;
        $userId = user_create($userData);

        if ($userId) user_send_registration_mail($userId, $userData);
    }

    if ( $userId )
    {
        $courseRegSucceed = user_add_to_course($userId, claro_get_current_course_id(), $userData['courseAdmin'], $userData['tutor'],false);

    }
    else
    {
        $courseRegSucceed = false;
    }
} // end if $cmd == 'registration'

if ($cmd == 'applySearch')
{
    // search on username, official_code, ...

    $displayResultTable = TRUE;

    if ( ! (   empty($userData['lastname'    ])
            && empty($userData['email'       ])
            && empty($userData['username'    ])
            && empty($userData['officialCode']) ) )
    {
        $userList = user_search( array('lastname'     => $userData['lastname'],
                                       'email'        => $userData['email'],
                                       'officialCode' => $userData['officialCode'],
        							   'username' 	  => $userData['username']),
                                       claro_get_current_course_id());
    }
    else
        $userList = array();
} // if $cmd == 'applySearch'

// Send mail notification
if ( $courseRegSucceed )
{
    $userData = user_get_properties($userId);

    user_send_enroll_to_course_mail($userId, $userData );
    // display message
    $messageList['info'][]= get_lang('%firstname %lastname has been registered to your course',
                            array ( '%firstname' => $userData['firstname'],
                                    '%lastname'  => $userData['lastname'])
                           );
}


/*=====================================================================
 Display Section
 =====================================================================*/

$htmlHeadXtra[] =
"<script>
highlight.previousValue = new Array();

function highlight(elementId)
{
	if (highlight.previousValue[elementId] == null)
	{
		this.element = document.getElementById(elementId);
		highlight.previousValue[elementId] = this.element.style.border;
		this.element.style.border='solid 2px red';
		setTimeout('highlight(\"' + elementId + '\")', 700);
	}
	else
	{
		document.getElementById(elementId).style.border=highlight.previousValue[elementId];
		delete highlight.previousValue[elementId];
	}
}
</script>";

// display header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title(array('mainTitle' =>$nameTools, 'supraTitle' => get_lang('Users')),
                'help_user.php');
echo claro_html_msg_list($messageList);

if ( $courseRegSucceed )
{
    echo '<p><a href="user.php' . claro_url_relay_context('?') . '">&lt;&lt; ' . get_lang('Back to user list') . '</a></p>' . "\n";
}
else
{
    if ($displayResultTable) //display result of search (if any)
    {
        $regUrlAddParam = '';
        if ( $userData['tutor'        ] ) $regUrlAddParam .= '&amp;tutor=1';
        if ( $userData['courseAdmin'  ] ) $regUrlAddParam .= '&amp;courseAdmin=1';

        echo '<a name="resultTable"></a>'
        .    '<table id="resultTable" class="claroTable emphaseLine" border="0" cellspacing="2">' . "\n"
        .    '<thead>' . "\n"
        .    '<tr class="superHeader">'
        .    '<th colspan="6">' . get_lang('Search result') . '</th>'
        .    '</tr>'
        .    '<tr class="headerX" align="center" valign="top">' . "\n"
        .    '<th>' . get_lang('Last name')           . '</th>' . "\n"
        .    '<th>' . get_lang('First name')          . '</th>' . "\n"
        .    '<th>' . get_lang('Administrative code') . '</th>' . "\n"
        .    '<th>' . get_lang('Username')               . '</th>' . "\n"
        .    '<th>' . get_lang('Email')               . '</th>' . "\n"
        .    '<th>' . get_lang('Enrol as student')            . '</th>' . "\n"
        .    '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        ;

        foreach ($userList as $thisUser)
        {
           echo '<tr valign="top">' . "\n"
           .    '<td>' . $thisUser['lastname'    ] . '</td>' . "\n"
           .    '<td>' . $thisUser['firstname'   ] . '</td>' . "\n"
           .    '<td>' . $thisUser['officialCode'] . '</td>' . "\n"
           .    '<td>' . $thisUser['username'   ] . '</td>' . "\n"
           .    '<td>' . $thisUser['email'       ] . '</td>' . "\n"
           .    '<td align="center">' . "\n"
           ;

            // deal with already registered users found in result

            if ( empty($thisUser['registered']) )
            {
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=registration'
                .    '&amp;userId=' . $thisUser['uid'] . $regUrlAddParam . claro_url_relay_context('&amp;'). '">'
                .    '<img src="' . get_path('imgRepositoryWeb') . 'enroll.gif" alt="' . get_lang('Enrol as student') . '" />'
                .    '</a>'
                ;
            }
            else
            {
                echo '<span class="highlight">'
                .    get_lang('Already enroled')
                .    '</span>'
                ;
            }

            echo '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
        }

        if ( sizeof($userList) == 0 )
        {
            echo '<td align="center" colspan="5">' . get_lang('No user found') . '</td>';
        }

        echo '</tbody>'
        .    '</table>'
        .    '<hr />'
        ;
    }

    //display form to add a user

    if ($displayForm)
    {
        echo '<p>' . get_lang('Add user manually') . ' :</p>'
        .    '<p>' . get_lang('He or she will receive email confirmation with login and password') . '</p>' . "\n"
        .    user_html_form_add_new_user($userData)
        ;
    }
} // end else of if ( $courseRegSucceed )

// display footer
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>