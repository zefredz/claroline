<?php // $Id$
/***************************************************************************
 *                           lang_english.php  -  description
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *	    copyright            : (C) 2001 The phpBB Group
 *  	 email                : support@phpbb.com
 *
 *     $Id$
 *
 *  ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/



// GENERIC

$langModify="Wijzigen";
$langDelete="Verwijderen";
$langTitle="Titel";
$langHelp="Help";
$langOk="OK";
$langBackList="Terug naar de lijst";



$langLoginBeforePost1 = "Om berichten in het forum te publiceren ";
$langLoginBeforePost2 = "moet u eerst ";
$langLoginBeforePost3 = "ingelogd zijn";


// page_header.php

$langNewTopic="Nieuw onderwerp";
$langAnswer="Antwoorden";
$langHelp="Help";
$langAdm="Beheren";
$langQuote="Mededeling";
$langEditDel="Wijzigen/Verwijderen";
$langSeen="Gelezen";
$langLastMsg="Laatste bericht";
$langLastMsgs ="Laatste berichten";

$l_forum 	= "Forum";
$l_forums	= "Forums";
$l_topic	= "Onderwerp";
$l_topics 	= "Onderwerpen";
$l_replies	= "Antwoorden";
$l_poster	= "Indiener";
$l_author	= "Auteur";
$l_views	= "keer bekeken";
$l_post 	= "bericht";
$l_posts 	= "berichten";
$l_message	= "Bericht";
$l_messages	= "berichten";
$l_subject	= "Onderwerp";
$l_body		= "$l_message";
$l_from		= "Van";   // Message from
$l_moderator 	= "Moderator";
$l_username 	= "Gebruikersnaam";
$l_password 	= "Wachtwoord";
$l_email 	= "Email";
$l_emailaddress	= "Emailadres";
$l_preferences	= "Voorkeuren";

$l_anonymous	= "Anoniem";  // Post
$l_guest	= "Gast"; // Whosonline
$l_noposts	= "Geen $l_posts";
$l_joined	= "Ingeschreven";
$l_gotopage	= "Ga naar pagina";
$l_nextpage 	= "Volgende pagina";
$l_prevpage     = "Vorige pagina";
$l_go		= "Ga naar";
$l_selectforum	= "$l_forum selectioneren";

$l_date		= "Datum";
$l_number	= "Aantal";
$l_name		= "Naam";
$l_options 	= "Opties";
$l_submit	= "Opsturen";
$l_confirm 	= "Bevestigen";
$l_enter 	= "Enter";
$l_by		= "van"; // Opgestuurd door 
$l_ondate	= "op"; // Bericht van: $username de $date
$l_new          = "Nieuw";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "On";
$l_off		= "Off";
$l_yes		= "Ja";
$l_no		= "Nee";

$l_click 	= "Klik";
$l_here 	= "hier";
$l_toreturn	= "om terug te keren";
$l_returnindex	= "$l_toreturn naar de forumindex";
$l_returntopic	= "$l_toreturn naar de onderwerpenlijst van het forum.";

$l_error	= "Fout";
$l_tryagain	= "Ga terug en probeer opnieuw.";
$l_mismatch 	= "Foute informatie.";
$l_userremoved 	= "Deze deelnemer werd uit de databank gehaald.";
$l_wrongpass	= "U hebt een verkeerd wachtwoord ingegeven.";
$l_userpass	= "Gelieve uw gebruikersnaam en wachtwoord in te brengen.";
$l_banned 	= "Uw werd uit dit forum verwijderd. Neem contact op met de systeembeheerder indien u vragen hebt.";
$l_enterpassword= "U moet uw wachtwoord inbrengen.";

$l_nopost	= "U kan in dit forum niets indienen.";
$l_noread	= "U kan dit forum niet lezen.";

$l_lastpost 	= "Laatste $l_post";
$l_sincelast	= "sinds uw laatste bezoek";
$l_newposts 	= "Nieuwe $l_posts $l_sincelast";
$l_nonewposts 	= "Geen nieuwe $l_posts $l_sincelast";

// Index page
$l_indextitle	= "Forumindex";

// Members and profile
$l_profile	= "Profiel";
$l_register	= "Registreren";
$l_onlyreq 	= "Enkel gewijzigd";
$l_location 	= "Locatie";
$l_viewpostuser	= "Berichten bekijken van deze gebruiker";
$l_perday       = "$l_messages per dag";
$l_oftotal      = "van het totaal";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "ICQ nummer";
$l_icqadd	= "Toevoegen";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "Adres van de website";
$l_occupation 	= "Beroep";
$l_interests 	= "Vrije tijd";
$l_signature 	= "Tekening";
$l_sigexplain 	= "Deze tekst wordt toegevoegd aan de berichten die u verstuurt.<BR>255 lettertekens maximaal!";
$l_usertaken	= "De $l_username die u gekozen hebt, is al in gebruik.";
$l_userdisallowed= "De gekozen $l_username wordt door de beheerder niet aanvaard. $l_tryagain";
$l_infoupdated	= "Uw gegevens werden bijgewerkt";
$l_publicmail	= "Aan de andere gebruikers uw $l_emailaddress laten zien";
$l_itemsreq	= "De velden met een * moeten ingevuld worden";

// Viewforum
$l_viewforum	= "Bekijk Forum";
$l_notopics	= "Er zijn geen onderwerpen in dit forum.";
$l_hotthres	= "Geen $hot_threshold $l_posts";
$l_islocked	= "$l_topic is afgesloten (Er kunnen geen nieuwe $l_posts opgestuurd worden)";
$l_moderatedby	= "Gemodereerd door";

// Private forums
$l_privateforum	= "Dit <b>Forum is privé</b>.";
$l_private 	= "$l_privateforum<br>Nota: U moet cookies kunnen aanvaarden om de privé-forums te kunnen gebruiken.";
$l_noprivatepost = "$l_privateforum, u kan geen berichten opsturen naar dit forum.";

// Viewtopic
$l_topictitle	= "Bekijken van $l_topic";
$l_unregistered	= "Ongeregistreerde gebruiker";
$l_posted	= "Ingediend op";
$l_profileof	= "Bekijk profiel van";
$l_viewsite	= "Bekijk website van";
$l_icqstatus	= "$l_icq status";  // Etat ICQ 
$l_editdelete	= "Dit $l_post wijzigen/verwijderen";
$l_replyquote	= "Beantwoorden met mededeling";
$l_viewip	= "Bekijk IP (Enkel voor moderators)";
$l_locktopic	= "Sluit dit $l_topic";
$l_unlocktopic	= "Open dit $l_topic";
$l_movetopic	= "$l_topic verplaatsen";
$l_deletetopic	= "$l_topic verwijderen";

// Functions
$l_loggedinas	= "Ingelogd als";
$l_notloggedin	= "Niet ingelogd";
$l_logout	= "Logout";
$l_login	= "Login";

// Page_header
$l_separator	= "» »";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Profiel wijzigen";
$l_editprefs	= "$l_preferences wijzigen";
$l_search	= "Opzoeken";
$l_memberslist	= "Gebruikerslijst";
$l_faq		= "FAQ";
$l_privmsgs	= "Privé $l_messages";
$l_sendpmsg	= "Privé-bericht indienen";
$l_statsblock   = '$statsblock = "Onze gebruikers hebben een totaal van -$total_posts- $l_messages.<br>
We hebben -$total_users- geregistreerde gebruikers.<br>
De meeste recente geregistreede gebruiker is -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"gebruiker is":"gebruikers zijn") ." <a href=\"$online_url\">nu</a> op dit forum.<br>";';
$l_privnotify   = '$privnotify = "<br>U hebt $new_message <a href=\"$privmsg_url\">new private ".($new_message>1?"berichten":"bericht")."</a>.";';

// Page_tail
$l_adminpanel	= "Administratiepaneel";
$l_poweredby	= "Onderhouden door";
$l_version	= "Versie";

// Auth

// Register
$l_notfilledin	= "Fout - U hebt niet alle gevraagde velden ingevuld.";
$l_invalidname	= "De gekozen gebruikersnaam \"$username\" is al gebruikt.";
$l_disallowname	= "De gekozen gebruikersnaam \"$username\" wordt niet door de beheerder aanvaard.";

$l_welcomesubj	= "Welkom in de forums van $sitename";
$l_welcomemail	=
"
$l_welcomesubj,

Gelieve deze e-mail te bewaren.


Uw gegevens zijn de volgende:

----------------------------
Gebruikersnaam: $username
Wachtwoord : $password
----------------------------

Vergeet uw wachtwoord niet. Het is in onze databank gecrypteerd en dus niet terug te vinden. Indien u het toch vergeten bent, dan sturen wij U een eenvoudig script op die het mogelijk maakt een nieuw wachtwoord in te geven.

Bedankt voor uw registratie.

$email_sig
";
$l_beenadded	= "U werd in de databank toegevoegd.";
$l_thankregister= "Bedankt voor uw registratie!";
$l_useruniq	= "Een gebruiker moet uniek zijn. Twee gebruikers kunnen niet dezelfde gebruikersnaam hebben.";
$l_storecookie	= "Bewaar mijn gebruikersnaam gedurende 1 jaar in een cookie.";

// Prefs
$l_prefupdated	= "$l_preferences bijgewerkt. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs	= "$l_preferences wijzigen";
$l_themecookie	= "Nota: Om de thema's te kunnen gebruiken, moet u cookies activeren.";
$l_alwayssig	= "Handtekening altijd toevoegen";
$l_alwaysdisable= "Altijd inactiveren"; // voor de 3 volgende zinnen gebruikt
$l_alwayssmile	= "$l_alwaysdisable $l_smilies";
$l_alwayshtml	= "$l_alwaysdisable $l_html";
$l_alwaysbbcode	= "$l_alwaysdisable $l_bbcode";
$l_boardtheme	= "Thema";
$l_boardlang    = "Taal";
$l_nothemes	= "Geen thema in de databank";
$l_saveprefs	= "$l_preferences bewaren";

// Search
$l_searchterms	= "Trefwoorden";
$l_searchany	= "Eén van deze woorden opzoeken";
$l_searchall	= "ALLE woorden opzoeken";
$l_searchallfrm	= "In alle forums opzoeken";
$l_sortby	= "Sorteren volgens";
$l_searchin	= "Opzoeken in";
$l_titletext	= "Titel en tekst";
$l_search	= "Opzoeken";
$l_nomatches	= "Er bestaan geen record overeenkomend met Uw vraag. Verfijn Uw opzoekingen";

// Whosonline
$l_whosonline	= "Wie is online?";
$l_nousers	= "Er is momenteel geen gebruiker in deze forums";


// Editpost
$l_notedit	= "U mag geen berichten van anderen wijzigen.";
$l_permdeny	= "U hebt het juiste $l_password niet gegeven of U hebt geen toelating om dit bericht te wijzigen. $l_tryagain";
$l_editedby	= "Dit $l_message werd gewijzigd door:";
$l_stored	= "Uw $l_message werd opgeslagen.";
$l_viewmsg	= "om uw $l_message te lezen.";
$l_deleted	= "Uw $l_post werd verwijderd.";
$l_nouser	= "Deze $l_username bestaat niet.";
$l_passwdlost	= "Ik ben mijn wachtwoord vergeten!";
$l_delete	= "Dit bericht verwijderen";

$l_disable	= "Inactiveren";
$l_onthispost	= "op dit bericht";

$l_htmlis	= "$l_html is";
$l_bbcodeis	= "$l_bbcode is";

$l_notify	= "Verwittigen per e-mail wanneer de antwoorden ingediend worden";

// Newtopic
$l_emptymsg	= "U moet een $l_message inbrengen. U mag geen leeg $l_message opsturen.";
$l_aboutpost	= "Info over indienen";
$l_regusers	= "Alle <b>geregistreerde</b> gebruikers";
$l_anonusers	= "<b>Anonieme</b> gebruikers";
$l_modusers	= "Alleen <B>moderators en beheerders</b>";
$l_anonhint	= "<br>(Om iets anoniem op te sturen moet U geen gebruikersnaam of wachtwoord inbrengen)";
$l_inthisforum	= "mogen nieuwe onderwerpen en antwoorden in dit forum indienen";
$l_attachsig	= "Handtekening tonen <font size=-2>(Dit mag gewijzigd of toegevoegd worden aan uw profiel)</font>";
$l_cancelpost	= "Dit bericht verwijderen";

// Reply
$l_nopostlock	= "Uw kan niet op dit onderwerp antwoorden. Dit is afgesloten";
$l_topicreview  = "Onderwerpsoverzicht";
$l_notifysubj	= "Er werd een antwoord op uw onderwerp ingediend.";
$l_notifybody	= 'Beste $m[username].\r\nU krijgt deze e-mail omdat een bericht dat u ingediend had op het forum van $sitename een antwoord heeft gekregen
	en u had gevraagd om verwittigd te worden.

U kan het onderwerp zien op:

http://$SERVER_NAME$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

Of de forumindex $sitename bekijken op

http://$SERVER_NAME$url_phpbb

Bedankt voor het gebruiken van het forum van $sitename.

Tot ziens.

$email_sig';


$l_quotemsg	= '[quote]\nPersoon $m[post_time], $m[username] heeft geschreven:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "U moet de gebruikersnaam inbrengen aan wie u dit $l_message wenst op te sturen.";
$l_sendothermsg	= "Ander privé-bericht opsturen";
$l_cansend	= "kan $l_privmsgs opsturen";  // Alle geregistreerde gebruikers kunnen private berichten opsturen
$l_yourname	= "Uw $l_username";
$l_recptname	= "$l_username van de bestemmeling";

// Replypmsg
$l_pmposted	= "Antwoord opgestuurd. U mag hier <a href=\"viewpmsg.$phpEx\" klikken> </a> om uw $l_privmsgs te bekijken";

// Viewpmsg
$l_nopmsgs	= "U hebt geen $l_privmsgs.";
$l_reply	= "Beantwoorden";

// Delpmsg
$l_deletesucces	= "Verwijderen uitgevoerd.";

// Smilies
$l_smilesym	= "Wat schrijven";
$l_smileemotion	= "Gevoel";
$l_smilepict	= "Beeld";

// Sendpasswd
$l_wrongactiv	= "De gegeven activatiesleutel is niet correct. Controleer de $l_message email die u gekregen hebt en zie na of u de activatiesleutel correct gecopieerd heeft.";
$l_passchange	= "Uw wachtwoord is gewijzigd. U kan nu naar uw <a href=\"bb_profile.$phpEx?mode=edit\">profiel</a> surfen en uw wachtwoord wijzigen.";
$l_wrongmail	= "Het e-mailadres dat u ingegeven heeft, komt niet overeen met dat van onze databank.";

$l_passsubj	= "Forums $sitename - Wachtwoordwijziging";

$l_pwdmessage	= 'Beste $checkinfo[username],
U krijgt deze e-mail omdat U (of iemand die zich voor U uitgeeft) gevraagd heeft om van wachtwoord te veranderen op het forum van $sitename. Indien dit bericht een vergissing is, mag U dit bericht negeren, en Uw wachtwoord zal hetzelfde blijven.

Uw nieuw wachtwoord is: $newpw

Om dit wachtwoord geldig te maken gaat U naar de volgende pagina:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

Uw wachtwoord zal pas veranderd worden wanneer u deze pagina bezoekt. U kan daarna terug uw wachtwoord wijzigen door naar uw profiel te gaan.

Bedankt voor het gebruiken van het forum van $sitename

$email_sig';

$l_passsent	= "Uw wachtwoord wordt veranderd door een nieuw, gegenereerd wachtwoord. Controleer Uw mailbox om te weten hoe deze procedure verder te zetten";
$l_emailpass	= "Wachtwoord e-mail verloren";
$l_passexplain	= "Gelieve dit formulier in te vullen, een nieuwe wachtwoord zal opgestuurd worden naar uw e-mailadres";
$l_sendpass	= "Wachtwoord opsturen";




// Groups Management Claroline

$langGroupSpaceLink="Groepsruimte";
$langGroupForumLink="Groepsforum";
$langGroupDocumentsLink="Groepsdocumenten";
$langMyGroup="Mijn groep";
$langOneMyGroups="onder mijn beheer";



?>
