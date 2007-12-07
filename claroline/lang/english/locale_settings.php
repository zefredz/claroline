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

$langNameOfLang['arabic'		]="arabian";
$langNameOfLang['brazilian'		]="brazilian";
$langNameOfLang['bulgarian'		]="bulgarian";
$langNameOfLang['croatian'		]="croatian";
$langNameOfLang['dutch'			]="dutch";
$langNameOfLang['english'		]="english";
$langNameOfLang['finnish'		]="finnish";
$langNameOfLang['french'		]="french";
$langNameOfLang['german'		]="german";
$langNameOfLang['greek'			]="greek";
$langNameOfLang['italian'		]="italian";
$langNameOfLang['japanese'		]="japanese";
$langNameOfLang['polish'		]="polish";
$langNameOfLang['simpl_chinese'	]="simplified chinese";
$langNameOfLang['spanish'		]="spanish";
$langNameOfLang['swedish'		]="swedish";
$langNameOfLang['thai'			]="thai";
$langNameOfLang['turkish'		]="turkish";

$charset = 'iso-8859-1';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$langDay_of_weekNames['short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
$langDay_of_weekNames['long'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$langMonthNames['long'] = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$dateTimeFormatShort = "%d-%m-%y %I:%M %p";
$timeNoSecFormat = '%I:%M %p';

?>
