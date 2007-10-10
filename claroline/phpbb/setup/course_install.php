<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    ############################## FORUMS  #######################################

    if ( get_conf('fill_course_example',true) )
    {
        // Create an example category
        
        $lastname = claro_get_current_user_data('lastName');
        $firstname = claro_get_current_user_data('firstName');
        $email = claro_get_current_user_data('mail');

        $TABLEPHPBBCATEGORIES   = $courseTbl['bb_categories'];//  "bb_categories";
        $TABLEPHPBBFORUMS       = $courseTbl['bb_forums'];//  "bb_forums";
        $TABLEPHPBBPOSTS        = $courseTbl['bb_posts'];//  "bb_posts";
        $TABLEPHPBBPOSTSTEXT    = $courseTbl['bb_posts_text'];//  "bb_posts_text";
        $TABLEPHPBBTOPICS       = $courseTbl['bb_topics'];//  "bb_topics";
        $TABLEPHPBBUSERS        = $courseTbl['bb_users'];//  "bb_users";

        claro_sql_query("INSERT INTO `".$TABLEPHPBBCATEGORIES."` VALUES (2,'"
            .addslashes(get_lang('sampleForumMainCategory'))."',1)");

        // Create a hidden category for group forums
        claro_sql_query("INSERT INTO `".$TABLEPHPBBCATEGORIES."` VALUES (1,'"
            .addslashes(get_lang('sampleForumGroupCategory'))."',2)");

        claro_sql_query("INSERT
                        INTO `".$TABLEPHPBBFORUMS."`
                        VALUES ( 1
                               , NULL
                               , '".addslashes(get_lang('sampleForumTitle'))."'
                               , '".addslashes(get_lang('sampleForumDescription'))."'
                               ,2,1,1,1,1,2,0,1)");
                               
        claro_sql_query("INSERT INTO `".$TABLEPHPBBTOPICS
            ."` VALUES (1,'"
            .addslashes(get_lang('sampleForumTopicTitle'))
            ."',-1,NOW(),1,0,1,1,'0','1', '".addslashes($lastname)."', '"
            .addslashes($firstname)."')");
            
        claro_sql_query("INSERT INTO `".$TABLEPHPBBPOSTS
            ."` VALUES (1,1,1,1,NOW(),'127.0.0.1',\"".addslashes($lastname)
            ."\",\"".addslashes($firstname)."\")");
            
        claro_sql_query("INSERT INTO `".$TABLEPHPBBPOSTSTEXT."` VALUES ('1', '"
            .addslashes(get_lang('sampleForumMessage'))."')");

        // Contenu de la table 'users'
        claro_sql_query("INSERT INTO `".$TABLEPHPBBUSERS."` VALUES (
           '1',
           '".addslashes($lastname." ".$firstname)."',
           NOW(),
           'password',
           '".addslashes($email)."',
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           '0',
           '0',
           '0',
           '0',
           '0',
           '0',
           '1',
           NULL,
           NULL,
           NULL
           )");
           
        claro_sql_query("INSERT INTO `".$TABLEPHPBBUSERS."` VALUES (
           '-1',       '".addslashes(get_lang('Anonymous'))."',       NOW(),       'password',       '',
           NULL,       NULL,       NULL,       NULL,       NULL,       NULL,       NULL,
           NULL,       NULL,       NULL,       NULL,       '0',       '0',       '0',       '0',       '0',
           '0',       '1',       NULL,       NULL,       NULL       )");
    }
?>