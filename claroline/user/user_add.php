<?php // $Id$

/**
 * CLAROLINE
 *
 * This tool allow to add a user in his course (an din the platform)
 * @version 1.9 $Revision$
 * @copyright 2001-2012 Universite catholique de Louvain (UCL)
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

define ( 'CLUSER_SEARCH_FORM', 'CLUSER_SEARCH_FORM' );
define ( 'CLUSER_ADD_FORM', 'CLUSER_ADD_FORM' );

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

$can_add_single_user     = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_enroll_single_user') )
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

ClaroBreadCrumbs::getInstance()->prepend( 
    get_lang('Users'), 
    Url::Contextualize('user.php') 
);

$dialogBox = new DialogBox();

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

$userData['tutor'      ] = isset($_REQUEST['tutor'      ]) ? (bool) $_REQUEST['tutor'      ] : null;
$userData['courseAdmin'] = isset($_REQUEST['courseAdmin']) ? (bool) $_REQUEST['courseAdmin'] : null;

$userData['confirmUserCreate'] = isset($_REQUEST['confirmUserCreate']) ? $_REQUEST['confirmUserCreate'] : null;

$userId = isset($_REQUEST['userId']) ? (int) $_REQUEST['userId'] : null;

$displayResultTable = false;
$displayForm        = true;
$errorMsgList       = array();
$formToDisplay = CLUSER_SEARCH_FORM;

if ( $cmd == 'rqRegistration' )
{
    if ( ( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() ) )
    {
        $formToDisplay = CLUSER_ADD_FORM;
    }
    else
    {
        $dialogBox->error(get_lang('Not allowed'));
        $cmd = null;
    }
}
elseif ( $cmd == 'registration' && ! $userId )
{
    if ( ! ( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() ) )
    {
        $dialogBox->error(get_lang('Not allowed'));
        $cmd = null;
    }
}

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
                                     claro_get_current_course_id(), false, true, false);

            $dialogBox->error(get_lang('This official code is already used by another user.')
                           . '<br />' . get_lang('Take one of these options') . ' : '
                           . '<ul>'
                           . '<li>'
                           . '<a href="#resultTable">'
                           . get_lang('Click on the enrollment command beside the concerned user')
                           . '</a>'
                           . '</li>'
                           . '<li>'
                           . '<a href="'.Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=cancel') . '">' . get_lang('Cancel the operation') . '</a>'
                           . '</li>'
                           . '</ul>'
                           );

             $displayResultTable = true;
        }
        elseif (    ! $userData['confirmUserCreate']
                 && ! ( empty($userData['lastname']) && empty($userData['email']) ) )
        {
            $userList = user_search( array('lastname' => $userData['lastname'    ],
                                           'email'    => $userData['email'       ]),
                                     claro_get_current_course_id(), false, true, false);
            if ( count($userList) > 0 )
            {
                 // PREPARE THE URL command TO CONFIRM THE USER CREATION
                 $confirmUserCreateUrl = array();
                 
                 foreach($userData as $thisDataKey => $thisDataValue)
                 {
                    $confirmUserCreateUrl[] = $thisDataKey .'=' . urlencode($thisDataValue);
                 }

                 $confirmUserCreateUrl = Url::Contextualize( $_SERVER['PHP_SELF']
                                       . '?cmd=registration&'
                                       . implode('&', $confirmUserCreateUrl)
                                       . '&confirmUserCreate=1' );


                 $dialogBox->warning( get_lang('Notice') . '. '
                    . get_lang('Users with similar settings exist on the system yet')
                    . '<br />' . get_lang('Take one of these options') . ' : '
                    . '<ul>'
                    . '<li>'
                    . '<a href="#resultTable" onclick="highlight(\'resultTable\');">'
                    . get_lang('Click on the enrollment command beside the concerned user')
                    . '</a>'
                    . '</li>'
                    . '<li>'
                    . '<a href="'.htmlspecialchars( $confirmUserCreateUrl ).'">'
                    . get_lang('Confirm the creation of a new user')
                    . '</a>'
                    . '<br /><small>'
                    . $userData['lastname'    ] . ' ' . $userData['firstname']
                    . $userData['officialCode'] . ' ' . $userData['email']
                    . '</small>'
                    . '</li>'
                    . '<li>'
                    . '<a href="'.htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=cancel' )) .'">'
                    . get_lang('Cancel the operation') . '</a>'
                    . '</li>'
                    . '</ul>'
                );

                $displayForm        = false;
                $displayResultTable = true;
            }
        }
        else
        {
            $userList = array();
        }
        
        if( !empty($errorMsgList) && count($userList) == 0 )
        {
            foreach( $errorMsgList as $errorMsg )
            {
                $dialogBox->error($errorMsg);
            }
        }
    }

    if ( ! $userId && $validUserData && count($userList) == 0 )
    {
        $userData['language'] = null;
        $userId = user_create($userData);

        if ($userId) user_send_registration_mail($userId, $userData,claro_get_current_course_id());
    }

    if ( $userId )
    {
        $courseRegSucceed = user_add_to_course($userId, claro_get_current_course_id(), $userData['courseAdmin'], $userData['tutor'],false);
        
        Console::log(
            "{$userId} enroled to course "
            . claro_get_current_course_id()
            . " by " . claro_get_current_user_id(),
                'COURSE_SUBSCRIBE'
        );
    }
    else
    {
        $courseRegSucceed = false;
    }
} // end if $cmd == 'registration'

if ( $cmd == 'applySearch' )
{
    // search on username, official_code, ...

    $displayResultTable = TRUE;

    if ( ! (   empty($userData['lastname'    ])
            && empty($userData['email'       ])
            && empty($userData['username'    ])
            && empty($userData['officialCode']) ) )
    {

        $userList = user_search( array('lastname'     => $userData['lastname'],
                                       'firstname'      => $userData['firstname'],
                                       'email'        => $userData['email'],
                                       'officialCode' => $userData['officialCode'],
                                       'username'       => $userData['username']),
                                       claro_get_current_course_id(),
                                       true,
                                       false,
                                       !claro_is_platform_admin() );
    }
    else
    {
        $userList = array();
    }
} // if $cmd == 'applySearch'

// Send mail notification
if ( $courseRegSucceed )
{
    $userData = user_get_properties($userId);

    user_send_enroll_to_course_mail($userId, $userData,claro_get_current_course_id() );
    // display message
    $dialogBox->success( get_lang('%firstname %lastname has been registered to your course',
                            array ( '%firstname' => $userData['firstname'],
                                    '%lastname'  => $userData['lastname'])
                           )
                     );
}


/*=====================================================================
 Display Section
 =====================================================================*/
