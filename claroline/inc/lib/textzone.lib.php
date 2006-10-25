<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 *
 * @version CLAROLINE 1.8 $Revision$
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @since 1.8.1
 *
 * @package CLKERNEL
 *
 */


class claro_text_zone
{

    function get_textzone_file_path($key, $context=null)
    {
        $textZoneFile = null;
        if (is_array($context) && array_key_exists(CLARO_CONTEXT_COURSE,$context))
        {
            if (is_array($context) && array_key_exists(CLARO_CONTEXT_GROUP,$context))
            {
                $textZoneFile =  get_conf('coursesRepositorySys') . claro_get_course_path($context['course']) . claro_get_course_group_path($context) . '/textzone/' . $key . '.inc.html';
            }
            $textZoneFile =  get_conf('coursesRepositorySys') . claro_get_course_path($context['course']) . '/textzone/' . $key . '.inc.html';

        }
        if(is_null($textZoneFile) || !file_exists($textZoneFile)) $textZoneFile = get_conf('rootSys') . 'platform/textzone/' . $key . '.inc.html';

        return $textZoneFile;
    }

    /**
     * return the content
     *
     * @param coursecode $key
     * @param array $context
     * @return string : html content
     */

        function get_content($key, $context=null)
        {
        $textZoneFile = claro_text_zone::get_textzone_file_path($key, $context=null);

            if(file_exists($textZoneFile)) $content = file_get_contents($textZoneFile);
            else                           $content = '' ;
            ;
            return $content;
        }
}
?>