<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------	

  /**
   * return the title of a course  
   *
   * @param $course_sys_code id of a course
   * @return string a string with the title of the course 
   */
   function get_course_title($course_sys_code)
   {
       $mainTbl = claro_sql_get_main_tbl();
        	
       $sql = "SELECT `intitule` , `fake_code` FROM `".$mainTbl["course"]."` WHERE `code` = '".$course_sys_code."'"; 
       $otherCourseInfo = claro_sql_query_fetch_all($sql);

	   $titleCourse = $otherCourseInfo[0]["fake_code"]." : ".$otherCourseInfo[0]["intitule"];
          
   	   return stripslashes($titleCourse);  
   }

	/**
    * return all info of a course 
    *
    * @param $cid the id of a course
    * @return array (array) an associative array containing all info of the course
    * @global $courseTablePrefix
    * @global $dbGlu
    */
    function get_info_course($cid)
    {    
        global $courseTablePrefix, $dbGlu;
        
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_course    = $tbl_mdb_names['course'   ];
        $tbl_category  = $tbl_mdb_names['category' ];
        
        if ($cid)
        {
            $sql =  "SELECT `c`.`code`, 
                        `c`.`intitule`, 
                        `c`.`fake_code`, 
                        `c`.`directory`, 
                        `c`.`dbName`, 
                        `c`.`titulaires`, 
                        `c`.`email`, 
                        `c`.`languageCourse`, 
                        `c`.`departmentUrl`, 
                        `c`.`departmentUrlName`, 
                        `c`.`visible`,
                        `cat`.`code` `faCode`, 
                        `cat`.`name` `faName`
                 FROM     `".$tbl_course."`    `c`
                 LEFT JOIN `".$tbl_category."` `cat`
                 ON `c`.`faculte` =  `cat`.`code`
                 WHERE `c`.`code` = '".$cid."'";

            $result = claro_sql_query($sql)  ;

            if (mysql_num_rows($result)>0)
            {
                $cData = mysql_fetch_array($result);

                $_course['name'        ]         = $cData['intitule'         ];
                $_course['officialCode']         = $cData['fake_code'        ]; // use in echo
                $_course['sysCode'     ]         = $cData['code'             ]; // use as key in db
                $_course['path'        ]         = $cData['directory'        ]; // use as key in path
                $_course['dbName'      ]         = $cData['dbName'           ]; // use as key in db list
                $_course['dbNameGlu'   ]         = $courseTablePrefix . $cData['dbName'] . $dbGlu; // use in all queries
                $_course['titular'     ]         = $cData['titulaires'       ];
                $_course['email'       ]         = $cData['email'            ];
                $_course['language'    ]         = $cData['languageCourse'   ];
                $_course['extLink'     ]['url' ] = $cData['departmentUrl'    ];
                $_course['extLink'     ]['name'] = $cData['departmentUrlName'];
                $_course['categoryCode']         = $cData['faCode'           ];
                $_course['categoryName']         = $cData['faName'           ];
                $_course['email'        ]        = $cData['email'            ];

                $_course['visibility'  ]         = (bool) ($cData['visible'] == 2 || $cData['visible'] == 3);
                $_course['registrationAllowed']  = (bool) ($cData['visible'] == 1 || $cData['visible'] == 2);

                // GET COURSE TABLE

                // read of group tools config related to this course

                $sql = "SELECT self_registration, 
                           private, 
                           nbGroupPerUser, 
                           forum, document, 
                           wiki, 
                           chat
                    FROM `".$_course['dbNameGlu']."group_property`";
            
                $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);
            
                $gpData = mysql_fetch_array($result);
            
                $_course ['registrationAllowed'] = (bool) ($gpData['self_registration'] == 1);
                $_course ['private'            ] = (bool) ($gpData['private'          ] == 1);
                $_course ['nbGroupPerUser'     ] = $gpData['nbGroupPerUser'];
                $_course ['tools'] ['forum'    ] = (bool) ($gpData['forum'            ] == 1);
                $_course ['tools'] ['document' ] = (bool) ($gpData['document'         ] == 1);
                $_course ['tools'] ['wiki'     ] = (bool) ($gpData['wiki'             ] == 1);
                $_course ['tools'] ['chat'     ] = (bool) ($gpData['chat'             ] == 1);
            }    
            else
            {
                return FALSE;
            }
        }
        else
        {
            $_course = NULL;
            //// all groups of these course
            ///  ( theses properies  are from the link  between  course and  group,
            //// but a group  can be only in one course)

            $_course ['registrationAllowed'] = FALSE;
            $_course ['tools'] ['forum'    ] = FALSE;
            $_course ['tools'] ['document' ] = FALSE;
            $_course ['tools'] ['wiki'     ] = FALSE;
            $_course ['tools'] ['chat'     ] = FALSE;
            $_course ['private'            ] = TRUE;
    	}

        return $_course;
    }
 
   /**
    * return all info of tool for a course
    *
    * @param $cid the id of a course
    * @return array (array) an associative array containing all info of tool for a course
    * @global $clarolineRepositoryWeb
    */ 
    function get_course_tool_list($cid)
    {
    	global $clarolineRepositoryWeb;
    	
    	$toolNameList = claro_get_tool_name_list();
    	
    	$_course = get_info_course($cid);
    	
    	$tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_tool = $tbl_mdb_names['tool'];
    	
    	$courseToolList = array();
    	
    	if ($cid) // have course keys to search data
   		{
        	$sql ="SELECT ctl.id             id,
                        pct.claro_label    label,
                        ctl.script_name    name,
                        ctl.access         access,
                        pct.icon           icon,
                        pct.access_manager access_manager,

                        IF(pct.script_url IS NULL ,
                           ctl.script_url,CONCAT('".$clarolineRepositoryWeb."', 
                           pct.script_url)) url

               			FROM `".$_course['dbNameGlu']."tool_list` ctl

               			LEFT JOIN `".$tbl_tool."` pct
               			ON       pct.id = ctl.tool_id
   
               			ORDER BY ctl.rank";
    
        	$result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);
        
         	while( $tlistData = mysql_fetch_array($result) ) 
        	{
        	    $courseToolList[] = $tlistData;
       		}
       		
       		$tmp = array();
       		
       		foreach($courseToolList as $courseTool)
       		{
       			if( isset($courseTool["label"]) )
       			{
       				$courseTool["name"] = $toolNameList[$courseTool["label"]];	
       			} 
       			$tmp[] = $courseTool;
       		}
       		
       		$courseToolList = $tmp;
       		unset( $tmp );
    	}
    	
    	return $courseToolList;
	}   
?>
