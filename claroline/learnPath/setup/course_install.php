<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    ############################### LEARNING PATH  ####################################

    if ( get_conf('fill_course_example',true) )
    {
        $questionId = 1;
        $exerciseId = 1;
        
        $TABLELEARNPATH         = $tbl_cdb_names['lp_learnPath'];//  "lp_learnPath";
        $TABLEMODULE            = $tbl_cdb_names['lp_module'];//  "lp_module";
        $TABLELEARNPATHMODULE   = $tbl_cdb_names['lp_rel_learnPath_module'];//  "lp_rel_learnPath_module";
        $TABLEASSET             = $tbl_cdb_names['lp_asset'];//  "lp_asset";

        // HANDMADE module type are not used for first version of claroline 1.5 beta so we don't show any exemple!

        claro_sql_query("INSERT INTO `".$TABLELEARNPATH."` VALUES ('1', '".addslashes(get_lang('sampleLearnPathTitle'))."', '".addslashes(get_lang('sampleLearnPathDescription'))."', 'OPEN', 'SHOW', '1')");

        claro_sql_query("INSERT INTO `".$TABLELEARNPATHMODULE."` VALUES ('1', '1', '1', 'OPEN', 'SHOW', '', '1', '0', '50')");
        claro_sql_query("INSERT INTO `".$TABLELEARNPATHMODULE."` VALUES ('2', '1', '2', 'OPEN', 'SHOW', '', '2', '0', '50')");

        claro_sql_query("INSERT INTO `".$TABLEMODULE."` VALUES ('1', '".addslashes(get_lang('sampleLearnPathDocumentTitle'))."', '".addslashes(get_lang('sampleLearnPathDocumentDescription'))."', 'PRIVATE', '1', 'DOCUMENT', '')");
        claro_sql_query("INSERT INTO `".$TABLEMODULE."` VALUES ('2', '".addslashes(get_lang('sampleQuizTitle'))."', '".addslashes(get_lang('sampleLearnPathQuizDescription'))."', 'PRIVATE', '2', 'EXERCISE', '')");

        claro_sql_query("INSERT INTO `".$TABLEASSET."` VALUES ('1', '1', '/Example_document.pdf', '')");
        claro_sql_query("INSERT INTO `".$TABLEASSET."` VALUES ('2', '2', '".$exerciseId."', '')");
    }
?>