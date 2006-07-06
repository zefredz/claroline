<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Forum
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLFRM
 *
 * @package CLFRM
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
function CLFRM_aivailable_context_tool()
{
    return array(CLARO_CONTEXT_COURSE, CLARO_CONTEXT_GROUP);
}

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLFRM_install_tool($context,$course_id)
{
    if (CLARO_CONTEXT_COURSE == $context)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

        // This table work for both context course & group
        $sql = "
        CREATE TABLE IF NOT EXISTS `" . $tbl_cdb_names['bb_categories'] . "` (
            cat_id int(10) NOT NULL auto_increment,
            cat_title varchar(100),
            cat_order varchar(10),
        PRIMARY KEY (cat_id)
        )";
        claro_sql_query($sql);

        // This table work for both context course & group
        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_forums'] . "`(
            forum_id int(10) NOT NULL auto_increment,
            group_id int(11) default NULL,
            forum_name varchar(150),
            forum_desc text,
            forum_access int(10) DEFAULT '1',
            forum_moderator int(10),
            forum_topics int(10) DEFAULT '0' NOT NULL,
            forum_posts int(10) DEFAULT '0' NOT NULL,
            forum_last_post_id int(10) DEFAULT '0' NOT NULL,
            cat_id int(10),
            forum_type int(10) DEFAULT '0',
            forum_order int(10) DEFAULT '0',
        PRIMARY KEY (forum_id),
            KEY forum_last_post_id (forum_last_post_id))
        ";
        claro_sql_query($sql);

        // This table work for both context course & group
        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_posts'] . "`(
            post_id int(10) NOT NULL auto_increment,
            topic_id int(10) DEFAULT '0' NOT NULL,
            forum_id int(10) DEFAULT '0' NOT NULL,
            poster_id int(10) DEFAULT '0' NOT NULL,
            post_time varchar(20),
            poster_ip varchar(16),
            nom varchar(30),
            prenom varchar(30),
        PRIMARY KEY (post_id),
            KEY post_id (post_id),
            KEY forum_id (forum_id),
            KEY topic_id (topic_id),
            KEY poster_id (poster_id)
        )";
        claro_sql_query($sql);

        $sql = "CREATE TABLE `" . $tbl_cdb_names['bb_posts_text'] . "` (
                post_id int(10) DEFAULT '0' NOT NULL,
                post_text text,
                PRIMARY KEY (post_id)
                )";

        claro_sql_query($sql);

        //  Structure de la table 'priv_msgs'
        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_priv_msgs'] . "` (
            msg_id int(10) NOT NULL auto_increment,
            from_userid int(10) DEFAULT '0' NOT NULL,
            to_userid int(10) DEFAULT '0' NOT NULL,
            msg_time varchar(20),
            poster_ip varchar(16),
            msg_status int(10) DEFAULT '0',
            msg_text text,
        PRIMARY KEY (msg_id),
            KEY msg_id (msg_id),
            KEY to_userid (to_userid)
        )";
        claro_sql_query($sql);

        //  Structure de la table 'topics'
        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_topics'] . "` (
            topic_id int(10) NOT NULL auto_increment,
            topic_title varchar(100),
            topic_poster int(10),
            topic_time varchar(20),
            topic_views int(10) DEFAULT '0' NOT NULL,
            topic_replies int(10) DEFAULT '0' NOT NULL,
            topic_last_post_id int(10) DEFAULT '0' NOT NULL,
            forum_id int(10) DEFAULT '0' NOT NULL,
            topic_status int(10) DEFAULT '0' NOT NULL,
            topic_notify int(2) DEFAULT '0',
            nom varchar(30),
            prenom varchar(30),
        PRIMARY KEY (topic_id),
            KEY topic_id (topic_id),
            KEY forum_id (forum_id),
            KEY topic_last_post_id (topic_last_post_id)
        )";
        claro_sql_query($sql);

        //  Structure de la table 'users'
        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_users'] . "` (
            user_id int(10) NOT NULL auto_increment,
            username varchar(40) NOT NULL,
            user_regdate varchar(20) NOT NULL,
            user_password varchar(32) NOT NULL,
            user_email varchar(50),
            user_icq varchar(15),
            user_website varchar(100),
            user_occ varchar(100),
            user_from varchar(100),
            user_intrest varchar(150),
            user_sig varchar(255),
            user_viewemail tinyint(2),
            user_theme int(10),
            user_aim varchar(18),
            user_yim varchar(25),
            user_msnm varchar(25),
            user_posts int(10) DEFAULT '0',
            user_attachsig int(2) DEFAULT '0',
            user_desmile int(2) DEFAULT '0',
            user_html int(2) DEFAULT '0',
            user_bbcode int(2) DEFAULT '0',
            user_rank int(10) DEFAULT '0',
            user_level int(10) DEFAULT '1',
            user_lang varchar(255),
            user_actkey varchar(32),
            user_newpasswd varchar(32),
        PRIMARY KEY (user_id)
        )";
        claro_sql_query($sql);

        //  Structure de la table 'whosonline'
        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_whosonline'] . "` (
            id int(3) NOT NULL auto_increment,
            ip varchar(255),
            name varchar(255),
            count varchar(255),
            date varchar(255),
            username varchar(40),
            forum int(10),
        PRIMARY KEY (id)
        )";
        claro_sql_query($sql);

        $sql = "
        CREATE TABLE `" . $tbl_cdb_names['bb_rel_topic_userstonotify'] . "` (
            `notify_id` int(10) NOT NULL auto_increment,
            `user_id` int(10) NOT NULL default '0',
            `topic_id` int(10) NOT NULL default '0',
        PRIMARY KEY  (`notify_id`),
        KEY `SECONDARY` (`user_id`,`topic_id`)
        )";
        claro_sql_query($sql);
        return true;
    }
    elseif (CLARO_CONTEXT_GROUP == $context)
    {
        // in fact code would be merged with creation  for course (to prevent a activation in a group of tool forum for a course witouh forum)
        return true;
    }
    else return false;

}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLFRM_enable_tool($context,$contextData)
{
    $user = get_init('_user');
    if (CLARO_CONTEXT_COURSE == $context)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextData));


        // category 1 is  always dedicated for groups. (auto hidden if empty)
        $sql = "INSERT INTO `" . $tbl_cdb_names['bb_categories'] . "`
            SET
            cat_id    = 1,
            cat_title = '" . addslashes(get_lang('sampleForumGroupCategory')) . "',
            cat_order = 2 ";
        claro_sql_query($sql);

        $sql = "INSERT INTO `" . $tbl_cdb_names['bb_categories'] . "`
            SET
            cat_id    = 2,
            cat_title = '" . addslashes(get_lang('sampleForumMainCategory')) . "',
            cat_order = 1 ";
        claro_sql_query($sql);

        $sql = "INSERT INTO `" . $tbl_cdb_names['bb_forums'] . "`
                SET forum_id           = 1,
                    group_id           = NULL,
                    forum_name         = '".addslashes(get_lang('sampleForumTitle'))."',
                    forum_desc         = '".addslashes(get_lang('sampleForumDescription'))."',
                    forum_access       = 2,
                    forum_moderator    = 1,
                    forum_topics       = 1,
                    forum_posts        = 1,
                    forum_last_post_id = 1,
                    cat_id             = 2,
                    forum_type         = 0,
                    forum_order        = 1
                ";
        claro_sql_query($sql);

        // Contenu de la table 'users'
        $sql = "
        INSERT INTO `".$tbl_cdb_names['bb_users']."`
        SET user_id        = 1,
            username       = '" . addslashes($user['lastName'] . " " . $user['firstName']) . "',
            user_regdate   = NOW(),
            user_password  = 'password',
            user_email     = '" . addslashes($user['mail']) . "',
            user_icq       = NULL,
            user_website   = NULL,
            user_occ       = NULL,
            user_from      = NULL,
            user_intrest   = NULL,
            user_sig       = NULL,
            user_viewemail = NULL,
            user_theme     = NULL,
            user_aim       = NULL,
            user_yim       = NULL,
            user_msnm      = NULL,
            user_posts     = 0,
            user_attachsig = 0,
            user_desmile   = 0,
            user_html      = 0,
            user_bbcode    = 0,
            user_rank      = 0,
            user_level     = 1,
            user_lang      = NULL,
            user_actkey    = NULL,
            user_newpasswd = NULL";
        claro_sql_query($sql);

        // Contenu de la table 'users'
        $sql = "
        INSERT INTO `".$tbl_cdb_names['bb_users']."`
        SET user_id        = -1,
            username       = '".addslashes(get_lang('Anonymous'))."',
            user_regdate   = NOW(),
            user_password  = 'password',
            user_email     = NULL,
            user_icq       = NULL,
            user_website   = NULL,
            user_occ       = NULL,
            user_from      = NULL,
            user_intrest   = NULL,
            user_sig       = NULL,
            user_viewemail = NULL,
            user_theme     = NULL,
            user_aim       = NULL,
            user_yim       = NULL,
            user_msnm      = NULL,
            user_posts     = 0,
            user_attachsig = 0,
            user_desmile   = 0,
            user_html      = 0,
            user_bbcode    = 0,
            user_rank      = 0,
            user_level     = 1,
            user_lang      = NULL,
            user_actkey    = NULL,
            user_newpasswd = NULL";
        claro_sql_query($sql);

        $sql = "
        INSERT INTO `" . $tbl_cdb_names['bb_topics'] . "`
        SET topic_id           = 1,
            topic_title        = '" . addslashes(get_lang('sampleForumTopicTitle')) . "',
            topic_poster       = -1,
            topic_time         = NOW(),
            topic_views        = 1,
            topic_replies      = '',
            topic_last_post_id = 1,
            forum_id           = 1,
            topic_status       = '0',
            topic_notify       = 1,
            nom       = '" . addslashes($user['lastName'] )  . "',
            prenom    = '" . addslashes($user['firstName'])  . "'
                    ";
        claro_sql_query($sql);

        $sql = "
                        INSERT INTO `" . $tbl_cdb_names['bb_posts_text'] .  "`
                        set post_id = 1,
                            post_text = '" . addslashes(get_lang('sampleForumMessage')) . "'";
        claro_sql_query($sql);

        $sql = "
        INSERT INTO `" . $tbl_cdb_names['bb_posts'] . "`
        SET post_id   = 1,
            topic_id  = 1,
            forum_id  = 1,
            poster_id = 1,
            post_time = NOW(),
            poster_ip = '127.0.0.1',
            nom       = '" . addslashes($user['lastName'] )  . "',
            prenom    = '" . addslashes($user['firstName'])  . "'
            ";
        claro_sql_query($sql);
        return true;
    }
    elseif (CLARO_CONTEXT_GROUP == $context)
    {
        $group = claro_get_group_data($contextData[CLARO_CONTEXT_GROUP],$contextData[CLARO_CONTEXT_COURSE]);

        $forumInsertId = create_forum( $group['name'] . ' - '. strtolower(get_lang("Forum"))
                                     , '' // forum description
                                     , 2  // means forum post allowed,
                                     , (int) GROUP_FORUMS_CATEGORY
                                     , $contextData[CLARO_CONTEXT_GROUP]
                                     );

    }
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLFRM_disable_tool($context,$course_id)
{
    return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLFRM_export_tool($context,$course_id)
{
    return true;
}

?>