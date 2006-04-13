<?php // $Id$
/**
 * CLAROLINE
 *
 * Wiki
 *
 * @version 1.8  $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLWIKI
 *
 * @package CLWIKI
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */


/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLWIKI_aivailable_context_tool()
{
    return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
}

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLWIKI_install_tool($context,$contextData)
{
    if (CLARO_CONTEXT_GROUP == $context || CLARO_CONTEXT_COURSE == $context )
    {
        if (CLARO_CONTEXT_COURSE == $context) $contextData[CLARO_CONTEXT_COURSE] = $contextData;
            $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextData[CLARO_CONTEXT_COURSE]));

            // Wiki
            $sql = "
            CREATE TABLE IF NOT EXISTS `".$tbl_cdb_names['wiki_properties']."`(
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `description` TEXT NULL,
                `group_id` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY(`id`)
            )" ;
            claro_sql_query ($sql);

            $sql = "
            CREATE TABLE IF NOT EXISTS `".$tbl_cdb_names['wiki_acls']."` (
                `wiki_id` INT(11) UNSIGNED NOT NULL,
                `flag` VARCHAR(255) NOT NULL,
                `value` ENUM('false','true') NOT NULL DEFAULT 'false'
            )";
            claro_sql_query ($sql);

            $sql = "
            CREATE TABLE IF NOT EXISTS `".$tbl_cdb_names['wiki_pages']."` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `wiki_id` int(11) unsigned NOT NULL default '0',
                `owner_id` int(11) unsigned NOT NULL default '0',
                `title` varchar(255) NOT NULL default '',
                `ctime` datetime NOT NULL default '0000-00-00 00:00:00',
                `last_version` int(11) unsigned NOT NULL default '0',
                `last_mtime` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`id`)
            )" ;
            claro_sql_query ($sql);

            $sql = "
            CREATE TABLE IF NOT EXISTS `".$tbl_cdb_names['wiki_pages_content']."` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `pid` int(11) unsigned NOT NULL default '0',
                `editor_id` int(11) NOT NULL default '0',
                `mtime` datetime NOT NULL default '0000-00-00 00:00:00',
                `content` text NOT NULL,
            PRIMARY KEY  (`id`)
            )" ;
            claro_sql_query ($sql);

        return true;
    }
    else return false;

}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLWIKI_enable_tool($context,$contextData)
{


    if (CLARO_CONTEXT_GROUP == $context )
    {
        require_once $GLOBALS['includePath'] . '/../wiki/lib/lib.createwiki.php';

        $group = claro_get_group_data($contextData[CLARO_CONTEXT_GROUP],$contextData[CLARO_CONTEXT_COURSE]);


        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextData[CLARO_CONTEXT_COURSE]));
        create_wiki( $contextData[CLARO_CONTEXT_GROUP], $group['name'] . ' - Wiki' );

        return true;
    }
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLWIKI_disable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLWIKI_export_tool($context,$course_id)
{
    return true;
}
?>