<?php // $Id$
/**
 * CLAROLINE
 *
 * language library
 * contains function to manage l10n
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/CLUSR
 * @package CLUSR
 * @author Claro Team <cvs@claroline.net>
 *
 */

define( 'LANG_KEY_DELIMITER', '%' );

/**
 * Get the translation of the string
 *
 * @param $name string name
 * @param $var_to_remplace array with variables to replace in translation
 *
 * @return string translation
 *
 */

function get_lang ($name,$var_to_replace=null)
{
    global $_lang;

    $translation = '';

    if ( isset($_lang[$name]) )
    {
        $translation = $_lang[$name];
    }
    else
    {
        // missing translation
        $translation = $name;
    }

    if ( !empty($var_to_replace) && is_array($var_to_replace) )
    {
        $search = array_keys($var_to_replace);
        array_walk($search,'lang_mk_key_delimiter');
        $replace = array_values($var_to_replace);

        // return translation with replacement
        return str_replace($search,$replace,$translation);
    }
    else
    {
        // return translation
        return $translation;
    }

}

function lang_mk_key_delimiter(&$string)
{
    $string = LANG_KEY_DELIMITER . $string . LANG_KEY_DELIMITER ;
}

/**
 * Get the translation of the block
 *
 * @param $name block name
 * @param $var_to_remplace array with variables to replace
 *
 * @return block translation
 *
 */

function get_block ($name,$var_to_replace=null)
{
    if ( !empty($var_to_replace) && is_array($var_to_replace) )
    {
        return get_lang($name,$var_to_replace);
    }
    else
    {
        return get_lang($name);
    }
}

class language
{
    /**
     * Load the global array ($_lang) with all translations of the language
     *
     * @param string $language language (default : english)
     * @param string $mode     TRANSLATION or PRODUCTION (default : PRODUCTION)
     *
     */

    function load_translation ($language=null,$mode=null)
    {
        global $_lang ;
        global $includePath, $urlAppend ;

        /*----------------------------------------------------------------------
          Initialise language array
          ----------------------------------------------------------------------*/

        $_lang = array();

        if ( is_null($language) ) $language = language::current_language();
        if ( is_null($mode) )     $mode = get_conf('CLAROLANG');

        /*----------------------------------------------------------------------
          Common language properties and generic expressions
          ----------------------------------------------------------------------*/

        if ( $mode == 'TRANSLATION' )
        {
            // TRANSLATION MODE : include the language file with all language variables

            include($includePath . '/../lang/english/complete.lang.php');

            if ($language  != 'english') // Avoid useless include as English lang is preloaded
            {
                include($includePath . '/../lang/' . $language . '/complete.lang.php');
            }

        }
        else
        {
            // PRODUCTION MODE : include the language file with variables used by the script

            /*
             * tool specific language translation
             */

            // build lang file of the tool
            $languageFilename = preg_replace('|^'.preg_quote($urlAppend.'/').'|', '',  $_SERVER['PHP_SELF'] );
            $pos = strpos($languageFilename, 'claroline/');

            if ($pos === FALSE || $pos != 0)
            {
                // if the script isn't in the claroline folder the language file base name is index
                $languageFilename = 'index';
            }
            else
            {
                // else language file basename is like claroline_folder_subfolder_...
                $languageFilename = dirname($languageFilename);
                $languageFilename = str_replace('/','_',$languageFilename);
            }

            // add extension to file
            $languageFile = $languageFilename . '.lang.php';

            if ( ! file_exists($includePath . '/../lang/english/' . $languageFile) )
            {
                include($includePath . '/../lang/english/complete.lang.php');
            }
            else
            {
                include($includePath . '/../lang/english/' . $languageFile);
            }

            // load previously english file to be sure every get_lang('variable')
            // have at least some content

            if ( $language != 'english' )
            {
                @include($includePath . '/../lang/' . $language . '/' . $languageFile);
            }

        }

    }

    function load_locale_settings($language=null)
    {
        global $includePath;

        global $iso639_1_code, $iso639_2_code, $charset,
               $langNameOfLang , $langDay_of_weekNames, $langMonthNames, $byteUnits,
               $text_dir, $left_font_family, $right_font_family,
               $number_thousands_separator, $number_decimal_separator,
               $dateFormatShort, $dateFormatLong, $dateTimeFormatLong, $dateTimeFormatShort, $timeNoSecFormat;

        /*
        * tool specific language translation
        */
        if ( is_null($language) ) $language = language::current_language();

        // include the locale settings language
        include($includePath.'/../lang/english/locale_settings.php');

        if ( $language != 'english' ) // Avoid useless include as English lang is preloaded
        {
            include($includePath.'/../lang/'.$language.'/locale_settings.php');
        }

        $GLOBALS['langNameOfLang'] = $langNameOfLang;
        $GLOBALS['langDay_of_weekNames'] = $langDay_of_weekNames;
        $GLOBALS['langMonthNames'] = $langMonthNames;

    }

