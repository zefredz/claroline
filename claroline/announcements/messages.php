<?php // $Id$
/**
 * CLAROLINE
 *
 * This modules allows to send messages to some chosen users groups from a course
 *
 * @todo keep trace of message sent
 * @todo translate spanish code
 * @todo factorise direct sql
 * @todo this code must works without js
 * @todo abstract sending following preffered notification way of user
 * @todo add roles as types of target to send to "all tutor, course_admin, students,.."
 *
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLANN
 *
 * @author Claro Team <cvs@claroline.net>
 */


/*
* > > > > > > > > > > > > MESSAGES MODULE < < < < < < < < < < < <
*
* Code    borrowed from parts
*
* originally written by Thomas Depraetere (depraetere@ipm.ucl.ac.be) - January 15 2002
* partially rewritten by Hugues Peeters (peeters@ipm.ucl.ac.be)      - April 19 2002,
* improved by Pablo Rey & Miguel Rubio (http://aula.cesga.es)        - February 2003
* partially rewritten by Roan Embrechts (roan_embrechts@yahoo.com)      - September 2003
* changes by Miguel Rubio (teleensino@cesga.es)                     - October 2003
* Refactored by Hugues Peeters (peeters@ipm.ucl.ac.be)               - October 30 2003
*
*/

/**************************************
CLAROLINE MAIN SETTINGS
**************************************/
$gidReset = true;
require '../inc/claro_init_global.inc.php'; //    settings initialisation

if ( ! claro_is_in_a_course() || ! claro_is_user_authenticated() ) claro_disp_auth_form(true);
if ( ! claro_is_course_manager() ) claro_die(get_lang('Not allowed'));

// get shared lib
include_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

$htmlHeadXtra[] = "<script type=\"text/javascript\" language=\"JavaScript\">

<!-- Begin javascript menu swapper

