<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------


require '../inc/claro_init_global.inc.php';

$nameTools  = $lang_course_enrollment;
$noPHP_SELF = true;

include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/admin.lib.inc.php");
if (! $_uid) claro_disp_auth_form();

				/*>>>>>>>>>>>> COMMANDS SECTION <<<<<<<<<<<<*/

define ('DISPLAY_USER_COURSES'  , 1);
define ('DISPLAY_COURSE_TREE'   , 2);
define ('DISPLAY_MESSAGE_SCREEN', 3);

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course           = $tbl_mdb_names['course'           ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
$tbl_course_nodes     = $tbl_mdb_names['category'         ];
$tbl_user             = $tbl_mdb_names['user'             ];
$tbl_class            = $tbl_mdb_names['class'            ];
$tbl_rel_class_user   = $tbl_mdb_names['rel_class_user'   ];

$tbl_category         = $tbl_course_nodes; // check if not used in include before remove
$tbl_course_nodes	  = $tbl_course_nodes; // check if not used in include before remove
$tbl_courses          = $tbl_courses; // check if not used in include before remove
$tbl_courseUser       = $tbl_rel_course_user; // check if not used in include before remove

// define user we are working with...

$userId = $_uid; //default use is enroll for myself...

if (isset($uidToEdit))
{
   $userId = $uidToEdit;
}

//security : only platform admin can edit other user than himself...
if (!$is_platformAdmin)
{
    $uidToEdit = $_uid;
    $userId = $_uid;
}
else
{
	if (isset($fromAdmin) && ($fromAdmin == "settings" || $fromAdmin == "usercourse" || $fromAdmin == "class"))
	{
		$userSettingMode = $uidToEdit;
	}
	$inURL = "&amp;uidToEdit=".$uidToEdit."&amp;fromAdmin=".$fromAdmin;

	if (isset($uidToEdit) && (!($uidToEdit == ""))) // in admin mode, there 2 possibilities : we might want to enroll ourself or either be here from admin tool
	{
    	$userId = $uidToEdit;
	} 
	else 
	{
    	$userId = $_uid;
	    $uidToEdit = $_uid;
	} //  if (isset($uidToEdit) && (!($uidToEdit == ""))) 
} // if (!$is_platformAdmin)


/*
 * Define bredcrumps
 */


if($addNewCourse || $selectCategory || isset($courseCode))
{
	$interbredcrump[]=array("url" => $_SERVER['PHP_SELF'],"name" => $lang_my_personnal_course_list);

	if($selectCategory || isset($courseCode))
	{
		$interbredcrump[]=array("url" => $_SERVER['PHP_SELF'].'?addNewCourse=1',"name" => $lang_main_categories_list);
	}
}

//bred different if we come from admin tool  
  	
if (isset($fromAdmin) && ($fromAdmin == "settings" || $fromAdmin == "usercourse" || $fromAdmin == "class"))
{
	$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
}
//bred different if we come from admin tool for a CLASS  
  	
if (isset($fromAdmin) && ($fromAdmin == "class"))
{
	$nameTools = $langRegisterClass;
	
	//find info about the class

        $sqlclass = "SELECT * FROM `".$tbl_class."` WHERE `id`='".$_SESSION['admin_user_class_id']."'";
        list($classinfo) = claro_sql_query_fetch_all($sqlclass);
}

/*
 * DB tables initialisation
 */

// Find info about user we are working with

$sql = "SELECT * FROM `".$tbl_user."` WHERE user_id=".$userId;
$userInfo = claro_sql_query($sql);
$userInfo = mysql_fetch_array($userInfo);

/*----------------------------------------------------------------------------
                           UNSUBSCRIBE FROM A COURSE
 ----------------------------------------------------------------------------*/

if ($cmd == 'exUnreg')
{
    if ( remove_user_from_course($userId, $_REQUEST['course']) )
    {
    	$message = $lang_your_enrollment_to_the_course_has_been_removed;
    }
    else
    {
    	$message = "Unable to remove your registration to the course";
    }

    $displayMode = DISPLAY_MESSAGE_SCREEN;
} //if ($cmd == 'exUnreg')




/*----------------------------------------------------------------------------
                             SUBSCRIBE TO A COURSE
  ----------------------------------------------------------------------------*/

if ($cmd == 'exReg')
{
    // if user is platform admin, register to private course can be forced. Otherwise not
    
    if ($is_platformAdmin)
    {
      $forceReg = true;
    }
    else
    {
      $forceReg = false;
    }
    
    // try to register user
    
    if ( add_user_to_course($userId, $_REQUEST['course'],$forceReg) )
    {
        if ($_uid!=$uidToEdit)
        {
           $message = $lang_user_has_been_enrolled_to_the_course;
        }
        else
        {
           $message = $lang_you_have_been_enrolled_to_the_course;
        }
        if ($_GET['asTeacher'] && $is_platformAdmin)
        {
            $properties['status'] = 1;
            $properties['role'] = "Professor";
            $properties['tutor'] = 1;
            update_user_course_properties($userId, $_REQUEST['course'], $properties);
        }
    }
    else
    {
    	$message = "Unable to enroll you to the course";
    }

    $displayMode = DISPLAY_MESSAGE_SCREEN;
} //if ($cmd == 'exReg')




/*----------------------------------------------------------------------------
                           SEARCH A COURSE TO REGISTER
  ----------------------------------------------------------------------------*/

if ( $cmd == 'rqReg' ) // show course of a specific category
{
    /*
     * 'SEARCH BY KEYWORD' MODE
     */

    if ( isset($_REQUEST['keyword']) )
    {
        $title      = $lang_select_course_in_search_results;
        $keyword    = trim($_REQUEST['keyword']);
        $result     = search_course($keyword);

        if ($result != false)
        {
            $courseList = $result;
        }
        else
        {
            $message = $lang_no_course_available_fitting_this_keyword;
        }

        $categoryList = array(); // empty category list to remain compatible display side ...

        $displayMode = DISPLAY_COURSE_TREE;

    } // end if isset keyword


    /*
     * 'BROWSE BY CATEGORY' MODE
     */

    if ( isset($_REQUEST['category']) )
    {
        $category = $_REQUEST['category'];

        /*
         * Get the courses contained in this category
         */
        
	// 1 platform admin can also see private courses so they must be displayed, other users can not.
	
	if (!$is_platformAdmin) 
	{
	    $visibility_cond = "(c.visible=\"2\" OR c.visible=\"1\")";
	}
	else
	{
	    $visibility_cond = "1=1";
	}
	
	// 2 build the query taking account with the user rights   
        
	$sql = "SELECT c.visible, c.intitule, c.directory, c.code, 
                        c.titulaires, c.languageCourse, c.fake_code officialCode,
                        cu.user_id enrolled

                FROM `".$tbl_course."` c

                LEFT JOIN `".$tbl_rel_course_user."` cu
                ON (c.code = cu.code_cours AND cu.user_id = ".$userId.")

                WHERE faculte = '".$category."'
                AND   ".$visibility_cond."

                ORDER BY UPPER(fake_code)";

        $courseList = claro_sql_query_fetch_all($sql);

        /*
         * Get the subcategories of this category
         */

        if ($category != '')
        {
            $sqlFilter = "# get the direct children categories

                          UPPER(`faculte`.`code_P`) = UPPER(\"".$category."\")

                          # get the current category

                          OR UPPER(`faculte`.`code`  ) = UPPER(\"".$category."\")";
        }
        else
        {
            $sqlFilter = "   `faculte`.`code`   IS NULL
                          OR `faculte`.`code_P` IS NULL";
        }

        $sql = "SELECT `faculte`.`code`  , `faculte`.`name`,
                       `faculte`.`code_P`, `faculte`.`nb_childs`,
                       COUNT( c.`cours_id` ) `nbCourse`

                FROM `".$tbl_course_nodes."` `faculte`

                # The two left are used for the course count

                LEFT JOIN `".$tbl_course_nodes."` `subCat`
                ON  `subCat`.`treePos` >= `faculte`.`treePos`
                AND `subCat`.`treePos` <= (`faculte`.`treePos` + `faculte`.`nb_childs`)

                LEFT JOIN `".$tbl_course."` c
                ON c.`faculte` = `subCat`.`code`
                AND ".$visibility_cond."

                # filter to get the current and direct children categories

                WHERE".$sqlFilter."

                GROUP  BY  `faculte`.`code`
                
                # ordered the brother subcategory

                ORDER  BY  `faculte`.`treePos`";

        $categoryList = claro_sql_query_fetch_all($sql);

        /*
         * Get the current category name and parent code
         */

        if (count($categoryList) > 0)
        {
            foreach($categoryList as $thisKey => $thisCategory)
            {
                if ($thisCategory['code'] == $category)
                {
                    $currentCategoryName = $thisCategory['name'  ];
                    $parentCategoryCode  = $thisCategory['code_P'];
                    
                    unset ( $categoryList[$thisKey] );
                    break;
                }
            } // end foreach

        } // end if count($categoryList) > 0
        
        $displayMode = DISPLAY_COURSE_TREE;

    } // end if isset category

} // end cmd == rqReg


if ($cmd == 'rqUnreg')
{
		$sql = "SELECT *
		        FROM `".$tbl_course."` `c`, `".$tbl_rel_course_user."` `cu`
		        WHERE `cu`.`user_id` = '".$userId."'
		        AND   `c`.`code`    = `cu`.`code_cours`
		        ORDER BY `c`.`fake_code`";

        $courseList = claro_sql_query_fetch_all($sql);

        $displayMode = DISPLAY_USER_COURSES;
} // if ($cmd == 'rqUnreg')






				/*>>>>>>>>>>>> DISPLAY SECTION <<<<<<<<<<<<*/

/*
 * SET 'BACK' LINK
 */

if ($cmd == 'rqReg' && ($category || ! is_null($parentCategoryCode) ) )
{
        $backUrl   = $_SERVER['PHP_SELF'].'?cmd=rqReg&category='.$parentCategoryCode;
        $backLabel = $lang_back_to_parent_category;
}
else
{

    if ($userSettingMode == true) //enroll page accessed by admin tool to set user settings
    {
        if ($fromAdmin == "settings") 
	{
		$backUrl   = "../admin/adminprofile.php?uidToEdit=".$userId;
        	$backLabel = $langBackToUserSettings;
	}
	if ($fromAdmin =="usercourse") // admin tool used: list of a user's courses.
	{
		$backUrl   = "../admin/adminusercourses.php?uidToEdit=".$userId;
        	$backLabel = $langBackToCourseList;
	}
	if ($fromAdmin == "class") // admin tool used : class registration
	{
		$backUrl   = "../admin/admin_class_user.php?";
        	$backLabel = $langBackToClass;
	}
    }
    else
    {
        $backUrl   = "../../index.php?";
	    $backLabel = $lang_back_to_my_personnal_course_list;
    }
} // ($cmd == 'rqReg' && ($category || ! is_null($parentCategoryCode) ) )

$backUrl .= $inURL; //notify userid of the user we are working with in admin mode and that we come from admin

$backLink = "\n\n<p><small><a href=\"".$backUrl."\" title=\"".$backLabel."\" >&lt;&lt; ".$backLabel."</a></small></p>\n\n";



				/*>>>>>>>>>>>> OUTPUT SECTION <<<<<<<<<<<<*/


//include header

include($includePath."/claro_init_header.inc.php");



echo $backLink;

switch ($displayMode)
{
	/*--------------------
	  COURSE LIST DISPLAY
	  --------------------*/

	case DISPLAY_COURSE_TREE :

		if (! $category) $currentCategoryName = $siteName;

		// Note : if we are at the root category we're at the top of the campus
		//        root name equal platform name
		//        $siteName comes from claro_main.conf.php
	
		/*
		 * TITLE DISPLAY
		 */
		
	if ($_REQUEST['fromAdmin'] != "class")
	{
        	claro_disp_tool_title( array('mainTitle' => $lang_course_enrollment." : ".$userInfo['prenom']." ".$userInfo['nom'],
                                     'subTitle'  => $lang_select_course_in.' '.$currentCategoryName));
	}
	else
	{
		claro_disp_tool_title( array('mainTitle' => $langEnrollClass." : ".$classinfo['name'],
                                     'subTitle'  => $lang_select_course_in.' '.$currentCategoryName));
	}
	
	
        if($message);
		{
			echo "<blockquote>",$message,"</blockquote>\n";
		}

		/*
		 * CATEGORY LIST
		 */

		if ( count($categoryList) > 0)
		{
			echo	"<h4>",$langCategories,"</h4>",

					"<ul>";
			
			foreach($categoryList as $thisCategory)
			{
				if ($thisCategory['code'] != $category)
				{
					echo "<li>";

					if ($thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0) 
					{
						echo	"<a href=\"",$_SERVER['PHP_SELF'],"?cmd=rqReg&category=",$thisCategory['code'],$inURL,"\">",
								$thisCategory['name'],
								"</a>",
								" <small>(".$thisCategory['nbCourse'].")</small>";
					}
					else
					{
						echo $thisCategory['name'];
					}

					echo "</li>";
				}
			} // end foreach categoryList

			echo "</ul>";
		}

		/*
		 * SEPARATOR BETWEEN CATEGORY LIST AND COURSE LIST
		 */

		if ( $courseList && $categoryList )
		{
			echo "<hr size=\"1\" noshade=\"noshade\">\n";
		}

		/*
		 * COURSE LIST
		 */

		if ($courseList)
		{
			echo "<h4>".$langCourseList."</h4>\n"
				."\n<blockquote>\n"
				."<table class=\"claroTable emphaseLine\" >\n";

            if ($userSettingMode) //display links to enroll as student and also as teacher (but not for a class)
	    {
               
	      if ($_REQUEST['fromAdmin']!="class")
	      { 	      
                echo "\n<thead>\n"
                    ."<tr class=\"headerX\">\n"
                    ."<th>&nbsp;</th>\n"
                    ."<th>".$langEnrollAsStudent."</th>\n"
                    ."<th>".$langEnrollAsTeacher."</th>\n"
                    ."<tr>\n"
                    ."\n</thead>\n";
	      }
	      else
	      {
              echo "\n<thead>\n"
                  ."<tr class=\"headerX\">\n"
                  ."<th></th>\n"
                  ."<th>".$langEnrollClass."</th>\n"
                  ."</tr>\n"
                  ."\n</thead>\n";
	      }
            }

            echo "\n<tbody>\n";

			foreach($courseList as $thisCourse)
			{
				echo "<tr>\n"
					."<td>\n".$thisCourse['officialCode']." - ".$thisCourse['intitule']
					."\n<br />\n"
					."<small>".$thisCourse['titulaires']."</small>\n"
					."</td>\n";

                //enroll link

                if ($userSettingMode) 
				{
                    if ($thisCourse['enrolled'])
                    {
                        echo "<td valign=\"top\" colspan=\"2\" align=\"center\">\n"
                        	."<small>"
                        	."<span class=\"highlight\">".$lang_already_enrolled."</span>"
                        	."</small>\n";
                    }
                    else
                    {
                         if ( $_REQUEST['fromAdmin'] != 'class') // class may not be enrolled as teachers
                         { 
                            echo "<td valign=\"top\" align=\"center\">\n"
                                ."<a href=\"".$_SERVER['PHP_SELF']."?cmd=exReg&course=".$thisCourse['code'].$inURL."\">"
                                ."<img src=\"".$imgRepositoryWeb."enroll.gif\" alt=\"".$langEnrollAsStudent."\">"
                                ."</a>\n"
                                ."</td>\n"
                                ."<td valign=\"top\" align=\"center\">\n"
                                ."<a href=\"".$_SERVER['PHP_SELF']."?cmd=exReg&asTeacher=true&course=".$thisCourse['code'].$inURL."\">"
                                ."<img src=\"".$imgRepositoryWeb."enroll.gif\"  alt=\"".$langEnrollAsTeacher."\">"
                               ."</a>"
                               ."</td>\n";
                        }
                        else 
                        {
                            echo "<td valign=\"top\"  align=\"center\">\n"
                                ."<a href=\"".$clarolineRepositoryWeb."admin/admin_class_course_registered.php?cmd=exReg&course=".$thisCourse['code']."&class=",$classinfo['id'].$inURL."\">"
                                ."<img src=\"".$imgRepositoryWeb."enroll.gif\" border=\"0\" alt=\"".$langEnrollClass."\">"
                                ."</a>\n"
                                ."</td>\n";
                        }
                    }
                }
                else
                {
                    echo "<td valign=\"top\">\n";

    				if ($thisCourse['enrolled'])
    				{
    					echo "<small>"
                             ."<span class=\"highlight\">".$lang_already_enrolled."</span>"
                             ."</small>\n";
    				}
    				else
    				{
    					echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exReg&course=".$thisCourse['code'].$inURL."\">"
    						 ."<img src=\"".$imgRepositoryWeb."enroll.gif\" border=\"0\" alt=\"".$lang_enroll."\">"
    						 ."</a>"
                            ."</td>\n";
    				}

               }
			   

			"</tr>\n";

			} // end foreach courseList

            echo    "\n</tbody>\n";
			echo	"\n</table>\n",

					"</blockquote>\n";
		}

		echo	 "\n<blockquote>\n"
				."<p>"
                ."<label for=\"keyword\">",$lang_or_search_from_keyword,"</label>"
                ." : </p>\n"
				."<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n"
				."<input type=\"hidden\" name=\"cmd\" value=\"rqReg\" />\n"
				."<input type=\"hidden\" name=\"fromAdmin\" value=\"".$fromAdmin."\" />\n"
				."<input type=\"hidden\" name=\"cmd\" value=\"rqReg\" />\n"
				."<input type=\"text\" name=\"keyword\" id=\"keyword\" />\n"
                ."<input type=\"hidden\" name=\"uidToEdit\" value=\"".$uidToEdit."\" />\n"
				."&nbsp;<input type=\"submit\" value=\"".$langSearch."\" />\n"
				."</form>\n"
				."</blockquote>\n";
	break;

    case DISPLAY_MESSAGE_SCREEN :

        // claro_disp_tool_title( $lang_course_enrollment);
        claro_disp_tool_title($lang_course_enrollment." : ".$userInfo['prenom']." ".$userInfo['nom']);

        echo '<blockquote>';	

        claro_disp_message_box( '<p>'.$message.'</p>'
                               .'<p align="center">'
                               .'<a href="'.$backUrl.'">'.$backLabel.'</a>'
                               .'</p>');
        echo '</blockquote>';

    break;


	/*------------------------------
	         DEFAULT DISPLAY
	  (actually DISPLAY_USER_COURSES)
	  -------------------------------*/

	case DISPLAY_USER_COURSES :

        claro_disp_tool_title( array('mainTitle' => $lang_course_enrollment." : ".$userInfo['prenom']." ".$userInfo['nom'],
                                     'subTitle' => $lang_remove_course_from_your_personnal_course_list));

        if (count($courseList) > 0)
        {
			echo	"<blockquote>\n"
				   ."<table class=\"claroTable\">\n";

            foreach($courseList as $thisCourse)
            {
				echo	"<tr>\n"

						."<td>\n"
						.$thisCourse['intitule']."\n"
						."<br><small>".$thisCourse['fake_code']." - ".$thisCourse['titulaires']."</small>\n"
						."</td>\n"

						."<td>\n";

				if($thisCourse['statut'] != 1)
				{
					echo "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exUnreg&amp;course=".$thisCourse['code'].$inURL
                         ."\" onclick=\"javascript:if(!confirm('"
                         .clean_str_for_javascript($lang_are_you_sure_to_remove_the_course_from_your_list)
                         ."')) return false;\">\n"
						 ."<img src=\"".$imgRepositoryWeb."unenroll.gif\" border=\"0\" alt=\"".$lang_unsubscribe."\">\n"
						 ."</a>\n";
				}
                else
                {
					echo	"<small><span class=\"highlight\">".$langCourseManager."</span></small>\n";
                }

				echo	 "</td>\n"
                        ."</tr>\n";
            } // foreach $courseList as $thisCourse

            echo     "</table>\n"
                    ."</blockquote>";
        }

		break;

} // end of switch ($displayMode)


echo $backLink;

include($includePath."/claro_init_footer.inc.php");


//////////////////////////////////////////////////////////////////////////////





/**
 * search a specific course based on his course code
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  string  $courseCode course code from the cours table
 *
 * @return array    course parameters
 *         boolean  FALSE  otherwise.
 */

function search_course($keyword)
{
    global $userId;
    global $is_platformAdmin;
    
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_course           = $tbl_mdb_names['course'           ];
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];

    $keyword = trim($keyword);
    
    if (!$is_platformAdmin) 
    {
        $visibility_cond = "(c.visible=\"2\" OR c.visible=\"1\")";
    }
    else
    {
        $visibility_cond = "1=1";
    }
    
    if (empty($keyword) ) return array();
    $upperKeyword = trim(strtoupper($keyword));

	$sql = 'SELECT c.intitule, c.titulaires, c.fake_code officialCode, c.code,
                   cu.user_id enrolled, c.visible
            FROM `'.$tbl_course.'` c
            
            LEFT JOIN `'.$tbl_rel_course_user.'` cu
            ON  c.code = cu.code_cours 
            AND cu.user_id = "'.$userId.'"

            WHERE '.$visibility_cond.'
	    AND   (UPPER(fake_code)  LIKE "%'.$upperKeyword.'%"
            OR    UPPER(intitule)   LIKE "%'.$upperKeyword.'%"
            OR    UPPER(titulaires) LIKE "%'.$upperKeyword.'%")

            
            
            ORDER BY officialCode';
                        
    $courseList = claro_sql_query_fetch_all($sql);

	if (count($courseList) > 0) return $courseList;
	else                        return false;
} // function search_course($keyword)



?>
