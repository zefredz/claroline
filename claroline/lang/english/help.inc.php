<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                                |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$           |
	  |   English Translation                                                |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */
// help_forums.php

$langHFor="Help Forums";
$langCloseWindow="Close window";

$langForContent="The forum is a written and asynchronous discussion tool.
 Where email allows one-to-one dialogue, forums allow public or semi-public
 dialogue.</p><p>Technically speaking, the students need only their
 browser to use claroline forums.</P><p>To organise forums, click on
 'Administer'. Discussions are organised in sets and subsets as
 following:</p><p><b>Category > Forum > Topic > Answers</b></p>To structure
 your students discussions, it is necessary to organise catgories and
 forums beforehand, leaving the creation of topics and answers to them. By
 default, the claroline forum contains only the category 'Public', a sample
 forum ans a sample topic.</p><p>The first thing you should do is deleting
 the sample topic and modify the first forum's name. Then, you can
 create, in the 'public' category, other forums, by groups or by themes, to
 fit your learning scenario requirements.</p><p>Don't mix Categories and
 forums, and don't forget that an empty category (without forums) does not
 appear on the student view.</p><p>The description of a forum can be the
 list of its members, the definition of a goal, a task, a theme...";



// help_home.php

$langHHome="Help Home Page";
$langHomeContent="For more convenience, claroline tools contain default entries.
 There is a small example in every tool to help you grasp quickly how it
 works. It is up to you to modify the example or to delete it.</p><p>For
 instance, here on the Home Page of your course website,there is a small
 introduction text saying 'This is the introduction text of your course. To
 replace it by your own text, click below on modify.' Click on modify, edit
 it and Ok. It's that simple. Every tool has the same logic:
 add, delete, modify, which is the logic of dynamic websites.</p><p>When
 you first create your website, most of the tools are active. Here again,
 it is up to you to deactivate the ones you don't need. You just have to
 click on 'deactivate'. Then it goes down to the grey section of your
 homepage an becomes invisible to your students.  However, you can
 reactivate it whenever you want, making it visible to the students once more.</p>
<p>You can add your own pages to your
 Home Page. These pages must be HTML pages (which can be created by any
 Word Processor or Web Composer). Use 'Upload page and link to Homepage' to
 send your page to the server. The standard header of your website will be
 automatically merged with your new document, so that you just need to
 concentrate on the content. If you want to link from your Home towards
 existaing websites or pages existing anywhere on the web (even inside your
 own site), use 'Add link on Homepage' The pages you have added to the Home
 page can deactivated then deleted, where the standard tools can be
 deactivated, but not deleted.</p><p>Once your course website is ready, go
 to 'Modify course info' and decide what level of confidentiality you want.
 By default, your course is hidden (because you work on it).</p>";



// help_claroline.php

$langHClar="Start Help";


$langClarContent="Here, professors and assistants create and administer
 courses websites. Students read (documents, agendas, informations) and,
 sometimes, make exercises, publish papers, participate to
 forum discussions...</p><b>Registration</b><p>The following instructions
 only apply if your version of claroline allows self-registration (some sites
 register you automatically). <br>If you are a Student, you
 just need to register selecting 'Follow courses (student)', then choose
 the courses you would like to follow.</p><p>If you are a Professor or an
 Assistant, register too, but select 'Create courses (professor)'. You will
 then have to fill a form with Course Code, Faculty and Course Title. Once
 this validated, you will be driven to the site you have just created and
 allowed to modify its content and organisation according to your
 requirements. </p><p>The 'To do' option is to allow feedback to your claroline website administrators.
 Things that you might post here include faults or suggestions for improvements. The 'To do'
 list is linked to on the Home Page of the campus (once
 logged in).</p>
<p>The support forum is different. It connects all Claroline users worldwide.
If you don't find the answer to a question inside your claroline campus and environment,
post a message there.</p>
<p>The link to Registration (if present) is on the Home Page of the campus
 (top right).</p><b>Login</b><p>On your next visits, type login/password
 and Ok to access your courses. The URL of the site is";
