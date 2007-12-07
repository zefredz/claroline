<?php
$langAFewSecondsAfterTheLoadOfPageUpgradeToolWillAutomaticallyContinueItsJobIfItDoesntClickOnTheButtonBelow = "Σε περίπτωση διακοπής <sup>*</sup>, το <em>σύστημα Αναβάθμισης Claroline </em> θα πρέπει να κάνει αυτόματη επανεκίνηση.
Σε διαφορετική περίπτωση, κάντε κλικ στο παρακάτω πλήκτρο.";
$langAddIntro = "Προσθήκη Εισαγωγικού Κειμένου";
$langAdministration = "Διαχείριση";
$langAgenda = "Ατζέντα";
$langAnnouncement = "Ανακοινώσεις";
$langCancel = "Ακύρωση";
$langChat = "Κουβεντούλα";
$langCodeAppearAlready = "Αυτός ο επίσημος κώδικας εμφανίζεται ήδη σε προηγούμενη σειρά του αρχείου CSV.";
$langCodeUsed = "Αυτός ο επίσημος κωδικός χρησιμοποιείται ήδη από άλλο χρήστη.";
$langConfigurationFile = "Διαμόρφωση αρχείου";
$langConfirmYourChoice = "Παρακαλώ επιβεβαιώστε την επιλογή σας";
$langCopy = "Αντιγραφή";
$langCourseHome = "Αρχική σελίδα μαθήματος(Course)";
$langCourseManager = "Διαχειριστής Μαθήματος";
$langDay_of_weekNames = "Array";
$langDelete = "Διαγραφή";
$langDescriptionCours = "Περιγραφή μαθήματος";
$langDocument = "Εγγραφα";
$langDone = "Ολοκλήρωση βηματων";
$langEmpty = "Αφήσατε μερικά πεδία κενά.<br>Πατήστε το πλήκτρο «Επιστροφή» του browser και ξαναδοκιμάστε.";
$langExercises = "Ασκήσεις";
$langFailed = "<span style=\"color: red\">Failed</span>";
$langFirstDefOfThisValue = "!!! Πρώτος ορισμός αυτής της εκτίμησης !!!";
$langForums = "Περιοχή συζητήσεων";
$langGroups = "Ομάδες Χρηστών";
$langHelp = "Βοήθεια";
$langIntroStep1 = "<p>Το <em>Claroline εργαλείο αναβάθμισης </em> θα προχωρήσει στην κεντρική ρύθμιση αναβάθμισης. Αυτές οι ρυθμίσεις ήταν αποθηκευμένες στο claroline/inc/conf/claro_main.conf.php στην προηγούμενη σειρά (version) platform .</p>";
$langIntroStep2 = "<p>Tώρα, το <em>Claroline Εργαλείο Αναβάθμισης</em> θα ετοιμάσει μία βάση δεδομένων αποθηκευμένα στο <b>main Claroline tables</b>
(χρήστες, κατηγορίες μαθήματος, λίστα εργαλείων, ...) και θα τα ορίσει να ειναι συμβατά με το νεό τύπο Claroline.</p><p class=\"help\">Note. Εξαρτώμενη απο την ταχύτητα του server σας η ποσότητα δεδομένων που θα αποθηκευτεί στη platform, η λειτουργία αυτή μπορεί να πάρει καποιο χρόνο.</p>";
$langIntroStep3 = "<p>Τώρα το <em>Claroline Εργαλείο Αναβάθμισης</em> θα ετοιμάσει τα δεδομένα <b>course</b> (κατάλογοι και πίνακες δεδομένων) ενα - ένα και θα το ορίσει να είναι συμβατό με το νέο τύπο Claroline  .<p class=\"help\">Note. Depending of the speed of your server or the amount of data stored on your platform, this operation may take some time.</p>";
$langIntroStep3Run = "<p>Το  <em>Claroline Εργαλείο Αναβάθμισης</em> προχωρά στα μαθήματα αναβάθμισης δεδομένων </p>";
$langLaunchRestoreCourseRepository = "Παρουσίαση αποκατάσταση του χώρου φύλαξης μαθήματος";
$langLaunchStep1 = "<p><button onclick=\"document.location='%s';\">Παρουσίαση (launch???) κεντρικών ρυθμίσεων αναβάθμισης της platform </button></p>";
$langLaunchStep2 = "<p><button onclick=\"document.location='%s';\">Παρουσίαση πινακων αναβαθμισης της κεντρικής platform</button></p>";
$langLaunchStep3 = "<p><button onclick=\"document.location='%s';\">Παρουσίαση αναβάθμισης δεδομένων μαθήματος</button></p>";
$langLearningPath = "Διαδρομή μάθησης";
$langLogin = "Login";
$langLogout = "Εξοδος";
$langMailAppearAlready = "Αυτό το mail εμφανίζεται ήδη και σε προηγούμενη γραμμή του αρχείου CSV .";
$langMailSynthaxError = "Mail synthax σφάλμα.";
$langMailUsed = "Mail χρησιμοποιείται ήδη απο άλλο χρήστη";
$langMakeABackupBefore = "
<p>The <em>Claroline Upgrade Tool</em> will retrieve the data of your previous Claroline
installation and set them to be compatible with the new Claroline version. This upgrade proceeds in three steps:</p>
<ol>
<li>
It will get your previous platform main settings and put them in a new configuration files
</li>
<li>
It will set the main Claroline tables (user, course categories, course list, ...) to be compatible with the new data structure.
</li>
<li>
It will update one by one each course data (directories, database tables, ...)
</li>
</ol>
<p>
Before starting the <em>Claroline Upgrade Tool</em>,
we recommend you to make yourself a complete backup of
the platform data (files and databases).
</p>
<table>
<tbody>
<tr valign=\"top\">
<td>
The data backup has been done</td>
<td>%s</td>
</tr>
</tbody>
</table>
<p>
The <em>Claroline Upgrade Tool</em>
is not able to start if you do not confirm that the data has been done.</p>

