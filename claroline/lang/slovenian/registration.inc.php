<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
	  |   English Translation                                                |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesch�<gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */

// userMAnagement
$langAdminOfCourse		= "admin";  //
$langSimpleUserOfCourse = "normal"; // strings for synopsis
$langIsTutor  			= "Ucitelj"; //

$langCourseCode			= "Tecaj";	// strings for list Mode
$langParamInTheCourse 	= "Stanje"; //

$langAddNewUser = "Dodaj uporabnika";
$langMember ="registriran";

$langDelete	="brisi";
$langLock	= "zakleni";
$langUnlock	= "odkleni";
// $langOk

$langHaveNoCourse = "ni tecaja";


$langFirstname = "Priimek"; // by moosh
$langLastname = "Ime"; // by moosh
$langEmail = "e-posta";// by moosh
$langRetrieve ="Poisci podatke o identiteti";// by moosh
$langMailSentToAdmin = "Posta je poslana administratorju.";// by moosh
$langAccountNotExist = "Ne najdem uporabnika.<BR>".$langMailSentToAdmin." Potrebno je rocno iskanje.<BR>";// by moosh
$langAccountExist = "Ta uporabnik obstaja.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "Posto lahko posljemo ";// by moosh
$langCaseSensitiveCaution = "Sistem je obcutljiv na velike ali male crke.";// by moosh
$langDataFromUser = "Podatki poslani uporabniku";// by moosh
$langDataFromDb = "Podatki v podatkovni bazi";// by moosh
$langLoginRequest = "Zahtevek po vstopu";// by moosh
$langExplainFormLostPass = "Vtipkaj, kar mislis, da si vnesel ob registraciji.";// by moosh
$langTotalEntryFound = "Vnos je najden";// by moosh
$langEmailNotSent = "Nekaj ne deluje, poslji posto k ";// by moosh
$langYourAccountParam = "To je tvoje uporabnisko ime-geslo";// by moosh
$langTryWith ="Poskusi z";// by moosh
$langInPlaceOf ="in ne z ";// by moosh
$langParamSentTo = "Informacija o identifikaciji je poslana k  ";// by moosh

// REGISTRATION - AUTH - inscription.php
$langRegistration="Registracija";
$langName="Priimek";
$langSurname="Ime";
$langUsername="Uporabnisko ime";
$langPass="Geslo";
$langConfirmation="Ponovi geslo";
$langEmail="e-posta";
$langStatus="Akcija";
$langRegStudent="Sledi tecajem";
$langRegAdmin="Tvori spletne strani tecaja";

// inscription_second.php
$langPassTwice="Vtipkal si dve razlicni gesli. V brkljalniku se vrni na prejsnjo stran in poskusi znova.";
$langEmptyFields="Nekaj polj si pustil praznih. V brkljalniku se vrni na prejsnjo stran in poskusi znova.";
$langUserFree="To uporabnisko ime ze obstaja. V brkljalniku se vrni na prejsnjo stran  in izberi drugo ime.";
$langYourReg="Tvoja registracija";
$langDear="Dragi";
$langYouAreReg="Registriran si";
$langSettings="z naslednjimi nastavitvami:\nIme uporabnika:";
$langAddress="Naslov ";
$langIs="je";
$langProblem="V primeru tezav, nam sporoci.";
$langFormula="S spostovanjem";
$langManager="Administrator";
$langPersonalSettings="Tvoje osebne nastavitve so zapisane in poslana je e-posta, da bi si lahko zapomnil svoje uporabnisko ime in geslo.</p>";

$langNowGoChooseYourCourses ="Sedaj lahko gres na izbiro zazelenih tecajev iz ustreznega seznama.";
$langNowGoCreateYourCourse  ="Sedaj lahko tvoris svoj tecaj";

$langYourRegTo="Registriran si";
$langIsReg="je bilo azurirano";
$langCanEnter="Sedaj lahko <a href=../../index.php>vstopis v kampus</a>";

// profile.php

$langModifProfile="Spremeni moj profil";
$langPassTwo="Vtipkal si dve razlicni gesli";
$langAgain="Poskusi znova!";
$langFields="Nekatera polja si pustil prazna";
$langUserTaken="To uporabnisko ime ze obstaja";
$langEmailWrong="Elektronski naslov ni popoln ali vsebuje nepravilne znake";
$langProfileReg="Tvoj novi profil je shranjen";
$langHome="Nazaj na domaco stran";
$langMyStats = "Vpogled v mojo statistiko";

// user.php

