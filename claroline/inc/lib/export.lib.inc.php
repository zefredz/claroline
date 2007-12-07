<?php // $Id$

// plusieures fonctions ci-dessous  ont été adaptées de fonctions  distribuées par www.nexen.net
/** 
 * Backup a db to a file 
 *
 * @param ressource    $link            lien vers la base de donnees 
 * @param string    $db_name        nom de la base de donnees 
 * @param boolean    $structure        true => sauvegarde de la structure des tables 
 * @param boolean    $donnees        true => sauvegarde des donnes des tables 
 * @param boolean    $format            format des donnees 
                                     'INSERT' => des clauses SQL INSERT
                                    'CSV' => donnees separees par des virgules
 * @param boolean    $insertComplet    true => clause INSERT avec nom des champs 
 * @param boolean    $verbose         true => comment are printed
 */ 
function backup_database($link , $db_name , $structure , $donnees , $format="SQL" , $whereSave=".", $insertComplet="",$verbose=false)
{ 

    $errorCode ="";
    if (!is_resource($link)) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] link is not a ressource";
        $error_no["backup"][] = "1";
        return false; 
    }

    mysql_select_db($db_name); 
    $format = strtolower($format); 
    $filename = $whereSave."/courseDbContent.".$format; 
    $format = strtoupper($format); 
    $fp = fopen($filename, "w");
    if (!is_resource($fp)) 
        return false; 
    // liste des tables 
    $res = mysql_list_tables($db_name, $link); 
    $num_rows = mysql_num_rows($res); 
    $i = 0; 
    while ($i < $num_rows) 
    { 
        if ($format=="PHP")
            fwrite($fp, "\nmysql_query(\"");
        if ($format=="HTML")
            fwrite($fp, "\n<h2>".$tablename."</h2><TABLE border=\"1\" width=\"100%\">");
        $tablename = mysql_tablename($res, $i); 
        if ($verbose)
            echo "[".$tablename."] ";
        if ($structure === true) 
        { 
            if ($format=="PHP" || $format=="SQL" )
                fwrite($fp, "DROP TABLE IF EXISTS `$tablename`;"); 
            if ($format=="PHP")
                fwrite($fp, "\");\n");
            if ($format=="PHP")
                fwrite($fp, "\nmysql_query(\"");
            // requete de creation de la table 
            $query = "SHOW CREATE TABLE `" . $tablename . "`"; 
            $resCreate = mysql_query($query); 
            $row = mysql_fetch_array($resCreate); 
            $schema = $row[1].";"; 
            if ($format=="PHP" || $format=="SQL" )
                fwrite($fp, "$schema"); 
            if ($format=="PHP")
                fwrite($fp, "\");\n\n");
        } 
        if ($donnees === true) 
        { 
            // les données de la table 
            $query = "SELECT * FROM $tablename";
            $resData = mysql_query($query); 
            if (mysql_num_rows($resData) > 0) 
            { 
                $sFieldnames = ""; 
                if ($insertComplet === true) 
                { 
                    $num_fields = mysql_num_fields($resData); 
                    for($j=0; $j < $num_fields; $j++) 
                    { 
                        $sFieldnames .= "`".mysql_field_name($resData, $j)."`, "; 
                    } 
                    $sFieldnames = "(".substr($sFieldnames, 0, -2).")"; 
                } 
                $sInsert = "INSERT INTO `$tablename` $sFieldnames values "; 
                while($rowdata = mysql_fetch_assoc($resData)) 
                { 
                    if ($format=="HTML")
                    {
                        $lesDonnees = "\n\t<tr>\n\t\t<td>".implode("\n\t\t</td>\n\t\t<td>", $rowdata)."\n\t\t</td>"; 
                    }
                    if ($format == "SQL" || $format=="PHP")
                    {
                        $lesDonnees = "<guillemet>".implode("<guillemet>,<guillemet>", $rowdata)."<guillemet>"; 
                        $lesDonnees = str_replace("<guillemet>", "'",addslashes($lesDonnees)); 
                        if ($format == "SQL") 
                        { 
                            $lesDonnees = $sInsert." ( ".$lesDonnees." );"; 
                        } 
                        if ($format=="PHP")
                            fwrite($fp, "\nmysql_query(\"");
                    }
                    fwrite($fp, "$lesDonnees"); 
                    if ($format=="PHP")
                        fwrite($fp, "\");\n");
                } 
            } 
        } 
        $i++; 
        if ($format=="HTML")
            fwrite($fp, "\n</TABLE>\n<HR>\n");
    } 
    echo "fin du backup au  format :".$format;
    
    fclose($fp); 
}


