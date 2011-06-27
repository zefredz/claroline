<!-- $Id$ -->

<?php echo $this->dialogBox->render(); ?>

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