<!-- $Id$ -->

<?php echo $this->dialogBox->render(); ?>

<p>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?view_as=csv"><?php echo get_lang('Export into CSV'); ?></a>
</p>

<table class="claroTable emphaseLineemphaseLine">
<thead>
  <tr>
    <th><?php echo get_lang('Course code'); ?></th>
    <th><?php echo get_lang('Course title'); ?></th>
    <th><?php echo get_lang('Lecturer(s)'); ?></th>
    <th><?php echo get_lang('Category'); ?></th>
    <?php
    foreach ($this->allExtensions as $ext) :
    ?>
       <th colspan="2"><?php echo get_lang($ext); ?></th>
    <?php
    endforeach;
    ?>
  </tr>
  <tr>
    <th> </th>
    <th> </th>
    <th> </th>
    <th> </th>
    <?php
    foreach ($this->allExtensions as $ext) :
    ?>
       <th><?php echo get_lang('Nb'); ?></th>
       <th><?php echo get_lang('Size'); ?></th>
    <?php
    endforeach;
    ?>
  </tr>
</thead>
<tbody>
  <?php
  foreach ($this->stats as $courseCode => $courseInfos) :
  ?>
     <tr>
        <td style="font-weight: bold;"><?php echo $courseCode; ?></td>
        <td><?php echo $courseInfos['courseTitle']; ?></td>
        <td><?php echo $courseInfos['courseTitulars']; ?></td>
         <td><?php echo $courseInfos['courseCategory']; ?></td>
        <?php
        foreach ($courseInfos['courseStats'] as $courseStats) :
        ?>
            <td><?php echo $courseStats['count']; ?></td>
            <td><?php echo format_bytes($courseStats['size']); ?></td>
        <?php
        endforeach;
        ?>
    </tr>
  <?php
  endforeach;
  ?>
</tbody>
</table>