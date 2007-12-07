<?php // $Id$ 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
/*
addUserToGroup($uid,$gid,$cid) {}
addUserToCourse($uid,$cid) {}
removeUserFromGroup($uid,$gid,$cid) {}
removeUserFromCourse($uid,$cid) {}
removeUserFromClaroline($uid) {}
removeCourseFromClaroline($cid) {}
removeGroupFromCourse($gid,$cid) {}
createCourse
createUser
createGroup
createDepartment
getListUser
getListUserOfGroup($gid,$cid) {}
getListUserOfCourse($cid) {}
getListGroupOfCourse($cid) {}
getListCourse() {}
getListCourseOfDepartment($did) {}
getListDepartmentOfDepartment($did) {}
getListDepartment() {}
isUserInGroup($uid,$gid,$cid) {}
isUserInCourse($uid,$cid) {}
isAdmin($uid) {}
isAdminOfCourse($uid,$cid) {}
isTutorOfGroup($uid,$gid,$cid) {}

isMailValid($uid) {}
isUrlValid ($cid,$lid) {};

changeTutorStatus($uid,$gid,$cid,$state) {}
changeAdminStatus($uid,$cid,$state) {}

// ***WRITE***  = CREATE, UPDATE , DELETE
// CREATE  if id don't exist
// DELETE  if content is empty
// UPDATE in other case

// ***READ*** = READ LIST, READ ONE
// READ LIST if  param =="ALL"
// READ ONE  if param == id

writeAnnouncement($cid, $aid,$content) {}
readAnnouncement($cid) {}
moveUpAnnouncement($cid,$aid) {}
moveDownAnnouncement($cid,$aid) {}

writeEvent($cid, $aid,$content) {}
readEvent($cid) {}

writeLink($cid, $lid, $name, $url, $description) {}
readLink($cid) {}

writeWorkSession($cid, $wsid, $tilte, $description, $deadline, $dateToShow, $onFilePerStudent) {}
readWorkSession($cid, $wsid) {}

writeCourseProperties($cid, $code, $intitule, $departmentid, $headerLabel, $headerUrl, $lang, $titular, $visible, ...) {}
readCourseProperties($cid) {}

//write($cid, $id, $content) {}
//read($cid) {}

// *** admin ***

checkUserWorkIntegrity ($cid,$uid) {};
checkUserGroupIntegrity ($cid,$uid) {};
checkUserCourseIntegrity($cid) {}
checkCourseGroupIntegrity() {}

+----------------------------------------------------------------------+

 */

/*

 Base de données icampus  sur le serveur localhost

#
# Structure de la table `cours`
#

CREATE TABLE `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(20) default NULL,
  `departmentUrl` varchar(180) default NULL,
  `departmentUrlName` varchar(30) default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text,
  `faculte` varchar(12) default NULL,
  `visible` tinyint(4) default NULL,
  `cahier_charges` varchar(250) default NULL,
  `scoreShow` int(11) NOT NULL default '1',
  `titulaires` varchar(200) default NULL,
  `fake_code` varchar(20) default NULL,
  `versionDb` varchar(10) NOT NULL default 'NEVER SET',
  `versionClaro` varchar(10) NOT NULL default 'NEVER SET',
  `lastVisit` date NOT NULL default '0000-00-00',
  `lastEdit` datetime NOT NULL default '0000-00-00 00:00:00',
  `expirationDate` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`cours_id`)
) TYPE=MyISAM COMMENT='data of courses';
# --------------------------------------------------------

#
# Structure de la table `cours_user`
#

CREATE TABLE `cours_user` (
  `code_cours` varchar(30) NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '0',
  `role` varchar(60) default NULL,
  `group` int(11) default '0',
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  `aEffacer` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`code_cours`,`user_id`)
) TYPE=MyISAM COMMENT='link between courses and users (subscribe state)';
# --------------------------------------------------------

#
# Structure de la table `user`
#

CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `email` varchar(100) default NULL,
  `statut` tinyint(4) default NULL,
  `aEffacer` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM COMMENT='data of users';

# Structure de la table `group_properties`
CREATE TABLE `group_properties` (
  `id` tinyint(4) NOT NULL auto_increment,
  `self_registration` tinyint(4) default '1',
  `private` tinyint(4) default '0',
  `forum` tinyint(4) default '1',
  `document` tinyint(4) default '1',
  `wiki` tinyint(4) default '0',
  `agenda` tinyint(4) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `student_group`
#

CREATE TABLE `student_group` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `description` text,
  `tutor` int(11) default NULL,
  `forumId` int(11) default NULL,
  `maxStudent` int(11) NOT NULL default '0',
  `secretDirectory` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Structure de la table `user_group`
#

CREATE TABLE `user_group` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `team` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `role` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;





*/

