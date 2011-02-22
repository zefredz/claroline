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
        
        <div id="userCommands">
            <p><?php echo claro_html_menu_horizontal( $this->userCommands ); ?></p>
        </div>
        
        <?php
        // User course (activated and deactivated) lists
        echo $this->templateMyCourses->render();
        ?>
        
        <fieldset class="captionBlock">
            <img class="iconDefinitionList" src="<?php  echo get_icon_url('hot'); ?>" alt="<?php echo get_lang('New items'); ?>" />
            <?php echo get_lang('New items'); ?>
            (<a href="<?php echo htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'notification_date.php')); ?>"><?php echo get_lang('to another date'); ?></a>)
            <?php
            if (substr($this->lastUserAction, strlen($this->lastUserAction) - 8) == '00:00:00' ) :
                echo ' ['
                   . claro_html_localised_date(
                        get_locale('dateFormatNumeric'),
                        strtotime($this->lastUserAction))
                   . ']';
            endif;
            ?>
        </fieldset>
        
        <?php
        
    else :
        if ( ! get_conf('course_categories_hidden_to_anonymous',false) ) :
            echo $this->templateCategoryBrowser->render();
        endif;
    endif;
    
    include_dock('campusHomePageBottom');
    ?>
    
</div>