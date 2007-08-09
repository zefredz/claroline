<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    ############################## EXERCISES #######################################
    
    $moduleWorkingDirectory = get_path('coursesRepositorySys') . $courseDirectory . '/exercise';
    
    if ( ! claro_mkdir($moduleWorkingDirectory, CLARO_FILE_PERMISSIONS,true) )
    {
        return claro_failure::set_failure(
                get_lang( 'Unable to create folder %folder'
                    ,array( '%folder' => $moduleWorkingDirectory ) ) );
    }

    if ( get_conf('fill_course_example',true) )
    {
        // Exercise
        $TABLEQWZEXERCISE   = $tbl_cdb_names['qwz_exercise'];
        $TABLEQWZQUESTION   = $tbl_cdb_names['qwz_question'];
        $TABLEQWZRELEXERCISEQUESTION = $tbl_cdb_names['qwz_rel_exercise_question'];
        $TABLEQWZANSWERMULTIPLECHOICE = $tbl_cdb_names['qwz_answer_multiple_choice'];
        
        // create question
        $questionId = claro_sql_query_insert_id("INSERT INTO `".$TABLEQWZQUESTION."` (`title`, `description`, `attachment`, `type`, `grade`)
            VALUES
            ('".addslashes(get_lang('sampleQuizQuestionTitle'))."', '".addslashes(get_lang('sampleQuizQuestionText'))."', '', 'MCMA', '10' )");

        claro_sql_query("INSERT INTO `".$TABLEQWZANSWERMULTIPLECHOICE."`(`questionId`,`answer`,`correct`,`grade`,`comment`)
            VALUES
            ('".$questionId."','".addslashes(get_lang('sampleQuizAnswer1'))."','0','-5','".addslashes(get_lang('sampleQuizAnswer1Comment'))."'),
            ('".$questionId."','".addslashes(get_lang('sampleQuizAnswer2'))."','0','-5','".addslashes(get_lang('sampleQuizAnswer2Comment'))."'),
            ('".$questionId."','".addslashes(get_lang('sampleQuizAnswer3'))."','1','5','".addslashes(get_lang('sampleQuizAnswer3Comment'))."'),
            ('".$questionId."','".addslashes(get_lang('sampleQuizAnswer4'))."','1','5','".addslashes(get_lang('sampleQuizAnswer4Comment'))."')");

        // create exercise
        $exerciseId = claro_sql_query_insert_id("INSERT INTO `".$TABLEQWZEXERCISE."` (`title`, `description`, `visibility`, `startDate`, `endDate`)
            VALUES
            ('".addslashes(get_lang('sampleQuizTitle'))."', '".addslashes(get_lang('sampleQuizDescription'))."', 'INVISIBLE', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR) )");
            
        // put question in exercise
        claro_sql_query("INSERT INTO `".$TABLEQWZRELEXERCISEQUESTION."` VALUES ($exerciseId, $questionId, 1)");
    }
?>