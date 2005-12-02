<?php // $Id$
/**
 * CLAROLINE 
 *
 * language library 
 * contains function to manage l10n
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/CLUSR
 * @package CLUSR
 * @author Claro Team <cvs@claroline.net>
 *
 */


function get_lang ($name,$var_to_replace=null)
{
    global $_lang;
   
    if ( isset($_lang[$name]) )
    {
        // return translation
        return $_lang[$name];
    }
    else
    {
        // missing translation
        return $name;
    }

}

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
