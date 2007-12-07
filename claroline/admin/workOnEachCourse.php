<?
 
@include('../include/config.php');

@include("../lang/english/trad4all.inc.php");
@include("../lang/$language/trad4all.inc.php");
header('Content-Type: text/html; charset='. $charset);
@include("../lang/french/admin.inc.php");
@include("../lang/english/admin.inc.php");
@include("../lang/$language/admin.inc.php");
@include("../lang/french/create_course.inc.php");
@include("../lang/english/create_course.inc.php");
@include("../lang/$language/create_course.inc.php");


$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");

$slqListOfCourses = "SELECT * FROM `$mainDbName`.`cours` ";
$resCourses = mysql_query($slqListOfCourses);
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Sans titre</title>
</head>

<body>
 This  page  is   for  php coder.
 
 it's a template code  to  make an admin work  on  each course database.
 

<?

while ($coursAmodifier = mysql_fetch_array($resCourses) )
{
	echo "<hr>Traitement du cours ".$coursAmodifier["code"]."<br>";
/*
Fields aivailable.
cours_id - code - languageCourse - intitule - description - faculte - visible - cahier_charges - scoreShow - titulaires - fake_code - departmentUrlName - departmentUrl - versionDb - versionClaro - lastVisit - lastEdit - expirationDate - activityState
*/
	addCat1ForGroupIfMissing($coursAmodifier["code"]);
?><?
}
?>

</body>
</html>
<?

function addCat1ForGroupIfMissing($courseId)
{
	Global $langCatagoryGroup;
	$sql ="INSERT ignoreINTO `".$courseId.".`catagories` VALUES (1, '".$langCatagoryGroup."', NULL)";

}

?>
?>