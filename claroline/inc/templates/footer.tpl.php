<?php  if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<div id="campusFooter">
    <hr />
    <div id="campusFooterLeft">
        <?php include_dock('campusFooterLeft'); ?>
        <?php echo $this->courseManager;?>
    </div>
    <div id="campusFooterRight">
        <?php include_dock('campusFooterRight'); ?>
        <?php echo $this->platformManager;?>
    </div>
    <div id="campusFooterCenter">
        <?php include_dock('campusFooterCenter'); ?>
        <?php echo $this->poweredBy;?>
    </div>
</div>
<!-- end of claroPage -->
</div>