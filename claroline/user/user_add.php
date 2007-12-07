<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool allow to add a user in his course (an din the platform)
 * @version 1.7 $Revision$
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 * @author Claro Team <cvs@claroline.net>
 * @package CLUSR
 */
/*=====================================================================
 Init Section
 =====================================================================*/ 

$tlabelReq = 'CLUSR___';

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin              ) claro_die($langNotAllowed);

// include configuration file
include $includePath . '/conf/user_profile.conf.php';

// include libraries
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/claro_mail.lib.inc.php';

// Initialise variables
$nameTools        = $langAddAU;
$interbredcrump[] = array ('url' => 'user.php', 'name' => $langUsers);

$messageList        = array();
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

$userData['lastname'        ] = isset($_REQUEST['lastname'        ]) ? strip_tags(trim($_REQUEST['lastname'    ])) : null;
$userData['firstname'       ] = isset($_REQUEST['firstname'       ]) ? strip_tags(trim($_REQUEST['firstname'   ])) : null;
$userData['officialCode'    ] = isset($_REQUEST['officialCode'    ]) ? strip_tags(trim($_REQUEST['officialCode'])) : null;
$userData['username'        ] = isset($_REQUEST['username'        ]) ? strip_tags(trim($_REQUEST['username'    ])) : null;
$userData['email'           ] = isset($_REQUEST['email'           ]) ? strip_tags(trim($_REQUEST['email'       ])) : null;
$userData['phone'           ] = isset($_REQUEST['phone'           ]) ? strip_tags(trim($_REQUEST['phone'       ])) : null;
$userData['password'        ] = isset($_REQUEST['password'        ]) ? trim($_REQUEST['password'               ])  : null;
$userData['password_conf'   ] = isset($_REQUEST['password_conf'   ]) ? trim($_REQUEST['password_conf'          ])  : null;

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
     * Enrollment of a completly new user from $userData
     * Enrollment of an existing user form its $userId
     */

    if ( $userData && ! $userId)
    {
        $errorMsgList = user_validate_form_registration($userData);

        if ( count($errorMsgList) == 0 ) $validUserData = true;
        else                             $validUserData = false;

        if ( in_array($langCodeUsed, $errorMsgList) ) // validation exception ...
        {
            $userList = user_search( array('officialCode' => $userData['officialCode']),
                                     $_cid, false, true);

            $messageList[] = $langCodeUsed
                           . '<br />' . $lang_take_one_of_these_choices . ' : '
                           . '<ul>'
                           . '<li>'
                           . '<a href="#resultTable" onclick="highlight(\'resultTable\');">'
                           . $lang_click_on_the_enrollment_command_beside_the_concerned_user
                           . '</a>'
                           . '</li>'
                           . '<li>'
                           . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=cancel">' . $lang_cancel_the_operation . '</a>'
                           . '</li>'
                           . '</ul>';

             $displayResultTable = true;
        }
        elseif (    ! $userData['confirmUserCreate'] 
                 && ! ( empty($userData['lastname']) && empty($userData['email']) ) )
        {
            $userList = user_search( array('lastname' => $userData['lastname'    ], 
                                           'email'    => $userData['email'       ]),
                                     $_cid, false, true);

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
                                       . '&amp;confirmUserCreate=1';


                 $messageList[] .= $lang_notice . ' '
                . $lang_users_with_similar_settings_exists_on_the_system_yet
                .'<br />' . $lang_take_one_of_these_choices . ' : '
                . '<ul>'
                . '<li>'
                . '<a href="#resultTable" onclick="highlight(\'resultTable\');">'
                . $lang_click_on_the_enrollment_command_beside_the_concerned_user
                . '</a>'
                . '</li>'
                . '<li>'
                . '<a href="'.$confirmUserCreateUrl.'">'
                . $lang_confirm_the_creation_of_a_new_user
                . '</a>'
                . '<br /><small>'
                . $userData['lastname'    ] . ' ' . $userData['firstname']
                . $userData['officialCode'] . ' ' . $userData['email']
                . '</small>'
                . '</li>'
                . '<li>'
                . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=cancel">' . 'Cancel the operation' . '</a>'
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
            $messageList   = array_merge($messageList, $errorMsgList);
        }
    }

    if ( ! $userId && $validUserData && count($userList) == 0 )
    {
        $userId = user_add($userData);
        if ($userId) user_send_registration_mail($userId, $userData);
    }

    if ( $userId )
    {
        $courseRegSucceed = user_add_to_course($userId, $_cid, 
                                               $userData['courseAdmin'],
                                               $userData['tutor'      ]);
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
            && empty($userData['officialCode']) ) )
    {
        $userList = user_search( array('lastname'     => $userData['lastname'], 
                                       'email'        => $userData['email'], 
                                       'officialCode' => $userData['officialCode']),
                                 $_cid);
    }
    else
        $userList = array();
} // if $cmd == 'applySearch'

