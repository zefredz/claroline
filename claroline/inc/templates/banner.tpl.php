<!-- Banner -->
<div id="topBanner">

<!-- Platform Banner -->
<div id="platformBanner">
    <div id="campusBannerLeft">
        <span id="siteName">
        <?php echo link_to_claro( $this->campus['siteName'], '', array('target' => '_top') ); ?>
        </span>
        <?php echo include_dock('campusBannerLeft'); ?>
    </div>
    <div id="campusBannerRight">
        <span id="institution"><?php echo $this->campus['institution'] ?></span>
        <?php echo include_dock('campusBannerRight'); ?>
    </div>
    <div class="spacer"></div>
</div>
<!-- End of Platform Banner -->

<?php if ( $this->userBanner ): ?>
<!-- User Banner -->
<div id="userBanner">
    <div id="userBannerLeft">
        <span id="userName">
        <?php echo get_lang( '%firstName% %lastName%'
            , array(  '%firstName%' => $this->user['firstName']
                    , '%lastName%' => $this->user['lastName'] ) ) ?> : 
        </span>
        <?php echo $this->userToolList; ?>
        <?php echo include_dock('userBannerLeft'); ?>
    </div>
    <div id="userBannerRight">
        <?php echo include_dock('userBannerRight'); ?>
    </div>

    <div class="spacer"></div>
</div>
<!-- End of User Banner -->
<?php endif; ?>

<?php if ( $this->courseBanner ): ?>
<!-- Course Banner -->
<div id="courseBanner">
    <div id="courseBannerLeft">
        <div id="course">
            <h2 id="courseName">
            <?php echo link_to_course($this->course['name']
                , $this->course['sysCode'], array('target' => '_top')); ?>
            </h2>
            <span id="courseCode">
            <?php echo "{$this->course['officialCode']} - {$this->course['titular']}"; ?>
            </span>
        </div>
        <?php echo include_dock('courseBannerLeft'); ?>
    </div>
    <div id="courseBannerRight">
        <?php echo $this->courseToolSelector; ?>
        <?php echo include_dock('courseBannerRight'); ?>
    </div>

    <div class="spacer"></div>
</div>
<!-- End of Course Banner -->
<?php endif; ?>

<?php if ( $this->breadcrumbLine ): ?>
<!-- BreadcrumbLine  -->
<div id="breadcrumbLine">
<hr />
<div class="breadcrumbTrails">
<?php echo $this->breadcrumbs->render(); ?>
</div>
<div id="toolViewOption">
<?php echo $this->viewmode->render(); ?>
</div>
<div class="spacer"></div>
<hr />
</div>
<!-- End of BreadcrumbLine  -->
<?php endif; ?>

</div>
<!-- End of Banner -->