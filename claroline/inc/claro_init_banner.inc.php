<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

$clarolineBannerOutput = "\n\n"
. '<!-- - - - - - - - - - -   Claroline Banner  - - - - - - - - - -  -->' . "\n"
. '<div id="topBanner">' . "\n\n"
. '<div id="platformBanner">' . "\n";

//CAMPUS BANNER LEFT DOCK declaration

$campusBannerLeftDock = new Dock('campusBannerLeft');
$bannerSiteName =  get_conf('siteLogo') != ''
                ? '<img src="' . get_conf('siteLogo') . '" alt="'.get_conf('siteName').'" >'
                : get_conf('siteName');
$siteNameOutput   = '<span id="siteName"><a href="'.$urlAppend.'/index.php" target="_top">'.$bannerSiteName.'</a></span>' . "\n";
$campusBannerLeftDock->addOutput($siteNameOutput);

$clarolineBannerOutput .= $campusBannerLeftDock->render();

//CAMPUS BANNER RIGHT DOCK declaration

$campusBannerRightDock = new Dock('campusBannerRight');
$institutionNameOutput = '';

$bannerInstitutionName = (get_conf('institutionLogo') != '')
                       ? '<img src="' . get_conf('institutionLogo') . '" alt="'.get_conf('institution_name').'" >'
                       : get_conf('institution_name')
                       ;


if( !empty($bannerInstitutionName) )
{
    if( !empty($institution_url) )
        $institutionNameOutput .= '<a href="'
            .$institution_url.'" target="_top">'
            .$bannerInstitutionName.'</a>'
            ;
    else
        $institutionNameOutput .= $bannerInstitutionName;
}

/* --- External Link Section --- */
if( !empty($_course['extLinkName']) )
{
    $institutionNameOutput .= get_conf('institution_name') != ''
        ? ' / '
        : ' '
        ;

    if( !empty($_course['extLinkUrl']) )
    {
        $institutionNameOutput .= '<a href="'
            . $_course['extLinkUrl'] . '" target="_top">'
            . $_course['extLinkName']
            . '</a>'
            ;
    }
    else
    {
        $institutionNameOutput .= $_course['extLinkName'];
    }
}

$institutionNameOutput = '<span id="institution">'
    . $institutionNameOutput
    . '</span>' . "\n"
    ;

$campusBannerRightDock->addOutput($institutionNameOutput);

$clarolineBannerOutput .= $campusBannerRightDock->render();

$clarolineBannerOutput .= '<div class="spacer"></div>' . "\n\n"
. '</div>' . "\n"
. '<!-- end of platformBanner -->' . "\n\n";

/******************************************************************************
                                  USER SECTION
 ******************************************************************************/


if(claro_get_current_user_id())
{
    $clarolineBannerOutput .= '<div id="userBanner">' . "\n";

    $userToolUrlList = array();
    //USER BANNER LEFT DOCK declaration

    $userBannerLeftDock = new Dock('userBannerLeft');

    $userNameOutput = '<span id="userName">'. $_user ['firstName'] . ' ' . $_user ['lastName'] .' : </span>';
    $userBannerLeftDock->addOutput($userNameOutput);

    $userToolUrlList[]= '<a href="'. $urlAppend.'/index.php" target="_top">'. get_lang('My course list').'</a>';
    $userToolList = claro_get_user_tool_list();

    foreach ($userToolList as $userTool)
    {
        $userToolUrlList[] = '<a href="'. get_module_url('CLCAL') . '/' . $userTool['entry'] . '" target="_top">'. get_lang('My calendar').'</a>';
    }

    $userToolUrlList[]  = '<a href="'. $clarolineRepositoryWeb. 'auth/profile.php" target="_top">'. get_lang('My User Account').'</a>';

    if(claro_is_platform_admin())
    {
        $userToolUrlList[] = '<a href="'. $clarolineRepositoryWeb.'admin/" target="_top">'. get_lang('Platform Administration'). '</a>';
    }

    $userToolUrlList[] = '<a href="'. $urlAppend.'/index.php?logout=true" target="_top">'. get_lang('Logout').'</a>';

    $userBannerLeftDock->addOutput(claro_html_menu_horizontal($userToolUrlList));
    $clarolineBannerOutput .= $userBannerLeftDock->render();

    //USER BANNER RIGHT DOCK declaration

    $userBannerRightDock = new Dock('userBannerRight');

    $clarolineBannerOutput .= $userBannerRightDock->render();

	$clarolineBannerOutput .= "\n" . '<div class="spacer"></div>' . "\n\n"
	. '</div>' . "\n"
	. '<!-- end of userBanner -->' . "\n\n";

} // end if _uid

/******************************************************************************
                              COURSE SECTION
 ******************************************************************************/

