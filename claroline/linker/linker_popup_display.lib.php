<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 * @author Renaud Fallier <renaud.claroline@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */
    /**
    * claro_linker_popup_display.lib
    *
    * is a lib of function for the display of the linker popup.
    * @package CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    **/

   /**
    * display the navigator in the popup
    *
    * @param path $baseServDir System Path to web base value
    * @param crl $current_crl the current crl of a resource
    */
    function displayNav( $baseServDir , $current_crl )
    {
         //allows to browse in a tool
        $nav = new Navigator($baseServDir, $current_crl);
        $elementCRLArray = CRLTool::parseCRL($current_crl);

         displayGeneralTitle();
         displayAttachmentList( $current_crl );
         display( $nav , $current_crl , $elementCRLArray );

        displayLinkerButtons();
    }

   /**
    * display the crl attached in the popup
    *
    * @param crl $current_crl the current crl of a resource
    */
   function displayAttachmentList($current_crl)
   {
       global $caddy;
       global $imgRepositoryWeb;

       $content = $caddy->getAttachmentList();

       if( is_array($content) && isset($content['crl']) && count( $content['crl'] ) > 0 )
       {
           echo '<hr /><b>' . get_lang('LinkerAttachements') . '</b>' . "\n";

           echo '<table style="border: 0px; font-size: 80%; width: 100%;">' . "\n";

           for($i = 0 ; $i<(count($content["crl"])) ; $i++)
           {
               echo '<tr><td>' . $content['title'][$i]
               .    '</td><td>'
               .    '<a href="' . $_SERVER['PHP_SELF']
               .    '?cmd=delete'
               .    '&amp;crl=' . $content["crl"][$i]
               .    '&amp;current_crl=' . urlencode($current_crl) . '" class="claroCmd">'
               .    '<img src="'.$imgRepositoryWeb.'/delete.gif" alt='.get_lang('Delete').'" />'
               .    '</a></td></tr>' . "\n"
               ;
           }

           echo '</table>' . "\n";

       }
       else
       {
           // AttachmentList is empty
       }
   }

   /**
    * display the link for the other course
    *
    * @param path $baseServDir
    * @param crl $current_crl
    */
    function displayInterfaceOfMyOtherCourse( $baseServDir , $current_crl )
    {
        $nav = new Navigator( $baseServDir , $current_crl );

        displayGeneralTitle();
        displayAttachmentList( $current_crl );
        displayOtherCourse( $nav , $current_crl );

        displayLinkerButtons();
    }

   /**
    * display the link for the public course
    *
    * @param path $baseServDir
    * @param crl $current_crl
    */
    function displayInterfaceOfPublicCourse( $baseServDir , $current_crl )
    {

        $nav = new Navigator( $baseServDir , $current_crl );

        displayGeneralTitle();
        displayAttachmentList( $current_crl );
        displayPublicCourse( $nav , $current_crl );

        displayLinkerButtons();
    }

    /**
    * display the tree of the current node
    *
    * @param $navigator
    * @param crl $crl
    * @global $_course a assosiatif array for the info of a course
    */
    function display( $navigator , $crl , $elementCRLArray )
    {
        $container = $navigator->getResource();
        $iterator = $container->iterator();

        echo '<div class="claroMessageBox" style="margin-top : 1em;margin-bottom : 1em;">' . "\n";

        displayOtherCoursesLink();
        displayPublicCoursesLink();
        displayExternalLink( $crl );
        displayCourseTitle( $navigator );
        displayParentLink ( $navigator );
        displayOtherInfo( $container );

        // permeat to check if the resource is empty
        $passed = FALSE;

        //--------------------------------------------------------
        // the loop of resources
        //--------------------------------------------------------
        while ($iterator->hasNext() )
        {

            if (! $passed )
            {
                $passed = TRUE;
            }

            $object = $iterator->getNext();

            if ($object->isContainer() && $object->isVisible() )
            {
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=browse'
                .    '&amp;current_crl=' . urlencode ($object->getCRL()).'">'
                .    $object->getName() . '</a>' . "\n"
                ;
            }
            else if ($object->isContainer() && !$object->isVisible() )
            {
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=browse'
                .    '&amp;current_crl=' . urlencode ($object->getCRL()) . '" class="invisible">'
                .    $object->getName() . '</a>' . "\n"
                ;
            }
            else if(!$object->isContainer() && !$object->isVisible() )
            {
                echo '<span class="invisible">' . $object->getName() . '</span>';
            }
            else
            {
                echo $object->getName();
            }

            if ($object->isLinkable() && $object->isVisible() )
            {
                echo "\t" . '&nbsp;<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=add&amp;crl=' . urlencode($object->getCRL())
                .    '&amp;current_crl=' . urlencode($crl) . '" '
                .    'class="claroCmd">'
                .    '[' . get_lang('LinkerAdd') . ']</a><br />' . "\n"
                ;
            }
            else if($object->isLinkable() && !$object->isVisible() )
            {
                echo "\t".'&nbsp;'
                .    '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=add&amp;crl=' . urlencode($object->getCRL())
                .    '&amp;current_crl=' . urlencode($crl) . '" class="claroCmd">'
                .    '[' . get_lang('LinkerAdd') . ']'
                .    '</a><br />' . "\n"
                ;
            }
            else
            {
                echo '<br />' . "\n";
            }
        }
        // if a directory is empty
        if (!$passed )
        {
            echo '&lt;&lt;&nbsp;' . get_lang('Empty') . '&nbsp;&gt;&gt;' . "\n";
        }
        echo '</div>';
    }

    /**
    * display the list the other course
    *
    * @global $platform_id  the id of the platforme
    * @throws E_USER_ERROR if it is not a array
    */
    function displayOtherCourse( $navigator , $crl )
    {
        global $platform_id;

        echo '<div class="claroMessageBox" style="margin-top : 1em;margin-bottom : 1em;">' . "\n";

        displayOtherCoursesLink( FALSE );
        displayPublicCoursesLink();
        displayExternalLink( $crl );
        echo '<br /><b>' . get_lang('LinkerMyOtherCourses') . '</b><hr />';
        displayParentLink ( $navigator , FALSE );

        $otherCourseInfo = $navigator->getOtherCoursesList();

        if( is_array($otherCourseInfo) )
        {
            foreach ($otherCourseInfo as $courseInfo )
            {
                $crl = CRLTool::createCRL($platform_id , $courseInfo['code'] );
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?fct=add'
                .    '&amp;cmd=browse'
                .    '&amp;current_crl=' . urlencode ($crl).'">'
                .    $courseInfo['fake_code'] . ' : ' . $courseInfo['intitule']
                .    '</a>' . "\n"

                .    '&nbsp;'

                .    '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=add'
                .    '&amp;crl=' . urlencode($crl)
                .    '&amp;current_crl=' . urlencode($crl) . '" class="claroCmd">'
                .    '[' . get_lang('LinkerAdd') . ']</A><br />' . "\n"
                ;
            }
        }
        else
        {
            trigger_error('Error: not an array',E_USER_ERROR);
        }

       echo '</div>';
    }


    /**
    * display the list the public course
    *
    * @global $platform_id  the id of the platforme
    * @throw E_USER_ERROR if it is not a array
    */
    function displayPublicCourse( $navigator , $crl )
    {
        global $platform_id;

        echo '<div class="claroMessageBox" style="margin-top : 1em;margin-bottom : 1em;">' . "\n";

        displayOtherCoursesLink( TRUE );
        displayPublicCoursesLink( FALSE );
        displayExternalLink( $crl );

        echo '<br /><b>' . get_lang('LinkerPublicCourses') . '</b><hr />';

        displayParentLink ( $navigator , FALSE );

        $publicCourseInfo = $navigator->getPublicCoursesList();

        if( is_array($publicCourseInfo) )
        {
            foreach ($publicCourseInfo as $courseInfo )
            {
                $crl = CRLTool::createCRL($platform_id , $courseInfo['code'] );
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?fct=add'
                .    '&amp;cmd=browse'
                .    '&amp;current_crl=' . urlencode ($crl).'">'
                .    $courseInfo['fake_code'] . ' : ' . $courseInfo['intitule']
                .    '</a>' . "\n"

                .    '&nbsp;'

                .    '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=add'
                .    '&amp;crl=' . urlencode($crl)
                .    '&amp;current_crl=' . urlencode($crl) . '" class="claroCmd">'
                .    '[' . get_lang('LinkerAdd') . ']</A><br />' . "\n"
                ;
            }
        }
        else
        {
            trigger_error('Error: not an array',E_USER_ERROR);
        }

        echo '</div>';
    }
    /**
    * display the link of the parent of the current node
    *
    * @param $navigator
    */
    function displayParentLink ( $navigator , $isLink = TRUE)
    {
        global $imgRepositoryWeb;

        $crlParent = $navigator->getParent();

        if( $isLink && $crlParent)
        {
            echo '<a href="' . $_SERVER['PHP_SELF']
            .    '?fct=add'
            .    '&amp;cmd=browse'
            .    '&amp;current_crl=' . urlencode ($crlParent) . '" class="claroCmd">'
            .    '<img src="' . $imgRepositoryWeb . 'parent.gif" border="0" alt="" />'
            .    get_lang('Up')
            .    '</a>'
            ;
        }
        else
        {
            echo '<span class="claroCmdDisabled">'
            .    '<img src="' . $imgRepositoryWeb . 'parentdisabled.gif" border="0" alt="" />'
            .    get_lang('Up')
            .    '</span>'
            ;
        }
        echo       '<br /><br />' . "\n";
    }

    /**
    * display the link of the other course
    *
    * @param $isLink boolean
    */
    function displayOtherCoursesLink( $isLink = TRUE )
    {
         if ( get_conf('otherCoursesAllowed') )
         {
             if( $isLink )
            {
                echo '<a href="' . $_SERVER['PHP_SELF']
                .    '?cmd=browseMyCourses" class="claroCmd">'
                .    get_lang('LinkerMyOtherCourses') . '</a>&nbsp;' . "\n"
                ;
            }
            else
            {
                echo '<span class="claroCmdDisabled">'
                .    get_lang('LinkerMyOtherCourses')
                .    '</span>'
                .    '&nbsp;' . "\n"
                ;
            }
        }

    }

    /**
    * display the link of the public course
    *
    * @param $isLink boolean
    */
    function displayPublicCoursesLink( $isLink = TRUE )
    {
         if ( get_conf('publicCoursesAllowed') )
         {
             if( $isLink )
            {
                echo '<a href="'.$_SERVER["PHP_SELF"].'?cmd=browsePublicCourses" class="claroCmd">';
                echo get_lang('LinkerPublicCourses')."</A>&nbsp;\n";
            }
            else
            {
                echo '<span class="claroCmdDisabled">'.get_lang('LinkerPublicCourses') . '</span> &nbsp;' . "guim\n";
            }
        }

    }

    /**
    * display the title of the course
    *
    * @param $navigator
    */
    function displayCourseTitle( $navigator )
    {
        echo '<br /><b>' . $navigator->getCourseTitle() . '</b><hr />';
    }

    /**
    * display other info for exemple in the forum tool
    * display whereiam
    *
    * @param container $container
    */
    function displayOtherInfo( $container )
    {
        if ($container->getName() != '')
        {
            echo '<h2>' . $container->getName() . ' </h2>' . "\n";
        }
    }

   /**
    * display the general title
    *
    * @global string get_lang('LinkerResourceAttachment')
    */
    function displayGeneralTitle()
    {
        echo '<h1>' . get_lang('LinkerResourceAttachment') . '</h1>';
    }

    /**
    * display the link of the external link
    *
    * @param crl $current_crl
    */
    function displayExternalLink($current_crl)
    {
         if ( get_conf('externalLinkAllowed') )
         {
            echo '<a href="http://claroline.net" class="claroCmd" '
            .    ' onclick="prompt_popup_for_external_link(\'' . $current_crl . '\');return false;">'
            .    get_lang('LinkerExternalLink') . '</a>' . "\n"
            ;
        }

    }

    /**
     * display a closer button
     *
     * @return void
     *
     */
    function displayLinkerButtons()
    {
        echo '<input type="submit" '
        .    'onclick="linker_confirm();return false;" '
        .    'value="' . get_lang('LinkerClosePopup') . '" >'
        ;
    }
?>