";
$langManager = "Διαχειριστής";
$langModeVerbose = "Τύπος Verbose";
$langModify = "Διόρθωση";
$langModifyProfile = "Αλλαγή του προφίλ μου";
$langMonthNames = "Array";
$langMyAgenda = "Το ημερολόγιο μου";
$langMyCourses = "Τα μαθήματά μου";
$langNbCoursesUpgraded = "<p style=\"text-align: center\"><strong>%s courses on %s already upgraded</strong><br /></p>";
$langNext = "Επόμενο";
$langNextStep = "<p><button onclick=\"document.location='%s';\">Επόμενο ></button></p>";
$langNo = "όχι";
$langNotAllowed = "Δεν επιτρέπεται";
$langOk = "Εντάξει";
$langOtherCourses = "Λίστα Μαθημάτων";
$langPassword = "Κωδικός";
$langPasswordSimple = "Ο κωδικός που δόθηκε είναι πολύ απλός ή παρόμοιος με το όνομα χρήστη.";
$langPlatformAccess = "Είσοδος στο campus";
$langPlatformAdministration = "Διαχείρηση Πλατφόρμας";
$langPoweredBy = "Με τη βοήθεια του";
$langReg = "Εγγραφή";
$langRemainingSteps = "Βήματα που απέμειναν";
$langRestoreCourseRepository = "Επαναφορά πηγης πληροφοριών";
$langStartAgain = "Επανεκίνηση";
$langStudent = "φοιτητής";
$langSucceeded = "έγινε επιτυχώς";
$langSwitchEditorToTextConfirm = "Η εντολή θα αφαιρέσει τη τρέχουσα διάταξη κειμένου. Θέλετε να συμεχίσετε ?";
$langTextEditorDisable = "Απενεργοποίηση επεξεργαστή κειμένου";
$langTextEditorEnable = "Ενεργοποίηση επεξεργαστή κειμένου";
$langTitleUpgrade = "<h2>Claroline Εργαλείο αναβάθμισης<br />απο %s σε %s </h2>";
$langTo = "στο";
$langTodo = "Να γίνουν";
$langUndist = "Undist";
$langUpgrade = "Αναβάθμιση";
$langUpgradeCourseFailed = "Αποτυχία αναβάθμισης";
$langUpgradeCourseSucceed = "Επιτυχία αναβάθμισης";
$langUpgradeStep0 = "Backup επιβεβαίωση";
$langUpgradeStep1 = "Βήμα 1 απο 3: κεντρικές ρυθμίσεις πλατφόρμας";
$langUpgradeStep2 = "Βήμα  2 απο 3: αναβάθμιση πινακων κεντρικής πλατφόρμας";
$langUpgradeStep3 = "Βήμα  3 απο 3: αναβάθμιση μαθημάτων";
$langUpgradeSucceed = "Tο <em>Εργαλείο Αναβάθμισης Claroline </em> έχει ολοκληρώσει την αναβάθμιση της πλατφόρμας.";
$langUserName = "Όνομα χρήστη";
$langUsernameAppearAlready = "Αυτό το όνομα χρήστη εμφανίζεται ήδη σε προηγούμενη σειρά του αρχείου  CSV.";
$langUsernameUsed = "Αυτό το όνομα χρήστη χρησιμοποείται ήδη απο άλλο χρήστη.";
$langUsers = "Χρήστες";
$langViewMode = "Παρουσίαση τρόπου";
$langWork = "Εργασίες Φοιτητών";
$langYes = "Ναι";
$lang_CourseHasNoRepository_s_NotFound = "<strong>Το μάθημα δεν έχει ύλη.</strong>
<br><small>%s</small> Δεν βρέθηκε. ";
$lang_RetryWithMoreDetails = "Ξαναπροσπάθησε με περισσότερες λεπτομέρειες";
$lang_TheClarolineMainTablesHaveBeenSuccessfullyUpgraded = "Οι κεντρικοί πίνακες του claroline έχουν αναβαθμιστεί με επιτυχία";
$lang_UpgradeFailedForCourses = "Το Εργαλείο αναβάθμισης δεν μπορεί να αναβαθμίσει τα παρακάτω μαθήματα  :";
$lang_click_here = "Κάνε κλικ εδώ";
$lang_continueCoursesDataUpgrade = "Συνέχισε αναβάθμιση δεδομένων μαθημάτων";
$lang_enter_your_user_name_and_password = "Eισαγωγή ονόματος χρήστη (username) και κωδικού (password)";
$lang_fileUpgrade = "Αναβάθμιση αρχείου :";
$lang_footer_p_CourseManager = "Υπεύθυνος για %s";
$lang_if_you_dont_have_a_user_account_profile_on = "Εαν δεν έχετε λογαριασμό χρήστη ανοιχτό";
$lang_if_you_wish_to_enroll_to_this_course = "Εαν επιθυμείτε να εγγραφείτε στο μάθημα αυτό";
$lang_oldFileBackup = "Παλαιού αρχείου backup :";
$lang_p_CannotCreate_s = "Δεν μπορεί να δημιουργήσει %s";
$lang_p_CannotRename_s_s = "Δεν μπορεί να ονομάστει ξανα %s σε %s";
$lang_p_UpgradeMainClarolineDatabase_s = "Αναβάθμιση κυρίας βάσης δεδομένων του Claroline (<em>%s</em>)";
$lang_p_UpgradingDatabaseOfCourse = "<table><tr valign=\"top\"><td><strong>%1\$s. </strong></td><td>Αναβάθμιση βάσης δεδομένων του μαθήματος <strong>%2\$s</strong><br><small>
DB Name : %3\$s <br>
Course ID: %4\$s</small></td></tr></table>";
$lang_p_YouCan_url_retryToUpgradeTheseCourse = "Διόρθωσε πρώτα το τεχνικό πρόβλημα και <a href=\"%s\"> προσπάθησε πάλι για αναβάθμισης</a>.";
$lang_p_d_affected_rows = "%d Επηρεασμένες σειρές";
$lang_p_d_coursesToUpgrade = "%s μαθήματα για αναβάθμιση";
$lang_p_d_errorFound = " %d σφάλματα βρέθηκαν";
$lang_p_expectedRemainingTime = " <!-- Χρόνος εκτέλεσης αυτού του μαθήματος [%01.2f s] - μέσο όρο  [%01.2f s] - σύνολο [%s] - μαθήματα που απομείναν [%d]. --><b>Αναμενόμενος χρόνος που απομένει %s</b>.";
$lang_p_platformManager = "Διαχειριστής για %s";
$lang_p_s_s_isInvalid = "%s : %s δεν είναι έγκυρο";
$lang_rules_s_in_s = "Κανόνες : %s in %s";
$lang_seeInTheStatusBarOfYourBrowser = "(*) δες στην γραμμη κατάστασης του browser σου.";
$lang_theClarolineUpgradeToolHasSuccessfulllyUpgradeAllYourPlatformCourses = "Η διαδικασία αναβάμισης του Claroline ολοκληρώθηκε";
$lang_this_course_is_protected = "Αυτό το μάθημα προστατεύεται";
$lang_upgradeToolCannotUpgradeThisCourse = "Το εργαλείο αναβάθμισης δεν μπορεί να αναβαθμίσει αυτό το μάθημα.  <br>
Διόρθωσε, πρώτα, το τεχνικό πρόβλημα και ξεκίνησε πάλι το εργαλείο αναβάθμισης.";
$lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course = "Το προφίλ χρήστη δεν φαινεται εγγεγραμένο σε αυτό το μάθημα";
?>