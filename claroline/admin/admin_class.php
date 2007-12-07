<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

//used langfile
$langFile = "admin";

//used libraries
require '../inc/claro_init_global.inc.php';
@include ($includePath."/installedVersion.inc.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) treatNotAuthorized();

//----------------------LANG TO ADD -------------------------------------------------------------------------------

$langAdministrationClassTools = "Classes";
$langName = "Name";
$langId = "Id";
$langEditSettings = "Edit settings";
$langNewClassName = "New Class name";
$langClassName = "Class name";
$langNameChanged = "Name of the class has been changed";
$langErrorClassNotEmpty = "This class still contains some sub classes, delete them first";
$langLocation = "Location";
$langCannotBeBlank = "You can not give a blank name to a class";
$langNewClassCreated = "The new class has been created";
$langCreateNewClass = "Create a new class";
$langClassMoved = "The class has been moved";
$langErrorMove = "You can not move a class in itself!";
$langTopLevel = "top level";
$langMove = "Move";
$langUsersMin = "users";

//---------------------- END LANG TO ADD ----------------------------------------------------------------------------

$is_allowedToAdmin   = $is_platformAdmin || $PHP_AUTH_USER;

/*
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user                  = $tbl_mdb_names['user'];
$tbl_class                 = $tbl_mdb_names['user_category'];
$tbl_class_user            = $tbl_mdb_names['user_rel_profile_category'];

// USED SESSION VARIABLES 

if (!isset($_SESSION['admin_visible_class'])) 
{
    $_SESSION['admin_visible_class'] = array(); 
}

// Deal with interbredcrumps  and title variable
$nameTools = $langAdministrationClassTools;
$interbredcrump[]    = array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);

// javascript confirm pop up declaration for header

  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\"".$langAreYouSureToDelete."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

//Header declaration

include($includePath."/claro_init_header.inc.php");

//display bredcrump and title

claro_disp_tool_title($nameTools);

/*-----------------------------------*/
/*	EXECUTE COMMAND	             */
/*-----------------------------------*/
switch ($cmd)	
{
  //Delete an existing class
  case "del" :
  
        //check if class contains some children

	$sql = "SELECT * FROM `".$tbl_class."`";
	$class_list = claro_sql_query_fetch_all($sql);
	$has_children = FALSE;
	foreach ($class_list as $search_parent)
	{
	    if ($_REQUEST['class'] == $search_parent['class_parent_id'])
	    {    
	        $has_children = TRUE;
	        break;
	    }
	}

	// delete the class itself	
	if (!$has_children) 
	{  
	    $sql = "DELETE FROM `".$tbl_class."` WHERE id='".$_REQUEST['class']."'";      
            claro_sql_query($sql);
	}
	else
	{
	    $dialogBox = $langErrorClassNotEmpty;
	}

        break;
  
  //Display form to create a new class
  case "formNew" :
  	$dialogBox ="<form action=\"$PHP_SELF\" >\n"
		   ."<table>\n"
		   ."   <tr>\n"
		   ."     <td>\n"
	       ."       $langNewClassName : \n"
		   ."     </td>\n"
		   ."     <td>\n"
		   ."       <input type=\"hidden\" name=\"cmd\" value=\"new\">\n"
		   ."       <input type=\"text\" name=\"classname\">\n"
		   ."     </td>\n"
		   ."   </tr>\n"
		   ."   <tr>\n"
		   ."     <td>\n"
		   ."       $langLocation :\n"
		   ."     </td>\n"
		   ."     <td>\n"
                   .        displaySelectBox()   
	           ."       <input type=\"submit\" value=\" Ok \">\n"
		   ."     </td>\n"
		   ."   </tr>\n"
		   ." </table>\n"
		   ."</form>\n ";      
        break;

  //Create a new class
  case "new" :
  	if ($_REQUEST['classname']=="") 
	{
	    $dialogBox = $langCannotBeBlank;
	}
	else
	{
	    $dialogBox = $langNewClassCreated;
	    $sql = "INSERT INTO `".$tbl_class."` SET `name`='".$_REQUEST['classname']."'";
	    if ($_REQUEST['theclass'] && ($_REQUEST['theclass']!="") && ($_REQUEST['theclass']!="root"))
	    {
	        $sql.=", `class_parent_id`='".$_REQUEST['theclass']."'"; 
            }       
            claro_sql_query($sql);
	}
        break;

  //Edit class properties with posted form	
  case "exEdit" :
        
  	if ($_REQUEST['classname']=="") 
	{
	    $dialogBox = $langCannotBeBlank;
	}
	else
	{
	    $sql_update="UPDATE `$tbl_class` set name='".$_REQUEST['classname']."' WHERE id='".$_REQUEST['class']."'";
	    claro_sql_query($sql_update);
	    $dialogBox = $langNameChanged;
	
	}
        break;
  
  //Show form to edit class properties (display form)	
  case "edit" :
        
        $sql = "SELECT * FROM `".$tbl_class."` WHERE `id`= '".$_REQUEST['class']."'";
	$result =  claro_sql_query_fetch_all($sql);
	foreach ($result as $resClass) 
	{
	  $class_name = $resClass['name'];
	}
	
        $dialogBox= "<form action=\"$PHP_SELF\" >\n"
		   ."<table>\n"
		   ."  <tr>\n"
		   ."    <td>\n"
	           ."      $langClassName : \n"
		   ."    </td>\n"
		   ."    <td>\n"
		   ."      <input type=\"hidden\" name=\"cmd\" value=\"exEdit\">\n"
		   ."      <input type=\"hidden\" name=\"class\" value=\"".$_REQUEST['class']."\">\n"
		   ."      <input type=\"text\" name=\"classname\" value=\"$class_name\">\n"
		   ."      <input type=\"submit\" value=\" Ok \">\n"
		   ."    </td>\n"
		   ."  </tr>\n"
		   ."</table>\n"
		   ."</form>\n ";
        break;

  //Open a class in the tree
  case "exOpen" : 
      $_SESSION['admin_visible_class'][$_REQUEST['class']]="open";      
      break;
      
  //Close a class in the tree
  case "exClose" : 
      $_SESSION['admin_visible_class'][$_REQUEST['class']]="close";      
      break;
  
  //Move a class in the tree (do it from posted info)
  case "exMove" : 
      
      if ($_REQUEST['theclass'] ==$_REQUEST['movedClassId']) 
      {
          $dialogBox = $langErrorMove;
      }
      else
      {
          if ($_REQUEST['theclass']=="root")
	  {
	     $parent="null"; 
	  }
	  else
	  {
	     $parent = $_REQUEST['theclass'];
	  }
	  $sql_update="UPDATE `$tbl_class` set class_parent_id=".$parent." WHERE id='".$_REQUEST['movedClassId']."'";
          claro_sql_query($sql_update);
          $dialogBox = $langClassMoved;
      }
      break;
      
  //Move a class in the tree (display form)
  case "move" : 
      
      $dialogBox =  " <table>"  
                   ."   <tr>\n"
		   ."     <td >\n"
		   ."       $langMove ".$_REQUEST['classname']." : "
		   ."     </td>\n"
		   ."     <td>\n"
		   ."       <form action=\"$PHP_SELF\">"
		   ."         <input type=\"hidden\" name=\"cmd\" value=\"exMove\">\n"
		   ."         <input type=\"hidden\" name=\"movedClassId\" value=\"".$_REQUEST['class']."\">\n"
                   .          displaySelectBox() 
                   ."         <input type=\"submit\" value=\" Ok \">\n"
                   ."       </form>"
		   ."     </td>\n"
		   ."   </tr>\n"
		   ." </table>";
      break;
	
}

/*-----------------------------------*/
/*	Get information 	     */
/*-----------------------------------*/

$sql = "SELECT * FROM `".$tbl_class."` ORDER BY `name`";
$class_list = claro_sql_query_fetch_all($sql);

/*-----------------------------------*/
/*	Display                      */
/*-----------------------------------*/

//display dialog Box (or any forms)

if($dialogBox)
{
    claro_disp_message_box($dialogBox);
    echo "<br>";
}

//display tool links

claro_disp_button("$PHP_SELF?cmd=formNew", $langCreateNewClass);
//display cols headers

echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">\n"
    ."  <tr class=\"headerX\">\n"
    ."    <th>\n"
    ."      $langClassName\n"
    ."    </th>\n"
    ."    <th>\n"
    ."      $langUsers\n"
    ."    </th>\n"
    ."    <th>\n"
    ."      $langEditSettings\n"
    ."    </th>\n"
    ."    <th>\n"
    ."      $langMove\n"
    ."    </th>\n"
    ."    <th>\n"
    ."      $langDelete\n"
    ."    </th>\n"
    ."  </tr>\n";

//display Class list

display_tree($class_list);

echo "</table>";

include($includePath."/claro_init_footer.inc.php");

/*-------END OF THE SCRIPT OUTPUT    -------------------------------------------------------*/


/*-----------------------------------*/
/*  	NEEDED FUNCTIONS :  	     */
/*-----------------------------------*/


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
	        if ($_SESSION['admin_visible_class'][$cur_class['id']]=="open")
		{
		    $open_close_link = "<a href=\"$PHP_SELF?cmd=exClose&class=".$cur_class['id']."\">\n"
		                      ."   <img src=\"".$clarolineRepositoryWeb."img/minus.jpg\" border=\"0\" >\n"
				      ."</a>\n";
		}
		else
		{
		    $open_close_link = "<a href=\"$PHP_SELF?cmd=exOpen&class=".$cur_class['id']."\">\n"
		                      ."  <img src=\"".$clarolineRepositoryWeb."img/plus.jpg\" border=\"0\" >\n"
				      ."</a>\n";
		}    
	    }
	    else
	    {
	        $open_close_link ="°"; 
	    }
	    
	    //DISPLAY CURRENT ELEMENT (CLASS)

	      //Name
		
	    echo "  <td>\n"
                ."    ".$blankspace.$open_close_link." ".$cur_class['name']
                ."  </td>\n";

	      //Users
	    
	    $sqlcount="SELECT COUNT(`user_id`) AS qty_user FROM `".$tbl_class_user ."` WHERE `class_id`='".$cur_class['id']."'";  
	    $resultcount = claro_sql_query_fetch_all($sqlcount);	   
	    $qty_user = $resultcount[0]['qty_user'];
	    
	    echo "  <td align=\"center\">\n"
	        ."    <a href=\"".$clarolineRepositoryWeb."admin/admin_class_user.php?class=".$cur_class['id']."\">\n"
                ."      <img src=\"".$clarolineRepositoryWeb."img/membres.gif\" border=\"0\"> "
		."        (".$qty_user."  ".$langUsersMin.") \n"
	        ."    </a>\n"
		."  </td>\n";
		
	      //edit settings	
			
            echo "  <td align=\"center\">\n"
	        ."    <a href=\"".$PHP_SELF."?cmd=edit&class=".$cur_class['id']."\">\n"
                ."      <img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" >\n"
	        ."    </a>\n"
		."  </td>\n";
	    
	      //Move	
		
            echo "  <td align=\"center\">\n"
	        ."    <a href=\"".$PHP_SELF."?cmd=move&class=".$cur_class['id']."&classname=".$cur_class['name']."\">\n"
                ."      <img src=\"".$clarolineRepositoryWeb."img/deplacer.gif\" border=\"0\" >\n"
		."    </a>\n"
	        ."  </td>\n";
	    
	      //delete	
		
            echo "  <td align=\"center\">\n"
	        ."    <a href=\"$PHP_SELF?cmd=del&class=".$cur_class['id']."\""
		."     onClick=\"return confirmation('",addslashes($cur_class['name']),"');\">\n"
                ."      <img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" >\n"
		."    </a>\n"
	        ."  </td>\n";
            echo "</tr>\n";
	    
	    // RECURSIVE CALL TO DISPLAY CHILDREN
	    
	    if ($_SESSION['admin_visible_class'][$cur_class['id']]=="open")
	    {
	        display_tree($class_list, $cur_class['id'], $deep+1);
	    }	    
	}
    }    
}

