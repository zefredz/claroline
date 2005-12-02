<?php // $Id$
/** 
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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

$tlabelReq = 'CLGRP___';
require '../inc/claro_init_global.inc.php';

// display login form
if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

// check user right
if ( ! $is_courseAdmin )
{
    claro_die(get_lang('NotAllowed'));
}

$nameTools = get_lang('GroupProperties');
$interbredcrump[]= array ('url' => 'group.php', 'name' => get_lang('Groups'));

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_course_group_property   = $tbl_cdb_names['group_property'];
$sql = "SELECT `self_registration`,`private`,`nbGroupPerUser`,
               `forum`,`document`,`wiki`,`chat`
        FROM `" . $tbl_course_group_property . "`";

/**
 * This awful code  make usage of a stupid table with only one record.
 * $_groupProperties ['registrationAllowed']
 * $_groupProperties ['private'            ]
 * $_groupProperties ['nbGroupPerUser'     ]
 * arent in fact properties of the courses about groups link to it.
 * $_groupProperties ['tools'] is a course_tool properties to se if 
 * roups can use or not these tools in the groups of this course
*/
$res = claro_sql_query($sql);
list($gpData) = claro_sql_fetch_all($res);
$_groupProperties ['registrationAllowed'] =   $gpData['self_registration'] == 1;
$_groupProperties ['private'            ] = !($gpData['private']           == 1);
$_groupProperties ['nbGroupPerUser'     ] =   $gpData['nbGroupPerUser'];

/**
 * @var $_groupProperties ['tools'] ['forum'    ] true = public
 */
$_groupProperties ['tools'] ['forum'    ] =   $gpData['forum']             == 1;

/**
 * @var $_groupProperties ['tools'] ['document' ]
 * doc is always  aivailable and private
 */
$_groupProperties ['tools'] ['document' ] =   $gpData['document']          == 1;
$_groupProperties ['tools'] ['wiki'     ] =   $gpData['wiki']              == 1;
$_groupProperties ['tools'] ['chat'   ]   =   $gpData['chat']              == 1;
session_register('_groupProperties');
$registrationAllowedInGroup = $_groupProperties ['registrationAllowed'];
$groupPrivate               = $_groupProperties ['private'            ];

if ($multiGroupAllowed)
{
    if ($_groupProperties ['nbGroupPerUser'] == 1)
    {
        $checkedNbGroupPerUser['ONE'] = 'checked="checked"';
    }
    elseif ($_groupProperties ['nbGroupPerUser'] > 1)
    {
        $checkedNbGroupPerUser['MANY'] = 'checked="checked"';
    }
    else//if (is_null($_groupProperties ['nbGroupPerUser'     ]))
    {
        $checkedNbGroupPerUser['ALL'] = 'checked="checked"';
    }
}

include($includePath . '/claro_init_header.inc.php');
echo claro_disp_tool_title( array('supraTitle' => get_lang('Groups'), 'mainTitle' => $nameTools));

?>

<form method="post" action="group.php">
<table border="0" width="100%" cellspacing="0" cellpadding="4">
    <tr>
        <td valign="top">
        <b><?php echo get_lang('GroupSelfRegistration') ?></b>
        </td>
    </tr>

    <tr>
        <td valign="top">
            <span class="item">
            <input type="checkbox" name="self_registration" id="self_registration" value="1" <?php if($registrationAllowedInGroup) echo "checked";    ?> >
            <label for="self_registration" ><?php echo get_lang('GroupAllowStudentRegistration'); ?></label>
            </span>
        </td>
    </tr>
<?php
    if ($multiGroupAllowed)
    {
?>
    <tr>
        <td valign="top">
        <b><?php echo get_lang('GroupLimit') ?></b>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <span class="item">
            <?php echo get_lang('QtyOfUserCanSubscribe_PartBeforeNumber');

            if (is_null($_groupProperties ['nbGroupPerUser']))
            {
                $nbGroupsPerUserShow = "ALL";
            }
            else
            {
                $nbGroupsPerUserShow = $_groupProperties ['nbGroupPerUser'     ];
            }
             ?>
            <select name="limitNbGroupPerUser" >
            <?php
                for( $i = 1; $i <= 10; $i++ )
                {
                    echo '<option value="'.$i.'"'
                    .    ( $nbGroupsPerUserShow == $i ? ' selected="selected" ' : '')
                    .    '>' . $i . '</option>'
                    ;
                }
                echo '<option value="ALL" '
                .    ($nbGroupsPerUserShow == "ALL" ? ' selected="selected" ' : '')
                .    '>ALL</option>'
                ;
            ?>
            </select>
            <?php echo get_lang('QtyOfUserCanSubscribe_PartAfterNumber') ?>
            </span>
        </td>
    </tr>

<?php
    }
?>
    <tr>
        <td><b><?php echo get_lang('Access'); ?></b></td>
    </tr>
    <tr>
        <td valign="top">
            <span class="item">
            <input type="radio" name="private" id="private_1" value="1" <?php
                if(!$groupPrivate)
                    echo "checked"?> >
            <label for="private_1"><?php echo get_lang('Private'); ?></label>
            <input type="radio" name="private" id="private_0" value="0" <?php
                if($groupPrivate)
                    echo "checked"?> >
            <label for="private_0"><?php echo get_lang('Public'); ?></label>
            </span>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <b><?php echo get_lang('GroupTools') ?></b>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <span class="item">
            <?php echo get_lang('GroupForum'); ?>
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="item">
            <?php echo get_lang('GroupDocument') ?>
            </span>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <span class="item">
            <input type="checkbox" name="chat" id="chat" value="1"
            <?php
                if($_groupProperties['tools'] ['chat'])
                    echo "checked" ?> >
            <label for="chat"><?php echo get_lang('Chat'); ?> <?php echo get_lang('GroupAlwaysPrivate'); ?></label>
            </span>
        </td>
    </tr>

    <tr>
        <td valign="top">
            <span class="item">
            <input type="checkbox" name="wiki" id="wiki" value="1"
            <?php
                if($_groupProperties['tools'] ['wiki'])
                    echo "checked" ?> >
            <label for="wiki"><?php echo get_lang('Wiki'); ?></label>
            </span>
        </td>
    </tr>

    <tr>
        <td valign="top">
            <input type="submit" name="properties" value="<?php echo get_lang('Ok') ?>"> 
            <?php echo claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')); ?>
        </td>
    </tr>
</table>
</form>
<?php
include $includePath . '/claro_init_footer.inc.php';
?>
