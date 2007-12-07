<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 *
 * @version 0.1 $Revision$
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @package CLTXTZON
 *
 */

if (!class_exists('claro_text_zone'))
{

    // TODO MOVE THIS NEW CLASS TO A DEDICATED LIB.
    class claro_text_zone
    {
        function get_content($key, $context=null)
        {
            $textZoneFile = null;
            if (is_array($context) && array_key_exists('course',$context))
            {
                if (is_array($context) && array_key_exists('group',$context))
                {
                    $textZoneFile =  get_conf('coursesRepositorySys') . claro_get_course_path($context['course']) . claro_get_course_group_path($context) . '/textzone/' . $key . '.inc.html';
                }
                $textZoneFile =  get_conf('coursesRepositorySys') . claro_get_course_path($context['course']) . '/textzone/' . $key . '.inc.html';

            }
            if(is_null($textZoneFile) || !file_exists($textZoneFile)) $textZoneFile = get_conf('rootSys') . 'platform/textzone/' . $key . '.inc.html';

            if(file_exists($textZoneFile)) $content = file_get_contents($textZoneFile);
            else                           $content = '' ;
            ;
            return $content;
        }
    }
}
function claro_get_course_group_path()
{
    return '';
}
?>