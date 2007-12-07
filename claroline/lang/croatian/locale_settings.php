<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.* $Revision: 
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   English Translation                                                |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

$iso639_1_code = "en";
$iso639_2_code = "eng";

unset($langNameOfLang);
unset($langDay_of_weekNames);
unset($langMonthNames);
unset($byteUnits);

$langNameOfLang['arabic'		]="arapski";
$langNameOfLang['brazilian'		]="brazilski";
$langNameOfLang['bulgarian'		]="bugarski";
$langNameOfLang['croatian'		]="hrvatski";
$langNameOfLang['dutch'			]="nizozemski";
$langNameOfLang['english'		]="engleski";
$langNameOfLang['finnish'		]="finski";
$langNameOfLang['french'		]="francuski";
$langNameOfLang['german'		]="njemaèli";
$langNameOfLang['greek'			]="grèki";
$langNameOfLang['italian'		]="talijanski";
$langNameOfLang['japanese'		]="japanski";
$langNameOfLang['polish'		]="poljski";
$langNameOfLang['simpl_chinese'	]="pojednostavljeni kineski";
$langNameOfLang['spanish'		]="španjolski";
$langNameOfLang['swedish'		]="švedski";
$langNameOfLang['thai'			]="thai";
$langNameOfLang['turkish'		]="turski";

$charset = 'utf-8';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('N', 'P', 'U', 'S', 'È', 'P', 'S');
$langDay_of_weekNames['short'] = array('Ned', 'Pon', 'Ut', 'Sri', 'Èet', 'Pet', 'Sub');
$langDay_of_weekNames['long'] = array('Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Èetvrtak', 'Petak', 'Subota');

$langMonthNames['init']  = array('S', 'V', 'O', 'T', 'S', 'L', 'S', 'K', 'R', 'L', 'S', 'P');
$langMonthNames['short'] = array('Sij', 'Velj', 'Ožu', 'Tra', 'Svi', 'Lip', 'Srp', 'Kol', 'Ruj', 'Lis', 'Stu', 'Pro');
$langMonthNames['long'] = array('Sijeèanj', 'Veljaèa', 'Ožujak', 'Travanj', 'Svibanj', 'Lipanj', 'Srpanj', 'Kolovoz', 'Rujan', 'Listopad', 'Studeni', 'Prosinac');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%d %b , %y";
$dateFormatLong  = '%A %d %B, %Y';
$dateTimeFormatLong  = '%d %B, %Y u %H:%M';
$dateTimeFormatShort = "%d-%m-%y %H:%M";
$timeNoSecFormat = '%H:%M';

?>