// function claro_copy_file($origDirPath, $destination) is in fileManagerLib.inc.php

function copydir($origine,$destination,$verbose=false)
{
    $dossier= @opendir($origine) or die( "<HR>impossible d'ouvrir ".$origine." [".__LINE__."]");
    if ($verbose)
        echo "<BR> $origine -> $destination";
/*    if (file_exists($destination))
    { 
        echo "la cible existe, ca ne va pas être possible";
        return 0;
    }
    */
    claro_mkdir($destination, 0770, true);

    if ($verbose)
        echo "
        <strong>
            [".basename($destination)."]
        </strong>
        <OL>";
    $total = 0;

    while ($fichier = readdir($dossier)) 
    {
        $l = array('.', '..');
        if (!in_array( $fichier, $l))
        {
            if (is_dir($origine."/".$fichier))
            {
                if ($verbose)
                    echo "
            <LI>";
                $total += copydir("$origine/$fichier", "$destination/$fichier",$verbose);
            }
            else 
            {
                copy("$origine/$fichier", "$destination/$fichier");
                if ($verbose)
                    echo "
            <LI>
                $fichier";
                $total++;
            }
            if ($verbose)
                echo "
            </LI>";
        }
    }
    if ($verbose)
        echo "
        </OL>";
    return $total;
}

/** 
 * Export a course to a zip file 
 *
 * @param integer    $currentCourseID    needed        sysId Of course to be exported 
 * @param boolean     $verboseBackup        def FALSE    echo  step of work
 * @param string    $ignore                def NONE     // future param  for selected bloc to export.
 * @param string    $formats            def ALL        ALL,SQL,PHP,XML,CSV,XLS,HTML

 
 * 1° Check if all data needed are aivailable
 * 2° Build the archive repository tree
 * 3° Build exported element and Fill  the archive repository tree
 * 4° Compress the tree
== tree structure ==                == here we can found ==
/archivePath/                        temporary files of export for the current claroline
    /$exportedCourseId                temporary files of export for the current course
        /$dateBackuping/            root of the future archive
            archive.ini                course properties
            readme.txt
            /originalDocs
            /html
            /sql
            /csv
            /xml
            /php
            ;

            about "ignore"
             As  we don't know what is  add in course  by the local admin  of  claroline,
             I  prefer follow the  logic : save all except ...
            
 */ 
