<!-- $Id$ -->

<p>
    <?php echo get_lang('Vous avez choisi d\'isoler les fichiers de type %types', array('%types' => implode(', ', $this->extensions))); ?><br/>
</p>
<p>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?view_as=csv">Exporter en CSV</a>
</p>

<table class="claroTable emphaseLineemphaseLine">
<thead>
  <tr class="headerX">
    <th><?php echo get_lang('Course code'); ?></th>
    <th><?php echo get_lang('Course title'); ?></th>
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
        <td style="font-weight: bold;"><?php echo $courseInfos['courseTitle']; ?></td>
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