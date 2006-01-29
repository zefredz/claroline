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

    $translation  = '';

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

/**
 * Load the global array ($_lang) with all translations of the language
 *
 * @param string $language language (default : english)
 * @param string $mode     TRANSLATION or PRODUCTION (default : PRODUCTION)
 *
 */

function load_language_translation ($language,$mode)
{
    global $_lang ;
    global $includePath, $urlAppend ;

    /*----------------------------------------------------------------------
      Initialise language array
      ----------------------------------------------------------------------*/

    $_lang = array();

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

        if ( isset($course_homepage) && $course_homepage == true )
        {
            $languageFilename = 'claroline_course_home';
        }
        else
        {
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

?>