function makeTheBackup($exportedCourseId, $verboseBackup="FALSE", $ignore="", $formats="ALL")
{
    GLOBAL     $error_msg,    $error_no, $db,
        
$archiveRepositorySys, $archiveRepositoryWeb,         // from configs files
$appendCourse, $appendMainDb,                        //
$archiveName, $mainDbName, 
$clarolineRepositorySys,$_course,$coursesRepositorySys,
$TABLEUSER, $TABLECOURSUSER, $TABLECOURS, $TABLEANNOUNCEMENT,
$langArchiveName, 
$langArchiveLocation, 
$langSizeOf, 
$langDisk_free_space,
$langCreateMissingDirectories,
$langBUCourseDataOfMainBase,
$langBUUsersInMainBase,
$langBUAnnounceInMainBase,
$langCopyDirectoryCourse,
$langFileCopied,
$langBackupOfDataBase,
$langBuildTheCompressedFile ;
////////////////////////////////////////////////////
// ****** 1° Check if all data needed are aivailable

// ****** 1° 1. language vars

if ($verboseBackup)
{
    if ($langArchiveName=="")                  $langArchiveName                 = "Archive name";
    if ($langArchiveLocation=="")              $langArchiveLocation             = "Archive location";
    if ($langSizeOf=="")                      $langSizeOf                      = "Size of";
    if ($langDisk_free_space=="")              $langDisk_free_space             = "Disk free";
    if ($langCreateMissingDirectories=="")  $langCreateMissingDirectories    = "Directory missing ";
    if ($langBUCourseDataOfMainBase=="")      $langBUCourseDataOfMainBase      = "Backup Course data";
    if ($langBUUsersInMainBase=="")          $langBUUsersInMainBase              = "Backup Users";
    if ($langBUAnnounceInMainBase=="")      $langBUAnnounceInMainBase        = "Backups announcement";
    if ($langCopyDirectoryCourse=="")          $langCopyDirectoryCourse         = "Copy files";
    if ($langFileCopied==""    )                  $langFileCopied                  = "File copied";
    if ($langBackupOfDataBase=="")          $langBackupOfDataBase              = "Backup of database";
    if ($langBuildTheCompressedFile=="")    $langBuildTheCompressedFile     = "zip file";
}

// ****** 1° 2. params.
    $errorCode =0;
    $stop = FALSE;
// ****** 1° 2. 1 params.needed
    if (!isset($exportedCourseId) )     
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] Course Id Missing";
        $error_no["backup"][] = "1";
        $stop = TRUE;
    }
    if (!isset($mainDbName) )     
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] Main Db name is Missing";
        $error_no["backup"][] = "2";
        $stop = TRUE;
    }
    if (!isset($archiveRepositorySys) ) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] archive Path not found";
        $error_no["backup"][] = "3";
        $stop = TRUE;
    }    
    if (!isset($appendMainDb) ) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] where place course datas from main db in archive";
        $error_no["backup"][] = "4";
        $stop = TRUE;
    }    
    if (!isset($appendCourse) ) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] where place course datas in archive";
        $error_no["backup"][] = "5";
        $stop = TRUE;
    }    
    if (!isset($TABLECOURS) ) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] name of table of course not defined";
        $error_no["backup"][] = "6";
        $stop = TRUE;
    }    
    if (!isset($TABLEUSER) ) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] name of table of users not defined";
        $error_no["backup"][] = "7";
        $stop = TRUE;
    }    
    if (!isset($TABLECOURSUSER) ) 
    {
        GLOBAL $error_msg,$error_no;
        $error_msg["backup"][] = "[".basename(__FILE__)."][".__LINE__."] name of table of subscription of users in courses not defined";
        $error_no["backup"][] = "8";
        $stop = TRUE;
    }    
    if ($stop)
    {
        return false;
    }
    
// ****** 1° 2. 2 params.optional
    if (!isset($verboseBackup) ) 
    {
        $verboseBackup = false;
    }

// ****** 1° 3. check if course exist

    //  not  done


//////////////////////////////////////////////
// ****** 2° Build the archive repository tree
// ****** 2° 1. fix names
    $shortDateBackuping  = date("YzBs"); // YEAR - Day in Year - Swatch - second 
    $archiveFileName = "archive.".$exportedCourseId.".".$shortDateBackuping.".zip";
    $dateBackuping  = $shortDateBackuping  ;
    $archiveDir .= $archiveRepositorySys.$exportedCourseId."/".$shortDateBackuping."/";
    $archiveDirOriginalDocs =     $archiveDir."originalDocs/"    ;
    $archiveDirHtml = $archiveDir."HTML/";
    $archiveDirCsv     = $archiveDir."CSV/";
    $archiveDirXml     = $archiveDir."XML/";
    $archiveDirPhp     = $archiveDir."PHP/";
    $archiveDirLog     = $archiveDir."LOG/";
    $archiveDirSql     = $archiveDir."SQL/";

    $systemFileNameOfArchive         = "claroBak-".$exportedCourseId."-".$dateBackuping.".txt";
    $systemFileNameOfArchiveIni     = "archive.ini";
    $systemFileNameOfReadMe         = "readme.txt";
    $systemFileNameOfarchiveLog     = "readme.txt";

