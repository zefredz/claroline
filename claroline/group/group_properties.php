<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 *
 * @package CLGRP
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
$tlabelReq = 'CLGRP';
require '../inc/claro_init_global.inc.php';

// $_groupProperties = claro_get_main_group_properties(claro_get_current_course_id());

include_once get_path('incRepositorySys') . '/lib/group.lib.inc.php';

// display login form
if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

// check user right
if ( ! claro_is_allowed_to_edit() )
{
    claro_die(get_lang("Not allowed"));
}

$nameTools = get_lang("Groups settings");
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Groups'), 'group.php' );

$_groupProperties = claro_get_main_group_properties(claro_get_current_course_id());


// session_register('_groupProperties');
$_SESSION['_groupProperties'] =& $_groupProperties;

$registrationAllowedInGroup = $_groupProperties ['registrationAllowed'];
$groupPrivate               = $_groupProperties ['private'];

if ( get_conf('multiGroupAllowed') )
{
    if ($_groupProperties ['nbGroupPerUser'] == 1)
    {
        $checkedNbGroupPerUser['ONE'] = 'checked="checked"';
    }
    elseif ($_groupProperties ['nbGroupPerUser'] > 1)
    {
        $checkedNbGroupPerUser['MANY'] = 'checked="checked"';
    }
    else//if (is_null($_groupProperties ['nbGroupPerUser']))
    {
        $checkedNbGroupPerUser['ALL'] = 'checked="checked"';
    }
}

$groupToolList = get_group_tool_list();

$out = '';

$out .= claro_html_tool_title( array('supraTitle' => get_lang("Groups"), 'mainTitle' => $nameTools));

$out .= '<form method="post" action="group.php">' . "\n"
.    claro_form_relay_context()
.    '<table border="0" width="100%" cellspacing="0" cellpadding="4">' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top">' . "\n"
.    '<b>' . get_lang("Registration") . '</b>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top">' . "\n"
.    '<span class="item">' . "\n"
.    '<input type="checkbox" name="self_registration" id="self_registration" value="1" '
.    (($registrationAllowedInGroup) ?  'checked="checked"':'')  . '  />' . "\n"
.    '<label for="self_registration" >'
.    get_lang("Students are allowed to self-register in groups")
.    '</label>' . "\n"
.    '</span>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
;

if ( get_conf('multiGroupAllowed') )
{
    if (is_null($_groupProperties['nbGroupPerUser']))
    {
        $nbGroupsPerUserShow = "ALL";
    }
    else
    {
        $nbGroupsPerUserShow = $_groupProperties ['nbGroupPerUser'];
    }

    $selector_nb_groups = '<select name="limitNbGroupPerUser" >' . "\n";
    for( $i = 1; $i <= 10; $i++ )
    {
        $selector_nb_groups .=  '<option value="'.$i.'"'
        . ( $nbGroupsPerUserShow == $i ? ' selected="selected" ' : '')
        .    '>' . $i . '</option>' ;
    }

    $selector_nb_groups .= '<option value="ALL" '
    . ($nbGroupsPerUserShow == "ALL" ? ' selected="selected" ' : '')
    . '>ALL</option>'
    . '</select>' ;

    $out .= '<tr>' . "\n"
    .    '<td valign="top">' . "\n"
    .    '<b>' . get_lang("Limit") . '</b>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    .    '<tr>' . "\n"
    .    '<td valign="top">' . "\n"
    .    '<span class="item">' . "\n"
    .    get_lang('A user can be a member of maximum %nb groups', array ( '%nb' => $selector_nb_groups )) . "\n"
    .    '</span>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    ;

}
$out .= '<tr>' . "\n"
.    '<td>' . "\n"
.    '<b>' . "\n"
.    get_lang("Access") . "\n"
.    '</b></td>' . "\n"
.    '</tr>' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top">' . "\n"
.    '<span class="item">' . "\n"
.    '<input type="radio" name="private" id="private_1" value="1" '
;
if($groupPrivate) $out .= "checked=\"checked\"";
$out .= '  />' . "\n"
.    '<label for="private_1">' . get_lang("Private") . '</label>' . "\n"
.    '<input type="radio" name="private" id="private_0" value="0" '
;
if(!$groupPrivate) $out .= 'checked="checked"';
$out .= '  />' . "\n"
.    '<label for="private_0">' . get_lang("Public") . '</label>' . "\n"
.    '</span>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
;

$out .= '<tr>' . "\n"
.    '<td valign="top">' . "\n"
.    '<b>' . get_lang("Tools") . '</b>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
;


foreach ($groupToolList as $groupTool)
{
    if( !array_key_exists($groupTool['label'],$_groupProperties['tools']) )
    {
        continue;
    }
    
    $toolName = claro_get_module_name ( $groupTool['label']);


    $out .= '<tr>' . "\n"
    .    '<td valign="top">' . "\n"
    .    '<span class="item">' . "\n"
    .    '<input type="checkbox" name="' . $groupTool['label'] . '" id="' . $groupTool['label'] . '" value="1" '
    ;

    if( isset( $_groupProperties['tools'] [$groupTool['label']] )
       && $_groupProperties['tools'] [$groupTool['label']]) $out .= "checked=\"checked\"";
    $out .= '  />' . "\n"
    .    '<label for="' . $groupTool['label'] . '">' . get_lang($toolName)  . '</label>' . "\n"
    .    '</span>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n"
    ;

}

$out .= '<tr>' . "\n"
.    '<td valign="top">' . "\n"
.    '<input type="submit" name="properties" value="' . get_lang("Ok") . '" />' . "\n"
.    claro_html_button(htmlspecialchars( $_SERVER['HTTP_REFERER'] ), get_lang("Cancel")) . '' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
.    '</form>' . "\n"
;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>