/**
 * Add this user to the group
 * @return errorcode : 0 = ok
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Add  this user  to  the  group
 */
function   addUserToGroup($uid,$gid,$cid)
{
	// validée : non
	// fonctionne : non
	return 0;
}

/**
 * Subscribre a user to a course.
 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc add a user to a course.
 */
function  addUserToCourse($uid,$cid) 
{
	// validée : non
	// fonctionne : non
	exit("this script use an uncomplete function : addUserToCourse ".__FILE__." ".__LINE__);
	GLOBAL $mainDbName;
	$sql = "
INSERT
	INTO `".$mainDbName."`.`cours_user`
		(`code_cours`, `user_id`, `statut`, `role`)
		VALUES 
		('".$cid."', '".$uid."', '5', ' ')"; 
			mysql_query($sqlInsertCourse) ;
//			echo "<BR>".$sqlInsertCourse."<BR>";
		    if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()."<br>";
		
}




/**
 * Remove the user from the group in course
 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Remove the user from the group in course
 */
function   removeUserFromGroup($uid,$gid,$cid) 
{
	// validée : non
	// fonctionne : non
	if (isset($uid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "User identification missing";
		return $msg_error;
	}
	if (isset($gid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "Group identification missing";
		return $msg_error;
	}
	if (isset($cid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "Course identification missing";
		return $msg_error;
	}
	
	
	return 0;
}




/**
 * Empty the group
 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Empty the group
 */
 function   removeAllUserFromGroup($cid, $gid)
{
	// validée : non
	// fonctionne : non
	if (isset($cid))
	{
		$msg_error['code'] = 1;
		$msg_error['msg'] = "Course identification missing";
		return $msg_error;
	}
	if (isset($gid))
	{
		$sql = "DELETE FROM `".$cid.".`user_group WHERE `team` = '".$gid."'";
	}
	else
	{
		$sql ="DELETE FROM `".$cid.".`user_group";
	}
									
	$result = mysql_query($sql);
	$result2 = mysql_query("UPDATE `".$cid.".`student_group
							SET tutor='0'");
	return 0;
}

/**
 * Unsubscribe the user of this course
 * @return true if all is right
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Unsubscribe the user of this course
 */
function   removeUserFromCourse($uid,$cid) 
{
	// validée : non
	// fonctionne : non
	GLOBAL $mainDbName;
//  $gid =
	removeUserFromGroup($uid,$gid,$cid);
	$sql ="DELETE from `".$mainDbName."`.`cours_user` where code_cours = '".$cid."' and user_id = '".$uid."'";
	mysql_query($sql);
	return 0;
}

/**
 * Unsubscribe user from the system
 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc unsubscribe user from the system
 */
function   removeUserFromClaroline($uid) 
{
	// validée : non
	// fonctionne : non
	GLOBAL $mainDbName;
//	$cid = 
//  $gid =
	removeUserFromGroup($uid,$gid,$cid);
	removeUserFromCourse($uid,$cid);
	// I prefers here, move user data to a bin-table.
	$sql ="INSERT INTO `".$mainDbName."`.`bin-user` SELECT * from `".$mainDbName."`.`user` where user_id = '".$uid."'";
	mysql_query($sql);
	$sql ="DELETE from `".$mainDbName."`.`user` where user_id = '".$uid."'";
	mysql_query($sql);
	return 0;
}


/**
 * Remove the course from the system
 * @return unknown
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Remove the course from the system
 */
function   removeCourseFromClaroline($cid)
{
	// validée : non
	// fonctionne : non
	return 0;
}




/**
 * Remove the group from this course
 * @return unknown
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Remove the group from this course
 */
function   removeGroupFromCourse($gid,$cid)  
{
	// validée : non
	// fonctionne : non
	
	// 1° Backup group data
	// 2° Remove all user  from the  group
	// 3° remove the group 
	
	return 0;
}

/**
 * Check if all relations between the course and users are goods
 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Check if all relations between the course and users are goods
 */
function   checkCourseUserIntegrity($uid,$cid)  
{

	return false;

	// validée : non
	// fonctionne : non
	if ($uid < 1 )
	{
		$msg_error['code'] = 1;
		$msg_error['msg'] = "user identification error";
		return $msg_error;
	}
	// is user exisiting ?
	// is course exisiting ?
	// is  user in course ?

	return 0;
}




/**
 * Users of courses are in an existing group ?
 * @return unknown
 * @param	uid user id (user_id in mainDb.user table)
 * @param	cid course id (code_cours in mainDb.cours table)
 * @author	Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc	Users of courses are in an existing group ?
 */
function   checkGroupUserIntegrity($cid,$gid="")  
{
	// validée : non
	// fonctionne : non

	GLOBAL $mainDbName;
	$sql = "select count(id) nb From `".$cid."`.`user_group` ug left join `".$mainDbName."`.`cours_user` cu on gu.`user` = cu.`user_id` where cu.`user_id` is Null";
	$res = mysql_query($sql);
	$nb = mysql_fetch_array($res);
	return $nb['nb'];

	return 0;
}




/**
 * Check relation  between The group and  the  Course
 * @return true All is right
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc	check relation  between The group and  the  Course
 */
function   checkCourseGroupIntegrity($cid="",$gid="")
{
	// validée : non
	// fonctionne : non
	return 0;
}




/**
 * Create a course
 * @return unknown
 * @param 
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Create a course.
 */
function  createCourse ()
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   createUser ()
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function createGroup ()
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param cid 
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function createDepartment ()
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListUser ($cid="",$gid="")
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListUserOfGroup($gid,$cid) 
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListUserOfCourse($cid)  
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListGroupOfCourse($cid)  
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListCourse()  
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListCourseOfDepartment($did)  
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListDepartmentOfDepartment($did)
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param did
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   getListDepartment($did="")
{
	// validée : non
	// fonctionne : non

	return 0;
}

/**
 * @param cid		int	course id (code_cours in mainDb.cours table)
 * @param aid		int	announcement id (id in courseDb.annonces table)
 * @param content	string	content of announcement
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 * @return unknown
 */
function   writeAnnouncement($cid, $aid,$content)
{
	// validée : non
	// fonctionne : non

	return 0;

}


/**
 * @param cid	int	course id (code_cours in mainDb.cours table)
 * @param aid	int	announcement id (id in courseDb.annonces table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc content of the announcement
 * @return array
 */
function   readAnnouncement($cid)
{
	// validée : non
	// fonctionne : non

	return 0;

}


/**
 * @param cid	int	course id (code_cours in mainDb.cours table)
 * @param aid	int	announcement id (id in courseDb.annonces table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc swap rank  with the  previous announcement
 * @return unknown
 */
function   moveUpAnnouncement($cid,$aid)
{
	// validée : non
	// fonctionne : non

	return 0;

}


/**
 * @param cid	int	course id (code_cours in mainDb.cours table)
 * @param aid	int	announcement id (id in courseDb.annonces table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc swap rank  with the next announcement
 * @return unknown
 */
function   moveDownAnnouncement($cid,$aid)
{
	// validée : non
	// fonctionne : non

	return 0;
}

/**
 * @param cid 		int	course id (code_cours in mainDb.cours table)
 * @param eid		int	event id (id in mainDb.agenda table)
 * @param content	string	content
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc write in agenda
 * @return unknown
 */
function writeEvent($cid, $eid,$content)
{
	// validée : non
	// fonctionne : non

	return 0;

}


/**
 * @param cid 		int	course id (code_cours in mainDb.cours table)
 * @param eid		int	event id (id in mainDb.agenda table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc read the agenda
 * @return unknown
 */
function   readEvent($cid, $eid)
{
	// validée : non
	// fonctionne : non

	return 0;
}

/**
 * @param cid 		int	course id (code_cours in mainDb.cours table)
 * @param lid		int	link id (id in courseDb.liens table)
 * @param name		string  label of the link
 * @param url		string  url
 * @param description	string  description of document
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 * @return array
 */
function   writeLink($cid, $lid, $name, $url, $description)
{
	// validée : non
	// fonctionne : non

	return 0;

}


/**
 * @param cid	int	course id (code_cours in mainDb.cours table)
 * @param lid	int	link id (id in courseDb.liens table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc read info about the link
 * @return array infos about the link
 */

function   readLink($cid, $lid)
{
	// validée : non
	// fonctionne : non

	return 0;
}

/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   writeWorkSession($cid, $wsid, $tilte, $description, $deadline, $dateToShow, $onFilePerStudent)
{
	// validée : non
	// fonctionne : non

	return 0;

}


/**
 * @param cid course id (code_cours in mainDb.cours table)
 * @param wsid	int	Work Session id (id in courseDb.workSession table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc read info about the session of work for this course
 * @return array info about work
 */
function   readWorkSession($cid, $wsid)
{

}

/**
 * @param cid 		int	course id (code_cours in mainDb.cours table)
 * @param code          string	public code for this course
 * @param intitule	string	name of the course
 * @param departmentid	int	id  of faculty (soon called department) (id in mainDb.deparment table)
 * @param headerLabel	string	name of the link added in the header when user is in this course
 * @param headerUrl	string	link added in the header when user is in this course
 * @param lang          string	language of the course
 * @param titular	string	titulars names
 * @param visible       boolean	is visible to unidentified users ?
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Write info aboute this course in database.
 * @return	boolean
 */
function   writeCourseProperties($cid, $code, $intitule, $departmentid, $headerLabel, $headerUrl, $lang, $titular, $visible, ...)
{
	// validée : non
	// fonctionne : non

	return 0;

}

/**

 * @return unknown
 * @param cid	int	course id (code_cours in mainDb.cours table)
 * @param cid	string	"ALL"
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc read  info about  this  course in course table
 */
function readCourseProperties($cid)
{
	// validée : non
	// fonctionne : non           
	GLOBAL $mainDbName;

        $sql = "select * from `".$mainDbName."`.`course` c where cours_id = '".$cid."'";
	$res = mysql_query_dbg($sql);
	if (mysql_num_rows($res)>0)
        {
        	$course = mysql_fetch_array($res);
        	return $course[0];
        };
	
        return false;
}


/**

 * @return 0 if not, >0 if  yes
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   isUserInGroup($uid,$gid,$cid)
{
	// validée : non
	// fonctionne : oui

	$sql = "select count(id) nb from `".$cid."`.`user_group` ug where user = '".$uid."' and team = '".$gid."' ";
	$res = mysql_query_dbg($sql);
	//$res = mysql_query($sql);
	$nb = mysql_fetch_array($res);
	return $nb['nb'];
}




/**

 * @return 0 if not, >0 if  yes
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   isUserInCourse($uid,$cid)  
{
	// validée : non
	// fonctionne : oui
	GLOBAL $mainDbName;
	$sql = "select count(user_id) nb From `".$mainDbName."`.`cours_user` where `user_id` = '".$uid."' and `code_cours` = '".$cid."' ";
	$res = mysql_query($sql);
	$nb = mysql_fetch_array($res);
	return $nb['nb'];
}




/**

 * @return 0 if not, >0 if  yes
 * @param uid user id (user_id in mainDb.user table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   isAdmin($uid) 
{
	// fonctionne : oui;									
	// validée : non
	GLOBAL $mainDbName;
	$sql = "select count(idUser) nb From `".$mainDbName."`.`admin` where `idUser` = '".$uid."' ";
	$res = mysql_query($sql);
	$nb = mysql_fetch_array($res);
	return $nb['nb'];
}




/**

 * @return 0 if not, >0 if  yes
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   isAdminOfCourse($uid,$cid)  
{
	// validée : non
	// fonctionne : oui

	if (!isset($uid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "User identification missing";
		return $msg_error;

	}
	if (!isset($cid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "Course identification missing";
		return $msg_error;	
	}
	GLOBAL $mainDbName;
	$sql = "select count(user_id) nb From `".$mainDbName."`.`cours_user` where `user_id` = '".$uid."' and code_cours = '".$cid."' and `statut` = '1'";
	$res = mysql_query($sql);
	$nb = mysql_fetch_array($res);
	return $nb['nb'];
}




/**

 * @return 0 if not, >0 if  yes
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */

function   isTutorOfGroup($uid,$gid,$cid) 
{
	// validée : non
	// fonctionne : oui

	if (!isset($uid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "User identification missing";
		return $msg_error;
		
	}
	if (!isset($cid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "Course identification missing";
		return $msg_error;	
	}
	if (!isset($gid))
	{
		$msg_error = 1;
		$msg_error['msg'] = "Groupe identification missing";
		return $msg_error;	
	}
	GLOBAL $mainDbName;
	$sql = "select count(id) nb From `".$cid."`.`student_group` where `tutor` = '".$uid."' and `id` = '".$gid."'";
	$res = mysql_query($sql);
	$nb = mysql_fetch_array($res);
	return $nb['nb'];

}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   isMailValid($uid) 
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @param gid group/team  id (id in courseDb.student_group table)
 * @param state status
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function   changeTutorStatus($uid,$gid,$cid,$state) 
{
	// validée : non
	// fonctionne : non

	return 0;
}




/**

 * @return unknown
 * @param uid user id (user_id in mainDb.user table)
 * @param cid course id (code_cours in mainDb.cours table)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc Entrez la description ici...
 */
function  changeAdminStatus($uid,$cid,$state) 
{
	// validée : non
	// fonctionne : non

	// vérfier les paramètres

	if  ($state==1||$state==5)
	{
		$msg_error = 1;
		$msg_error['msg'] = "param state using  old system";
		return $msg_error;
	}
	elseif  ($state=="")
	{
		$msg_error = 1;
		$msg_error['msg'] = "param state missing";
		return $msg_error;
	}
	// vérfier les paramètres
	if  ($uid==""||$cid==""||$state=="")
	{
		$msg_error = 1;
		$msg_error['msg'] = "param missing";
		return $msg_error;
	}

	$sql = "update `".$mainDbName."`.`cours_user` set statut = ".$state." where user_id = ".$uid." and code_cours = '".$cid."'";
	mysql_query($sql);
	return(0);
}

/**
 * function   mergeUsersAccount($uidToKeep,$uidToDelete[,$uidToDelete]...) 
 * @return $msg_error = 1;
 * @return $msg_error['msg'] = "Accounts are in the same course.";
 * @param integer $uidToKeep
 * @param integer $uidToDelete 
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc This  function   got  to  merge 2 account
 */

function mergeUsersAccount($uidToKeep,$uidToDelete) 
{

	GLOBAL $mainDbName,$administratorSurname,$administratorName,$emailAdministrator,$siteName,$charset;

	// validée : non
	// fonctionne : non

	// http://www.icampus.ucl.ac.be/CLARO01/phiki/phiki.php3?FusionUtilisateurs

	$langUserWithManyAccountInYourCourse ="Problème sur : ";
	$langUserWithManyAccountInACourse = "Problème d'inscription multiple dans un cas de fusion";
	// actually  we go to begin  automation  working  only  if twe id  aren't  subscribed  in same  course
	
	// in mainDb
	$mailToAdminOfCourse = true;
	
	// if  there  is mor than  param
	$numargs = func_num_args();
	if ($numargs>2)
	{
		// multi-merge. 
		// mailToAdminOfCourse is dislabed
	    $mailToAdminOfCourse = false;
		
	}
	$uidToDeletes = func_get_args();
	$uidToKeep = array_shift($uidToDeletes);
	
	echo "<HR>";
	print_r($uidToDeletes);
	echo "<HR>";
	//1 °  Keep info  about the  2 users .
	
    	
	$sql = "Select * From `".$mainDbName."`.`user` Where  `user_id` ='".$uidToKeep."' or `user_id` ='".implode($uidToDeletes,"' or `user_id` ='")."'";
	echo $sql;
	$res = mysql_query($sql);
	if ( mysql_errno()>0)
	{
		$msg_error = 1;
		$msg_error['msg'] = "can't find users";
		return $msg_error;
	}
	while ($ligne = mysql_fetch_assoc($res)) // I can't use ($users = mysql_fetch_array($res))
	{
		$users[]=$ligne;                     // because  that add an empty $users[] on end
	}

	echo "<HR><PRE>";
	print_r($users);
	echo "</PRE><HR>";
	
	//2°  Check  if  user is  subscribed  to a same course with two account.
	// if  yes :  send  an msg and  stop  function (later we  can continue)
	$sql = "
Select count(`cours_user`.user_id) nbAccount, 
			`cours_user`.`code_cours` , `cours`.`fake_code`  
	From `".$mainDbName."`.`cours_user` 
	LEFT JOIN cours 
		ON cours_user.code_cours = cours.code 
		Where  `user_id` ='".$uidToKeep."'  or `user_id` ='".implode($uidToDeletes,"' or `user_id` ='")."'
	GROUP BY `cours_user`.`code_cours`
	";
	echo "<PRE>".$sql."</PRE>";
	$res = mysql_query_dbg($sql);
	$ok =true;
	while ($course = mysql_fetch_array($res))
	{
		$courses[]=$course;
		if ($course["nbAccount"] > 1 )
		{
			$ok= false;

			if ($mailToAdminOfCourse && 0) // remove && 0  to authorise mail to admin
			{
				$sqlAdminOfCourse ="
Select 
		email, nom, prenom From user , cours_user 
	Where
			user.user_id = cours_user.user_id 
		and cours_user.statut = 1 
		and cours_user.code = '".$course["code_cours"]."'";
				
				$resAdminOfCourse =  mysql_query_dbg($sqlAdimOfCourse);
				while ($adminOfCourse = mysql_fetch_array($resAdminOfCourse))
				{
					 $mailTo[] = addslashes($adminOfCourse["prenom"]." ".$adminOfCourse["nom"])." <".$adminOfCourse["email"].">";
				}
			}

			$mailTo[] =  "\"".addslashes($administratorSurname." ".$administratorName)."\" <".$emailAdministrator.">";

			$adminOfCourseEmail = implode($mailTo,", ");
			$subjectMail = $langUserWithManyAccountInYourCourse." ".$courses["fake_code"];

			$emailheaders  = "From: \"".addslashes($administratorSurname." ".$administratorName)."\" <".$emailAdministrator.">\r\n";
			$emailheaders .= "Reply-To: \"".addslashes($administratorSurname." ".$administratorName)."\" <".$emailAdministrator.">\r\n";
			$emailheaders .= "Return-path: $emailAdministrator\n"; 
			$emailheaders .= "Errors-To: $emailAdministrator\n";
			$emailheaders .= "MIME-Version: 1.0\r\n";
			$emailheaders .= "Content-Type: text/html; charset=".$charset."\r\n";
			$emailheaders .= "X-Priority: 2\r\n";
			$emailheaders .= "X-Mailer: PHP / ".phpversion()."\r\n";
			$emailheaders .="Comments: Signal des multiaccount ";

			$message = "
	Bonjour,
	une procédure de fusion d'utilisateur sur ".$siteName." est en  cours.\n
	Plus clairement, un utilisateur dispose de plusieurs comptes utilisateurs sur le système.\n
	Ce qui n'est pas favorable à la bonne utilsation de ".$siteName.".\n
	De tous les comptes créé par cet utilisateur, un seul subsitera. 
	Hors cet utilisateur s'est inscrit avec plus d'un compte 
	au cours, ".$courses["fake_code"].", que vous administrez.
	
	Pour éviter de désoganiser votre dispositif pédagogique sur ce cours, 
	nous ne préferons pas  proceder arbitrairement à la désincription de 
	votre cours de ces \"utilisateurs\" multiples.\n
	

			
			
	Voici donc la liste des  utilisateurs qui ne font qu'un.\n\n";
	while (list(,$user)=each($users))
		$message .= "
		 - id ".$user["user_id"]." - ".$user["prenom"]." ".$user["nom"]."";
		
		// ils faudrait ajouter les groupes.
		
	$message .= "
	\n
	Pourriez vous dans l'outil \"utilisateurs\" de ce cours, désinscrire  tous ceux
	que vous ne désirez pas conserver.
	
	Il faudra bien sur en conserver un pour que l'utilisateur continue à suivre votre cours.

	En fait, le principal problème se présentera si vous avez placé un compte dans un groupe
	et un compte dans un autre groupe. Seul le compte conservé restra dans un groupe. 
	L'autre groupe serait défait d'un membre. 
	
	Mis à part ce point, la disparition de ce compte en trop ne pose aucun problème
	
	Je reste à votre disposition au pour tout renseignement  supplémentaire au ".$administratorPhone."
	\n
	----------
	".$siteName."
	".$administratorSurname." ".$administratorName." 
	".$emailAdministrator;
			
			if ($adminOfCourseEmail!="")
			{
				//mail($adminOfCourseEmail,$subjectMail,$message,$headerMail);
				echo "<PRE>",$adminOfCourseEmail,"<BR>",$subjectMail,"<BR>",$message,"<BR>",$emailheaders,"</PRE>";
				
			}
		}	// end if  account>1
		
	} // all courses check for  multi-account
	
	if (!$ok)
	{
		// Merge  not possible
		// mail a repport to admin.
		

			/******* MAIL TO SEND *********/
			/******* To *********/
			$mailTo = addslashes($administratorSurname." ".$administratorName)."\" <".$emailAdministrator.">";
			/******* Subject *********/
			$subjectMail = $langUserWithManyAccountInACourse;
			/******* Headers *********/
			$emailheaders  = "From: \"".addslashes($administratorSurname." ".$administratorName)."\" <".$emailAdministrator.">\r\n";
			$emailheaders .= "Reply-To: \"".addslashes($administratorSurname." ".$administratorName)."\" <".$emailAdministrator.">\r\n";
			$emailheaders .= "Return-path: $emailAdministrator\n"; 
			$emailheaders .= "Errors-To: $emailAdministrator\n";
			$emailheaders .= "MIME-Version: 1.0\r\n";
			$emailheaders .= "Content-Type: text/html; charset=".$charset."\r\n";
			$emailheaders .= "X-Priority: 2\r\n";
			$emailheaders .= "X-Mailer: PHP / ".phpversion()."\r\n";
			$emailheaders .="Comments: Signal des multiaccount ";
			/******* Body *********/
			$message = "

	Rapport d'erreur sur  une fusion de comptes utilisateurs";
			reset($users);
			while (list(,$user)=each($users))
			{
				$message .= "
				";
				while (list($fieldName,$value)=each($user))
					$message .= " - ".$fieldName." : ".$value;
			}	
 			$message .= "
			
			
			";
			/******** Action *********/
			if ($adminOfCourseEmail!="" && $message!="")
			{
				// mail($adminOfCourseEmail,$subjectMail,$message,$headerMail);
				echo "<PRE>TO :",$adminOfCourseEmail,"<HR>Sujet:",$subjectMail,"<HR>",$message,"<HR>",$emailheaders,"</PRE>";
				
			}
			
	}
	//3° If  we are  are,  there  no course  with the  2 users
	//3°.1 We can Change user in claroline
	//  affected table admin - cours_user - loginout - todo containing a link  to  user_id
	
	$sql = "Update `".$mainDbName."`.`admin` 		Set `idUser` ='".$uidToKeep."' 		Where `idUser` IN '".implode($uidToDeletes,"','")."'";
	echo "<PRE>".$sql."</PRE>";
	//$res = mysql_query_ShowError($sql);
	$sql = "Update `".$mainDbName."`.`cours_user`	Set `user_id` ='".$uidToKeep."' 	Where `user_id`  IN '".implode($uidToDeletes,"','")."'";
	echo "<PRE>".$sql."</PRE>";
	//$res = mysql_query_ShowError($sql);

	//3°.2 We Change user in course where he is.
	//  affected table student_group - user_group -  # containing a link  to  user_id

	$sql = "
Select * From `".$mainDbName."`.`cours_user` Where  `user_id` ='".$uidToKeep."' or `user_id`  IN '".implode($uidToDeletes,"','")."'";
	echo "<HR><PRE>";
	print_r($sql);
	echo "</PRE><HR>";

	
	$res = mysql_query($sql);
	while ($courses = mysql_fetch_array($res))
	{
		// For  each  course 	
		$sql = "Update `".$courses["code"]."`.`student_group` 	Set `tutor` ='".$uidToKeep."' 	Where `tutor`  IN '".implode($uidToDeletes,"','")."'";
	echo "<PRE>".$sql."</PRE>";
	//		$res = mysql_query_ShowError($sql);
		$sql = "Update `".$courses["code"]."`.`user_group` 	Set `user` ='".$uidToKeep."' 	Where `user`  IN '".implode($uidToDeletes,"','")."'"; 
	echo "<PRE>".$sql."</PRE>";
	//	$res = mysql_query_ShowError($sql);
    };
	// 4° backup users
	/// DEBUG  NOTE :  je ne sais pas si c'est la solution à choisir
	$sql="
Insert 
	Into deletedUser
	 select * from user where user_id  IN '".implode($uidToDeletes,"','")."'";	
	echo "<PRE>".$sql."</PRE>";
	//$res = mysql_query_ShowError($sql);
	// 5° delete user
	$sql="
Delete
	from user 
	where user_id  IN
		 '".implode($uidToDeletes,"','")."'";
	echo "<PRE>".$sql."</PRE>";
	//	$res = mysql_query_ShowError($sql);
	return 0;
}


/**
 * this function return the array of users who's not connected since $nbDays days.
 * @return sting
 * @param integer	$nbDays
 * @param string	$orderBy default " nbLogin desc, maxWhen Asc"
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @desc return  the array of users who's not connected since $nbDays days.
 * @package mainDbLib
 */
function   usersNotLoggedSince($nbDays,$orderBy=" nbLogin desc, maxWhen Asc")
{
	// validée : non
	// fonctionne : non

	// in  mainDb
	GLOBAL $mainDbName;
	if (!isset($nbDays)||$nbdays==""||is_numeric($nbDays))
		exit("param \$nbDays missing");
		
	$sql = "
	SELECT max( loginout.when ) maxWhen, count( loginout.idLog ) nbLogin, user.user_id, user.username, user.prenom, user.nom, user.email
FROM `".$mainDbName."`loginout, `".$mainDbName."`user
WHERE (
loginout.id_user = user.user_id
)
GROUP BY loginout.id_user
HAVING (
maxwhen < DATE_ADD( CURDATE( ) , INTERVAL - ".$nbDays."
DAY )
)
ORDER BY ".$orderBy;

	$res = mysql_query_dbg($sql);
	while ($users = mysql_fetch_array($res))
		$lastLogin[]=$users;

	
	//compose return
	return $lastLogin;
}



function mysql_query_dbg($sql,$db="###")
{
	GLOBAL $debug;
	if (!isset($debug))
		$debug = false;
	
    if ($db=="###")
	{
		$val =  @mysql_query($sql);
	}
	else
	{
		$val =  @mysql_query($sql,$db);
	}

	if ($debug)
	{
		echo "<!-- \n $sql\n-->";
		if (mysql_errno())
		{
			echo "<HR>".mysql_errno().": ".mysql_error()."<br><PRE>$sql</PRE><HR>";
		}
	}

	return $val;
}


?>