###################
    if ($verboseBackup)
    {
        echo "<hr><u>",$langArchiveName,"</u> : "
            ,"<strong>",basename($systemFileNameOfArchive),"</strong><br><u>",$langArchiveLocation,"</u> : "
            ,"<strong>",realpath($systemFileNameOfArchive),"</strong><br><u>",$langSizeOf," ",realpath("../../".$exportedCourseId."/"),"</u> : "
            ,"<strong>",claro_get_file_size("../../".$exportedCourseId."/"),"</strong> bytes <br>";
        if (  function_exists(diskfreespace))
            echo "<u>".$langDisk_free_space."</u> : <strong>".diskfreespace("/")."</strong> bytes";
        echo "<hr>" ;
    }
    claro_mkdir($archiveDirOriginalDocs.$appendMainDb    ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirHtml.$appendMainDb            ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirCsv.$appendMainDb            ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirXml.$appendMainDb             ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirPhp.$appendMainDb             ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirLog.$appendMainDb             ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirSql.$appendMainDb             ,CLARO_FILE_PERMISSIONS, true);

    claro_mkdir($archiveDirOriginalDocs.$appendCourse    ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirHtml.$appendCourse            ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirCsv.$appendCourse            ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirXml.$appendCourse             ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirPhp.$appendCourse             ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirLog.$appendCourse             ,CLARO_FILE_PERMISSIONS, true);
    claro_mkdir($archiveDirSql.$appendCourse            ,CLARO_FILE_PERMISSIONS, true);

    $dirCourBase = $archiveDirSqlCourse;
    $dirMainBase = $archiveDirSqlMainDb;

/////////////////////////////////////////////////////////////////////////
// ****** 3° Build exported element and Fill  the archive repository tree
    if ( $verboseBackup )
        echo "
build config file
<hr>" ;

    // ********************************************************************
    // build config file
    // ********************************************************************
        $stringConfig="<?php
/*
      +----------------------------------------------------------------------+
      CLAROLINE version ".$clarolineVersion." 
      +----------------------------------------------------------------------+
      This file was generate by script " . $_SERVER['PHP_SELF'] . "
      ".date("r")."                  |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
*/

// Claroline Version was :  ".$clarolineVersion."
// Source was  in ".realpath("../../".$exportedCourseId."/")."
// find in ".$archiveDir."/courseBase/courseBase.sql sql to rebuild the cours base
// find in ".$archiveDir."/".$exportedCourseId." to content of directory of course

/**
 * options
 ";
        $stringConfig .="
 */";

// ********************************************************************
// Copy of  from DB main
// fields about this course
// ********************************************************************
//  info  about cours
// ********************************************************************

    if ( $verboseBackup )
        echo "
<LI>
".$langBUCourseDataOfMainBase."  ".$exportedCourseId."
<HR>
<PRE>";
    $sqlInsertCourse = "
INSERT INTO cours SET ";
    $csvInsertCourse ="\n";
    $iniCourse ="[".$exportedCourseId."]\n";
    $sqlSelectInfoCourse ="Select * from `".$TABLECOURS."` `cours` where code = '".$exportedCourseId."' ";
    $resInfoCourse = claro_sql_query($sqlSelectInfoCourse) ;
    $infoCourse = mysql_fetch_array( $resInfoCourse );
    for( $noField=0; $noField < mysql_num_fields( $resInfoCourse ); $noField++)
    {
        if ($noField>0)
            $sqlInsertCourse .= ", ";
        $nameField = mysql_field_name($resInfoCourse,$noField);
        /*echo "
        <BR>
        $nameField ->  ".$infoCourse["$nameField"]." ";
        */
        $sqlInsertCourse .= "$nameField = '".$infoCourse["$nameField"]."'";
        $csvInsertCourse .= "'".addslashes($infoCourse["$nameField"])."';";
    }

//     buildTheIniFile
    $iniCourse .= 
"name=".                strtr($infoCourse['intitule'       ],"()","[]")."\n".
"officialCode=".        strtr($infoCourse['fake_code'      ],"()","[]")."\n". // use in echo
"adminCode=".            strtr($infoCourse['code'              ],"()","[]")."\n". // use as key in db
"path=".                strtr($infoCourse['code'           ],"()","[]")."\n". // use as key in path
"dbName=".                strtr($infoCourse['code'           ],"()","[]")."\n". // use as key in db list
"titular=".                strtr($infoCourse['titulaire'      ],"()","[]")."\n".
"language=".            strtr($infoCourse['language'       ],"()","[]")."\n".
"extLinkUrl=".            strtr($infoCourse['departementUrl' ],"()","[]")."\n".
"extLinkName=".            strtr($infoCourse['departementName'],"()","[]")."\n".
"categoryCode=".        strtr($infoCourse['faCode'         ],"()","[]")."\n".
"categoryName=".        strtr($infoCourse['faName'         ],"()","[]")."\n".
"visibility=".            ($infoCourse['visible']==2 || $infoCourse['visible']==3).
"registrationAllowed=".    ($infoCourse['visible']==1 || $infoCourse['visible']==2);

    $sqlInsertCourse .= ";";
