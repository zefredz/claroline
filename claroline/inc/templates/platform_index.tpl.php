<!-- $Id$ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<div id="rightSidebar">

    <?php
    if ( claro_is_user_authenticated() ) :
        FromKernel::uses('display/userprofilebox.lib');
        
        $userProfileBox = new UserProfileBox(true);
        echo $userProfileBox->render();
        
    else :
        // Display preferred language form
        echo claro_display_preferred_language_form();
        
        // Display login form
        include_template('loginzone.tpl.php');
    endif;
    ?>
    
    <?php include_dock('campusHomePageRightMenu'); ?>
    
    <?php include_textzone('textzone_right.inc.html'); ?>

</div>

<div id="leftContent">
    
    <?php
    include_textzone( 'textzone_top.inc.html', '<div style="text-align: center">
    <img src="'.get_icon_url('logo').'" border="0" alt="Claroline logo" />
    <p><strong>Claroline Open Source e-Learning</strong></p>
    </div>' );
    
    include_dock('campusHomePageTop');
    
    if( claro_is_user_authenticated() ) :
        include_textzone( 'textzone_top.authenticated.inc.html' );
    else :
        include_textzone( 'textzone_top.anonymous.inc.html' );
    endif;
    
    if ( claro_is_user_authenticated() ) :
        $userCommands = array();
        
        $userCommands[] = '<a href="' . $_SERVER['PHP_SELF'] . '" class="claroCmd">'
                        . '<img src="' . get_icon_url('mycourses') . '" alt="" /> '
                        . get_lang('My course list')
                        . '</a>' . "\n";
        
        // 'Create Course Site' command. Only available for teacher.
        if (claro_is_allowed_to_create_course()) :
            $userCommands[] = '<a href="claroline/course/create.php" class="claroCmd">'
                            . '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
                            . get_lang('Create a course site')
                            . '</a>' . "\n";
                            
        elseif ( $GLOBALS['currentUser']->isCourseCreator ) :
            $userCommands[] = '<span class="claroCmdDisabled">'
                            . '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
                            . get_lang('Create a course site')
                            . '</span>' . "\n";
        endif;
        
        if (get_conf('allowToSelfEnroll',true)) :
            $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqReg&amp;categoryId=0" class="claroCmd">'
                            . '<img src="' . get_icon_url('enroll') . '" alt="" /> '
                            . get_lang('Enrol on a new course')
                            . '</a>' . "\n";
            
            $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="claroCmd">'
                            . '<img src="' . get_icon_url('unenroll') . '" alt="" /> '
                            . get_lang('Remove course enrolment')
                            . '</a>' . "\n";
        endif;
        
        $userCommands[] = '<a href="claroline/course/platform_courses.php" class="claroCmd">'
                        . '<img src="' . get_icon_url('course') . '" alt="" /> '
                        . get_lang('All platform courses')
                        . '</a>' . "\n";
        
        echo '<a name="myCourseList"></a>'
           . '<p>' . claro_html_menu_horizontal( $userCommands ) . '</p>' . "\n";
        
        // Clean session code
        if (isset($_SESSION['courseSessionCode'])) :
            $_SESSION['courseSessionCode'] = null;
        endif;
        
        // User course (activated and deactivated) lists
        $userCourseList = render_user_course_list();
        $userCourseListDesactivated =  render_user_course_list_desactivated();
        
        $template = new CoreTemplate('mycourses.tpl.php');
        $template->assign('userCourseList', $userCourseList);
        $template->assign('userCourseListDesactivated', $userCourseListDesactivated);
        
        echo $template->render();
        
        echo '<fieldset class="captionBlock">'
            . '<img class="iconDefinitionList" src="' . get_icon_url( 'hot' ) . '" alt="New items" />'
            . get_lang('New items'). ' ('
            . '<a href="'. htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'notification_date.php')) . '" >' . get_lang('to another date') . '</a>';
        
        if ($_SESSION['last_action'] != '1970-01-01 00:00:00') :
           $last_action =  $_SESSION['last_action'];
        
        else :
            $last_action = date('Y-m-d H:i:s');
        endif;
        
        $nbChar = strlen($last_action);
        if (substr($last_action,$nbChar - 8) == '00:00:00' ) :
            echo ' [' . claro_html_localised_date( get_locale('dateFormatNumeric'),
                strtotime($last_action)) . ']';
        endif;
        
        echo ')</fieldset>' ;
        
    else :
        if ( ! get_conf('course_categories_hidden_to_anonymous',false) ) :
            echo $this->templateCategoryBrowser->render();
        endif;
    endif;
    
    include_dock('campusHomePageBottom');
    ?>

</div>