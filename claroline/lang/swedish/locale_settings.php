<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$englishLangName = "Swedish";

$iso639_1_code = "sv";
$iso639_2_code = "swe";

$langNameOfLang['arabic'		]="arabiska";
$langNameOfLang['brazilian'		]="brasilianska";
$langNameOfLang['bulgarian'		]="bulgarianska";
$langNameOfLang['croatian'		]="croatianska";
$langNameOfLang['dutch'			]="dutchska";
$langNameOfLang['english'		]="engelska";
$langNameOfLang['finnish'		]="finska";
$langNameOfLang['french'		]="franska";
$langNameOfLang['german'		]="tyska";
$langNameOfLang['italian'		]="italienska";
$langNameOfLang['japanese'		]="japanska";
$langNameOfLang['polish'		]="polska";
$langNameOfLang['simpl_chinese'	]="enkel kinesiska";
$langNameOfLang['spanish'		]="spanska";
$langNameOfLang['swedish'		]="svenska";
$langNameOfLang['thai'			]="thailändska";
$langNameOfLang['turkish'		]="turkiska";

$charset = 'iso-8859-1';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'O', 'T', 'F', 'L');
$langDay_of_weekNames['short'] = array('Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör');
$langDay_of_weekNames['long'] = array('Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec');
$langMonthNames['long'] = array('Januari', 'Februari', 'Mars', 'April', 'Maj', 'Juni', 'Juli', 'Augusti', 'September', 'Oktober', 'November', 'December');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$timeNoSecFormat = '%I:%M %p';


?>
