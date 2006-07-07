<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLRSS
 * @since 1.8
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @see http://www.stervinou.com/projets/rss/
 * @see http://feedvalidator.org/
 * @see http://rss.scripting.com/
 */


/**
 * This lib use
 * * cache lite
 * * rssendar/class.rss.inc.php
 *
 */

if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');

define('RSS_FILE_EXT', 'xml');
include claro_get_conf_repository() . 'rss.conf.php';

function build_rss($context)
{
    if (is_array($context) && count($context) > 0)
    {
        include_once dirname(__FILE__) . '/pear/XML/Serializer.php';

        $rssRepositoryCacheSys = get_conf('rootSys') . get_conf('rssRepositoryCache','tmp/cache/rss/');
        if (!file_exists($rssRepositoryCacheSys))
        {
            require_once dirname(__FILE__) . '/fileManage.lib.php';
            claro_mkdir($rssRepositoryCacheSys, CLARO_FILE_PERMISSIONS, true);
            if (!file_exists($rssRepositoryCacheSys))
                return claro_failure::set_failure('CANT_CREATE_RSS_DIR');
        }

        $options = array(
        'indent'    => '    ',
        'linebreak' => "\n",
        'typeHints' => FALSE,
        'addDecl'   => TRUE,
        'encoding'  => get_conf('charset'),
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
            $rssEmail = $_course['email'];
            $rssLink = get_conf('coursesRepositoryWeb') . $_course['path'];
            if (array_key_exists(CLARO_CONTEXT_GROUP,$context))
            {
                $rssFilePath .= 'g'.$context[CLARO_CONTEXT_GROUP] . '.';
                $rssTitle .= '[' . get_lang('group') . $context[CLARO_CONTEXT_GROUP] . ']';
                $rssDescription .= get_lang('group') . $context[CLARO_CONTEXT_GROUP];
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
        'language'       => get_conf('iso639_1_code'),
        'docs'           => 'http://blogs.law.harvard.edu/tech/rss',
        'pubDate'        => date("r",time())
        );

        $toolLabelList = rss_get_tool_compatible_list();
        foreach ($toolLabelList as $toolLabel)
        {
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

        foreach ($data['channel'] as $itemKey => $item)
        {
            // $data['channel'][$itemKey][x] = filter($item[x]);
            $data['channel'][$itemKey]['title'] = trim(strip_tags($item['title']));
            $data['channel'][$itemKey]['title'] = (empty($data['channel'][$itemKey]['title'])?get_lang('Item').':'.$itemKey:$data['channel'][$itemKey]['title'] );
        }

        $serializer = new XML_Serializer($options);

        if ($serializer->serialize($data))
        {
            if(is_writable($rssFilePath))
            {
            if( false !== $fprss = fopen($rssFilePath, 'w'))
            {
                fwrite($fprss, $serializer->getSerializedData());
                fclose($fprss);
            }
            else
            {
                return claro_failure::set_failure('CANT_OPEN_RSS_FILE');
            }
            }
            else
            {
                return claro_failure::set_failure('CANT_OPEN_RSS_FILE_REAND_ONLY');
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
    if (is_null($rssToolList))
    {
        if(get_conf('rssUseCache',true))
        {
            include_once dirname(__FILE__) . '/pear/Lite.php';

            // Cache_lite setting & init
            $cache_options = array(
            'cacheDir' => get_conf('rootSys') . get_conf('rssRepositoryCache','tmp/cache/rss/') . 'sources/',
            'lifeTime' => get_conf('cache_lifeTime', get_conf('rssCacheLifeTime'), 600000), // 600.000 little less than a week
            'automaticCleaningFactor' => 500,
            );
            if (get_conf('CLARO_DEBUG_MODE',false) )
            {
                $cache_options ['pearErrorMode'] = CACHE_LITE_ERROR_DIE;
                $cache_options ['lifeTime'] = 60;
                $cache_options ['automaticCleaningFactor'] = 10;
            }

            if (! file_exists($cache_options['cacheDir']) )
            {
                include_once dirname(__FILE__) . '/fileManage.lib.php';
                claro_mkdir($cache_options['cacheDir'],CLARO_FILE_PERMISSIONS,true);
                if (! file_exists($cache_options['cacheDir']) )
                    return claro_failure::set_failure('CANT_CREATE_CACHE_RSS_SOURCE_LIST');
            }

            $rssToolListCache = new Cache_Lite($cache_options);

            if (false === ($rssToolListSerialized = $rssToolListCache->get('rssToolList')))
            {
                $toolList = $GLOBALS['_courseToolList'];
                foreach ($toolList as $tool)
                {
                    $toolLabel = trim($tool['label'],'_');
                    $rssToolLibPath = get_module_path($toolLabel) . '/connector/rss.write.cnr.php';
                    $rssToolFuncName =  $toolLabel . '_write_rss';
                    if ( file_exists($rssToolLibPath)
                    )
                    {
                        include_once $rssToolLibPath;
                        if (function_exists($rssToolFuncName))
                        {
                            $rssToolList[] = $toolLabel;
                        }
                    }

                }
                $rssToolListSerialized = serialize($rssToolList);
                $rssToolListCache->save($rssToolListSerialized, 'rssToolList');
            }
            else
                $rssToolList = unserialize($rssToolListSerialized);

        }
        else
        $rssToolList = array();

    } // if is_null $rssToolList -> if not use static

    return $rssToolList;
}
?>