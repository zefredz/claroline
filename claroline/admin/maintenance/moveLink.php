<?php // $Id$
/*
	In the  new claroline, the  tools link does'nt exist anymore.
	the table "link" is  moved to document in  file .url.
*/

/****** MOVE FILES **********/
include_once($includePath."/lib/fileUpload.lib.php");
include_once($includePath."/lib/fileManage.lib.php");

$sql_get_links="
SELECT id, url, titre, description
FROM `".$tbl_links."`
";

$res = claro_sql_query($sql_get_links);

if(mysql_errno()==0 && mysql_num_rows($res)>0)
{
	$linkRepository = "links";
	
	// create_unexisting_directory from fileUpload.lib.php
	$linkRepository = create_unexisting_directory($linkRepository);
	
	if (!$linkRepository) echo "** creation of $linkRepository error";
	$sql_get_visibility_of_tool_link = "
	SELECT vilibility FROM `".$tbl_old_tool_list."` 
	WHERE lien ='../claroline/link/link.php';";
	$result_linkVisibility = mysql_query($sql_get_visibility_of_tool_link);
	$linkVisibility = ((mysql_fetch_array($result_linkVisibility) == 1) ?'v':'i');
	update_db_info('update', $linkRepository, array( 'visible' => $linkVisibility ) );

	while ($linkToMove=myqsl_fetch_array($res))
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
		    create_link_file( $baseWorkDir.$linkRepository.'/'.$fileName.$linkFileExt, 
		                      $url);
							          $directoryName = dirname($file);
	        
	        if ( $directoryName == '/' || $directoryName == '\\' )
	        {
	            // When the dir is root, PHP dirname leaves a '\' for windows or a '/' for Unix
	            $directoryName = '';
	        }
	        $newComment = trim($linkToMove['description']); // remove spaces
			update_db_info('update', $file, array( 'comment' => $newComment ) );
			$sql_get_links="
			DELETE 
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
	
	//	créer un fichier texte avec le contenu.
		
		$fileBadUrl = fopen($baseWorkDir.$linkRepository.'/url.txt');
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

?>