// Send mail notification
if ( $courseRegSucceed )
{
    user_send_enroll_to_course_mail($userId, user_get_data($userId) );
    $messageList[]= sprintf("$langTheU %s %s $langAddedToCourse.",$userData['firstname'],$userData['lastname']);
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
include($includePath.'/claro_init_header.inc.php');

echo claro_disp_tool_title(array('mainTitle' =>$nameTools, 'supraTitle' => $langUsers),
                'help_user.php');

// message box

if ( count($messageList) > 0 ) 
{
    echo claro_disp_message_box( implode('<br />', $messageList) );
}

if ( $courseRegSucceed ) 
{
    echo '<p><a href="user.php">&lt;&lt; ' .  $langBackToUsersList . '</a></p>' . "\n";
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
        .    '<th colspan="5">' . $langSearchResult . '</th>'
        .    '</tr>'
        .    '<tr class="headerX" align="center" valign="top">' . "\n"
        .    '<th>' . $langLastName     . '</th>' . "\n"
        .    '<th>' . $langFirstName    . '</th>' . "\n"
        .    '<th>' . $langEmail        . '</th>' . "\n"
        .    '<th>' . $langOfficialCode . '</th>' . "\n"
        .    '<th>' . $langSubscription     . '</th>' . "\n"
        .    '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        ;

        foreach ($userList as $thisUser)
        {
           echo '<tr valign="top">'                      . "\n"
           .    '<td>' . $thisUser['lastname'    ] . '</td>' . "\n"
           .    '<td>' . $thisUser['firstname'   ] . '</td>' . "\n"
           .    '<td>' . $thisUser['email'       ] . '</td>' . "\n"
           .    '<td>' . $thisUser['officialCode'] . '</td>' . "\n"
           .    '<td align="center">'                    . "\n"
           ;

            // deal with already registered users found in result

            if ( empty($thisUser['registered']) )
            {
                echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=registration&amp;userId=' . $thisUser['uid'] . $regUrlAddParam . '">'
                .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="'.$langSubscription.'" />'
                .    '</a>'
                ;
            }
            else
            {
                echo '<small class="highlight">' 
                .    $lang_already_enrolled 
                .    '</small>'
                ;
            }

            echo '</td>' . "\n"
                .'</tr>' . "\n";
        }

        if ( sizeof($userList) == 0 )
        {
            echo '<td align="center" colspan="5">' . $langNoUserFound . '</td>';
        }

        echo '</tbody>'
        .    '</table>'
        .    '<hr />'
        ;
    }

    //display form to add a user

    if ($displayForm)
    {
        echo  '<p>' . $langOneByOne . ' :</p>'
            . '<p>' . $langUserOneByOneExplanation . '</p>' . "\n";

        user_display_form_add_new_user($userData);
    }
} // end else of if ( $courseRegSucceed ) 

// display footer
include $includePath . '/claro_init_footer.inc.php';
?>