if (claro_is_in_a_course())
{

    //COURSE BANNER LEFT DOCK declaration

    /*------------------------------------------------------------------------
                         COURSE TITLE, CODE & TITULARS
      ------------------------------------------------------------------------*/

    $courseBannerLeftDock = new Dock('courseBannerLeft');

    $clarolineBannerOutput .= '<div id="courseBanner">' . "\n";

    $courseName = '<div id="course">' . "\n"
	. '<h2 id="courseName"><a href="'. $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars(claro_get_current_course_id()) . '" target="_top">'.$_course['name'] .'</a></h2>' . "\n";
    $courseBannerLeftDock->addOutput($courseName);

    $courseCodeDisplay = '<span id="courseCode">'. $_course['officialCode'] . ' - ' . $_course['titular'] . '</span>' . "\n"
    . '</div>' . "\n";

    $courseBannerLeftDock->addOutput($courseCodeDisplay);

    $clarolineBannerOutput .= $courseBannerLeftDock->render();


    //COURSE BANNER LEFT DOCK declaration

    $courseBannerRightDock = new Dock('courseBannerRight');

    /*------------------------------------------------------------------------
                             COURSE TOOLS SELECTOR
      ------------------------------------------------------------------------*/

    /*
     * Language initialisation of the tool names
     */
    if (is_array($_courseToolList) && $is_courseAllowed)
    {
        $toolNameList = claro_get_tool_name_list();

        foreach($_courseToolList as $_courseToolKey => $_courseToolDatas)
        {

            if (isset($_courseToolDatas['name']) && !is_null($_courseToolDatas['name']) && isset($_courseToolDatas['label']))
            {
                $_courseToolList[ $_courseToolKey ] [ 'name' ] = $toolNameList[ $_courseToolDatas['label'] ];
            }
            else
            {
                $external_name = $_courseToolList[ $_courseToolKey ] [ 'external_name' ] ;
                $_courseToolList[ $_courseToolKey ] [ 'name' ] = get_lang($external_name);
            }
            // now recheck to be sure the value is really filled before going further
            if ($_courseToolList[ $_courseToolKey ] [ 'name' ] =='')
                $_courseToolList[ $_courseToolKey ] [ 'name' ] = get_lang('No name');
        }
        $courseToolSelector = '<form action="'.$clarolineRepositoryWeb.'redirector.php" name="redirector" method="POST">' . "\n"
        . '<select name="url" size="1" onchange="top.location=redirector.url.options[selectedIndex].value" >' . "\n\n";

        $courseToolSelector .= '<option value="' . $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars(claro_get_current_course_id()) .'" style="padding-left:22px;background:url('.$imgRepositoryWeb.'course.gif) no-repeat">' . get_lang('Course Home') . '</option>' . "\n";

        if (is_array($_courseToolList))
        {
            foreach($_courseToolList as $_courseToolKey => $_courseToolData)
            {
                //find correct url to access current tool

                if (isset($_courseToolData['url']))
                {
                    if (!empty($_courseToolData['label']))
                    $_courseToolData['url'] = get_module_url($_courseToolData['label']) . '/' . $_courseToolData['url'];
                    // reset group to access course tool

                    if (claro_is_in_a_group() && !$_courseToolData['external'])
                    $_toolDataUrl = strpos($_courseToolData['url'], '?') !== false
                        ? $_courseToolData['url'] . '&amp;gidReset=1'
                        : $_courseToolData['url'] . '?gidReset=1'
                        ;
                    else $_toolDataUrl = $_courseToolData['url'];

                }

                //find correct url for icon of the tool

                if (isset($_courseToolData['icon']))
                {
                    $_toolIconUrl = $imgRepositoryWeb.$_courseToolData['icon'];
                }

                // select "groups" in group context instead of tool
                if ( claro_is_in_a_group() )
                {
                    $toolSelected = $_courseToolData['label'] == 'CLGRP___' ? 'selected="selected"' : '';
                }
                else
                {
                    $toolSelected = $_courseToolData['id'] == claro_get_current_tool_id() ? 'selected="selected"' : '';
                }

                $_courseToolDataName = $_courseToolData['name'];
                $courseToolSelector .= '<option value="' . $_toolDataUrl . '" '
                .   $toolSelected
                .   'style="padding-left:22px;background:url('.$_toolIconUrl.') no-repeat">'
                .    get_lang($_courseToolDataName)
                .    '</option>'."\n"
                ;
            }
        } // end if is_array _courseToolList
        $courseToolSelector .= "\n"
		. '</select>' . "\n"
		. '<noscript>' . "\n"
		. '<input type="submit" name="gotool" value="go">' . "\n"
		. '</noscript>' . "\n"
		. '</form>' . "\n\n";

        $courseBannerRightDock->addOutput($courseToolSelector);

    } // end if is_array($courseTooList) && $isCourseAllowed

    $clarolineBannerOutput .= $courseBannerRightDock->render();

	$clarolineBannerOutput .= "\n".'<div class="spacer"></div>' . "\n\n"
	. '</div>' . "\n"
	. '<!-- end of courseBanner -->' . "\n\n";
} // end if _cid

$clarolineBannerOutput .= '</div>' . "\n"
. '<!-- - - - - - - - - - -  End of Banner  - - - - - - - - - -  -->' . "\n\n";

/******************************************************************************
                                BREADCRUMB LINE
 ******************************************************************************/