//    echo $csvInsertCourse."<BR>";    
    $stringConfig .= "
# Insert Course
#------------------------
#    ".$sqlInsertCourse."
#------------------------
    ";
    if ( $verboseBackup )
        echo "</PRE>";

    $fcoursql = fopen($archiveDirSql.$appendMainDb."course.sql", "w");
    fwrite($fcoursql, $sqlInsertCourse); 
    fclose($fcoursql);

    $fcourcsv = fopen($archiveDirCsv.$appendMainDb."course.csv", "w");
    fwrite($fcourcsv, $csvInsertCourse); 
    fclose($fcourcsv);

    $fcourini = fopen($archiveDir.$systemFileNameOfArchiveIni, "w");
    fwrite($fcourini, $iniCourse); 
    fclose($fcourini);

    echo $iniCourse, " ini Course";
// ********************************************************************
//  info  about users
// ********************************************************************

//    if ($backupUser )
    {
        if ( $verboseBackup )
            echo "
        <LI>
            ".$langBUUsersInMainBase." ".$exportedCourseId."
            <hR>
        <PRE>";
        
        // recup users
        $sqlUserOfTheCourse ="
    SELECT
        `user`.*
        FROM `".$TABLEUSER."`, `".$TABLECOURSUSER."`
        WHERE `user`.`user_id`=`cours_user`.`user_id`
            AND `cours_user`.`code_cours`='".$exportedCourseId."'";
        $resUsers = claro_sql_query($sqlUserOfTheCourse);
        $nbUsers = mysql_num_rows($resUsers);
        if ($nbUsers>0)
        {
            $nbFields = mysql_num_fields($resUsers);
            $sqlInsertUsers = "";
            $csvInsertUsers = "";
            $htmlInsertUsers = "<table>\t<TR>\n";
        //
        // creation of headers 
        //
            for($noField=0; $noField < $nbFields; $noField++)
            {
                $nameField = mysql_field_name($resUsers,$noField);
                $csvInsertUsers .= "'".addslashes($nameField)."';";
                $htmlInsertUsers .= "\t\t<TH>".$nameField."</TH>\n";
            }
            $htmlInsertUsers .= "\t</TR>\n";
        
        //
        // creation of body
        //
            while($users = mysql_fetch_array($resUsers))
            {
                $htmlInsertUsers .= "\t<TR>\n";
                $sqlInsertUsers .= "
        INSERT IGNORE INTO user SET ";
                $csvInsertUsers .= "\n";
                for($noField=0; $noField < $nbFields; $noField++)
                {
                    if ($noField>0)
                        $sqlInsertUsers .= ", ";
                    $nameField = mysql_field_name($resUsers,$noField);
                    /*echo "
                        <BR>
                        $nameField ->  ".$users["$nameField"]." ";
                    */
                    $sqlInsertUsers .= "$nameField = '".$users["$nameField"]."' ";
                    $csvInsertUsers .= "'".addslashes($users["$nameField"])."';";
                    $htmlInsertUsers .= "\t\t<TD>".$users["$nameField"]."</TD>\n";                
                }
                $sqlInsertUsers .= ";";
                $htmlInsertUsers .= "\t</TR>\n";
            }
            $htmlInsertUsers .= "</TABLE>\n";
            
            $stringConfig .= "
    # INSERT Users
    #------------------------------------------
    #    ".$sqlInsertUsers."
    #------------------------------------------
        ";
            $fuserssql = fopen($archiveDirSql.$appendMainDb."users.sql", "w");
            fwrite($fuserssql, $sqlInsertUsers); 
            fclose($fuserssql);
        
            $fuserscsv = fopen($archiveDirCsv.$appendMainDb."users.csv", "w");
            fwrite($fuserscsv, $csvInsertUsers); 
            fclose($fuserscsv);

            $fusershtml = fopen($archiveDirHtml.$appendMainDb."users.html", "w");
            fwrite($fusershtml, $htmlInsertUsers); 
            fclose($fusershtml);
        }
        else
        {
            if ( $verboseBackup )
                echo "<HR><div align=\"center\">NO user in this course !!!!</div><HR>";
    
        }
        if ( $verboseBackup )
            echo "</PRE>";
    }
