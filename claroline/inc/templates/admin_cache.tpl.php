<!-- $Id$ -->

<?php echo claro_html_tool_title(get_lang('Cache management')); ?>

<?php echo $this->dialogBox->render(); ?>

<table class="claroTable">
    <thead>
        <tr>
            <th><?php echo get_lang( 'Path' ); ?></th>
            <th><?php echo get_lang( 'File count' ); ?></th>
            <th><?php echo get_lang( 'File size' ); ?></th>
            <th><?php echo get_lang( 'Cleanup' ); ?></th>
        </tr>
    </thead>
    <tbody>

    <?php foreach ( $this->stats as $path => $stat ): ?>

        <tr>
            <td><?php echo $path; ?></td>
            <td><?php echo $stat['count']; ?></td>
            <td><?php echo format_file_size($stat['size']); ?></td>
            <td style="text-align: center;">
                <?php if ( 0 == $stat['size'] ): ?>
                    -
                <?php else: ?>
                    <a class="checkFolderCleanup" href="<?php echo Url::Contextualize ( php_self () . '?cmd=' . $this->cmdList[$path] ); ?>"><img src="<?php echo get_icon_url('delete'); ?>" alt="<?php echo get_lang('Cleanup'); ?>" /></a>
                <?php endif; ?>
            </td>
            
        </tr>
        
    <?php endforeach; ?>
        
    </tbody>
</table>

<script type="text/javascript">
$(function(){
    $('.checkFolderCleanup').click(function(){
        return confirm("<?php echo get_lang( "You are going to delete this cache content, continue ?" ); ?>");
    });
});
</script>