function move(fbox,    tbox)
{
    var    arrFbox    = new Array();
    var    arrTbox    = new Array();
    var    arrLookup =    new    Array();

    var    i;
    for    (i = 0;    i <    tbox.options.length; i++)
    {
        arrLookup[tbox.options[i].text]    = tbox.options[i].value;
        arrTbox[i] = tbox.options[i].text;
    }

    var    fLength    = 0;
    var    tLength    = arrTbox.length;

    for(i =    0; i < fbox.options.length;    i++)
    {
        arrLookup[fbox.options[i].text]    = fbox.options[i].value;

        if (fbox.options[i].selected &&    fbox.options[i].value != \"\")
        {
            arrTbox[tLength] = fbox.options[i].text;
            tLength++;
        }
        else
        {
            arrFbox[fLength] = fbox.options[i].text;
            fLength++;
        }
    }

    arrFbox.sort();
    arrTbox.sort();
    fbox.length    = 0;
    tbox.length    = 0;

    var    c;
    for(c =    0; c < arrFbox.length; c++)
    {
        var    no = new Option();
        no.value = arrLookup[arrFbox[c]];
        no.text    = arrFbox[c];
        fbox[c]    = no;
    }
    for(c =    0; c < arrTbox.length; c++)
    {
        var    no = new Option();
        no.value = arrLookup[arrTbox[c]];
        no.text    = arrTbox[c];
        tbox[c]    = no;
    }
}

function valida()
{
    var f =    document.datos;
    var dat;

    var incorreo = f.elements['incorreo[]'];

    if (incorreo.length <    1) {
        alert(\"" . clean_str_for_javascript(get_lang('You must select some users')) . "\");
        return false;
    }
    for    (var i=0; i<incorreo.length; i++)
        incorreo[i].selected = incorreo[i].checked = true

    dat=f.emailContent.value;
    if(dat.length == 0)
    {
        //old: Debe    introducir el Texto    del    Mensaje
        alert(\"" . clean_str_for_javascript(get_lang('You must introduce the message text')) . "\");
        f.emailContent.focus();
        f.emailContent.select();
        return false;
    }

    f.submit();
    return true;
}

//    End    -->
</script>";

$interbredcrump[]= array ('url' => '../announcements/announcements.php', 'name' => get_lang('Announcement'));

$nameTools = get_lang('Messages');

include('../inc/claro_init_header.inc.php');

/*
* DB tables definition
*/

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_group      = $tbl_cdb_names['group_team'];
$tbl_groupUser  = $tbl_cdb_names['group_rel_team_user'];

$tbl_user       = $tbl_mdb_names['user'];
$tbl_courseUser = $tbl_mdb_names['rel_course_user'];

/*
* Various connection variables from the initialisation scripts
*/
$_user   = claro_get_current_user_data();
$_course = claro_get_current_course_data();
$courseCode      = $_course['officialCode'];
$courseName      = $_course['name'        ];
$senderFirstName = $_user  ['firstName'   ];
$senderLastName  = $_user  ['lastName'    ];
$senderMail      = $_user  ['mail'        ];

echo claro_html_tool_title(get_lang('Messages'));

/*
* DEFAULT DISPLAY SETTINGS
*/

$displayForm = TRUE;

/*
* SUBMIT ANNOUNCEMENT COMMAND
*/

if ( isset($_REQUEST['submitAnnouncement']) )
{

    $userIdList = array();
    $groupIdList = array();

    if ( isset($_REQUEST['incorreo']) )
    {
        /*
         * Explode the values of incorreo in groups and users
         */

        foreach($_REQUEST['incorreo'] as $thisIncorreo)
        {
            list($type, $elmtId) = explode(':', $thisIncorreo);

            switch($type)
            {
                case 'GROUP':
                $groupIdList[] = $elmtId;
                break;

                case 'USER':
                $userIdList[] = $elmtId;
                break;
            }

        } // end while

        /*
        * Select the students of the different groups
        */

        if ( !empty($groupIdList) )
        {
            $groupIdList = implode(', ',$groupIdList);

            $sql = "SELECT `user`
                    FROM `".$tbl_groupUser."` AS `user_group`
                    WHERE `team` IN (".$groupIdList.")";

            $groupMemberList = claro_sql_query_fetch_all($sql);

            if ( is_array($groupMemberList) && !empty($groupMemberList) )
            {
                foreach ( $groupMemberList as $groupMember )
                {
                    $userIdList[] = $groupMember['user']; // complete the user id list ...
                }
            }
        }

        /*
        * Send the differents mails
        */

        // email subject
        $emailSubject = '[' . get_conf('siteName') . ' - '
                      . $_course['officialCode'] . '] '
                      . get_lang('Message from your lecturer');

        // email content
        $emailBody = $_REQUEST['emailContent'] . "\n" . "\n"
        .            '--' . "\n"
        .            $senderFirstName . ' ' . $senderLastName . "\n"
        .            $_course['name'] . ' (' . $_course['categoryName'] . ')' . "\n"
        .            get_conf('siteName') . "\n"
        .            '(' . get_lang('Message from your lecturer') . ')'
        ;

        $countUnvalid = 0;
        $messageFailed = '';

        $sentMailCount = claro_mail_user($userIdList, $emailBody, $emailSubject,
                             $senderMail, $senderFirstName.' '.$senderLastName);

        $message = '<p>' . get_lang('Message sent') . '<p>';

        $unsentMailCount = count($userIdList) - $sentMailCount;

        if ( $unsentMailCount > 0 )
        {
            $messageUnvalid    =  get_block('blockUsersWithoutValidEmail',
                    array('%userQty' => count($userIdList),
                          '%userInvalidQty' => $unsentMailCount,
                          '%messageFailed'  => $messageFailed
                    ));

            $message .= $messageUnvalid;
        }

    } // end if - $_REQUEST['incorreo']

} // end if - $_REQUEST['submitAnnouncement']

/*
* DISPLAY ACTION MESSAGE
*/

if ( !empty($message) )
{
    echo claro_html_message_box($message)
    .    '<br />' . "\n"
    .    '<a href="' . $_SERVER['PHP_SELF'] . '">&lt;&lt;&nbsp;' . get_lang('Return to the list') . '</a>'
    .    '<br />' . "\n"
    ;

    $displayForm = FALSE;
}

/*----------------------------------------
DISPLAY FORM    TO FILL    AN ANNOUNCEMENT
(USED FOR ADD AND MODIFY)
--------------------------------------*/

if ( $displayForm == TRUE )
{
    /*
    * Get user    list of    this course
    */

    $sql =    "SELECT `u`.`nom`     AS `lastName`,
                      `u`.`prenom`  AS `firstName`,
                      `u`.`user_id` AS `uid`
                 FROM `" . $tbl_user .     "` AS `u`
                    , `" . $tbl_courseUser."` AS `cu`
                 WHERE `cu`.`code_cours` = '" . addslashes(claro_get_current_course_id()) . "'
                 AND `cu`.`user_id` = `u`.`user_id`
                 ORDER BY `u`.`nom`, `u`.`prenom`";

    $singleUserList = claro_sql_query_fetch_all($sql);

    $userList = array();

    if ( is_array($singleUserList) && !empty($singleUserList) )
    {
        foreach ( $singleUserList as $singleUser  )
        {
            $userList[] = $singleUser;
        }
    }

    /*
    * Get group list of this course
    */

    $sql = "SELECT `g`.`id`,
                   `g`.`name`,
                    COUNT(`gu`.`id`) AS `userNb`
            FROM `" . $tbl_group . "` AS `g` LEFT JOIN `" . $tbl_groupUser . "` AS `gu`
            ON `g`.`id` = `gu`.`team`
            GROUP BY `g`.`id`";

    $groupSelect = claro_sql_query_fetch_all($sql);

    $groupList = array();

    if ( is_array($groupSelect) && !empty($groupSelect) )
    {
        foreach ( $groupSelect as $groupData  )
        {
            $groupList[] = $groupData;
        }
    }


    /*
    * Create Form
    */

    echo get_lang('To send a message, select groups of users (marked with a * in the front) or single users from the list on the left.') . "\n" ;

    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="datos" '
    .    'onSubmit="return valida();">' . "\n"
    .    claro_form_relay_context()
    .    '<center>' . "\n"
    .    '<table border="0" cellspacing="3" cellpadding="4">' . "\n"
    .    '<tr valign="top" align="center">'
    .    '<td>' . "\n"
    .    '<p><b>' . get_lang('User list') . '</b></p>' . "\n"
    .    '<select name="nocorreo[]" size="15" multiple="multiple">' . "\n"
    ;

    if ( $groupList )
    {
        foreach( $groupList as $thisGroup )
        {
            echo '<option value="GROUP:' . $thisGroup['id'] . '">'
            .    '* ' . $thisGroup['name'] . ' (' . $thisGroup['userNb'] . ' ' . get_lang('Users') . ')'
            .    '</option>' . "\n";
        }
    }

    echo '<option value="">'
    .    '---------------------------------------------------------'
    .    '</option>' . "\n"
    ;

    // display user list

    foreach ( $userList as $thisUser )
    {
        echo '<option value="USER:' . $thisUser['uid'] . '">'
        .    ucwords(strtolower($thisUser['lastName'] . ' ' . $thisUser['firstName']))
        .    '</option>' . "\n"
        ;
    }

    // WATCH OUT ! form elements are called by numbers "form.element[3]"...
    // because select name contains "[]" causing a javascript
    // element name problem List of selected users

    echo '</select>' . "\n"
    .    '</td>' . "\n"
    .    '<td valign="middle">' . "\n"
    .    '<input type="button" onClick="move(this.form.elements[\'nocorreo[]\'],this.form.elements[\'incorreo[]\'])" value="   >>   " />' . "\n"
    .    '<p>&nbsp;</p>' . "\n"
    .    '<input type="button" onClick="move(this.form.elements[\'incorreo[]\'],this.form.elements[\'nocorreo[]\'])" value="   <<   " />' . "\n"
    .    '</td>' . "\n"
    .    '<td>' . "\n"
    .    '<p>' . "\n"
    .    '<b>' . get_lang('Selected Users') . '</b></p>' . "\n"
    .    '<p>'
    .    '<select name="incorreo[]" size="15" multiple="multiple" style="width:200" width="20">'
    .    '</select>'
    .    '</p>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tr>' . "\n"
    .    '<td colspan="3">' . "\n"
    .    '<b>' . get_lang('Announcement') . '</b>' . "\n"
    .    '<br />' . "\n"
    .    '<center>'
    .    '<textarea wrap="physical" rows="7" cols="60" name="emailContent"></textarea>'
    .    '</center>'
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tr>' . "\n"
    .    '<td colspan="3" align="center">' . "\n"
    .    '<input type="submit" name="submitAnnouncement" value="' . get_lang('Submit') . '" />'
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    ;

} // end if - $displayForm ==  TRUE

echo '</table>' . "\n\n"
.    '</center>' . "\n\n"
.    '</form>' . "\n\n"
;

include (get_path('incRepositorySys') . '/claro_init_footer.inc.php');
?>
