<!-- $Id$ -->

<form method="post" action="<?php echo $this->formAction; ?>">
    <fieldset>
        <?php echo $this->relayContext ?>
        <input type="hidden" name="cmd" value="exEdit" />
        <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
        
        <?php if (!empty($this->descId)) : ?>
        <input type="hidden" name="descId" value="<?php echo $this->descId; ?>" />
        <input type="hidden" name="descCategory" value="<?php echo htmlspecialchars($this->description->getCategory()); ?>" />
        <?php else : ?>
        <input type="hidden" name="descCategory" value="<?php echo htmlspecialchars($this->category); ?>" />
        <?php endif; ?>
        
        <dl>
            <dt>
                <label for="descTitle"><?php echo get_lang('Title'); ?></label>
            </dt>
            <dd>
                <?php if ($this->tips['isTitleEditable']) : ?>
                <input type="text" name="descTitle" id="descTitle" size="50" value="<?php echo htmlspecialchars($this->description->getTitle()); ?>" />
                <?php else : ?>
                <?php echo $this->tips['presetTitle']; ?>
                <input type="hidden" name="descTitle" id="descTitle" value="<?php echo $this->tips['presetTitle']; ?>" />
                <?php endif; ?>
            </dd>
            <dt>
                <label for="descContent"><?php echo get_lang('Content'); ?></label>
            </dt>
            <dd>
                <?php echo claro_html_textarea_editor('descContent', $this->description->getContent(), 20, 80); ?>
            </dd>
        </dl>
    </fieldset>
    
    <p>
        <input type="submit" name="save" value="<?php echo get_lang('Ok'); ?>" />&nbsp;
        <?php echo claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])), get_lang('Cancel')); ?>
    </p>
    
    <?php if (!empty($this->tips['question'])) : ?>
    <h4><?php echo get_lang("Question to lecturer"); ?></h4>
    <p><?php echo $this->tips['question']; ?></p>
    <?php endif; ?>
    
    <?php if (!empty($this->tips['information'])) : ?>
    <h4><?php echo get_lang("Information to give to students"); ?></h4>
    <p><?php echo $this->tips['information']; ?></p>
    <?php endif; ?>
</form>