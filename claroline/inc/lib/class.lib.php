<?php // $Id$
/**
 * CLAROLINE
 *
 * Library for class
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Guillaume Lederer <guillaume@claroline.net>
 *
 * @since 1.6
 */


function register_class_to_course($class_id, $course_code) 
{
    global $lang_p_s_s_has_been_sucessfully_registered_to_the_course_p_name_firstname;
    $tbl_mdb_names  = claro_sql_get_main_tbl();
    $tbl_user       = $tbl_mdb_names['user'];
    $tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];
    //get the list of users in this class 
    
    $sql = "SELECT * FROM `".$tbl_class_user."` `rel_c_u`, `".$tbl_user."` `u` 
                    WHERE `class_id`='". (int)$class_id."' 
               AND `rel_c_u`.`user_id` = `u`.`user_id`";
    $result = claro_sql_query_fetch_all($sql);
    
    //subscribe the users each by each
    
    $resultLog = array();
    
    foreach ($result as $user)
    {
        $done = user_add_to_course($user['user_id'], $course_code); 
        if ($done)
        {
            $resultLog['OK'][] = $user;
        }
        else
        {
            $resultLog['KO'][] = $user;
        } 
    }
    return $resultLog;
}

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

function display_tree_class_in_admin ($class_list, $parent_class = null, $deep = 0) 
{

    //global variables needed    

    global $clarolineRepositoryWeb;
    global $imgRepositoryWeb;
    global $tbl_class_user; 
    global $langUsersMin;

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
	            if (isset($_SESSION['admin_visible_class'][$cur_class['id']]) && $_SESSION['admin_visible_class'][$cur_class['id']]=="open")
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
	            $open_close_link =" ° "; 
	        }
	    
    	    //DISPLAY CURRENT ELEMENT (CLASS)

	        //Name
		
            echo "<tr>\n"
                ."  <td>\n"
                ."    ".$blankspace.$open_close_link." ".$cur_class['name']
                ."  </td>\n";

            //Users
	    
    	    $sqlcount = " SELECT COUNT(`user_id`) AS qty_user 
                          FROM `".$tbl_class_user ."` 
                          WHERE `class_id`='" . (int)$cur_class['id'] . "'";  
	        $resultcount = claro_sql_query_fetch_all($sqlcount);	   
	        $qty_user = $resultcount[0]['qty_user'];
	    
    	    echo "  <td align=\"center\">\n"
	            ."    <a href=\"".$clarolineRepositoryWeb."admin/admin_class_user.php?class=".$cur_class['id']."\">\n"
                ."      <img src=\"".$imgRepositoryWeb."user.gif\" border=\"0\"> "
		        ."        (".$qty_user."  ".$langUsersMin.") \n"
                ."    </a>\n"
                ."  </td>\n";
		
            //edit settings	
			
            echo "  <td align=\"center\">\n"
	            ."    <a href=\"".$_SERVER['PHP_SELF']."?cmd=edit&amp;class=".$cur_class['id']."\">\n"
                ."      <img src=\"".$imgRepositoryWeb."edit.gif\" border=\"0\" >\n"
	            ."    </a>\n"
		        ."  </td>\n";
	    
            //Move	
		
            echo "  <td align=\"center\">\n"
	            ."    <a href=\"".$_SERVER['PHP_SELF']."?cmd=move&amp;class=".$cur_class['id']."&classname=".$cur_class['name']."\">\n"
                ."      <img src=\"".$imgRepositoryWeb."move.gif\" border=\"0\" >\n"
        		."    </a>\n"
	            ."  </td>\n";
	    
            //delete	
		
            echo "  <td align=\"center\">\n"
                ."    <a href=\"".$_SERVER['PHP_SELF']."?cmd=del&amp;class=".$cur_class['id']."\""
                ."     onClick=\"return confirmation('".clean_str_for_javascript($cur_class['name'])."');\">\n"
                ."      <img src=\"".$imgRepositoryWeb."delete.gif\" border=\"0\" >\n"
                ."    </a>\n"
                ."  </td>\n";
            echo "</tr>\n";
            
	    
            // RECURSIVE CALL TO DISPLAY CHILDREN
	    
            if (isset($_SESSION['admin_visible_class'][$cur_class['id']]) && ($_SESSION['admin_visible_class'][$cur_class['id']]=="open"))
            {
	            display_tree_class_in_admin($class_list, $cur_class['id'], $deep+1);
            }	    
	    }
    }    
}

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

