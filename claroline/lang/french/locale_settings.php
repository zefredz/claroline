<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.0 $Revision$
      +----------------------------------------------------------------------
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or
      |   modify it under the terms of the GNU General Public License
      |   as published by the Free Software Foundation; either version 2
      |   of the License, or (at your option) any later version.
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>
      +----------------------------------------------------------------------+
 */

$englishLangName = "french";
$localLangName = "français";

$iso639_1_code = "fr";
$iso639_2_code = "fre";

$langNameOfLang['arabic'		] = "arabe";
$langNameOfLang['brazilian'		] = "brésilien";
$langNameOfLang['croatian'		] = "croate";
$langNameOfLang['catalan'		] = "catalan";
$langNameOfLang['dutch'			] = "néerlandais";
$langNameOfLang['english'		] = "anglais";
$langNameOfLang['finnish'		] = "finlandais";
$langNameOfLang['french'		] = "français";
$langNameOfLang['german'		] = "allemand";
$langNameOfLang['greek'			] = "grec";
$langNameOfLang['italian'		] = "italien";
$langNameOfLang['japanese'		] = "japonais"; // JCC 
$langNameOfLang['polish'		] = "polonais";
$langNameOfLang['simpl_chinese'	] ="chinois simple";
$langNameOfLang['spanish'		] = "espagnol";
$langNameOfLang['swedish'		] = "suédois";
$langNameOfLang['thai'			] = "thaïlandais";
$langNameOfLang['turkish'		] = "turc";

$charset = 'iso-8859-1';
$text_dir = 'ltr';
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ' ';
$number_decimal_separator = ',';
$byteUnits = array('Octets', 'Ko', 'Mo', 'Go');

$langDay_of_weekNames['init'] = array('D', 'L', 'M', 'M', 'J', 'V', 'S');
$langDay_of_weekNames['short'] = array('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'); // JCC 
$langDay_of_weekNames['long'] = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc');
$langMonthNames['long'] = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%a %d %b %y";
$dateFormatLong  = '%A %d %B %Y';
$dateTimeFormatLong  = '%A %d %B %Y à %H:%M';
$dateTimeFormatShort = "%d/%m/%y %H:%M";
$timeNoSecFormat = '%H:%M';

?>