    function current_language()
    {
        global $_course, $_user, $platformLanguage, $_cid, $_uid ;

        if ( isset($_cid) && isset($_course['language']) )
        {
            // course language
            return $_course['language'];
        }
        else
        {
            if ( isset($_uid) && isset($_user['language']) )
            {
                // user language
                return $_user['language'];
            }
            else
            {
                if ( isset($_REQUEST['language']) )
                {
                    // selected language
                    $_SESSION['language'] = $_REQUEST['language'];
                    return $_REQUEST['language'];
                }
                else
                {
                    if ( empty($_SESSION['language']) )
                    {
                        // default platform language
                        return $platformLanguage;
                    }
                    else
                    {
                        return $_SESSION['language'];
                    }
                }
            }
        }

    }
}

/**
*   Displays a form (drop down menu) so the user can select his/her preferred language.
*   The form works with or without javascript
*   TODO : need some refactoring there is a lot of function to get platform language
*/

function claro_display_preferred_language_form()
{
    global $langNameOfLang ;

    $platformLanguage = get_conf('platformLanguage');
    $language_list = get_conf('language_to_display');

    $form = '';

    if ( is_array($language_list) && count($language_list) > 1 )
    {
        // get the the current language
        $user_language = language::current_language();

        // build language selector form
        $form .= '<form action="'.$_SERVER['PHP_SELF'].'" name="language_selector" method="post" style="margin:5px;">' . "\n"
            . '<select name="language" onchange="top.location=this.options[selectedIndex].value">' . "\n";

        foreach ( $language_list as $language )
        {
            $form .= '<option value="'.$_SERVER['PHP_SELF'].'?language='.urlencode($language).'"'
                . ($language==$user_language?'selected="selected"':' ') . '>'
                . (isset($langNameOfLang[$language])?$langNameOfLang[$language]:$language)
                . '</option>' . "\n";
        }

        $form .= '</select>' . "\n"
            . '<noscript><input type="submit" value="'.get_lang('Ok').'" /></noscript>' . "\n"
            . '</form>' . "\n";
    }

    return $form;
}


/**
 * return an array with names of months
 *
 * @param string $size size / format of strings
 *                           'long' for complete name
 *                           'short' or 'abbr' for abbreviation
 * @return array of 12 strings (0 = january)
 */


function get_lang_month_name_list($size='long')
{
    switch ($size)
    {
        case 'abbr' : {}
        case 'short' :
        {
            $nameList = array(
            get_lang('Jan'),
            get_lang('Feb'),
            get_lang('Mar'),
            get_lang('Apr'),
            get_lang('May'),
            get_lang('Jun'),
            get_lang('Jul'),
            get_lang('Aug'),
            get_lang('Sep'),
            get_lang('Oct'),
            get_lang('Nov'),
            get_lang('Dec'),
            );
        }
        default : {}
        case 'long' :
        {
            $nameList = array(
            get_lang('January'),
            get_lang('February'),
            get_lang('March'),
            get_lang('April'),
            get_lang('May'),
            get_lang('June'),
            get_lang('July'),
            get_lang('August'),
            get_lang('September'),
            get_lang('October'),
            get_lang('November'),
            get_lang('December'),
            );
        }   break;
    }
    return $nameList;
}

/**
 * return an array with names of weekdays
 *
 * @param string $size size / format of strings
 *                           'long' for complete name
 *                           'short' or 'abbr' for abbreviation
 * @return array of 7 strings (0 = monday)
 */

function get_lang_weekday_name_list($size='long')
{
    switch ($size)
    {
        case 'abbr' :
        case 'short' :
        {
            $nameList = array(
            get_lang('Sun'),
            get_lang('Mon'),
            get_lang('Tue'),
            get_lang('Wed'),
            get_lang('Thu'),
            get_lang('Fri'),
            get_lang('Sat'),
            );

        }

        case 'long' : {}
        default :
        {
            $nameList = array(
            get_lang('Sunday'),
            get_lang('Monday'),
            get_lang('Tuesday'),
            get_lang('Wednesday'),
            get_lang('Thursday'),
            get_lang('Friday'),
            get_lang('Saturday'),
            );

        }   break;

    }
    return $nameList;
}
?>