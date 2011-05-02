<!-- $Id$ -->

<?php if (!empty($this->relatedCourses)) : ?>
<ul class="coursesTabs">
    <?php foreach ($this->relatedCourses as $relatedCourse) : ?>
    <li<?php if ($relatedCourse['id'] == $this->course['id']) : ?> class="current"<?php endif; ?>>
        <a class="qtip" href="<?php echo htmlspecialchars(Url::Contextualize(get_path('clarolineRepositoryWeb') . 'course/index.php', array('cid'=>$relatedCourse['sysCode']))); ?>" title="<?php echo $relatedCourse['title']; ?>"><?php echo $relatedCourse['officialCode']; ?></a>
    </li>
    <?php endforeach; ?>
    <li class="more"><a href="#more">&raquo;</a></li>
</ul>
<?php endif; ?>

<hr class="clearer" />

<div class="tabbedCourse">

<div class="courseInfos">
    <h2>
        <?php echo link_to_course($this->course['name'], $this->course['sysCode']); ?>
    </h2>
    <p>
        <b><?php echo $this->course['officialCode']; ?></b><br />
        <?php echo $this->course['titular']; ?>
    </p>
    <hr class="clearer" />
</div>

<hr class="clearer" />

<div class="courseContent">
    <table class="courseTable">
      <tr>
        <td class="toolList">
            <?php
            if (is_array($this->toolLinkList))
            {
                echo claro_html_list($this->toolLinkList);
            }
            ?>
            
            <?php if (claro_is_user_authenticated() && !empty($this->otherToolsList)) : ?>
            <ul>
                <?php foreach ($this->otherToolsList as $otherTool) : ?>
                <li><?php echo $otherTool; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <br />
            
            <?php
            if ( claro_is_allowed_to_edit() ) :
                echo claro_html_list($this->courseManageToolLinkList,  array('id'=>'courseManageToolList'));
            endif;
            ?>
        </td>
        
        <td class="coursePortletList">
            <?php
                echo $this->dialogBox->render();
            ?>
            
            <?php
                if ( claro_is_allowed_to_edit() ) :
                    echo '<p>'."\n"
                       . '<a href="'
                       . htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
                       . '?portletCmd=rqAdd')).'">'
                       . '<img src="'.get_icon_url('default_new').'" alt="'.get_lang('Add a new portlet').'" /> '
                       . get_lang('Add a portlet to your course homepage').'</a>'."\n"
                       . '</p>';
                endif;
                
                if ($this->portletIterator->count() > 0)
                {
                    foreach ($this->portletIterator as $portlet)
                    {
                        if ($portlet->getVisible() || !$portlet->getVisible() && claro_is_allowed_to_edit())
                        {
                            echo $portlet->render();
                        }
                    }
                }
                elseif ($this->portletIterator->count() == 0 && claro_is_allowed_to_edit())
                {
                    echo get_block('blockIntroCourse');
                }
            ?>
        </td>
      </tr>
    </table>
</div>

</div>