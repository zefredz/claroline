<!-- $Id$ -->


    <div class="coursePortletList">
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
    </div>
