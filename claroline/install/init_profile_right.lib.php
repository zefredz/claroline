<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * Funtions to initialise all course profiles, action, ...
 */

function create_required_profile ()
{
    require_once get_conf('includePath') . '/lib/right/profileToolRight.class.php';

    $tbl_mdb_names = claro_sql_get_tbl( array('course_tool',
                                              'right_profile',
                                              'right_rel_profile_action',
                                              'right_action' ));

    /**
     * Initialise anonymous profile
     */

    $profile = new RightProfile();
    $profile->setName('Anonymous');
    $profile->setLabel(ANONYMOUS_PROFILE);
    $profile->setDescription('Course visitor (the user has no account on the platform)');
    $profile->setType(PROFILE_TYPE_COURSE);
    $profile->setIsRequired(true);
    $profile->save();

    /**
     * Initialise guest profile
     */

    $profile = new RightProfile();
    $profile->setName('Guest');
    $profile->setLabel(GUEST_PROFILE);
    $profile->setDescription('Course visitor (the user has an account on the platform, but is not enrolled in the course)');
    $profile->setType(PROFILE_TYPE_COURSE);
    $profile->setIsRequired(true);
    $profile->save();

    /**
     * Initialise user profile
     */

    $profile = new RightProfile();
    $profile->setName('User');
    $profile->setLabel(USER_PROFILE);
    $profile->setDescription('Course member (the user is actually enrolled in the course)');
    $profile->setType(PROFILE_TYPE_COURSE);
    $profile->setIsRequired(true);
    $profile->save();

    /**
     * Initialise manager profile
     */

    $profile = new RightProfile();
    $profile->setName('Manager');
    $profile->setLabel(MANAGER_PROFILE);
    $profile->setDescription('Course Administrator');
    $profile->setType(PROFILE_TYPE_COURSE);
    $profile->setIsLocked(true);
    $profile->setIsRequired(true);
    $profile->setIsCourseManager(true);
    $profile->save();

    return true ;

}

function init_default_right_profile ()
{
    require_once get_conf('includePath') . '/lib/right/profileToolRight.class.php';

    $tbl_mdb_names = claro_sql_get_tbl( array('course_tool',
                                              'right_profile',
                                              'right_rel_profile_action',
                                              'right_action' ));

    $sql = " SELECT `id` as `toolId`
             FROM `" . $tbl_mdb_names['course_tool'] . "`" ;

    $result = claro_sql_query_fetch_all_cols($sql);
    $toolList = $result['toolId'];

    /**
     * Initialise anonymous profile
     */

    $profile = new RightProfile();
    $profile->load(ANONYMOUS_PROFILE);
    $profileAction = new RightProfileToolRight();
    $profileAction->load($profile);
    $profileAction->setToolListRight($toolList,'user');
    $profileAction->save();

    /**
     * Initialise guest profile
     */

    $profile = new RightProfile();
    $profile->load(GUEST_PROFILE);
    $profileAction = new RightProfileToolRight();
    $profileAction->load($profile);
    $profileAction->setToolListRight($toolList,'user');
    $profileAction->save();

    /**
     * Initialise user profile
     */

    $profile = new RightProfile();
    $profile->load(USER_PROFILE);
    $profileAction = new RightProfileToolRight();
    $profileAction->load($profile);
    $profileAction->setToolListRight($toolList,'user');
    $profileAction->save();

    /**
     * Initialise manager profile
     */

    $profile = new RightProfile();
    $profile->load(MANAGER_PROFILE);
    $profileAction = new RightProfileToolRight();
    $profileAction->load($profile);
    $profileAction->setToolListRight($toolList,'manager');
    $profileAction->save();

    return true ;

}

?>
