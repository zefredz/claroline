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

$cidReset=true;
$gidReset=true;
require '../inc/claro_init_global.inc.php';

$interbredcrump[]   = array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
/*--------------------------------------------------------------------
               LIST OF COUNTRY ISO CODES AND COUNTRY NAMES
  --------------------------------------------------------------------*/
$isoCode = array();

$isoCode['Z1'] = "Other";
$isoCode['AD'] = "Andorra";
$isoCode['AE'] = "United Arab Emirates";
$isoCode['AF'] = "Afghanistan";
$isoCode['AG'] = "Antigua and Barbuda";
$isoCode['AI'] = "Anguilla";
$isoCode['AL'] = "Albania";
$isoCode['AM'] = "Armenia";
$isoCode['AN'] = "Netherlands Antilles";
$isoCode['AO'] = "Angola";
$isoCode['AP'] = "Asia/Pacific Region";
$isoCode['AQ'] = "Antarctica";
$isoCode['AR'] = "Argentina";
$isoCode['AS'] = "American Samoa";
$isoCode['AT'] = "Austria";
$isoCode['AU'] = "Australia";
$isoCode['AW'] = "Aruba";
$isoCode['AZ'] = "Azerbaijan";
$isoCode['BA'] = "Bosnia and Herzegovina";
$isoCode['BB'] = "Barbados";
$isoCode['BD'] = "Bangladesh";
$isoCode['BE'] = "Belgium";
$isoCode['BF'] = "Burkina Faso";
$isoCode['BG'] = "Bulgaria";
$isoCode['BH'] = "Bahrain";
$isoCode['BI'] = "Burundi";
$isoCode['BJ'] = "Benin";
$isoCode['BM'] = "Bermuda";
$isoCode['BN'] = "Brunei Darussalam";
$isoCode['BO'] = "Bolivia";
$isoCode['BR'] = "Brazil";
$isoCode['BS'] = "Bahamas";
$isoCode['BT'] = "Bhutan";
$isoCode['BV'] = "Bouvet Island";
$isoCode['BW'] = "Botswana";
$isoCode['BY'] = "Belarus";
$isoCode['BZ'] = "Belize";
$isoCode['CA'] = "Canada";
$isoCode['CC'] = "Cocos (Keeling) Islands";
$isoCode['CD'] = "Congo, The Democratic Republic of the";
$isoCode['CF'] = "Central African Republic";
$isoCode['CG'] = "Congo";
$isoCode['CH'] = "Switzerland";
$isoCode['CI'] = "Cote D'Ivoire";
$isoCode['CK'] = "Cook Islands";
$isoCode['CL'] = "Chile";
$isoCode['CM'] = "Cameroon";
$isoCode['CN'] = "China";
$isoCode['CO'] = "Colombia";
$isoCode['CR'] = "Costa Rica";
$isoCode['CU'] = "Cuba";
$isoCode['CV'] = "Cape Verde";
$isoCode['CX'] = "Christmas Island";
$isoCode['CY'] = "Cyprus";
$isoCode['CZ'] = "Czech Republic";
$isoCode['DE'] = "Germany";
$isoCode['DJ'] = "Djibouti";
$isoCode['DK'] = "Denmark";
$isoCode['DM'] = "Dominica";
$isoCode['DO'] = "Dominican Republic";
$isoCode['DZ'] = "Algeria";
$isoCode['EC'] = "Ecuador";
$isoCode['EE'] = "Estonia";
$isoCode['EG'] = "Egypt";
$isoCode['EH'] = "Western Sahara";
$isoCode['ER'] = "Eritrea";
$isoCode['ES'] = "Spain";
$isoCode['ET'] = "Ethiopia";
$isoCode['EU'] = "Europe";
$isoCode['FI'] = "Finland";
$isoCode['FJ'] = "Fiji";
$isoCode['FK'] = "Falkland Islands (Malvinas)";
$isoCode['FM'] = "Micronesia, Federated States of";
$isoCode['FO'] = "Faroe Islands";
$isoCode['FR'] = "France";
$isoCode['FX'] = "France, Metropolitan";
$isoCode['GA'] = "Gabon";
$isoCode['GD'] = "Grenada";
$isoCode['GE'] = "Georgia";
$isoCode['GF'] = "French Guiana";
$isoCode['GH'] = "Ghana";
$isoCode['GI'] = "Gibraltar";
$isoCode['GL'] = "Greenland";
$isoCode['GM'] = "Gambia";
$isoCode['GN'] = "Guinea";
$isoCode['GP'] = "Guadeloupe";
$isoCode['GQ'] = "Equatorial Guinea";
$isoCode['GR'] = "Greece";
$isoCode['GS'] = "South Georgia and the South Sandwich Islands";
$isoCode['GT'] = "Guatemala";
$isoCode['GU'] = "Guam";
$isoCode['GW'] = "Guinea-Bissau";
$isoCode['GY'] = "Guyana";
$isoCode['HK'] = "Hong Kong";
$isoCode['HM'] = "Heard Island and McDonald Islands";
$isoCode['HN'] = "Honduras";
$isoCode['HR'] = "Croatia";
$isoCode['HT'] = "Haiti";
$isoCode['HU'] = "Hungary";
$isoCode['ID'] = "Indonesia";
$isoCode['IE'] = "Ireland";
$isoCode['IL'] = "Israel";
$isoCode['IN'] = "India";
$isoCode['IO'] = "British Indian Ocean Territory";
$isoCode['IQ'] = "Iraq";
$isoCode['IR'] = "Iran, Islamic Republic of";
$isoCode['IS'] = "Iceland";
$isoCode['IT'] = "Italy";
$isoCode['JM'] = "Jamaica";
$isoCode['JO'] = "Jordan";
$isoCode['JP'] = "Japan";
$isoCode['KE'] = "Kenya";
$isoCode['KG'] = "Kyrgyzstan";
$isoCode['KH'] = "Cambodia";
$isoCode['KI'] = "Kiribati";
$isoCode['KM'] = "Comoros";
$isoCode['KN'] = "Saint Kitts and Nevis";
$isoCode['KP'] = "Korea, Democratic People's Republic of";
$isoCode['KR'] = "Korea, Republic of";
$isoCode['KW'] = "Kuwait";
$isoCode['KY'] = "Cayman Islands";
$isoCode['KZ'] = "Kazakhstan";
$isoCode['LA'] = "Lao People's Democratic Republic";
$isoCode['LB'] = "Lebanon";
$isoCode['LC'] = "Saint Lucia";
$isoCode['LI'] = "Liechtenstein";
$isoCode['LK'] = "Sri Lanka";
$isoCode['LR'] = "Liberia";
$isoCode['LS'] = "Lesotho";
$isoCode['LT'] = "Lithuania";
$isoCode['LU'] = "Luxembourg";
$isoCode['LV'] = "Latvia";
$isoCode['LY'] = "Libyan Arab Jamahiriya";
$isoCode['MA'] = "Morocco";
$isoCode['MC'] = "Monaco";
$isoCode['MD'] = "Moldova, Republic of";
$isoCode['MG'] = "Madagascar";
$isoCode['MH'] = "Marshall Islands";
$isoCode['MK'] = "Macedonia";
$isoCode['ML'] = "Mali";
$isoCode['MM'] = "Myanmar";
$isoCode['MN'] = "Mongolia";
$isoCode['MO'] = "Macau";
$isoCode['MP'] = "Northern Mariana Islands";
$isoCode['MQ'] = "Martinique";
$isoCode['MR'] = "Mauritania";
$isoCode['MS'] = "Montserrat";
$isoCode['MT'] = "Malta";
$isoCode['MU'] = "Mauritius";
$isoCode['MV'] = "Maldives";
$isoCode['MW'] = "Malawi";
$isoCode['MX'] = "Mexico";
$isoCode['MY'] = "Malaysia";
$isoCode['MZ'] = "Mozambique";
$isoCode['NA'] = "Namibia";
$isoCode['NC'] = "New Caledonia";
$isoCode['NE'] = "Niger";
$isoCode['NF'] = "Norfolk Island";
$isoCode['NG'] = "Nigeria";
$isoCode['NI'] = "Nicaragua";
$isoCode['NL'] = "Netherlands";
$isoCode['NO'] = "Norway";
$isoCode['NP'] = "Nepal";
$isoCode['NR'] = "Nauru";
$isoCode['NU'] = "Niue";
$isoCode['NZ'] = "New Zealand";
$isoCode['OM'] = "Oman";
$isoCode['PA'] = "Panama";
$isoCode['PE'] = "Peru";
$isoCode['PF'] = "French Polynesia";
$isoCode['PG'] = "Papua New Guinea";
$isoCode['PH'] = "Philippines";
$isoCode['PK'] = "Pakistan";
$isoCode['PL'] = "Poland";
$isoCode['PM'] = "Saint Pierre and Miquelon";
$isoCode['PN'] = "Pitcairn";
$isoCode['PR'] = "Puerto Rico";
$isoCode['PS'] = "Palestinian Territory";
$isoCode['PT'] = "Portugal";
$isoCode['PW'] = "Palau";
$isoCode['PY'] = "Paraguay";
$isoCode['QA'] = "Qatar";
$isoCode['RE'] = "Reunion";
$isoCode['RO'] = "Romania";
$isoCode['RU'] = "Russian Federation";
$isoCode['RW'] = "Rwanda";
$isoCode['SA'] = "Saudi Arabia";
$isoCode['SB'] = "Solomon Islands";
$isoCode['SC'] = "Seychelles";
$isoCode['SD'] = "Sudan";
$isoCode['SE'] = "Sweden";
$isoCode['SG'] = "Singapore";
$isoCode['SH'] = "Saint Helena";
$isoCode['SI'] = "Slovenia";
$isoCode['SJ'] = "Svalbard and Jan Mayen";
$isoCode['SK'] = "Slovakia";
$isoCode['SL'] = "Sierra Leone";
$isoCode['SM'] = "San Marino";
$isoCode['SN'] = "Senegal";
$isoCode['SO'] = "Somalia";
$isoCode['SR'] = "Suriname";
$isoCode['ST'] = "Sao Tome and Principe";
$isoCode['SV'] = "El Salvador";
$isoCode['SY'] = "Syrian Arab Republic";
$isoCode['SZ'] = "Swaziland";
$isoCode['TC'] = "Turks and Caicos Islands";
$isoCode['TD'] = "Chad";
$isoCode['TF'] = "French Southern Territories";
$isoCode['TG'] = "Togo";
$isoCode['TH'] = "Thailand";
$isoCode['TJ'] = "Tajikistan";
$isoCode['TK'] = "Tokelau";
$isoCode['TL'] = "East Timor";
$isoCode['TM'] = "Turkmenistan";
$isoCode['TN'] = "Tunisia";
$isoCode['TO'] = "Tonga";
$isoCode['TR'] = "Turkey";
$isoCode['TT'] = "Trinidad and Tobago";
$isoCode['TV'] = "Tuvalu";
$isoCode['TW'] = "Taiwan, Province of China";
$isoCode['TZ'] = "Tanzania, United Republic of";
$isoCode['UA'] = "Ukraine";
$isoCode['UG'] = "Uganda";
$isoCode['UK'] = "United Kingdom";
$isoCode['UM'] = "United States Minor Outlying Islands";
$isoCode['US'] = "United States";
$isoCode['UY'] = "Uruguay";
$isoCode['UZ'] = "Uzbekistan";
$isoCode['VA'] = "Holy See (Vatican City State)";
$isoCode['VC'] = "Saint Vincent and the Grenadines";
$isoCode['VE'] = "Venezuela";
$isoCode['VG'] = "Virgin Islands, British";
$isoCode['VI'] = "Virgin Islands, U.S.";
$isoCode['VN'] = "Vietnam";
$isoCode['VU'] = "Vanuatu";
$isoCode['WF'] = "Wallis and Futuna";
$isoCode['WS'] = "Samoa";
$isoCode['YE'] = "Yemen";
$isoCode['YT'] = "Mayotte";
$isoCode['YU'] = "Yugoslavia";
$isoCode['ZA'] = "South Africa";
$isoCode['ZM'] = "Zambia";
$isoCode['ZR'] = "Zaire";
$isoCode['ZW'] = "Zimbabwe";

