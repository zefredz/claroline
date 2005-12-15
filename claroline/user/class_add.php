<?php // $Id$


$tlabelReq = "CLUSR___";
require '../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);
if ( !$is_courseAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/admin.lib.inc.php';
include($includePath."/lib/user.lib.php");
require_once $includePath . '/lib/class.lib.php';
include($includePath.'/lib/claro_mail.lib.inc.php');

// javascript confirm pop up declaration for header

$htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\"" . clean_str_for_javascript(get_lang('ConfirmEnrollClassToCourse')) . "\"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_users           = $tbl_mdb_names['user'             ];
$tbl_courses_users   = $tbl_rel_course_user;
$tbl_rel_users_groups= $tbl_cdb_names['group_rel_team_user'    ];
$tbl_groups          = $tbl_cdb_names['group_team'             ];
$tbl_class           = $tbl_mdb_names['user_category'];
$tbl_class_user      = $tbl_mdb_names['user_rel_profile_category'];

/*---------------------------------------------------------------------*/
/*----------------------EXECUTE COMMAND SECTION------------------------*/
/*---------------------------------------------------------------------*/

if (isset($_REQUEST['cmd']))
     $cmd = $_REQUEST['cmd'];
else $cmd = null;
  
switch ($cmd)
{  
  //Open a class in the tree
  case "exOpen" : 
    $_SESSION['class_add_visible_class'][$_REQUEST['class']]="open";      
    break;
      
  //Close a class in the tree
  case "exClose" : 
    $_SESSION['class_add_visible_class'][$_REQUEST['class']]="close";      
    break;
      
  // subscribe a class to the course    
  case "subscribe" :           
    $dialogBox = "<b>Class ".$_REQUEST['classname']. " " . get_lang('HasBeenEnrolled') . "</b><br />";
    $sql = " SELECT U.`user_id`,
                    U.`nom` as `lastname` , 
        		    U.`prenom` as `firstname` , 
        		    U.`username` , 
        		    U.`email` , 
        		    U.`officialCode` , 
        		    U.`phoneNumber` as `phone` 
               FROM `".$tbl_class_user."` AS CU,`".$tbl_users."` AS U 
               WHERE CU.`user_id`=U.`user_id` AND CU.`class_id`='". (int)$_REQUEST['class']."'  
               ORDER BY U.`nom`";
    $user_list = claro_sql_query_fetch_all($sql);
      
    foreach ($user_list as $user)
    {        
        $user_id = $user['user_id'];

        if ( user_add_to_course($user['user_id'], $_cid) )
    	{   
            // send mail to user
            user_send_enroll_to_course_mail ($user_id, $user);
            // add message 
	        $dialogBox .= $user['firstname']." ".$user['lastname']. " " . get_lang('IsNowRegistered') . "<br />";
        }
    	else
	    {
            switch (claro_failure::get_last_failure())
            {
                case 'already_enrolled_in_course' : 
	                $dialogBox .= $user['firstname']." ".$user['lastname']." " . get_lang('IsAlreadyRegistered') . "<br />";
                    break;
        	    default: 
	                $dialogBox .= $user['firstname']." ".$user['lastname']." " . get_lang('UnableToEnrollInCourse') . "<br />";
            }            
        }
    }
      
    break;   
}

/*---------------------------------------------------------------------*/
/*----------------------FIND information SECTION-----------------------*/
/*---------------------------------------------------------------------*/

$sql = "SELECT * 
        FROM `".$tbl_class."` 
        ORDER BY `name`";
$class_list = claro_sql_query_fetch_all($sql);

/*---------------------------------------------------------------------*/
/*----------------------DISPLAY SECTION--------------------------------*/
/*---------------------------------------------------------------------*/

// find which display is to be used

$display = "tree";

// set bredcrump


$nameTools = get_lang('EnrollClass');
$interbredcrump[]    = array ('url' => "user.php", 'name' => get_lang('Users'));

// display top banner

include $includePath . '/claro_init_header.inc.php';

// Display tool title

echo claro_disp_tool_title(get_lang('AddClass'));

// Display Forms or dialog box (if needed)

if(isset($dialogBox)&& $dialogBox!="")
{
    echo claro_disp_message_box($dialogBox);
}

switch ($display)
{

    case "tree";

    
    // display tool links

    echo "<a class=\"claroCmd\" href=\"user.php\">".get_lang('BackToList')."</a><br /><br />";

    // display cols headers

        echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">\n"
            ." <thead>\n"
            ."  <tr class=\"headerX\">\n"
            ."    <th>\n"
            ."      get_lang('Class')\n"
            ."    </th>\n"
            ."    <th>\n"
            ."      get_lang('Users')\n"
            ."    </th>\n"
            ."    <th>\n"
            ."      get_lang('SubscribeToCourse')\n"
            ."    </th>\n"
            ."  </tr>\n"
            ."</thead>\n";

    // display Class list (or tree)
        echo "<tbody>\n";
        display_tree_class_in_user($class_list);
        echo "</tbody>\n</table>\n";
        break;

    case "class_added" :
        echo get_lang('DispClassAdded');
        break;
}

// footer banner

include $includePath . '/claro_init_footer.inc.php';
?>
