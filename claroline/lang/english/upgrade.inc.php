<?php

/* Welcome Page */

$langTitleUpgrade = "<h2>Claroline Upgrade Tool<br />\n
                     from 1.4.* to 1.5</h2>\n";

$langDone = "Steps done";
$langTodo = "Steps todo";
$langAchieved = "Upgrade Process Achieved";

/* Step 0 */

$langStep0 = "Backup confirm";
$langMakeABackupBefore = "<p>The <em>Claroline Upgrade Tool</em> will retrieve the data of your previous Claroline
installation and set them to be compatible with the new Claroline version. This upgrade proceeds in three steps:</p>\n
<ol>\n
<li>It will get your previous platform main settings and put them in new configuration files</li>\n
<li>It will set the main Claroline tables (user, course categories, course list, ...) to be compatible with the new data structure.</li>\n
<li>It will update one by one each course data (directories, database tables, ...)</li>\n
</ol>\n
<p>Before proceeding to this upgrade:</p>\n
<table>
<tbody>
<tr valign=\"top\"><td>-</td><td>Make a whole backup of all you platform data (files and databases)</td><td>%s</td></tr>\n
</tbody>
</table>
<p>You won't be allowed to start the upgrade process before this point is marked as 'done'.</p>
";
$langConfirm = "done";

/* Step 1 */

$langStep1 = "Step 1 of 3: platform main settings";
$langIntroStep1 = "<p>The <em>Claroline Upgrade Tool</em> is going to proceed to the main setting upgrade. 
                These settings were stored into claroline/include/config.inc.php in your previous platform version.</p>";
$langLaunchStep1 = "<p><button onclick=\"document.location='%s';\">Launch platform main settings upgrade</button></p>";

/* Step 2 */

$langStep2 = "Step 2 of 3: main platform tables upgrade";
$langIntroStep2 = "<p>Now, the <em>Claroline Upgrade Tool</em> is going upgrade the data stored into the main Claroline tables 
                    (users, course categories, tools list, ...) and set it compatible with the new Claroline version.</p>
                   <p class=\"help\">Note: According to the speed of your server or the amount of data stored on your platform, this 
                   operation may take some time.</p>";
$langLaunchStep2 = "<p><button onclick=\"document.location='%s';\">Launch main platform tables upgrade</button></p>";
$langNextStep = "<p><button onclick=\"document.location='%s';\">Next ></button></p>";

/* Step 3 */

$langStep3 = "Step 3 of 3: courses upgrade";
$langIntroStep3 = "<p>Now the <em>Claroline Upgrade Tool</em> is going update course data (directories and database tables) one by one.
                   <p class=\"help\">Note: According to the speed of your server or the amount of data stored on your platform,
                   this operation may take some time.</p>";
$langLaunchStep3 = "<p><button onclick=\"document.location='%s';\">Launch course data upgrade</button></p>";
$langIntroStep3Run = "<p>The <em>Claroline Upgrade Tool</em> proceeds to the courses data upgrade</p>" ;
$langNbCoursesUpgraded = "<p style=\"text-align: center\"><strong>%s courses on %s already upgraded</strong><br /></p>";

/* stuff for all */

$langYes="yes";
$langNo="no";
$langSucceed="succeed";
$langFailed="<span style=\"color: red\">Failed</span>";
$langNextStep = "<p><button onclick=\"document.location='%s';\">Next ></button></p>";

?>