/*---------------------------------------------------------------------*/
  
if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
require_once($includePath.'/lib/nusoap.php');

//SECURITY CHECK
$is_allowedToAdmin     = $is_platformAdmin;
if (!$is_allowedToAdmin) claro_disp_auth_form();


// status codes
// keep in mind that these code must be the same than those in the 
// soap server file that is on claroline.net
define("CAMPUS_ADDED", 1);
define("LOCAL_URL_ERROR", 2);
define("CAMPUS_ALREADY_IN_LIST", 3);
define("SQL_ERROR", 4);
define("COUNTRY_CODE_ERROR", 5);

/*============================================================================
						INIT SOAP CLIENT
  ============================================================================*/
$soapclient = new soapclient('http://www.claroline.net/worldwide/worldwide_soap.php');

/*============================================================================
						COMMANDS
  ============================================================================*/
  
// -- register campus
if( isset($_REQUEST['register']) )
{
	$country = ( isset($_REQUEST['country']) ) ? $_REQUEST['country']: '' ;
	$parameters = array('campusName' => $siteName, 'campusUrl' => $rootWeb,
						'institutionName' => $institution_name, 'institutionUrl' => $institution_url,
						'country' => $country, 'adminEmail' => $administrator_email
						);

	// make the soap call to register the campus
	$soapResponse = $soapclient->call('registerCampus', $parameters);

	if( $soapResponse == CAMPUS_ADDED )
	{
		$dialogBox = $langCampusRegistrationSubmitted;
	}
	elseif( $soapResponse == LOCAL_URL_ERROR )
	{
	    $dialogBox = $langRegisterLocalUrl;
	}
	elseif( $soapResponse == CAMPUS_ALREADY_IN_LIST )
	{
		$dialogBox = $langCampusAlreadyRegistered;
	}
	elseif( $soapResponse == COUNTRY_CODE_ERROR )
	{
		$dialogBox = $langCountryCodeError;
	}
	else
	{
		$dialogBox = $langUnkownSOAPError;
	}
}

