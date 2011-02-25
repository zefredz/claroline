<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * @version     1.9 $Revision$
 * @copyright   (c) 2001-2008 Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLRSS
 * @since       1.9
 * @author      Claro Team <cvs@claroline.net>
 * @see         http://www.stervinou.com/projets/rss/
 * @see         http://feedvalidator.org/
 * @see         http://rss.scripting.com/
 */


/**
 * This lib use
 * * cache lite
 * * rssendar/class.rss.inc.php
 *
 */

define('RSS_FILE_EXT', 'xml');

include_once claro_get_conf_repository() . 'CLKCACHE.conf.php';
include_once claro_get_conf_repository() . 'rss.conf.php';

function build_rss($context)
{
    if (is_array($context) && count($context) > 0)
    {
        include_once dirname(__FILE__) . '/thirdparty/pear/XML/Serializer.php';

        $rssRepositoryCacheSys = get_path('rootSys') . get_conf('rssRepositoryCache','tmp/cache/rss/');
        if (!file_exists($rssRepositoryCacheSys))
        {
            require_once dirname(__FILE__) . '/fileManage.lib.php';
            claro_mkdir($rssRepositoryCacheSys, CLARO_FILE_PERMISSIONS, true);
            if (!file_exists($rssRepositoryCacheSys))
            return claro_failure::set_failure('CANT_CREATE_RSS_DIR');
        }
        
        $outEnc = 'utf-8';
        $inEnc = get_conf('charset');

        $options = array(
        'indent'    => '    ',
        'linebreak' => "\n",
        'typeHints' => FALSE,
        'addDecl'   => TRUE,
        'encoding'  => $outEnc,
        'rootName'  => 'rss',
        'defaultTagName' => 'item',
        'rootAttributes' => array('version' => '2.0', 'xmlns:dc'=>'http://purl.org/dc/elements/1.1/')
        );

        $rssFilePath = $rssRepositoryCacheSys . '/' ;
        if (array_key_exists(CLARO_CONTEXT_COURSE,$context))
        {
            $rssFilePath .= $context[CLARO_CONTEXT_COURSE] . '.';

            $_course = claro_get_course_data($context[CLARO_CONTEXT_COURSE]);
            $rssTitle = '[' . get_conf('siteName') . '] '.$_course['officialCode'];
            $rssDescription = $_course['name'];
            $rssEmail = $_course['email'] == '' ? get_conf('administrator_email') : $_course['email'];
            $rssLink = get_path('rootWeb') .  get_path('coursesRepositoryAppend') . claro_get_course_path();
            if (array_key_exists(CLARO_CONTEXT_GROUP,$context))
            {
                $rssFilePath .= 'g'.$context[CLARO_CONTEXT_GROUP] . '.';
                $rssTitle .= '[' . get_lang('Group') . $context[CLARO_CONTEXT_GROUP] . ']';
                $rssDescription .= get_lang('Group') . $context[CLARO_CONTEXT_GROUP];
            }
        }
        else
        {
            $rssEmail = '';
        }

        $rssFilePath = $rssFilePath . RSS_FILE_EXT;


        $data['channel'] = array(
        'title'          => $rssTitle,
        'description'    => $rssDescription,
        'link'           => $rssLink,
        'generator'      => 'Claroline-PEARSerializer',
        'webMaster'      => get_conf('administrator_email'),
        'managingEditor' => $rssEmail,
        'language'       => get_locale('iso639_1_code'),
        'docs'           => 'http://blogs.law.harvard.edu/tech/rss',
        'pubDate'        => date("r",time())
        );

        $toolLabelList = rss_get_tool_compatible_list();



        foreach ($toolLabelList as $toolLabel)
        {
            if ( is_tool_activated_in_course(
                get_tool_id_from_module_label( $toolLabel ),
                $context[CLARO_CONTEXT_COURSE]
            ) )
            {
                if ( ! is_module_installed_in_course($toolLabel,$context[CLARO_CONTEXT_COURSE]) )
                {
                    install_module_in_course( $toolLabel,$context[CLARO_CONTEXT_COURSE] );
                }
                
                $rssToolLibPath = get_module_path($toolLabel) . '/connector/rss.write.cnr.php';
                $rssToolFuncName =  $toolLabel . '_write_rss';
                if ( file_exists($rssToolLibPath)
                )
                {
                    include_once $rssToolLibPath;
                    if (function_exists($rssToolFuncName))
                    {
                        $rssItems = call_user_func($rssToolFuncName, $context );
                        $data['channel'] = array_merge($data['channel'], $rssItems);
                    }
                }
            }
        }

        foreach ($data['channel'] as $itemKey => $item)
        {
            // $data['channel'][$itemKey][x] = filter($item[x]);
            $data['channel'][$itemKey]['title'] = trim(strip_tags($item['title']));
            $data['channel'][$itemKey]['title'] = (empty($data['channel'][$itemKey]['title'])?get_lang('Item').':'.$itemKey:$data['channel'][$itemKey]['title'] );
        }

        $serializer = new XML_Serializer($options);

        if ($serializer->serialize($data))
        {
            if( is_writable($rssFilePath)
                || (!file_exists($rssFilePath) && is_writable(dirname($rssFilePath))))
            {
                $contents = iconv( $inEnc, $outEnc, $serializer->getSerializedData() );
                
                if ( false === file_put_contents( $rssFilePath, $contents ) )
                {
                    return claro_failure::set_failure('CANT_OPEN_RSS_FILE');
                }
            }
            else
            {
                return claro_failure::set_failure('CANT_OPEN_RSS_FILE_READ_ONLY');
            }

        }
        return $rssFilePath;

    }
    return false;

}


/**
 * Build the list of claro label of tool having a rss creator.
 *
 * @return array of claro_label
 *
 * This function use 2 level of cache.
 * - memory Cache to compute only one time the list by script execution
 * - if enabled : use cache lite
 */
function rss_get_tool_compatible_list()
{
    static $rssToolList = null;

    if ( is_null( $rssToolList ) )
    {
        $rssToolList = array();

        $toolList = $GLOBALS['_courseToolList'];

        foreach ( $toolList as $tool )
        {
            $toolLabel = trim($tool['label'],'_');

            $rssToolLibPath = get_module_path($toolLabel) . '/connector/rss.write.cnr.php';

            $rssToolFuncName =  $toolLabel . '_write_rss';

            if ( file_exists($rssToolLibPath)
            )
            {
                require_once $rssToolLibPath;
                
                if (function_exists($rssToolFuncName))
                {
                    $rssToolList[] = $toolLabel;
                }
            }

        }

    } // if is_null $rssToolList -> if not use static

    return $rssToolList;
}
