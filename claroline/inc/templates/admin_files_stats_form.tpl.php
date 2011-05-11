<!-- $Id$ -->

<?php echo $this->dialogBox->render(); ?>

<p>
    <?php echo get_lang('You\'ve chosen to isolate the following extensions: %types.  If you wish to modify these extensions, check the advanced platform settings', array('%types' => implode(', ', $this->extensions))); ?><br/>
</p>

<table>
  <tr>
    <td>
        <form method="post" action="<?php echo $this->formAction; ?>">
            <input type="hidden" name="cmd" id="cmd" value="run" />
            <input type="hidden" name="viewAs" id="viewAs" value="html" />
            <input type="submit" name="changeProperties" value="<?php echo get_lang('Get HTML statistics'); ?>" />
        </form>
    </td>
    <td>
        <form method="post" action="<?php echo $this->formAction; ?>">
            <input type="hidden" name="cmd" id="cmd" value="run" />
            <input type="hidden" name="viewAs" id="viewAs" value="csv" />
            <input type="submit" name="changeProperties" value="<?php echo get_lang('Get CSV statistics'); ?>" />
        </form>
    </td>
  </tr>
</table>