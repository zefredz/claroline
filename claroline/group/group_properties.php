<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
	  |  This is  just a script tou  print out the for.                      |
	  |  There is no data working.                                           |
      +----------------------------------------------------------------------+
 */

$langFile = "group";
require '../inc/claro_init_global.inc.php';
include('../inc/conf/group.conf.php');
@include('../inc/lib/debug.lib.inc.php');
$nameTools = $langGroupProperties;
$interbredcrump[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_course_group_property   = $tbl_cdb_names['group_property'         ];
$sql = "
SELECT `self_registration`,`private`,`nbGroupPerUser`,`forum`,`document`,`wiki`,`chat`
	FROM `".$tbl_course_group_property."`";

/* 
This awful code  make usage of a table with only one record.
	$_groupProperties ['registrationAllowed']
	$_groupProperties ['private'            ]
	$_groupProperties ['nbGroupPerUser'     ]
	arent in fact properties of the courses about groups link to it.
	$_groupProperties ['tools'] is a course_tool properties to se if 
	groups can use or not these tools in the groups of this course
*/
$res = claro_sql_query($sql);
list($gpData) = claro_sql_fetch_all($res);
$_groupProperties ['registrationAllowed'] =   $gpData['self_registration'] == 1;
$_groupProperties ['private'            ] = !($gpData['private']           == 1);
$_groupProperties ['nbGroupPerUser'     ] =   $gpData['nbGroupPerUser'];
$_groupProperties ['tools'] ['forum'    ] =   $gpData['forum']             == 1;
$_groupProperties ['tools'] ['document' ] =   $gpData['document']          == 1;
$_groupProperties ['tools'] ['wiki'     ] =   $gpData['wiki']              == 1;
$_groupProperties ['tools'] ['chat'   ]   =   $gpData['chat']              == 1;
session_register("_groupProperties");
$registrationAllowedInGroup = $_groupProperties ['registrationAllowed'];
$groupPrivate 				= $_groupProperties ['private'            ];

if ($multiGroupAllowed)
{
    if ($_groupProperties ['nbGroupPerUser'     ]==1)
    {
        $checkedNbGroupPerUser["ONE"] = "checked=\"checked\"";
    }
    elseif ($_groupProperties ['nbGroupPerUser'     ]>1)
    {
        $checkedNbGroupPerUser["MANY"] = "checked=\"checked\"";
    }
    else//if (is_null($_groupProperties ['nbGroupPerUser'     ]))
    {
    $checkedNbGroupPerUser["ALL"] = "checked=\"checked\"";
		}
	}

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title( array('mainTitle' => $nameTools,
                             'subTitle' => $nameTools));

?>

<form method="post" action="group.php">
<table border="0" width="100%" cellspacing="0" cellpadding="4">
	<tr>
		<td valign="top">
				<input type="checkbox" name="self_registration" id="self_registration" value="1" <?php if($registrationAllowedInGroup) echo "checked";	?> >
				<label for="self_registration" >
					<?php echo $langGroupAllowStudentRegistration; ?>
				</label>
		</td>
	</tr>
<?php
	if ($multiGroupAllowed)
	{
?>
	<tr>
		<td valign="top">
        <b><?php echo $langGroupLimit ?></b><br>
<!--
			<?php echo $langQtyOfUserCanSubscribe_PartBeforeNumber ?>
			<ul>
				<LI>
					<input type="radio" name="maxGroupByUser" value="ONE" <?php echo $checkedNbGroupPerUser["ONE"] ?>>
					<?php echo $langOneGroupPerUser ?>
				</LI>
				<LI>
					<input type="radio" name="maxGroupByUser" value="ALL" <?php echo $checkedNbGroupPerUser["ALL"] ?>>
					<?php echo $langAllGroups ?>
				</LI>
				<LI>
					<input type="radio" name="maxGroupByUser" value="MANY" <?php echo $checkedNbGroupPerUser["MANY"] ?>>
					<?php echo $langLimitNbGroupPerUser ?>
					<input type="text" name="limitNbGroupPerUser" value="<?php echo $_groupProperties ['nbGroupPerUser'     ]?>" size="4" align="right" >
				</LI>
			</ul>
-->
			<?php echo $langQtyOfUserCanSubscribe_PartBeforeNumber;

			if (is_null($_groupProperties ['nbGroupPerUser']))
			{
				$nbGroupsPerUserShow = "ALL";
			}
			else
			{
				$nbGroupsPerUserShow = $_groupProperties ['nbGroupPerUser'     ];
			}
			 ?>
			<input type="hidden" name="maxGroupByUser" value="MANY" >
			<select name="limitNbGroupPerUser" >
				<OPTION value="<? echo $nbGroupsPerUserShow ?>" selected="selected" ><?php echo $nbGroupsPerUserShow ?></OPTION>
				<OPTION value="1"  >    1</OPTION>
				<OPTION value="2"  >    2</OPTION>
				<OPTION value="3"  >    3</OPTION>
				<OPTION value="4"  >    4</OPTION>
				<OPTION value="5"  >    5</OPTION>
				<OPTION value="6"  >    6</OPTION>
				<OPTION value="7"  >    7</OPTION>
				<OPTION value="8"  >    8</OPTION>
				<OPTION value="9"  >    9</OPTION>
				<OPTION value="10"  >   10</OPTION>
				<OPTION value="ALL"  >  ALL</OPTION>
				<!--OPTION value="CHOOSE"  ><?php echo $langChooseAValue ?></OPTION-->
			</select>
			<?php echo $langQtyOfUserCanSubscribe_PartAfterNumber ?>


			</fieldset>



		</td>
	</tr>

<?
	}
?>
	<tr>
		<td valign="top">
			<p>
				<b>
					<?php echo $langGroupTools ?>
				</b>
			</p>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<input type="hidden" name="forum" value="1">
			
			<!-- Patch : deactivated Group Forum option for Claroline 1.4.0. 
			     because cannot be changed after group creation
			<?php
				if($_groupProperties['tools'] ["forum"])
					echo "checked"?>--> 

				<?php echo $langGroupForum; ?>
		
			<input type="radio" name="private" id="private_1" value="1" <?
				if(!$groupPrivate)
					echo "checked"?> >
				<label for="private_1"><?php echo $langPrivate; ?></label>
			<input type="radio" name="private" id="private_0" value="0" <?
				if($groupPrivate)
					echo "checked"?> >
				<label for="private_0"><?php echo $langPublic; ?></label>
		</td>
	</tr>
	<tr>
		<td>
			<input type="hidden" name="document" value="1">
			
			<!-- Patch : deactivated Group Forum option for Claroline 1.4.0. 
			     because cannot be changed after group creation
			<?
				if($_groupProperties['tools'] ["document"])
					echo "checked"?> -->


				<?php echo "$langGroupDocument $langGroupDocumentAlwaysPrivate"; ?>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<input type="submit" name="properties" value="<?php echo $langOk ?>">
		</td>
	</tr>
</table>
</form>
<?php
include($includePath."/claro_init_footer.inc.php");
?>
