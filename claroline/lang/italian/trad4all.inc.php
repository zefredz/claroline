<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$        |
      |   Italian translation                                                |
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
      | Translator:                                                          |
      +----------------------------------------------------------------------+
 */

/* Original was : Pietro Danesi <danone@aruba.it>  07.09.2001 init version  in PHPMyAdmin */

$englishLangName = "italian";
$localLangName = "italiano";

$iso639_2_code = "it";
$iso639_1_code = "ita";

$langNameOfLang[arabic]="arabo";
$langNameOfLang[brazilian]="brasiliano";
$langNameOfLang[english]="inglese";
$langNameOfLang[finnish]="finlandese";
$langNameOfLang[french]="francese";
$langNameOfLang[german]="tedesco";
$langNameOfLang[italian]="italiano";
$langNameOfLang[japanese]="giapponese";
$langNameOfLang[polish]="polacco";
$langNameOfLang[simpl_chinese]="cinese semplificato";
$langNameOfLang[spanish]="spagnolo";
$langNameOfLang[swedish]="svedese";
$langNameOfLang[thai]="tailandese";
$langNameOfLang[turkish]="turco";

$charset = 'iso-8859-1';
$text_dir = 'ltr';
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = '.';
$number_decimal_separator = ',';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('D', 'L', 'M', 'M', 'G', 'V', 'S'); //italian days
$langDay_of_weekNames['short'] = array('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'); //italian days
$langDay_of_weekNames['long'] = array('Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'); //italian days
$day_of_weekNames = $langDay_of_weekNames;

$langMonthNames['init'] = array('G', 'F', 'M', 'A', 'M', 'G', 'L', 'A', 'S', 'O', 'N', 'D'); //italian months
$langMonthNames['short'] = array('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'); //italian months
$langMonthNames['long'] = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'); //italian months
$monthNames = $langMonthNames;
// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous


$dateFormatShort =  "%a %d %b %y";
$dateFormatLong  = '%A %d %B %Y';
$dateTimeFormatLong  = '%A %d %B %Y ore %H:%M';
$timeNoSecFormat = '%H:%M';


// GENERIC

$langBack="Indietro";
$langNext="Avanti";
$langBackHome="Ritorna all'inizio";
$langPropositions="Proponi un miglioramento";
$langMaj="Aggiorna";
$langModify="Modifica";
$langDelete="Elimina";
$langTitle="Titolo";
$langHelp="Aiuto";
$langOk="Conferma";
$langAddIntro="Aggiungi un testo di introduzione";
$langBackList="Ritorna all'elenco";

?>