<h4 class="header"><?php if( !empty( $this->notification_bloc ) ) : echo $this->notification_bloc;  endif; echo $this->topic_subject; ?></h4>
<?php foreach( $this->postList as $thisPost ) : ?>
<div id="post<?php echo $thisPost['post_id']; ?>" class="threadPost">
  <?php
  $userData = user_get_properties( $thisPost['poster_id'] );
  $picturePath = user_get_picture_path( $userData );
  
  if (claro_is_user_authenticated()
      && $this->claro_notifier
      && $this->claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $this->date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $this->forum_id."-".$this->topic_id))
  {
      $class = 'item hot';
  }
  else
  {
      $class = 'item';
  }
  
  if ( $picturePath && file_exists( $picturePath ) )
  {
      $pictureUrl = user_get_picture_url( $userData );
  }
  else
  {
      $pictureUrl = null;
  }
  ?>
  <div class="threadPostInfo">
    <?php if ( $this->is_anonymous == 'not_anonymous' || claro_is_platform_admin() ) : ?>
    <?php if( !is_null( $pictureUrl ) ) : ?><div class="threadPosterPicture"><img src="<?php echo $pictureUrl; ?>" alt=" " /></div><?php endif; ?>
    <span style="font-weight: bold;"><?php echo $thisPost[ 'firstname' ]; ?> <?php echo $thisPost[ 'lastname' ]; ?></span>
    <br />
    <?php endif; ?>
    <small><?php echo claro_html_localised_date(get_locale('dateTimeFormatLong'), datetime_to_timestamp( $thisPost['post_time']) ); ?></small>
  </div>
  <div class="threadPostContent">
    <span class="threadPostIcon <?php echo $class; ?>"><img src="<?php echo get_icon_url( 'post' ); ?>" alt="" /></span><br />
    <?php echo claro_parse_user_text( $thisPost[ 'post_text' ] ); ?>
    <?php if( $this->is_allowedToEdit ) : ?>
    <p>
      <a href="<?php  echo htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') . '/editpost.php?post_id=' . $thisPost['post_id'] )); ?>">
        <img src="<?php echo get_icon_url('edit'); ?>" alt="<?php echo get_lang('Edit'); ?>" />
      </a>
      <a href="<?php echo htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') . '/editpost.php?post_id=' . $thisPost['post_id'] . '&amp;delete=delete&amp;submit=submit')); ?>" onclick="return confirm_delete();" >
        <img src="<?php echo get_icon_url('delete'); ?>" alt="<?php echo get_lang('Delete'); ?>" />
      </a>
    </p>
    <?php endif; ?>
  </div>
  <div class="spacer"></div>
</div>
<?php endforeach; ?>