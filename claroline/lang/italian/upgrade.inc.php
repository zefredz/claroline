<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.* (1)				                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   Italian translation  October 2004                                  |
      +----------------------------------------------------------------------+
      +----------------------------------------------------------------------+
      | Translator: GIOACCHINO POLETTO (info@polettogioacchino.com           |
	  |             ALESSANDRO BUGATTI (alessandro.bugatti@istruzione.it)    |
      +----------------------------------------------------------------------+
 */
/* Welcome Page */

$langTitleUpgrade = "<h2>Strumento per l'aggiornamento di Claroline<br />\n
                     dalla 1.4.* alla 1.5</h2>\n";

$langDone = "Passaggi fatti";
$langTodo = "Passaggi da fare";
$langAchieved = "Processo d'aggiornamento terminato";

/* Step 0 */

$langStep0 = "Conferma il backup";
$langMakeABackupBefore = "<p>Lo <em>strumento per l'aggiornamento di Claroline</em> recupererà i dati dalla tua precedente installazione
e li renderà compatibili con la nuova versione di Claroline. Questo aggiornamento viene fatto in tre passaggi:</p>\n
<ol>\n
<li>Prenderà le configurazioni principali della tua precedente installazione e le metterà nei nuovi file di configurazione</li>\n
<li>Renderà le principali tabelle di Claroline (utenti, categorie dei corsi, elenco dei corsi, ...) compatibili con la nuova struttura dati.</li>\n
<li>Aggiornerà uno per uno tutti i dati dei corsi (directory, tabelle del database, ...)</li>\n
</ol>\n
<p>Prima di procedere all'aggiornamento:</p>\n
<table>
<tbody>
<tr valign=\"top\"><td>-</td><td>Fai un backup completo di tutti i file della piattaforma (file e database)</td><td>%s</td></tr>\n
</tbody>
</table>
<p>Non potrai procedere fino a quando la casella 'fatto' non verrà spuntata.</p>
";
$langConfirm = "fatto";

/* Step 1 */

$langStep1 = "Passaggio 1 of 3: configurazioni principali";
$langIntroStep1 = "<p>Lo <em>strumento per l'aggiornamento di Claroline</em> è pronto per l'aggiornamento delle configurazioni principali.
                Queste informazioni erano scritte all'interno del file claroline/include/config.inc.php nella tua precedente installazione.</p>";
$langLaunchStep1 = "<p><button onclick=\"document.location='%s';\">Procedi con l'aggiornamento dei file di configurazione</button></p>";

/* Step 2 */

$langStep2 = "Passaggio 2 di 3: aggiornamento delle tabelle";
$langIntroStep2 = "<p>Ora lo <em>strumento per l'aggiornamento di Claroline</em> aggiornerà i dati contenuti nelle tabelle principali di Claroline
                    (utenti, categorie dei corsi, lista degli strumenti, ...) e li renderà compatibili con la nuova versione.</p>
                   <p class=\"help\">Attenzione: a seconda della velocità del tuo server o della quantità di dati contenuti sulla piattaforma, questa
                   operazione potrebbe durare un po' di tempo.</p>";
$langLaunchStep2 = "<p><button onclick=\"document.location='%s';\">Procedi con l'aggiornamento delle tabelle</button></p>";
$langNextStep = "<p><button onclick=\"document.location='%s';\">Prossimo ></button></p>";

/* Step 3 */

$langStep3 = "Passaggio 3 di 3: aggiornamento dei corsi";
$langIntroStep3 = "<p>Ora lo <em>strumento per l'aggiornamento di Claroline</em> aggiornerà i dati dei corsi (directory e tabelle del database) uno per uno.
                   <p class=\"help\">Attenzione: a seconda della velocità del tuo server o della quantità di dati contenuti sulla piattaforma, questa
                   operazione potrebbe durare un po' di tempo.</p>";
$langLaunchStep3 = "<p><button onclick=\"document.location='%s';\">Procedi con l'aggiornamento dei dati dei corsi</button></p>";
$langIntroStep3Run = "<p>Lo <em>strumento per l'aggiornamento di Claroline</em> procede con l'aggiornamento dei dati dei corsi</p>" ;
$langNbCoursesUpgraded = "<p style=\"text-align: center\"><strong>%s i corsi sono %s già aggiornati</strong><br /></p>";

/* stuff for all */

$langYes="si";
$langNo="no";
$langSucceed="successo";
$langFailed="<span style=\"color: red\">Fallito</span>";


?>
