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
    
    // Home page presentation texts
    if( claro_is_user_authenticated() ) :
        include_textzone( 'textzone_top.authenticated.inc.html' );
    else :
        include_textzone( 'textzone_top.anonymous.inc.html' );
    endif;
    
    
    if ( claro_is_user_authenticated() ) :
        ?>
        
        <table class="homepageTable">
          <tr>
          
            <td class="userCommands">
                <?php echo claro_html_tool_title(get_lang('User commands')); ?>
                <?php echo claro_html_list( $this->userCommands ); ?>
            </td>
            <td class="myCourseList">
                <?php echo claro_html_tool_title(get_lang('My course list')); ?>
                <?php echo $this->templateMyCourses->render(); ?>
            </td>
          
          </tr>
        </table>
        
        <?php
    
    else :
        if ( ! get_conf('course_categories_hidden_to_anonymous',false) ) :
            echo $this->templateCategoryBrowser->render();
        endif;
    endif;
    
    include_dock('campusHomePageBottom');
    ?>
    
</div>