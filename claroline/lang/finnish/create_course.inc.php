<?php

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
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
      +----------------------------------------------------------------------+
*/


/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/

// GENERIC

$langModify="Muokkaa";
$langDelete="Poista";
$langTitle="Otsikko";
$langHelp="Apua";
$langOk="Ok";
$langAddIntro="Lisää johdanto";
$langBackList="Takaisin listaan";

// create_course.php

$langLn="Language";

$langCreateSite="Luo kurssin sivusto";
$langFieldsRequ="Täytä kaikki kentät";
$langTitle="Kurssin otsikko";
$langEx="Esimerkki: <i>Kirjallisuuden historia</i>";
$langFac="Koulutusala";
$langTargetFac="Kurssista vastaava koulutusala: ";
$langCode="Kurssin koodi";
$langMax="Korkeintaan 12 merkkiä, esimerkiksi <i>Luokka2121</i>";
$langDoubt="Jos et tiedä kurssin koodia, kysy: ";
$langProgram="Kurssin ohjelma</a>. Jos kurssilla ei ole koodia, keksi sellainen, esimerkiksi jos kurssisi käsittelee keksintöjä, niin anna kurssin koodiksi <i>Keksinnöt</i>";
$langProfessors="Opettaja(t)";
$langExplanation="Kun klikkaat Ok, niin sivustolle luodaan foorumi, esityslista, dokumenttien hallinta yms. Voit muuttaa sivustoa omalla tunnuksellasi.";
$langEmpty="Jätit joitain kenttiä tyhjäksi.<br>Mene takaisin ja yritä uudetaan.<br>Jos et huomannut kurssin koodia, katso kurssin ohjelmasta.";
$langCodeTaken="Tämä kurssi on jo käytössä.<br>Mene takaisin ja yritä uudetaan.";

// tables MySQL

$langFormula="Sinun opettajasi: ";
$langForumLanguage="english";	// other possibilities are english, spanish (this uses phpbb language functions)
$langTestForum="testifoorumi";
$langDelAdmin="Poista tämä foorumi hallintatyökalujen avulla.";
$langMessage="Kun poistat testifoorumin, poistat myös kaikki viestit.";
$langExMessage="Testiviesti";
$langAnonymous="Tuntematon";
$langExerciceEx="Testitehtävä";
$langAntique="Antiikin filosofian hitoria";
$langSocraticIrony="Sokraattinen ironia on...";
$langManyAnswers="(Vastauksia voi olla useampi)";
$langRidiculise="Naurualainen tapa vaikuttaa puhetoveriin, jotta tämä myöntäisi olevansa väärässä.";
$langNoPsychology="Ei. Sokraattinen ironia ei liity psykologiaan, se käsittelee argumentaatiota.";
$langAdmitError="Myöntää virheensä, jotta puhetoveri tekisi samoin.";
$langNoSeduction="Ei. Sokraattinen ironia ei ole houkutukseen perustuva startegia tai esimerkkiin perustuva menetelmä.";
$langForce="Pakottaa puhekumppani kysymyksillä ja lisäkysymyksillä myöntämään, että hän ei tiedä, mitä hän väittää tietävänsä.";
$langIndeed="Todellakin. Sokraattinen ironia on kyselyyn perustuva menetelmä. Kreikan sana \"eirotao\" merkitsee \"tehdä kysymys\".";
$langContradiction="Käyttämällä myöntämisen periaatetta pakotetaan puhekumppani umpikujaan.";
$langNotFalse="Tämä vastaus ei ole väärin. On totta, että puhekumppanin tietämättömyys paljastaminen tarkoittaa kieltävien johtopäätelmien näyttämistä, jotka johtuvat hänen lähtökohdistaan.";

// Home Page MySQL Table "accueil"

$langAgenda="Esityslista";
$langLinks="Linkit";
$langDoc="Dokumentit";
$langVideo="Video";
$langWorks="Opiskelijoiden paperit";
$langCourseProgram="Kurssin ohjelma";
$langAnnouncements="Ilmoitukset";
$langUsers="Käyttäjät";
$langForums="Foorumit";
$langExercices="harjoitukset";
$langStatistics="Statistiikka";
$langAddPageHome="Lisää sivu ja linkki kotisivulle";
$langLinkSite="Lisää linkki kotisivulle";
$langModifyInfo="Muokka kurssin infoa";

// Other SQL tables

$langAgendaTitle="Tiistai 11. joulukuuta - Ensimmäineen oppi: Newton 18";
$langAgendaText="Yleinen johdanto filosofian ja metodologian perusteisiin";
$langMillikan="Millikan koe";
$langVideoText="Tämä on esimerkki RealVideo-tiedostosta. Vit lisätä mitä tahansa tiedostoja (.mov, .rm, .mpeg...), kunhan oppilailla on mahdollisuus on avata ne sopivalla ohjelmalla.";
$langGoogle="Nopea ja tehokas haukone.";
$langIntroductionText="Tämä on kurssin johdanto. vaihda tähän oma tekstisi, klikkaa alla <b>Muokkaa</b>.";
$langIntroductionTwo="Tämä sivu mahdollistaa kenen tahansa oppilaan tai ryhmän lisätä dokumentin kurssin sivustolle. Voit käyttää HTML-sivua kunhan siinä ei ole kuvia.";
$langCourseDescription="Kirjoita tähän kuvaus kurssista, se näkyy kurssien listassa.";
$langProfessor="Opettaja";
$langAnnouncementEx="Tämä on esimerkki ilmoituksesta. Vain opettajat ja ylläpitäjät voivat julaista ilmoituksia.";
$langJustCreated="Loit juuri kurssin sivuston.";
$langEnter="Sisään.";

?>