function display_tree_class_in_user($class_list, $parent_class = null, $deep = 0) 
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
	        if (isset($_SESSION['class_add_visible_class'][$cur_class['id']]) && $_SESSION['class_add_visible_class'][$cur_class['id']]=="open")
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
	    
	    $sqlcount=" SELECT COUNT(`user_id`) AS qty_user 
                    FROM `".$tbl_class_user ."` 
                    WHERE `class_id`='". (int)$cur_class['id']."'";
	    $resultcount = claro_sql_query_fetch_all($sqlcount);   
	    $qty_user = $resultcount[0]['qty_user'];
	    
	    echo "  <td align=\"center\">\n"
	        .$qty_user."  ".$langUsersMin." \n"
		."  </td>\n";
		
	      //add to course link	
			
            echo "  <td align=\"center\">\n"
	        ."    <a onClick=\"return confirmation('".clean_str_for_javascript($cur_class['name'])."');\" href=\"".$_SERVER['PHP_SELF']."?cmd=subscribe&amp;class=".$cur_class['id']."&amp;classname=".$cur_class['name']."\">\n"
                ."      <img src=\"".$imgRepositoryWeb."enroll.gif\" border=\"0\" alt=\"".$langSubscribeToCourse."\">\n"
	        ."    </a>\n"
		."  </td>\n";
	    
            echo "</tr>\n";
	    
	    // RECURSIVE CALL TO DISPLAY CHILDREN
	    
	    if (isset($_SESSION['class_add_visible_class'][$cur_class['id']]) && ($_SESSION['class_add_visible_class'][$cur_class['id']]=="open"))
	    {
	        display_tree_class_in_user($class_list, $cur_class['id'], $deep+1);
	    }	    
	}
    }    
}


/**
 *This function create the select box to choose the parent class
 *
 * @param  the pre-selected class'id in the select box  
 * @param  space to display for children to show deepness  
 * @global $tbl_class
 * @global $langTopLevel
 * @return void
*/
function displaySelectBox($selected=null,$space="&nbsp;&nbsp;&nbsp;") 
{       
	global $tbl_class;
	global $langTopLevel;
	
	$sql = " SELECT * 
             FROM `".$tbl_class."`
             ORDER BY `name`";
	$classes = claro_sql_query_fetch_all($sql);
	
	$result = "<select name=\"theclass\">\n"
	    ."<option value=\"root\"> ".$langTopLevel." </option>"; 
	$result .= buildSelectClass($classes,$selected,null,$space);
	$result .= "</select>\n";
	return $result;
}
    
/**
 * This function create the list for the select box to choose the parent class
 *
 * @author Guillaume Lederer
 * @param  tab containing at least all the classes with their id, parent_id and name
 * @param  parent_id of the class we want to display the children of 
 * @param  the pre-selected class'id in the select box  
 * @param  space to display for children to show deepness  
 * @return string to output
 *
*/    
function buildSelectClass($classes,$selected,$father=null,$space="&nbsp;&nbsp;&nbsp;")
{
    $result = "";
    if($classes)
    {
        foreach($classes as $one_class)
        {
            //echo $one_class["class_parent_id"]." versus ".$father."<br>";

            if($one_class['class_parent_id']==$father)
            {
                $result .= '<option value="'.$one_class['id'].'" ';
                if ($one_class['id'] == $selected)
                {
                    $result .= 'selected ';
                }
                $result .= '> '.$space.$one_class['name'].' </option>'."\n";
                $result .=  buildSelectClass($classes,$selected,$one_class['id'],$space.'&nbsp;&nbsp;&nbsp;');
            }
        }
    }
    return $result;
}

?>
