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

echo "<p>dutch/home_page.inc included.</p>";

// header
$langMyCourses="Mijn cursussen";
$langModifyProfile="Mijn profiel";
$langLogout="Logout";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";
// end header

// GENERIC

$langModify="Wijzigen";
$langDelete="Verwijderen";
$langTitle="Titel";
$langHelp="Help";
$langOk="OK";
$langAddIntro="INLEIDENDE TEKST TOEVOEGEN";
$langBackList="Terug naar de lijst";





// index.php CAMPUS HOME PAGE

$langInvalidId="Deze identificatie is niet geldig. Indien U nog niet ingeschreven bent, gelieve het <a href='claroline/auth/inscription.php'>inschrijvingsformulier</a> in te vullen.";
$langMyCourses="Mijn cursussen";
$langCourseCreate="Cursussite aanmaken";
$langModifyProfile="Mijn profiel";
$langTodo="Todo";
$langWelcome="cursussite(s) hieronder hebben vrije toegang. De andere cursussen vragen een gebruikersnaam en een wachtwoord. Die kunt u krijgen door een klik op 'Registratie'. Het is mogelijk voor de docenten en assistenten om een nieuwe cursus te creëren door een klik op 'Registratie'."; 
$langUserName="Gebruikersnaam";
$langPass="Wachtwoord";
$langEnter="Enter";
$langHelp="Help";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";

?>