/**
     *This function create the select box to choose the parent class
     *
     * @author  Guillaume Lederer
     * @param  the pre-selected class'id in the select box  
     * @param  space to display for children to show deepness  
     * @return  - void
     *
     * @desc : create the select box 
*/
    function displaySelectBox($selected=null,$space="&nbsp;&nbsp;&nbsp;") 
    {       
	global $tbl_class;
	global $langTopLevel;
	
	$sql ="SELECT * FROM `".$tbl_class."`  ORDER BY `name`";
	$classes = claro_sql_query_fetch_all($sql);
	
	$result .= "<select name=\"theclass\">\n"
	    ."<option value=\"root\"> ".$langTopLevel." </option>"; 
	$result .= buildSelectClass($classes,$selected,null,$space);
	$result .= "</select>\n";
	return $result;
    }
    
/**
     *This function create the list for the select box to choose the parent class
     *
     * @author Guillaume Lederer
     * @param  tab containing at least all the classes with their id, parent_id and name
     * @param  parent_id of the class we want to display the children of 
     * @param  the pre-selected class'id in the select box  
     * @param  space to display for children to show deepness  
     * @return  - void
     *
     * @desc : create the select box 
*/    
    function buildSelectClass($classes,$selected,$father=null,$space="&nbsp;&nbsp;&nbsp;")
    {
	if($classes)
        {            
	    foreach($classes as $one_class)
            {
                //echo $one_class["class_parent_id"]." versus ".$father."<br>";
		
		if($one_class['class_parent_id']==$father)
                {
                    $result .= "<option value=\"".$one_class['id']."\" ";
		    if ($one_class['id'] == $selected)
		    {
                        $result .= "selected ";
		    }
                    $result .= "> ".$space.$one_class['name']." </option>\n";
                    $result .=  buildSelectClass($classes,$selected,$one_class["id"],$space."&nbsp;&nbsp;&nbsp;");
                }
            }
        }
	return $result;
    }

?>