/* hack to prevent autocompletion from browser */
$jsloader = JavascriptLoader::getInstance();
$jsloader->load('jquery');

$htmlHeadXtra[] =
'<script type="text/javascript">
    $(document).ready(
        function() {
            $("#password").val("");
        }
    );
</script>';
/* end of hack */

$out = '';

$out .= claro_html_tool_title(
    array('mainTitle' =>$nameTools, 'supraTitle' => get_lang('Users')),
    'help_user.php'
);

$out .= $dialogBox->render();

if ( $courseRegSucceed )
{
    $out .= '<p><a href="' 
        . htmlspecialchars(Url::Contextualize( get_module_entry_url('CLUSR') ) ) 
        . '">&lt;&lt; ' . get_lang('Back to user list') . '</a></p>' . "\n"
        ;
}
else
{
    if ($displayResultTable) //display result of search (if any)
    {
        $enrollmentLabel = $userData['courseAdmin'] 
            ? get_lang('Enrol as teacher') 
            : get_lang('Enrol as student')
            ;
        
        $enrollmentLabel .= $userData['tutor'] 
            ? '&nbsp;-&nbsp;' . get_lang('tutor') 
            : ''
            ;
                
        $regUrlAddParam = '';
        
        if ( $userData['tutor'        ] ) $regUrlAddParam .= '&tutor=1';
        
        if ( $userData['courseAdmin'  ] ) $regUrlAddParam .= '&courseAdmin=1';

        $out .= '<a name="resultTable"></a>'
            . '<table id="resultTable" class="claroTable emphaseLine" border="0" cellspacing="2">' . "\n"
            . '<thead>' . "\n"
            . '<tr class="superHeader">'
            . '<th colspan="6">' . get_lang('Search result') . '</th>'
            . '</tr>'
            . '<tr class="headerX" align="center" valign="top">' . "\n"
            . '<th>' . get_lang('Last name')           . '</th>' . "\n"
            . '<th>' . get_lang('First name')          . '</th>' . "\n"
            . '<th>' . get_lang('Administrative code') . '</th>' . "\n"
            . '<th>' . get_lang('Username')               . '</th>' . "\n"
            . '<th>' . get_lang('Email')               . '</th>' . "\n"
            . '<th>' . $enrollmentLabel            . '</th>' . "\n"
            . '</tr>' . "\n"
            . '</thead>' . "\n"
            . '<tbody>' . "\n"
            ;

        foreach ($userList as $thisUser)
        {
           $out .= '<tr valign="top">' . "\n"
            . '<td>' . htmlspecialchars($thisUser['lastname'    ]) . '</td>' . "\n"
            . '<td>' . htmlspecialchars($thisUser['firstname'   ]) . '</td>' . "\n"
            . '<td>' . htmlspecialchars($thisUser['officialCode']) . '</td>' . "\n"
            . '<td>' . htmlspecialchars($thisUser['username'   ]) . '</td>' . "\n"
            . '<td>' . htmlspecialchars($thisUser['email'       ]) . '</td>' . "\n"
            . '<td align="center">' . "\n"
            ;

            // deal with already registered users found in result
            if ( empty($thisUser['registered']) )
            {
                $out .= '<a href="' 
                    . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                        . '?cmd=registration'
                        . '&userId=' . $thisUser['uid'] . $regUrlAddParam )) 
                    . '">'
                    . '<img src="' . get_icon_url('enroll') . '" alt="' . $enrollmentLabel . '" />'
                    . '</a>'
                    ;
            }
            else
            {
                $out .= '<span class="highlight">'
                    . get_lang('Already enroled')
                    . '</span>'
                    ;
            }

            $out .= '</td>' . "\n"
                . '</tr>' . "\n"
                ;
        }

        if ( sizeof($userList) == 0 )
        {
            $out .= '<td align="center" colspan="5">' . get_lang('No user found') . '</td>';
        }

        $out .= '</tbody>'
            . '</table>'
            . '<hr />'
            ;
    }

    //display form to add a user
    if ( $displayForm )
    {
        if ( ( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() )
            && $formToDisplay == CLUSER_ADD_FORM )
        {
            //if ( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() )
            {
                $out .= '<p>'
                    . '<a class="claroCmd" href="'
                    . htmlspecialchars( Url::Contextualize(
                        $_SERVER['PHP_SELF'] ) )
                    . '">'
                    . '<img src="'.get_icon_url('search').'" alt="" />'
                    . get_lang('Search for an existing user')
                    . '</a>'
                    . ' | '
                    . '<span class="claroCmdDisabled">'
                    . '<img src="'.get_icon_url('user').'" alt="" />'
                    . get_lang('Create a new user').'</span>'
                    . '</p>'
                    ;
            }
            
            //if( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() )
            {
                $tpl = new CoreTemplate('course_user_add.tpl.php');
            
                $out .= $tpl->render();
            }
            /*else
            {
                // claro_die(get_lang('Not allowed'));
            }*/
        }
        else
        {
            if ( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() )
            {
                $out .= '<p>'
                    . '<span class="claroCmdDisabled">'
                    . '<img src="'.get_icon_url('search').'" alt="" />'
                    . get_lang('Search for an existing user')
                    . '</span>'
                    . ' | '
                    . '<a class="claroCmd" href="'
                    . htmlspecialchars( Url::Contextualize(
                        $_SERVER['PHP_SELF']
                        . '?cmd=rqRegistration' ) )
                    . '">'
                    . '<img src="'.get_icon_url('user').'" alt="" />'
                    . get_lang('Create a new user').'</a>'
                    . '</p>'
                    ;
            }
            
            $tpl = new CoreTemplate('course_user_search.tpl.php');
            
            $out .= $tpl->render();            
        }
    }
} // end else of if ( $courseRegSucceed )

$claroline->display->body->appendContent($out);

echo $claroline->display->render();
