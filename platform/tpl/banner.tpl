<!-- Banner -->
<div id="topBanner">

<!-- Platform Banner -->
<div id="platformBanner">
    <div id="campusBannerLeft">
        <span id="siteName"><a href="%path(url)%/index.php" target="_top">%banner.siteName%</a></span>
        %dock(campusBannerLeft)%
    </div>
    <div id="campusBannerRight">
        <span id="institution">%banner.institution%</span>
        %dock(campusBannerRight)%
    </div>
    <div class="spacer"></div>
</div>
<!-- End of Platform Banner -->

<!-- %block(userBanner):% -->
<!-- User Banner -->
<div id="userBanner">
    <div id="userBannerLeft">
        <span id="userName">%user.firstName% %user.lastName% : </span>
        %userToolList%
        %dock(userBannerLeft)%
    </div>
    <div id="userBannerRight">
        %dock(userBannerRight)%
    </div>

    <div class="spacer"></div>
</div>
<!-- End of User Banner -->
<!-- %end(userBanner)% -->

<!-- %block(courseBanner):% -->
<!-- Course Banner -->
<div id="courseBanner">
    <div id="courseBannerLeft">
        <div id="course">
            <h2 id="courseName">
            <a href="%path(clarolineRepositoryWeb)%course/index.php?cid=%html(course[sysCode])%" target="_top">
            %course[name]%
            </a>
            </h2>
            <span id="courseCode">%course[officialCode]% - %course[titular]%</span>
        </div>
        %dock(courseBannerLeft)%
    </div>
    <div id="courseBannerRight">
        %course.toolSelector%
        %dock(courseBannerRight)%
    </div>

    <div class="spacer"></div>
</div>
<!-- End of Course Banner -->
<!-- %end(courseBanner)% -->

<!-- Breadcrumps  -->
%breadcrumps%
<!-- End of Breadcrumps  -->

</div>
<!-- End of Banner -->