if( claro_is_in_a_course() || isset($nameTools) || ( isset($interbredcrump) && is_array($interbredcrump) ) )
{
        $clarolineBannerOutput .= '<div id="breadcrumbLine">' . "\n\n"
        . '<hr />' . "\n";

        $breadcrumbUrlList = array();
        $breadcrumbNameList = array();

        $breadcrumbUrlList[]  = $urlAppend . '/index.php';
        $breadcrumbNameList[] = $siteName;

        if ( claro_is_in_a_course() )
        {
            $breadcrumbUrlList[]  = $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars(claro_get_current_course_id());
            $breadcrumbNameList[] = $_course['officialCode'];
        }

        if ( claro_is_in_a_group() )
        {
            $breadcrumbUrlList[]  = get_module_url('CLGRP') . '/index.php?cidReq=' . htmlspecialchars(claro_get_current_course_id());
            $breadcrumbNameList[] = get_lang('Groups');
            $breadcrumbUrlList[]  = get_module_url('CLGRP') . '/group_space.php?cidReq=' . htmlspecialchars(claro_get_current_course_id()).'&gidReq=' . (int) claro_get_current_group_id();
            $breadcrumbNameList[] = claro_get_current_group_data('name');
        }

        if (isset($interbredcrump) && is_array($interbredcrump) )
        {
            while ( (list(,$bredcrumpStep) = each($interbredcrump)) )
            {
                $breadcrumbUrlList[] = $bredcrumpStep['url'];
                $breadcrumbNameList[] = $bredcrumpStep['name'];
            }
        }

        if (isset($nameTools) && !(isset($course_homepage) && $course_homepage == TRUE))
        {
            $breadcrumbNameList[] = $nameTools;

            if (isset($noPHP_SELF) && $noPHP_SELF)
            {
                $breadcrumbUrlList[] = null;
            }
            elseif ( isset($noQUERY_STRING) && $noQUERY_STRING)
            {
                $breadcrumbUrlList[] = $_SERVER['PHP_SELF'];
            }
            else
            {
                // set Query string to empty if not exists
                if (!isset($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING'] = '';
                $breadcrumbUrlList[] = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
            }
        }

        $clarolineBannerOutput .= claro_html_breadcrumbtrail($breadcrumbNameList, $breadcrumbUrlList,
                                        ' &gt; ', $imgRepositoryWeb . 'home.gif');

    if ( ! claro_is_user_authenticated() )
    {
        $clarolineBannerOutput .= "\n".'<div id="toolViewOption" style="padding-right:10px">'
            .'<a href="'.$clarolineRepositoryWeb.'auth/login.php'
            .'?sourceUrl='.urlencode( (isset( $_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on'||$_SERVER['HTTPS']==1) ? 'https://' : 'http://'). $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']). '" target="_top">'
            .get_lang('Login')
            .'</a>'
            .'</div>'."\n";
    }
    elseif ( claro_is_in_a_course() && ! claro_is_course_member() && $_course['registrationAllowed'] && ! claro_is_platform_admin() )
    {
        $clarolineBannerOutput .= '<div id="toolViewOption">'
        .    '<a href="' . $clarolineRepositoryWeb . 'auth/courses.php?cmd=exReg&course='.claro_get_current_course_id().'">'
        .     '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt=""> '
        .    '<b>'.get_lang('Enrolment').'</b>'
        .    '</a>'
        .    '</div>' . "\n";
    }
    elseif ( claro_is_display_mode_available() )
    {
        $clarolineBannerOutput .= "\n".'<div id="toolViewOption">'                    ."\n";

        if ( isset($_REQUEST['View mode']) )
        {
            $clarolineBannerOutput .= claro_disp_tool_view_option($_REQUEST['View mode']);
        }
        else
        {
            $clarolineBannerOutput .= claro_disp_tool_view_option();
        }

        if ( claro_is_platform_admin() && ! claro_is_course_member() )
        {
            $clarolineBannerOutput .= ' | <a href="' . $clarolineRepositoryWeb . 'auth/courses.php?cmd=exReg&course='.claro_get_current_course_id().'">'
            .     '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt=""> '
            .    '<b>'.get_lang('Enrolment').'</b>'
            .    '</a>'
            ;
        }

        $clarolineBannerOutput .= "\n".'</div>'                                       ."\n";
    }


    $clarolineBannerOutput .= '<div class="spacer"></div>'                       ."\n"
    .    '<hr />'                                           ."\n"
    .    '</div>' . "\n";

} // end if isset(claro_get_current_course_id()) isset($nameTools) && is_array($interbredcrump)
else
{
    // $clarolineBannerOutput .= '<div style="height:1em"></div>';
}

$clarolineBannerOutput .= '<!-- - - - - - - - - - -  End of Claroline Banner  - - - - - - - - - - -->' . "\n";

if ( get_conf('claro_brailleViewMode',false))
{
    $claro_banner = $clarolineBannerOutput;
}
else
{
    echo $clarolineBannerOutput;
    $claro_banner = false;
}
?>