/*  End  of  backup user */

if ($saveAnnouncement)
{
// ********************************************************************
//  info  about announcment
// ********************************************************************
    if ( $verboseBackup )
        echo "
    <LI>
        ".$langBUAnnounceInMainBase." ".$exportedCourseId."
        <hR>
    <PRE>";
    
    // recup annonce
    $sqlAnnounceOfTheCourse ="
SELECT
    *
    FROM  `".$TABLEANNOUNCEMENT."` 
    WHERE code_cours='".$exportedCourseId."'";

    $resAnn = claro_sql_query($sqlAnnounceOfTheCourse);
    $nbFields = mysql_num_fields($resAnn);
    $sqlInsertAnn = "";
    $csvInsertAnn = "";
    $htmlInsertAnn .= "<table>\t<TR>\n";
//
// creation of headers 
//
    for($noField=0; $noField < $nbFields; $noField++)
    {
        $nameField = mysql_field_name($resUsers,$noField);
        $csvInsertAnn .= "'".addslashes($nameField)."';";
        $htmlInsertAnn .= "\t\t<TH>".$nameField."</TH>\n";
    }
    $htmlInsertAnn .= "\t</TR>\n";

//
// creation of body
//
    while($announce = mysql_fetch_array($resAnn))
    {
        $htmlInsertAnn .= "\t<TR>\n";
        $sqlInsertAnn .= "
INSERT INTO users SET ";
        $csvInsertAnn .= "\n";
        for($noField=0; $noField < $nbFields; $noField++)
        {
            if ($noField>0)
                $sqlInsertAnn .= ", ";
            $nameField = mysql_field_name($resAnn,$noField);
                /*echo "
                <BR>
                $nameField ->  ".$users["$nameField"]." ";
                */
            $sqlInsertAnn .= "$nameField = '".addslashes($announce["$nameField"])."' ";
            $csvInsertAnn .= "'".addslashes($announce["$nameField"])."';";
            $htmlInsertAnn .= "\t\t<TD>".$announce["$nameField"]."</TD>\n";                
        }
        $sqlInsertAnn .= ";";
        $htmlInsertAnn .= "\t</TR>\n";
    }
    if ( $verboseBackup )
        echo "</PRE>";
    $htmlInsertAnn .= "</TABLE>\n";

    
    $stringConfig .= "
#INSERT ANNOUNCE
#------------------------------------------
#    ".$sqlInsertAnn."
#------------------------------------------
    ";
    $fannsql = fopen($archiveDirSql.$appendMainDb."annonces.sql", "w");
    fwrite($fannsql, $sqlInsertAnn); 
    fclose($fannsql);

    $fanncsv = fopen($archiveDirCsv.$appendMainDb."annnonces.csv", "w");
    fwrite($fanncsv, $csvInsertAnn); 
    fclose($fanncsv);

    $fannhtml = fopen($archiveDirHtml.$appendMainDb."annonces.html", "w");
    fwrite($fannhtml, $htmlInsertAnn); 
    fclose($fannhtml);

/*  End  of  backup Annonces */
}
    // we can copy file of course
    if ( $verboseBackup )
        echo "
        <LI>
            ".$langCopyDirectoryCourse;
            
    $nbFiles = copydir($coursesRepositorySys.$_course['path'], $archiveDirOriginalDocs.$appendCourse, $verboseBackup);
    if ( $verboseBackup )
        echo "
            <strong>
                ".$nbFiles."
            </strong>
            ".$langFileCopied."
            <br>
        </li>";
    $stringConfig .= "
