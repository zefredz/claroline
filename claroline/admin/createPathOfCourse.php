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

//	mkdir("../../$repertoire/document/dir", 0777);
##########################################################################
############### COPY IMAGES ##############################################
##########################################################################
/*
	copy("../img/pastille.png", "../../$repertoire/img/pastille.png");
	copy("../img/forum.png", "../../$repertoire/img/forums.png");
	copy("../img/agenda.png", "../../$repertoire/img/agenda.png");
	copy("../img/travaux.png", "../../$repertoire/img/travaux.png");
	copy("../img/quiz.png", "../../$repertoire/img/quiz.png");
	copy("../img/documents.png", "../../$repertoire/img/documents.png");
	copy("../img/liens.png", "../../$repertoire/img/liens.png");
	copy("../img/introduction.png", "../../$repertoire/img/introduction.png");
	copy("../img/resultats.png", "../../$repertoire/img/resultats.png");
	copy("../img/cahier.png", "../../$repertoire/img/cahier.png");
	copy("../img/ligne.png", "../../$repertoire/img/ligne.png");
	copy("../img/enregistrer.png", "../../$repertoire/img/enregistrer.png");
	copy("../img/identifier.png", "../../$repertoire/img/identifier.png");
	copy("../img/valves.png", "../../$repertoire/img/valves.png");
	copy("../img/membres.png", "../../$repertoire/img/membres.png");
	copy("../img/remise.png", "../../$repertoire/img/remise.png");
	copy("../img/enseignants.png", "../../$repertoire/img/enseignants.png");
	copy("../img/videos.gif", "../../$repertoire/img/videos.gif");
	copy("../img/video.gif", "../../$repertoire/img/video.gif");
	copy("../img/works.gif", "../../$repertoire/img/works.gif");
	copy("../img/work.gif", "../../$repertoire/img/work.gif");

	copy("../img/group.png", "../../$repertoire/img/group.png");
	copy("../img/info.gif", "../../$repertoire/img/info.gif");

#############COPIER DOCUMENTS ####################################
	copy("../document/Example_document.pdf", "../../$repertoire/document/Example_document.pdf");

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
