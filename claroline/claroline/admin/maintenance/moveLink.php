<?php // $Id$
/*
	In the  new claroline, the  tools link does'nt exist anymore.
	the table "link" is  moved to document in  file .url.
*/

/**
 * include lib to move files 
*/

include_once($includePath."/lib/fileUpload.lib.php");
include_once($includePath."/lib/fileManage.lib.php");

$tbl_links = $currentCourseDbNameGlu."liens" ;
$tbl_old_tool_list = $currentCourseDbNameGlu."accueil" ;
$dbTable = $currentCourseDbNameGlu."document" ;
$docRepository = $currentcoursePathSys."document/";

/**
 * select link
*/ 

$sql_get_links=" SELECT id, url, titre, description
                 FROM `".$tbl_links."` ";

$result_links = claro_sql_query($sql_get_links);

if(mysql_errno()==0 && mysql_num_rows($result_links)>0)
{
	$linkRepository = $docRepository . "links";
	
	// create_unexisting_directory from fileUpload.lib.php
	$linkRepository = create_unexisting_directory($linkRepository);
	
     	if (!$linkRepository)
        {
            echo "<p class=\"error\">Creation of $linkRepository error</p>";
            $nbError++;          
        }
        else
        {
            $sql_get_visibility_of_tool_link = " SELECT visible FROM `".$tbl_old_tool_list."`" . 
                                               " WHERE lien ='../claroline/link/link.php';";
                                            
            $result_linkVisibility = mysql_query($sql_get_visibility_of_tool_link);
            $linkVisibility = ((mysql_fetch_array($result_linkVisibility) == 1) ?'v':'i');
        
            $doc_path_rep = str_replace($docRepository,"/",$linkRepository);
        
            update_db_info('update', $doc_path_rep, array( 'visible' => $linkVisibility ) );
                
            while ( $linkToMove = mysql_fetch_array($result_links, MYSQL_ASSOC))
            {
                $fileName = replace_dangerous_char($linkToMove['titre']);
		$url = trim($linkToMove['url']);
                
		// check for "http://", if the user forgot "http://" or "ftp://" or ...
		// the link will not be correct
		if( !ereg( "://",$url ) )
		{
		    // add "http://" as default protocol for url
		    $url = "http://".$url;
		}
		
		if ( ! empty($fileName) && ! empty($url) )
		{
		    $linkFileExt = ".url";
                    $url_file = $linkRepository.'/'.$fileName.$linkFileExt;
		    create_link_file($url_file, $url);
                    $newComment = addslashes(trim($linkToMove['description'])); // remove spaces
                    $doc_path_file = str_replace($docRepository,"/",$url_file);
                    update_db_info('update', $doc_path_file, array( 'comment' => $newComment ) );
                    $sql_get_links="DELETE 
                                    FROM `".$tbl_links."`
                                    WHERE id = ".$linkToMove['id']."";
                    $res=mysql_query($sql_get_links);
		}
		else
		{
                    $badUrl[]=$linkToMove;
		}
            };
	
            if (is_array($badUrl))
            {
	
                // create a text file with bad url
		
		$fileBadUrl = fopen($linkRepository.'/url.txt');
		foreach($badurl as  $linkInfo)
		{
			fwrite("== ".$linkInfo['url']." ==",$fileBadUrl);
			fwrite($linkInfo['url'],$fileBadUrl);
			fwrite("-- ".$linkInfo['description'],$fileBadUrl);
			fwrite("----------------------------",$fileBadUrl);
		}
		fclose($fileBadUrl);
            }
        }
}

?>