// ".$nbFiles." was in ".realpath($archiveDirOriginalDocs);

// ********************************************************************
// Copy of  DB course
// with mysqldump
// ********************************************************************
    if ( $verboseBackup )
        echo "
        <LI>
            ".$langBackupOfDataBase." ".$exportedCourseId."  (SQL)
            <hr>";
    backup_database($db , $exportedCourseId , true, true , 'SQL' , $archiveDirSql.$appendCourse,true,$verboseBackup);
    if ( $verboseBackup )
        echo "
        </LI>
        <LI>
            ".$langBackupOfDataBase." ".$exportedCourseId."  (PHP)
            <hr>";
    backup_database($db , $exportedCourseId , true, true , 'PHP' , $archiveDirPhp.$appendCourse,true,$verboseBackup);
    if ( $verboseBackup )
        echo "
        </LI>
        <LI>
            ".$langBackupOfDataBase." ".$exportedCourseId."  (CSV)
            <hr>";
    backup_database($db , $exportedCourseId , true, true , 'CSV' , $archiveDirCsv.$appendCourse,true,$verboseBackup);
    if ( $verboseBackup )
        echo "
        <LI>
            ".$langBackupOfDataBase." ".$exportedCourseId."  (HTML)
            <hr>";
    backup_database($db , $exportedCourseId , true, true , 'HTML' , $archiveDirHtml.$appendCourse,true,$verboseBackup);
    if ( $verboseBackup )
        echo "
        <LI>
            ".$langBackupOfDataBase." ".$exportedCourseId."  (XML)
            <hr>";
    backup_database($db , $exportedCourseId , true, true , 'XML' , $archiveDirXml.$appendCourse,true,$verboseBackup);
    if ( $verboseBackup )
        echo "
        <LI>
            ".$langBackupOfDataBase." ".$exportedCourseId."  (LOG)
            <hr>";
    backup_database($db , $exportedCourseId , true, true , 'LOG' , $archiveDirLog.$appendCourse,true,$verboseBackup);

// ********************************************************************
// Copy of DB course
// with mysqldump
// ********************************************************************
    $fdesc = fopen($archiveDir.$systemFileNameOfArchive, "w");
    fwrite($fdesc,$stringConfig);
    fclose($fdesc);
    if ( $verboseBackup )
        echo "
        </LI>
    </OL>
    
    <br>";

///////////////////////////////////
// ****** 4° Compress the tree

    if (extension_loaded("zlib"))
    {

        $whatZip[]     = $archiveRepositorySys.$exportedCourseId."/".$shortDateBackuping."/HTML";
        $forgetPath = $archiveRepositorySys.$exportedCourseId."/".$shortDateBackuping."/";
        $prefixPath    = $exportedCourseId;

        $zipCourse = new PclZip($archiveRepositorySys.$archiveFileName);
        $zipRes= $zipCourse->create(    $whatZip, 
                                        PCLZIP_OPT_ADD_PATH, $prefixPath,    
                                        PCLZIP_OPT_REMOVE_PATH, $forgetPath    );
        if ($zipRes==0)
        {
            echo "<font size=\"+1\" color=\"#FF0000\">",$zipCourse->errorInfo(true),"</font>";
        }
        else
        for ($i=0; $i<sizeof($zipRes); $i++) 
        {
            for(reset($zipRes[$i]); $key = key($zipRes[$i]); next($zipRes[$i])) 
            {
                echo "File $i / [$key] = ".$list[$i][$key]."<br>";
            }
            echo "<br>";
        }
        $pathToArchive = $archiveRepositoryWeb.$archiveFileName;
        if ( $verboseBackup  )    echo "<hr>".$langBuildTheCompressedFile;
    } 

?>
    <!--
    <hr>
    3° - Si demandé suppression des éléments sources qui viennent d'être archivés
    <font color="#FF0000">
        non réalisé
    </font>
    -->
    <?php
    return 1;
}  // function makeTheBackup()

function setValueIfNotInSession($varname,$value)
{
    GLOBAL $$varname,$_SESSION;
    if (!isset($_SESSION["$varname"]))
    {
        $$varname = $value;
        session_register("$varname");
    }
}
?>