$langClarContent3="</p><p><b>Educational Theory</b><p>For the professors,
 prepare a course on the internet is a question of Educational Theory
 too.";
$langClarContent4="is at your disposal to help you during the different
 steps of your teaching project evolution: from tool design to its
 integration in a clear and coherent strategy and objective evaluation of
 its impact on student learning.</p>";



// help_document.php

$langHDoc="Help Documents";

$langDocContent="<p>The Documents tool is similar to the FileManager of
 your desktop computer.</p><p>You can upload files of any type (HTML, Word,
 Powerpoint, Excel, Acrobat, Flash, Quicktime, etc.). Your only concern
 must be that your students have the corresponding software to read them.
 Some file types can contain viruses, it is your responsibilty not to
 upload virus contaminated files. It is a worthwhile precaution to check documents with
 antivirus software before uploading them.</p>
<p>The documents are presented in alphabetical order.<br><b>Tip : </b>If
 you want to present them in a different order, numerate them: 01, 02,
 03...</p>
<p>You can :</p>
<h4>Upload a document</h4>
<ul>
  <li>Select the file on your computer using the Browse button <input
 type=submit value=Browse name=submit2>
	on the right of your screen.</li>
		<li>
			Launch the upload with the Upload Button <input type=submit value=Upload name=submit2>.
		</li>
	</ul>
	<h4>
		Rename a document (a directory)
	</h4>
	<ul>
		<li>
			click on the <img src=".$clarolineRepositoryWeb."img/edit.gif width=20 height=20 align=baseline>
			button in the Rename column
		</li>
		<li>
			Type the new name in the field (top left)
		</li>
		<li>
			Validate by clicking <input type=submit value=Ok name=submit24>.
		</li>
	</ul>
		<h4>
			Delete a document (or a directory)
		</h4>
		<ul>
			<li>
				Click on <img src=".$clarolineRepositoryWeb."img/delete.gif width=20 height=20>
				in column 'Delete'.
			</li>
		</ul>
		<h4>
			Make a document (or directory) invisible to students
		</h4>
		<ul>
			<li>
				Click on <img src=".$clarolineRepositoryWeb."img/visible.gif width=20 height=20>
				in column 'Visible/invisible'.
			</li>
			<li>
				The document (or directory) still exists but it is not visible by students anymore.
			</li>
			<li>
				To make it invisible back again, click on
				<img src=".$clarolineRepositoryWeb."img/invisible.gif width=24 height=20>
				in column 'Visible/invisible'
			</li>
		</ul>
		<h4>
			Add or modify a comment to a document (or a directory)
		</h4>
		<ul>
			<li>
				Click on <img src=".$clarolineRepositoryWeb."img/comment.gif width=20 height=20> in column 'Comment'
			</li>
			<li>
				Type new comment in the corresponding field (top right).
			</li>
			<li>
				Validate by clicking <input type=submit value=OK name=submit2>
			.</li>
		</ul>
		<p>
		To delete a comment, click on <img src=".$clarolineRepositoryWeb."img/comment.gif width=20 height=20>,
		delete the old comment in the field and click
		<input type=submit value=OK name=submit22>.
		<hr>
		<p>
			You can organise your content through filing. For this:
		</p>
		<h4>
			<b>
				Create a directory
			</b>
		</h4>
		<ul>
			<li>
				Click on
				<img src=".$clarolineRepositoryWeb."img/dossier.gif width=20 height=20>
				'Create a directory' (top left)
			</li>
			<li>
				Type the name of your new directory in the corresponding field (top left)
			</li>
			<li>
				Validate by clicking <input type=submit value=OK name=submit23>.
			</li>
		</ul>
		<h4>
			Move a document (or directory)
		</h4>
		<ul>
			<li>
				Click on button <img src=".$clarolineRepositoryWeb."img/deplacer.gif width=34 height=16>
				in column 'Move'
			</li>
			<li>
				Choose the directory into which you want to move the document (or directory) in the corresponding scrolling menu (top left) (note: the word 'root' means you cannot go upper than that level in the document tree of the server).
			</li>
			<li>
				Validate by clicking on <input type=submit value=OK name=submit232>.
			</li>
		</ul>
