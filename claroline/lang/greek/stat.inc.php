<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   Greek Translation                                                  |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschι <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Costas Tsibanis		<costas@noc.uoa.gr>                      |
      |          Yannis Exidaridis 	<jexi@noc.uoa.gr>                        |
      +----------------------------------------------------------------------+
 */
 $msgAdminPanel = "Πίνακας Διαχείρισης";
 $msgStats = "Στατιστικά";
 $msgStatsBy = "Στατιστικά σύμφωνα με";
 $msgHours = "ώρες";
 $msgDay = "μέρα";
 $msgWeek = "εβδομάδα";
 $msgMonth = "μήνα";
 $msgYear = "χρόνος";
 $msgFrom = "από ";
 $msgTo = "προς ";
 $msgPreviousDay = "προηγούμενη μέρα";
 $msgNextDay = "επόμενη μέρα";
 $msgPreviousWeek = "προηγούμενη εβδομάδα";
 $msgNextWeek = "επόμενη εβδομάδα";
 $msgCalendar = "ημερολόγιο";
 $msgShowRowLogs = "show row logs";
 $msgRowLogs = "row logs";
 $msgRecords = "εγγραφές";
 $msgDaySort = "Ταξινόμηση σύμφωνα με την ημέρα";
 $msgMonthSort = "Ταξινόμηση σύμφωνα με το μήνα";
 $msgCountrySort = "Ταξινόμηση σύμφωνα με τη χώρα";
 $msgOsSort = "Ταξινόμηση σύμφωνα με το λειτουργικό σύστημα";
 $msgBrowserSort = "Ταξινόμηση σύμφωνα με το Browser";
 $msgProviderSort = "Ταξινόμηση σύμφωνα με το παροχέα υπηρεσιών";
 $msgTotal = "Συνολικά";
 $msgBaseConnectImpossible = "Δεν είναι δυνατή η επιλογή βάσης δεδομένων";
 $msgSqlConnectImpossible = "Δεν ειναι δυνατή η σύνδεση με τον εξυπηρέτη SQL";
 $msgSqlQuerryError = "Δεν είναι δυνατό το ερώτημα SQL";
 $msgBaseCreateError = "Παρουσιάστηκε σφάλμα κατά την διάρκεια της δημιουργίας ezboo";
 $msgMonthsArray = array("Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος");
 $msgDaysArray = array("Κυριακή","Δευτέραy","Τρίτη","Τετάρτη","Πέμπτη","Παρασκευή","Σάββατο");
 $msgDaysShortArray=array("Κ","Δ","Τ","Τ","Π","Π","Σ");
 $msgToday = "Σήμερα";
 $msgOther = "Αλλο";
 $msgUnknown = "Αγνωστο";
 $msgServerInfo = "Πληροφορίες για τον εξυπηρέτη της php";
 $msgStatBy = "Στατιστικά με";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Διαχειριστής:</b> Ενα cookie έχει δημιουργηθεί στον υπολογιστή σας,<BR>
     Δεν θα εμφανίζεστε πλέον στα logs σας.<br><br><br><br>";
 $msgCreateCookError = "<b>Διαχειριστής:</b>το cookie δεν ήταν δυνατόν να αποθηκευθεί στον υπολογιστή σας<br>
     Ελέγξτε τις ρυθμίσεις του browser και ανανεώστε ξανά τη σελίδα.<br><br><br><br>";
 $msgInstalComments = "<p>Η αυτόματη διαδικασία εγκατάστασης θα προσπαθήσει να:</p>
       <ul>
         <li>δημιουργήσει ένα πίνακα που ονομάζεται <b>liste_domaines</b> στην βάση δεδομένων<br>
           </b>Ο πίνακας αυτόματα θα συμπληρωθεί με ονόματα χωρών με βάση τους κωδικούς από το InterNIC</li>
         <li>δημιουργία ενός πίνακα που ονομάζεται <b>logezboo</b><br>
           Αυτός ο πίνακας θα αποθηκεύει τα logs</li>
       </ul>
       <font color=\"#FF3333\">Πρέπει να έχετε τροποποιήσει κατάλληλα το αρχείο<ul><li><b>config_sql.php3</b> με το  
<b>όνομα χρήστη</b>, <b>συνθηματικό</b> και τη<b>βάση δεδομένων </b> για τη σύνδεση με τον SQL εξυπηρέτη.</li><br><li>Το αρχείο
<b>config.inc.php3</b> 
πρέπει να έχει τροποποιηθεί για την επιλογή κατάλληλης γλώσσας.</font></li></ul><br>Μπορείτε να χρησιμοποιήσετε για αυτόν το σκοπό 
οποιοδήποτε επεξεργαστή κειμένου (π.χ. Notepad).";
 $msgInstallAbort = "Εγκατάλειψη του SETUP";
 $msgInstall1 = "Αν δεν υπάρχει μύνημα λάθους παραπάνω, η εγκατάσταση είναι επιτυχημένη.";
 $msgInstall2 = "Εχουν δημιουργηθεί 2 πίνακες στη βάση δεδομένων";
 $msgInstall3 = "Μπορείτε τώρα να ανοίξετε το κύριο interface";
 $msgInstall4 = "Για να συμπληρώσετε το πίνακά σας όταν οι σελίδες φορτωθούν, πρέπει να τοποθετήσετε μία ετικέτα στις σελίδες που θέλετε να παρακολουθείτε.";

 $msgUpgradeComments ="Η νέα έκδοση του ezBOO WebStats χρησιμοποιεί τον ίδιο πίνακα <b>logezboo</b> όπως οι προηγούμενες εκδόσεις.<br>
  						Αν οι χώρες δεν είναι στα Αγγλικά, πρέπει να διαγράψετε τον πίνακα <b>liste_domaine</b> 
  						και να ξεκινήσετε την εγκατάσταση.<br>
  						Αυτό δεν θα έχει αποτέλεσμα στον πίνακα <b>logezboo</b> .<br>
  						Το μύνημα λάθους ειναι φυσιολογικό. :-)";


$langStats="Στατιστικά";
?>