// -- get current status
if( !isset($_REQUEST['register']) )
{
	$parameters = array('campusUrl' => $rootWeb);
	$soapResponse = $soapclient->call('getCampusRegistrationStatus', $parameters);

	if( $soapResponse )
	{
	    $dialogBox = $langCurrentStatus."<br />\n";

		switch($soapResponse)
		{
		    case 'SUBMITTED' :
				$dialogBox .= $langCampusSubmitted;
				break;
		    case 'REGISTERED' :
				$dialogBox .= $langCampusRegistered;
				break;
		    case 'UNREGISTERED' :
				$dialogBox .= $langCampusRemoved;
				break;
		    case 'HIDDEN' :
				$dialogBox .= $langCampusDeleted;
				break;
			default :
				// unknown status ?
				break;
		}
		$alreadyRegistered = TRUE;
	}
	// else : there is no current status or an erroe occurred so don't show current status
}

/*============================================================================
						DISPLAY
  ============================================================================*/
$nameTools = $langRegisterMyCampus;
// bread crumb à ajouter

include($includePath."/claro_init_header.inc.php");

$title['mainTitle'] = $nameTools;
$title['subTitle'] = $langAddMyCampusOnClarolineNet;
claro_disp_tool_title($title);

if( isset($dialogBox) && $dialogBox != '' ) claro_disp_message_box($dialogBox);

if( !isset($_REQUEST['register']) && ! ( isset($alreadyRegistered) && $alreadyRegistered ) )
{
	echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n"
	    ."<ul>\n"
		."<li>".$langSiteName." : ".stripslashes($siteName)."</li>\n"
		."<li>".$langURL."<a href=\"".$rootWeb."\">".$rootWeb."</a></li>\n"
		."<li>".$langInstitution." : ".stripslashes($institution_name)."</li>\n"
		."<li>".$langInstitutionUrl." : <a href=\"".$institution_url."\">".$institution_url."</a></li>\n"
		."<li>".$langEmail." : ".$administrator_email."</li>"
		."<li>"
		."<label for=\"country\">".$langCountry." : </label>\n"
		."<select name=\"country\" id=\"country\" />\n";

	$optionString = "";
	foreach( $isoCode as $code => $country)	
	{
		$optionString .= "<option value=\"".$code."\">".$country."</option>\n";
	}
	
	echo $optionString
		."</select>"
		."</li>\n"
	    ."</ul>\n"
	    ."<br />\n"
		."<input type=\"submit\" name=\"register\" value=\"".$langRegisterMyCampus."\" />"
		."</form>\n";
}

include($includePath."/claro_init_footer.inc.php");


?>
