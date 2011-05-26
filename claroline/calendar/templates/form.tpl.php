<!-- $Id$ -->

<form method="post" action="<?php echo $this->formAction; ?>">
    <fieldset>
        <?php echo $this->relayContext ?>
        <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
        <input type="hidden" name="cmd" value="<?php echo $this->cmd; ?>" />
        <input type="hidden" name="id" value="<?php echo $this->event['id']; ?>" />
        
        <dl>
            <dt><label for="title"><?php echo get_lang('Title'); ?></label></dt>
            <dd>
                <input size="80" type="text" name="title" id="title"
                value="<?php echo htmlspecialchars($this->event['title']); ?>" />
            </dd>
            
            <dt><?php echo get_lang('Date'); ?></dt>
            <dd>
                <?php echo claro_html_date_form('fday', 'fmonth', 'fyear', $this->event['date'], 'long' ); ?>
                <?php echo claro_html_time_form('fhour','fminute', $this->event['date']); ?>
                <p class="notice"><?php echo get_lang('(d/m/y hh:mm)'); ?></p>
            </dd>
            
            <dt>
                <label for="lasting"><?php echo get_lang('Lasting'); ?></label>
            </dt>
            <dd>
                <input type="text" name="lasting" id="lasting" size="20" maxlength="20"
                value="<?php echo htmlspecialchars($this->event['lastingAncient']); ?>" />
            </dd>
            
            <dt>
                <label for="location"><?php echo get_lang('Location'); ?></label>
            </dt>
            <dd>
                <input type="text" name="location" id="location" size="20" maxlength="20"
                value="<?php echo htmlspecialchars($this->event['location']); ?>" />
            </dd>
            
            <dt>
                <label for="speakers"><?php echo get_lang('Speakers'); ?></label>
            </dt>
            <dd>
                <input type="text" name="speakers" id="speakers" size="20" maxlength="200"
                value="<?php echo (isset($this->event['speakers']) ? (htmlspecialchars($this->event['speakers'])) : ('')); ?>" /><br />
                <p class="notice"><?php echo get_lang('If more than one, separated by a coma'); ?></p>
            </dd>
            
            <dt><label for="content"><?php echo get_lang('Detail'); ?></label></dt>
            <dd>
                <?php echo claro_html_textarea_editor('content', $this->event['content'], 12, 67 ); ?>
            </dd>
        </dl>
    </fieldset>
    
    <?php echo ResourceLinker::renderLinkerBlock(); ?>
    <input type="submit" class="claroButton" name="submitEvent" value="<?php echo get_lang('Ok'); ?>" />
    <?php echo claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])), get_lang('Cancel')); ?>
</form>