$langUsers="Uporabniki";
$langModRight="Spremeni administratorske pravice";
$langNone="Nobeden";
$langAll="Vsakdo";
$langNoAdmin="sedaj <b>NIMA administratorskih pravic na teh straneh</b>";
$langAllAdmin="sedaj <b>IMA administratorske pravice na teh straneh</b>";
$langModRole="Spremeni vlogo";
$langRole="Vloga";
$langIsNow="je sedaj";
$langInC="pri tem tecaju";
$langFilled="Nekaj polj si pustil praznih.";
$langUserNo="Izbrano ime uporabnika ";
$langTaken="ze obstaja. Izberi drugo.";
$langOneResp="Eden od administratorjev tecaja";
$langRegYou="vas je registriral za ta tecaj";
$langTheU="Uporabnik";
$langAddedU="je bil dodan. Poslana mu je bila e-posta z imenom uporabnika ";
$langAndP="in njegovim geslom";
$langDereg="je bil zbrisan s tega tecaja";
$langAddAU="Dodaj uporabnika";
$langStudent="student";
$langBegin="zacetek.";
$langPreced50 = "Prejsnjih 50";
$langFollow50 = "Naslednjih 50";
$langEnd = "konec";
$langAdmR="Administratorske pravice";
$langUnreg = "Izpisi";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Spremeni seznam tecajev</big><br><br>
Oznaci tecaje, ki bi jim rad sledil.<br>
Zbrisi kljukice pri tecajih, ki te ne zanimajo vec.<br>Nato klikni gumb Ok na koncu seznama";
$langTitular = "Avtor";
$langCanNotUnsubscribeYourSelf = "Ne mores se izpisati iz tecaja, ki ga upravljas. To lahko naredi le nek drug administrator tecaja.";

$langGroup="Skupina";
$langUserNoneMasc="-";
$langTutor="Ucitelj";
$langTutorDefinition="Ucitelj (sme nadzorovati skupine)";
$langAdminDefinition="Administrator (sme spreminjati vsebine spletnih strani tecaja)";
$langDeleteUserDefinition="Izpis (brisanje iz seznama uporabnikov <b>tega</b> tecaja)";
$langNoTutor = "ni ucitelj tega tecaja";
$langYesTutor = "je ucitelj tega tecaja";
$langUserRights="Pravice uporabnika";
$langNow="sedaj";
$langOneByOne="Uporabnika dodaj rocno";
$langUserMany="Vnos seznama uporabnikov s pomocjo tekstovne datoteke";
$langNo="ne";
$langYes="da";
$langUserAddExplanation="vsaka vrstica datoteke mora imeti natancno 5 polj:
		<b>Priimek&nbsp;&nbsp;&nbsp;Ime&nbsp;&nbsp;&nbsp;
		uporabnisko_ime&nbsp;&nbsp;&nbsp;geslo&nbsp;
		&nbsp;&nbsp;elektronski_naslov</b>, v tem vrstnem redu in locenih s TAB.
		Uporabniki bodo dobili po e-posti potrditev z uporabniskim imenom in geslom.";
$langSend="Poslji";
$langDownloadUserList="Nalozi seznam";
$langUserNumber="stevilo";
$langGiveAdmin="Naredi administratorja";
$langRemoveRight="Odstrani to pravico";
$langGiveTutor="Naredi ucitelja";
$langUserOneByOneExplanation="Po e-posti bo dobil potrditev skupaj z uporabniskim imenom in geslom";
$langBackUser="Povratek na seznam uporabnikov";
$langUserAlreadyRegistered="Uporabnik z enakom priimkom in imenom je ze registriran za ta tecaj.";

$langAddedToCourse="je bil registriran za tvoj tecaj";
$langGroupUserManagement="Upravljanje s skupino";
$langIsReg="Tvoje spremembe so zapisane";
$langPassTooEasy ="to geslo je prevec preprosto. Uporabi geslo, kot na primer ";

$langIfYouWantToAddManyUsers="Ce zelis dodati svojemu tecaju skupino uporabnikov, se povezi z administratorjems.";

$langCourses="tecaji.";

$langLastVisits="Moji zadnji obiski";
$langSee		= "Pojdi&nbsp;na";
$langSubscribe	= "Vpis";
$langCourseName	= "Ime&nbsp;tecaja";
$langLanguage	= "Jezik";

$langConfirmUnsubscribe = "Potrdi izpis uporabnika";
$langAdded = "Dodano";
$langDeleted = "Brisano";
$langPreserved = "Ohranjeno";

$langDate = "Datum";
$langAction = "Akcija";
$langLogin = "Vstop";
$langLogout = "Izstop";
$langModify = "Spreminjanje";

$langUserName = "User name";

$langEdit = "Uredi";
$langCourseManager = "Upravnik tecaja";
$langManage				= "Upravljanje kampusa";
$langAdministrationTools = "Orodja administratorja";
$langModifProfile	= "Uredi profil";
$langUserProfileReg	= "azurirano";



$lang_lost_password = "Pozabljeno geslo";
$lang_enter_email_and_well_send_you_password ="Vnesi elektronski naslov, ki si ga uporabljal pri registraciji in poslali ti bomo tvoje geslo.";
$lang_your_password_has_been_emailed_to_you = "Geslo smo ti poslali po elektronski posti.";
$lang_no_user_account_with_this_email_address = "Noben uporabnik nima tega elektronskega naslova.";
$langCourses4User = "Tecaji tega uporabnika";
$langCoursesByUser = "Tecaji po uporabnikih";

?>
