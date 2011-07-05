<!-- $Id$ -->

    <div class="coursePortletList">
        <?php
            echo $this->dialogBox->render();
        ?>
        
        <?php if ( claro_is_allowed_to_edit() && !empty($this->form) ) : ?>
        <?php echo $this->form; ?>
        <?php endif; ?>
        
        <?php
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
