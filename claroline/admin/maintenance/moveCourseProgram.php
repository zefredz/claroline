<?php // $Id$

/*
	In the  new claroline, the  tools CourseProgram does'nt exist anymore.

	But  it's was no more than 	a link to an externa page.
	
	It was very  locally interest.

	The  link  is change  like a "added link" with external tools, and   hided  in  all course.
	
	
	must be set with $currentCourseProgram;
*/
	
	if (checkurl ($currentCourseProgram) < 400)
	{
		if ($verbose)
			echo "<br>Course program (".$currentCourseProgram.") change in a simple link in course home page";
		$sqlForUpdate[] = "# Course program (".$currentCourseProgram.") change in a simple link in course home page";
		$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `lien` = '".$currentCourseProgram."' WHERE lien LIKE '%ourse_program/cahier.ph%'";
	}
	else
	{
		if ($verbose)
			echo "<br>Course program (".$currentCourseProgram.") INVALID -> Removed";
		$sqlForUpdate[] = "# Course program (".$currentCourseProgram.") INVALID -> Removed";
		$sqlForUpdate[] = "Delete From `".$currentCourseDbNameGlu."accueil` WHERE lien LIKE '%ourse_program/cahier.ph%'";
	}
?>