<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
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

// GENERIC

$langModify="wijzigen";
$langDelete="Verwijderen";
$langTitle="Titel";
$langHelp="help"; //not in english version?
$langOk="OK";
$langAddIntro="INLEIDENDE TEKST TOEVOEGEN";
$langBackList="Terug naar de lijst";
// header
$langMyCourses="Mijn cursussen";
$langModifyProfile="Mijn profiel";
$langLogout="Logout";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";
// end header

// DOCUMENT

$langDoc="Documenten";
$langDownloadFile= "Upload volgend bestand naar de server";
$langDownload="Uploaden";
$langCreateDir="Folder aanmaken";
$langName="Naam";
$langNameDir="Naam van nieuwe folder";
$langSize="Grootte";
$langDate="Datum";
$langMove="Verplaatsen";
$langRename="Naam veranderen";
$langComment="Commentaar";
$langVisible="Zichtbaar/onzichtbaar";
$langCopy="Knippen";
$langTo="naar";
$langNoSpace="Upload is niet geslaagd. Niet genoeg ruimte op de harde schijf.";
$langDownloadEnd="Upload is geslaagd";
$langFileExists="Onmogelijk.<br>Er bestaat al een bestand met dezelfde naam.";
$langIn="in";
$langNewDir="Naam van nieuwe folder";
$langImpossible="Onmogelijk";
$langAddComment="commentaar toevoegen/verwijderen";
$langUp="Hoger niveau";
$langDocCopied="Bestand is gekopieerd";
$langDocDeleted="Folder/Bestand is verwijderd.";
$langElRen="Naam van Folder/bestand is gewijzigd.";
$langElRen="Naam van Folder/bestand is gewijzigd.";
$langDirCr="Folder is aangemaakt";
$langDirMv="Folder/Bestand is verplaatst.";
$langComMod="Commentaar is gewijzigd.";
$langViMod="De zichtbaarheid werd gewijzigd.";

$langFileError="Het te uploaden bestand is niet geldig.";
$langMaxFileSize="Maximum bestandsgrootte is";
$langRoot="basis";

// Special for group documents
$langGroupManagement="Groepsbeheer";
$langGroupSpace="Groepsruimte";
$langGroupSpaceLink="Ruimte van deze groep";
$langGroupForumLink="Forum van deze groep";
$langZipNoPhp="Het ZIP bestand mag geen php-bestanden bevatten";
$langUncompress="ZIP bestand decomprimeren";
$langDownloadAndZipEnd=" ZIP bestand werd opgestuurd en gedecomprimeerd";
$langAreYouSureToDelete = "Wilt u dit document verwijderen: ";

$langPublish = "Publiceren";
$langMissingImagesDetected = "Ontbrekende beelden gedetecteerd";

$langMakeMiniweb = "Maak miniweb";
$langReadMiniweb = "Lees miniweb";
?>