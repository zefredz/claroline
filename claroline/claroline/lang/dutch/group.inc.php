<?php // $Id$
/*
	  +----------------------------------------------------------------------+
	  | CLAROLINE version 1.5.*                             |
	  +----------------------------------------------------------------------+
	  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
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
	  |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
	  +----------------------------------------------------------------------+
 */

/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/
// header
$langMyCourses="Mijn cursussen";
$langModifyProfile="Mijn profiel";
$langLogout="Logout";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";
// end header

$langAll = "ALLE";
$langGroupManagement="Groepen";
$langHelp="help";
$langNewGroupCreate="Nieuwe groep(en) aanmaken";
$langGroupCreation="Aanmaak van een groep";
$langCreate="Aanmaken";
$langNewGroups="nieuwe groepen";
$langMax="Maximum";
$langPlaces="plaatsen per groep (facultatief)";
$langGroupPlacesThis="plaatsen (facultatief)";
$langCreate="aanmaken";
$langDeleteGroups="Alle groepen verwijderen";
$langGroupsAdded="groep(en) werd(en) toegevoegd";
$langGroupsDeleted="Alle groepen werden verwijderd";
$langGroupDel="Groep verwijderd";


$langGroupsEmptied="Alle groepen zijn leeg";
$langEmtpyGroups="Alle groepen leegmaken";
$langGroupsFilled="Alle groepen werden ingevuld";
$langFillGroups="Alle groepen invullen";
$langGroupsProperties="Groepseigenschappen";
$langStudentRegAllowed="De gebruikers <b>mogen</b> zichzelf inschrijven";
$langStudentRegNotAllowed="De gebruikers <b>mogen</b> zichzelf <b>niet</b> inschrijven";
$langPrivateAccess="Het groepsforum is <b>privé</b>";
$langNoPrivateAccess="Het groepsforum is <b>publiek</b>";
$langTools="Functies";
$langForums="Forum";
$langDocuments="Documenten";
$langModify="wijzigen";
$langGroup="Groep";
$langExistingGroups="Groepen";
$langRegistered="Ingeschreven";
$langEdit="Wijzigen";
$langDelete="Verwijderen";



// Group Properties
$langGroupProperties="Groepseigenschappen";
$langGroupAllowStudentRegistration="De gebruikers mogen zichzelf in de groepen inschrijven";
$langGroupAllowStudentUnregistration="De gebruikers mogen zichzelf uitschrijven uit groepen.";
$langGroupPrivatise="Privé groepsforum (toegang voorbehouden aan de deelnemers van de groep).";
$langGroupTools="Functies";
$langGroupForum="Forum";
$langGroupDocument="Documenten";
$langValidate="OK";
$langGroupPropertiesModified="De groepseigenschappen werden gewijzigd";


// Group space

$langGroupSpace="Groepsruimte";
$langGroupThisSpace="Ruimte van deze groep";
$langGroupName="Naam van de groep";
$langGroupDescription="Beschrijving";
$langGroupMembers="deelnemers";
$langSubscribed ="gebruiker(s) ingeschreven voor de cursus";
$langAdminsOfThisCours ="cursusbeheerder(s)";
$langEditGroup="Deze groep wijzigen";
$langUncompulsory="(facultatief)";
$langNoGroupStudents="Gebruiker(s) zonder groep";
$langGroupMembers="Deelnemer(s) van deze groep";
$langGroupValidate="Ok";
$langGroupCancel="verlaten";
$langGroupSettingsModified="Parameters van deze groep zijn gewijzigd.";
$langGroupStudentsInGroup="gebruiker(s) in de groepen ingeschreven";
$langGroupStudentsRegistered="gebruiker(s) voor de cursus ingeschreven";
$langGroupNoGroup="gebruiker(s) zonder groep";
$langGroupUsersList="zie lijst van <a href=../user/user.php>deelnemers</a>";
$langGroupTooMuchMembers="Het aantal deelnemers overschrijdt het maximum aantal dat U bepaald had. 
	De samenstelling van de groep werd niet gewijzigd. U mag het maximum aantal hieronder wijzigen";
$langGroupTutor="Lesgever";
$langGroupNoTutor="(geen)";
$langGroupNone="(geen)";
$langGroupNoneMasc="(geen)";
$langGroupUManagement="Beheer van deelnemers";
$langAddTutors="Lijst van de lesgevers beheren";

$langForumGroup="Groepsforum";
$langMyGroup="mijn groep";
$langOneMyGroups="één van mijn groepen";
$langGroupSelfRegistration="Inschrijven";
$langGroupSelfRegInf="zich inschrijven";
$langRegIntoGroup="Inschrijven in deze groep";
$langGroupNowMember="U bent nu deelnemer van deze groep.";
$langYes="ja";
$langNo="nee";


$langPrivate="privé";
$langPublic="publiek";
$langForumType="Type forum";
$langPropModify="Eigenschappen wijzigen";
$langState="Status";


$langGroupFilledGroups="De groepen werden aangevuld met de gebruikers uit de lijst 'Gebruikers'.";

$langStudentsNotInThisGroups = "Deelnemer(s) niet in deze groep";

$langQtyOfUserCanSubscribe_PartBeforeNumber = "Een gebruiker kan slechts deelnemer zijn van maximum ";
$langQtyOfUserCanSubscribe_PartAfterNumber = "&nbsp;groepen";

$langNoLimitForQtyOfUserCanSubscribe ="Een gebruiker kan deelnemer zijn van alle groepen";
$langGroupDocumentAlwaysPrivate = "zijn altijd privé.";

$langOneGroupPerUser		= "één groep";
$langAllGroups				= "alle groepen";
$langLimitNbGroupPerUser	= "maximum aantal groepen :";
$langGroupLimit				= "Begrenzing";
$langUserCanBeMemberOf		= "Een gebruiker kan deelnemer zijn van";
$langChooseAValue			= "Geef een waarde";

//student edit section
$langStudentUnsubscribe = "Uitschrijven uit de groep";
$langStudentDeletesHimself = "U bent uitgeschreven uit de groep. Terug naar";
$langStudentTriesDeleteOtherStudent = "Je kan geen andere studenten uit groepen uitschrijven.";
?>