<center>
<p>";




// Help_user.php

$langHUser="Help Users";
$langUserContent="<b>Roles</b><p>Roles have no computer related function.
 They do not give rights on operating the system. They just indicate to
 Humans, who is who. You can modify them by clicking on 'modify' under
 'role', then typing whatever you want: professor, assistant, student,
 visitor, expert...</P><hr>
<b>Admin rights</b>
<p>Admin rights, on the other hand, correspond to the technical
 authorisation to modify the content and organisation of the course
 website. For the moment, you can only choose between giving all the admin
 rights and giving none of them.</P>
<p>To allow an assistant, for instance, to co-admin the site, you need to
 register him in the course or be sure he is already registerd, then click
 on 'modify' under 'admin rights', then click 'all', then 'Ok'.</P><hr>
<b>Co-chairmen</b>
<p>To mention in the header of the course website the name of a
 co-chairmen, use the tool 'Modify course information' (orange tools). This
 modification does not register your co-chairmen as a user of the course.
 The field 'Professors' is completely independant of the Users
 list.</p><hr>
<b>Add a user</b>
<p>To add a user for your course, fill the fields and validate. The person
will receive an email telling him/her you have registered him/her and telling
him/her or reminding him/her his/her login and  password.</p>";

// Help Group

$langHelpGroups="Help groups";
$langGroupContent="<p><b>Introduction</b></p>
<p>This tool allows to create and manage work groups.
At creation (Create groups), groups are emtpy. There are
many ways to fill them:
<ul><li>automatically ('Fill groups'),</li>
<li>manually ('Edit'),</li>
<li>self-registration by students diants (Groups settings: 'Self registration allowed...').</li>
</ul>
These three ways can be combined. You can, for instance, ask students to self-register first.
Then discover that some of them didn't and decide then to fill groups automatically in
order to complete them. You can also edit each group to compose membership one student
at a time after or before self-registration and/or automatical filling.</p>
<p>Groups filling, whether automatical or manual, works only if there are already students
registered in the course (don't mix registration to the course with registration into groups).
Students list is visible in <b>Users</b> tool. </p><hr noshade size=1>
<p><b>Create groups</b></p>
<p>To create new groups, click on 'Create new group(s)' and determine number of groups to
create. Maximum number of members is optional but we suggest to chose one. If you leave max. field
unchanged, groups size maximum will be infinite.</p><hr noshade size=1>
<p><b>Group settings</b></p>
<p>You can determine Group settings globally (for all groups).
<b>Students are allowed to self-register in groups</b>:
<p>You create empty groups, students self-register.
If you have defined a maximum number, full groups do not accept new members.
This method is good for teachers who do not know students list when
creating groups.</p>
<b>Outils</b>:</p>
<p>Every group possesses either a forum (private or public) or a Documents area
(a shared file manager) or (most frequent) both.</p>
<hr noshade size=1>
<p><b>Manual edit</b></p>
<p>Once groups created (Create groups), you see at bottom of page, a list of groups
with a series of informations and functions
<ul><li><b>Edit</b> to modify manually Group name, description, tutor,
members list.</li>
<li><b>Delete</b> deletes a group.</li></ul>
<hr noshade size=1>";

// help_exercise.php

$langHExercise="Help exercises";

$langExerciseContent="<p>The exercise tool allows you to create exercises that will contains as many questions as you like.<br><br>
There are various types of answers available for the creation of your questions :<br><br>
<ul>
  <li>Multiple choice (Unique answer)</li>
  <li>Multiple choice (multiple answers)</li>
  <li>Matching</li>
  <li>Fill in the blanks</li>
</ul>
An exercise gathers a certain number of questions under a common theme.</p>
<hr>
<b>Exercise creation</b>
<p>In order to create an exercise, click on the link &quot;New exercise&quot;.<br><br>
Type the exercise name, as well as an optional description of it.<br><br>
You can also choose between 2 exercise types :<br><br>
<ul>
  <li>Questions on an unique page</li>
  <li>One question per page (sequential)</li>
</ul>
and tell if you want or not questions to be randomly sorted at the time of the exercise running.<br><br>
Then, save your exercise. You will go to to the question administration for this exercise.</p>
<hr>
<b>Question adding</b>
<p>You can now add a question into the exercise previously created. The description is optional, as well as the picture that you have the possibility of linking to your question.</p>
<hr>
<b>Multiple choice</b>
<p>This is the famous MAQ (multiple answer question) / MCQ (multiple choice question).<br><br>
In order to create a MAQ / MCQ :<br><br>
<ul>
  <li>Define answers for your question. You can add or delete an answer by clicking on the right button</li>
  <li>Check via the left box the correct answer(s)</li>
  <li>Add an optional comment. This comment won't be seen by the student till this one has replied to the question</li>
  <li>Give a weighting to each answer. The weighting can be any positive or negatif integer, or zero</li>
  <li>Save your answers</li>
</ul></p>
<hr>
<b>Fill in the blanks</b>
<p>This allows you to create a text with gaps. The aim is to let student find words that you have removed from the text.<br><br>
To remove a word from the text, and so to create a blank, put this word between brackets [like this].<br><br>
Once the text has been typed and blanks defined, you can add a comment that will be seen by the student when it replies to the question.<br><br>
Save your text, and you will enter the next step that will allow you to give a weighting to each blank. For example, if the question worths 10 points and you have 5 blanks, you can give a weighting of 2 points to each blank.</p>
<hr>
<b>Matching</b>
<p>This answer type can be chosen so as to create a question where the student will have to connect elements from an unit U1 with elements from an unit U2.<br><br>
It can also be used to ask students to sort elements in a certain order.<br><br>
First define the options among which the student will be able to choose the good answer. Then, define the questions which will have to be linked to one of the options previously defined. Finally, connect via the drop-down menu elements from the first unit with those of the second one.<br><br>
Notice : Several elements from the first unit can point to the same element in the second unit.<br><br>
Give a weighting to each correct matching, and save your answer.</p>
<hr>
<b>Exercise modification</b>
<p>In order to modify an exercise, the principle is the same as for the creation. Just click on the picture <img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" align=\"absmiddle\"> beside the exercise to modify, and follow instructions above.</p>
<hr>
<b>Exercise deleting</b>
<p>In order to delete an exercise, click on the picture <img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" align=\"absmiddle\"> beside the exercise to delete.</p>
<hr>
<b>Exercise enabling</b>
<p>So as for an exercise to be used, you have to enable it by clicking on the picture <img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" align=\"absmiddle\"> beside the exercise to enable.</p>
<hr>
<b>Exercise running</b>
<p>You can test your exercise by clicking on its name in the exercise list.</p>
<hr>
<b>Random exercises</b>
<p>At the time of an exercise creation / modification, you can tell if you want questions to be drawn in a random order among all questions of the exercise.<br><br>
That means that, by enabling this option, questions will be drawn in a different order each time students will run the exercise.<br><br>
If you have got a big number of questions, you can also choose to randomly draw only X questions among all questions available in that exercise.</p>
<hr>
<b>Question pool</b>
<p>When you delete an exercise, questions of its own are not removed from the data base, and can be reused into a new exercise, via the question pool.<br><br>
The question pool also allows to reuse a same questions into several exercises.<br><br>
By default, all questions of your course are shown. You can show the questions related to an exercise, by chosing this one in the drop-down menu &quot;Filter&quot;.<br><br>
Orphan questions are questions that don't belong to any exercise.</p>";
?>
