<?php // $Id$


$tlabelReq = "CLUSR___";
require '../inc/claro_init_global.inc.php';

if (!($_cid)) 	claro_disp_select_course();

include($includePath."/lib/admin.lib.inc.php");

// javascript confirm pop up declaration for header

$htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\"" . clean_str_for_javascript($langConfirmEnrollClassToCourse) . "\"))
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
        $dialogBox = "<b>Class ".$_REQUEST['classname']." $langHasBeenEnrolled </b><br>";
        $sql = "SELECT * FROM `".$tbl_class_user."` AS CU,`".$tbl_users."` AS U WHERE CU.`user_id`=U.`user_id` AND CU.`class_id`='".$_REQUEST['class']."'  ORDER BY U.`nom`";
        $user_list = claro_sql_query_fetch_all($sql);
      
        foreach ($user_list as $user)
        {        
            if (add_user_to_course($user['user_id'], $_cid, true))
            {     
	            $dialogBox .= $user['prenom']." ".$user['nom']." $langIsNowRegistered<br>";
	        }
            else
	        {
	            $dialogBox .= $user['prenom']." ".$user['nom']." $langIsAlreadyRegistered<br>";
	        }
        }
      
        break;   
}

/*---------------------------------------------------------------------*/
/*----------------------FIND information SECTION-----------------------*/
/*---------------------------------------------------------------------*/

$sql = "SELECT * FROM `".$tbl_class."` ORDER BY `name`";
$class_list = claro_sql_query_fetch_all($sql);


/*---------------------------------------------------------------------*/
/*----------------------DISPLAY SECTION--------------------------------*/
/*---------------------------------------------------------------------*/

// find which display is to be used

$display = "tree";

// set bredcrump


$nameTools = $langAddClass;
$interbredcrump[]    = array ("url"=>"user.php", "name"=> $langUsers);

// display top banner

include($includePath."/claro_init_header.inc.php");

// Display tool title

claro_disp_tool_title($langAddAClassToCourse);

// Display Forms or dialog box (if needed)

if($dialogBox)
{
    claro_disp_message_box($dialogBox);
}

switch ($display)
{

    case "tree";

    
    // display tool links

    echo "<a class=\"claroCmd\" href=\"user.php\">".$langBackToList."</a><br><br>";

    // display cols headers

        echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">\n"
            ." <thead>\n"
            ."  <tr class=\"headerX\">\n"
            ."    <th>\n"
            ."      $langClass\n"
            ."    </th>\n"
            ."    <th>\n"
            ."      $langUsers\n"
            ."    </th>\n"
            ."    <th>\n"
            ."      $langSubscribeToCourse\n"
            ."    </th>\n"
            ."  </tr>\n"
            ."</thead>\n";

    // display Class list (or tree)
        echo "<tbody>\n";
        display_tree($class_list);
        echo "</tbody>\n</table>\n";
        break;

    case "class_added" :
        echo $langDispClassAdded;
        break;
}

// footer banner

include($includePath."/claro_init_footer.inc.php");

// END OF OUTPUT

/*------------------------Needed function for recursion in tree --------------------------*/


/**
 * Display the tree of classes
 *
 * @author Guillaume Lederer
 * @param  list of all the classes informations of the platform
 * @param  list of the classes that must be visible
 * @return 
 *
 * @see
 *
 */

function display_tree($class_list, $parent_class = null, $deep = 0) 
{

    //global variables needed    

    global $clarolineRepositoryWeb;
    global $tbl_class_user; 
    global $langUsersMin;
    global $imgRepositoryWeb;
    global $langSubscribeToCourse;

    foreach ($class_list as $cur_class)
    {
        
	if (($parent_class==$cur_class['class_parent_id']))
        {
            
	    //Set space characters to add in name display
	    
	    $blankspace = "&nbsp;&nbsp;&nbsp;";	
	    for ($i = 0; $i < $deep; $i++) 
	    {
                $blankspace .= "&nbsp;&nbsp;&nbsp;";
            } 
    
	    //see if current class to display has children
	    
	    $has_children = FALSE;
	    foreach ($class_list as $search_parent)
            {
	        if ($cur_class['id'] == $search_parent['class_parent_id'])
		{    
		    $has_children = TRUE;
		    break;
		}
	    }
	    
	    //Set link to open or close current class
	    
	    if ($has_children)
	    {
	        if ($_SESSION['class_add_visible_class'][$cur_class['id']]=="open")
		{
		    $open_close_link = "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exClose&amp;class=".$cur_class['id']."\">\n"
		                      ."   <img src=\"".$imgRepositoryWeb."minus.gif\" border=\"0\" >\n"
				      ."</a>\n";
		}
		else
		{
		    $open_close_link = "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exOpen&amp;class=".$cur_class['id']."\">\n"
		                      ."  <img src=\"".$imgRepositoryWeb."plus.gif\" border=\"0\" >\n"
				      ."</a>\n";
		}    
	    }
	    else
	    {
	        $open_close_link = "°"; 
	    }
	    
	    //DISPLAY CURRENT ELEMENT (CLASS)

	      //Name
		
	    echo "  <tr>\n"
                ."<td>\n"
                ."    ".$blankspace.$open_close_link." ".$cur_class['name']
                ."  </td>\n";

	      //Users
	    
	    $sqlcount="SELECT COUNT(`user_id`) AS qty_user FROM `".$tbl_class_user ."` WHERE `class_id`='".$cur_class['id']."'";
	    $resultcount = claro_sql_query_fetch_all($sqlcount);   
	    $qty_user = $resultcount[0]['qty_user'];
	    
	    echo "  <td align=\"center\">\n"
	        .$qty_user."  ".$langUsersMin." \n"
		."  </td>\n";
		
	      //add to course link	
			
            echo "  <td align=\"center\">\n"
	        ."    <a onClick=\"return confirmation('".clean_str_for_javascript($cur_class['name'])."');\" href=\"".$_SERVER['PHP_SELF']."?cmd=subscribe&amp;class=".$cur_class['id']."&amp;classname=".$cur_class['name']."\">\n"
                ."      <img src=\"".$imgRepositoryWeb."enroll.gif\" border=\"0\" >\n"
	        ."    </a>\n"
		."  </td>\n";
	    
            echo "</tr>\n";
	    
	    // RECURSIVE CALL TO DISPLAY CHILDREN
	    
	    if ($_SESSION['class_add_visible_class'][$cur_class['id']]=="open")
	    {
	        display_tree($class_list, $cur_class['id'], $deep+1);
	    }	    
	}
    }    
}
?>
