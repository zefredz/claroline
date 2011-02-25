<form action="admincourses.php" method="get" >
<table border="0">
<tr>
  <td align="right">
   <label for="code"><?php echo get_lang('Administrative code'); ?></label> : <br />
  </td>
  <td colspan="3">
    <input type="text" size="40" name="code" id="code" value="<?php echo htmlspecialchars($this->code); ?>"/>
  </td>
</tr>
<tr>
  <td align="right">
   <label for="intitule"><?php echo get_lang('Course title')?></label> :  <br />
  </td>
  <td colspan="3">
    <input type="text" size="40" name="intitule"  id="intitule" value="<?php echo htmlspecialchars($this->intitule); ?>"/>
  </td>
</tr>
<tr>
  <td align="right">
   <label for="category"><?php echo get_lang('Category'); ?></label> : <br />
  </td>
  <td colspan="3">
  <?php echo claro_html_form_select( 'category'
                                 , $this->category_array
                                 , ''
                                 , array('id'=>'category'))
                                 ; ?>
</td>
</tr>
<tr>
<td align="right">
<label for="searchLang"><?php echo get_lang('Language')?></label> : <br />
</td>
<td colspan="3">
<?php echo claro_html_form_select( 'searchLang'
                                 , $this->language_list
                                 , ''
                                 , array('id'=>'searchLang'))
                                 ; ?>    </td>
</tr>
<tr>
  <td align="right">
   <?php echo get_lang('Course access') ?>   :
  </td>
  <td>
   <input type="radio" name="access" value="public"  id="access_public"  <?php if ($this->access=="public") echo "checked";?>  />
   <label for="access_public"><?php echo get_lang('Public') ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="platform" id="access_platform" <?php if ($this->access=="platform") echo "checked";?> />
    <label for="access_platform"><?php echo get_lang('Platform') ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="private" id="access_private" <?php if ($this->access=="private") echo "checked";?> />
    <label for="access_private"><?php echo get_lang('Private') ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="all"        id="access_all"     <?php if ($this->access=="all") echo "checked";?> />
    <label for="access_all"><?php echo get_lang('All') ?></label>
  </td>
</tr>
<tr>
  <td align="right">
      <?php echo get_lang('Enrolment') ?>    :
  </td>
  <td>
      <input type="radio" name="subscription" value="allowed" id="subscription_allowed" <?php if ($this->subscription=="allowed") echo "checked";?> />
      <label for="subscription_allowed"><?php echo get_lang('Allowed') ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="key"  id="subscription_key" <?php if ($this->subscription=="key") echo "checked";?> />
    <label for="subscription_key"><?php echo get_lang('Allowed with enrolment key') ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="denied"  id="subscription_denied" <?php if ($this->subscription=="denied") echo "checked";?> />
    <label for="subscription_denied"><?php echo get_lang('Denied') ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="all"  id="subscription_all" <?php if ($this->subscription=="all") echo "checked";?> />
    <label for="subscription_all"><?php echo get_lang('All') ?></label>
  </td>
</tr>
<tr>
  <td align="right">
      <?php echo get_lang('Visibility') ?>    :
  </td>
  <td>
      <input type="radio" name="visibility" value="visible" id="visibility_show" <?php if ($this->visibility=="visible") echo "checked";?> />
      <label for="visibility_show"><?php echo get_lang('Show') ?></label>
  </td>
  <td>
      <input type="radio" name="visibility" value="invisible"  id="visibility_hidden" <?php if ($this->visibility=="invisible") echo "checked";?> />
      <label for="visibility_hidden"><?php echo get_lang('Hidden') ?></label>
  </td>
  <td>
      <input type="radio" name="visibility" value="all"  id="visibility_all" <?php if ($this->visibility == "all") echo "checked";?> />
    <label for="visibility_all"><?php echo get_lang('All') ?></label>
  </td>
</tr>
<tr>
  <td>

  </td>
  <td colspan="3">
    <input type="submit" class="claroButton" value="<?php echo get_lang('Search course')?>"  />
  </td>
</tr>
</table>
</form>