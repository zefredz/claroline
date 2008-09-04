<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<table width="100%" border="0" cellpadding="4">
<tr>
<td valign="top">

<?php 
include_textzone( 'textzone_top.inc.html', '<div style="text-align: center">
<img src="'.get_icon_url('logo').'" border="0" alt="Claroline logo" />
<p><strong>Claroline Open Source e-Learning</strong></p>
</div>' ); 
?>

<?php 
if( claro_is_user_authenticated() ) : 
    include_textzone( 'textzone_top.authenticated.inc.html' );
else :
    include_textzone( 'textzone_top.anonymous.inc.html' );
endif; 
?>

<?php include_dock('campusHomePageTop'); ?>

<?php
if ( claro_is_user_authenticated() ) :

    /**
     * Commands line
     */
    $userCommands = array();

    $userCommands[] = '<a href="' . $_SERVER['PHP_SELF'] . '" class="claroCmd">'
    .    '<img src="' . get_icon_url('mycourses') . '" alt="" /> '
    .    get_lang('My course list')
    .    '</a>';

    if (claro_is_allowed_to_create_course()) // 'Create Course Site' command. Only available for teacher.
    {
        $userCommands[] = '<a href="claroline/course/create.php" class="claroCmd">'
        .    '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
        .    get_lang('Create a course site')
        .    '</a>';
    }

    if (get_conf('allowToSelfEnroll',true))
    {
        $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=" class="claroCmd">'
        .    '<img src="' . get_icon_url('enroll') . '" alt="" /> '
        .    get_lang('Enrol on a new course')
        .    '</a>';

        $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="claroCmd">'
        .    '<img src="' . get_icon_url('unenroll') . '" alt="" /> '
        .    get_lang('Remove course enrolment')
        .    '</a>';
    }

    $userCommands[] = '<a href="'.$_SERVER['PHP_SELF'].'?category=" class="claroCmd">'
    .                 '<img src="' . get_icon_url('course') . '" alt="" /> '
    .     get_lang('All platform courses')
    .                 '</a>'
    ;

    echo '<a name="myCourseList"></a><p>' . claro_html_menu_horizontal($userCommands) . '</p>' . "\n";

    if ( isset($_REQUEST['category']) || (isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'search' ) )
    {
        // DISPLAY PLATFORM COURSE LIST and search result
        require get_path('incRepositorySys') . '/index_platformcourses.inc.php';
    }
    else
    {
        // DISPLAY USER OWN COURSE LIST
        require get_path('incRepositorySys') . '/index_mycourses.inc.php';
    }
else :
    if ( ! get_conf('course_categories_hidden_to_anonymous',false) )
    {
        // DISPLAY PLATFORM COURSE LIST
        require get_path('incRepositorySys') . '/index_platformcourses.inc.php';
    }
endif;
?>

<?php include_dock('campusHomePageBottom'); ?>

</td>
<td width="200" valign="top" class="claroRightMenu">

<?php 
if ( claro_is_user_authenticated() ) :

    FromKernel::uses('display/userprofilebox.lib');
    
    $userProfileBox = new UserProfileBox(true);
    echo $userProfileBox->render();
    // Display module digest
    // require get_path('incRepositorySys') . '/index_mydigest.inc.php';

else :
    // Display preferred language form
    echo claro_display_preferred_language_form();
    // Display login form
    include_template('loginzone.tpl.php');
endif;
?>

<?php include_dock('campusHomePageRightMenu'); ?>

<?php include_textzone('textzone_right.inc.html'); ?>

</td>
</tr>
</table>