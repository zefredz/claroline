<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Sans titre</title>
</head>

<body>
<?
$repertoire = $currentCourseID;
echo "création ../../$repertoire ou ".realpath("../../$repertoire");
###########################################################################
################ CREATE DIRECTORIES #######################################
###########################################################################
	umask(0);
	mkdir("../../$repertoire", 0777);
	mkdir("../../$repertoire/image", 0777);
	mkdir("../../$repertoire/document", 0777);
 	mkdir("../../$repertoire/page", 0777);
 	mkdir("../../$repertoire/video", 0777);
	mkdir("../../$repertoire/work", 0777);
	mkdir("../../$repertoire/group", 0777);

####################################################################
################CREER PAGE ACCUEIL #################################
####################################################################
	*/
	$fd=fopen("../../$repertoire/index.php", "w");
$string="<?php
session_start();
session_register(\"dbname\");
include(\"../claroline/course_home/course_home.php\");
?>";

    fwrite($fd, "$string");

?>


</body>
</html>
