<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /*
        TODO get wikiID from $_GET
        TODO set creatorId and editorId using userId
     */

    /**
     * @version CLAROLINE 1.7
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license GENERAL PUBLIC LICENSE (GPL)
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
     
    $tlabelReq = 'CLWIKI__';

    require_once "../inc/claro_init_global.inc.php";
    require_once $includePath . "/conf/CLWIKI.conf.php";
    
    // check and set user access level for the tool
    
    if ( is_null( $_cid ) )
    {
        if ( is_null( $_uid ) )
        {
            claro_disp_auth_form();
        }
        else
        {

            claro_disp_select_course();
        }
    }
    
    // set admin mode and groupId
    
    $is_allowedToAdmin = false;

    if ( $_gid && $is_groupAllowed )
    {
        // group context
        $grouId = $_gid;

        $is_allowedToAdmin = $is_groupTutor || $is_courseAdmin || $is_platformAdmin;
    }
    elseif ( $_gid && ! $is_groupAllowed )
    {
        die( "<center>You are not allowed to see this group's wiki !!!</center>" );
    }
    else
    {
        // course context
        $groupId = 0;

        $is_allowedToAdmin = $is_courseAdmin || $is_platformAdmin;
    }
    
    // unquote GPC if magic quote gpc enabled
    
    claro_unquote_gpc();
    
    // classes and libraries
    
    require_once "lib/class.clarodbconnection.php";
    require_once "lib/class.wiki2xhtmlrenderer.php";
    require_once "lib/class.wikipage.php";
    require_once "lib/class.wikistore.php";
    require_once "lib/class.wiki.php";
    require_once "lib/lib.requestfilter.php";
    require_once "lib/lib.wiki.php";
    require_once "lib/lib.wikisql.php";
    require_once "lib/lib.wikidisplay.php";
    require_once "lib/lib.javascript.php";
    
    // set request variables
    
    $wikiId = ( isset( $_REQUEST['wikiId'] ) ) ? (int) $_REQUEST['wikiId'] : 0;
    
    // Database nitialisation
    
    $tblList = claro_sql_get_course_tbl();
    $config = array();
    $config["tbl_wiki_properties"] = $tblList[ "wiki_properties" ];
    $config["tbl_wiki_pages"] = $tblList[ "wiki_pages" ];
    $config["tbl_wiki_pages_content"] = $tblList[ "wiki_pages_content" ];
    $config["tbl_wiki_acls"] = $tblList[ "wiki_acls" ];

    $con = new ClarolineDatabaseConnection();
    
    // DEVEL_MODE database initialisation
    // DO NOT FORGET TO REMOVE FOR PROD !!!
    if( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) )
    {
        init_wiki_tables( $con, false );
    }
    
    // Objects instantiation
    
    $wikiStore = new WikiStore( $con, $config );
    
    if ( ! $wikiStore->wikiIdExists( $wikiId ) )
    {
        die ( $langWikiInvalidWikiId );
    }
    
    $wiki = $wikiStore->loadWiki( $wikiId );
    $wikiPage = new WikiPage( $con, $config, $wikiId );
    $wikiRenderer = new Wiki2xhtmlRenderer( $wiki );
    
    $accessControlList = $wiki->getACL();
    
    // --------------- Start of access rights management --------------
    
    // Wiki access levels
    
    $is_allowedToEdit = false;
    $is_allowedToRead = false;
    $is_allowedToCreate = false;
    
    // set user access rights using user status and wiki access control list

    if ( $_gid && $is_groupAllowed )
    {
        // group_context
        if ( is_array( $accessControlList ) )
        {
            $is_allowedToRead = $is_allowedToAdmin
                || ( $is_groupMember && WikiAccessControl::isAllowedToReadPage( $accessControlList, 'group' ) )
                || ( $is_courseMember && WikiAccessControl::isAllowedToReadPage( $accessControlList, 'course' ) )
                || WikiAccessControl::isAllowedToReadPage( $accessControlList, 'other' );
            $is_allowedToEdit = $is_allowedToAdmin
                || ( $is_groupMember && WikiAccessControl::isAllowedToEditPage( $accessControlList, 'group' ) )
                || ( $is_courseMember && WikiAccessControl::isAllowedToEditPage( $accessControlList, 'course' ) )
                || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'other' );
            $is_allowedToCreate = $is_allowedToAdmin
                || ( $is_groupMember && WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'group' ) )
                || ( $is_courseMember && WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'course' ) )
                || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'other' );
        }

    }
    else
    {
        // course context
        if ( is_array( $accessControlList ) )
        {
            // course member
            if ( $is_courseMember )
            {
                $is_allowedToRead = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToReadPage( $accessControlList, 'course' );
                $is_allowedToEdit = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'course' );
                $is_allowedToCreate = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'course' );
            }
            // not a course member
            else
            {
                $is_allowedToRead = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToReadPage( $accessControlList, 'other' );
                $is_allowedToEdit = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'other' );
                $is_allowedToCreate = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'other' );
            }
        }
    }
    
    // --------------- End of  access rights management ----------------
    
    // filter action

    if ( $is_allowedToEdit || $is_allowedToCreate )
    {
        $valid_actions = array( "edit", "preview", "save"
            , "show", "restore", "diff", "history"
            );
    }
    else
    {
        $valid_actions = array( "show", "history", "diff" );
    }

    $_CLEAN = filter_by_key( 'action', $valid_actions, "R", true );
    
    $action = ( isset( $_CLEAN['action'] ) ) ? $_CLEAN['action'] : 'show';
    
    // set request variables
    
    $creatorId = $_uid; // $_uid

    $title = ( isset( $_REQUEST['title'] ) ) ? strip_tags( $_REQUEST['title'] ) : '';
    
    if ( $action == "edit" )
    {
        if ( isset( $_REQUEST['content'] ) )
        {
            $content = ( $_REQUEST['content'] == '' ) ? "__CONTENT__EMPTY__" : $_REQUEST['content'];
        }
        else
        {
            $content = '';
        }
    }
    else
    {
        $content = ( isset( $_REQUEST['content'] ) ) ? strip_tags( $_REQUEST['content'] ) : '';
    }
    
    // use __MainPage__ if empty title

    if ( $title === '' )
    {
        // create wiki main page in a localisation compatible way
        $title = '__MainPage__';
        
        if( $wikiStore->pageExists( $wikiId, $title ) )
        {
            // do nothing
        }
        // TODO : remove
        elseif ( ( ! $wikiStore->pageExists( $wikiId, $title ) )
            && ( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) ) )
        {
            init_wiki_main_page( $con, $wikiId, $creatorId );
        }
        else
        {
            // something weird's happened
            die ( "Missing page title" );
        }
    }
    
    // --------- Start of wiki command processing ----------
    
    $message = '';
    
    switch( $action )
    {
        // edit page content
        case "edit":
        {
            if( $wikiStore->pageExists( $wikiId, $title ) )
            {
                $wikiPage->loadPage( $title );
                
                if ( $content == '' )
                {
                    $content = $wikiPage->getContent();
                }
                
                if  ( $content == "__CONTENT__EMPTY__" )
                {
                    $content = '';
                }

                $title = $wikiPage->getTitle();
            }
            else
            {
                $content = '';
                $message = "This page is empty, use the editor to add content.";
            }
            break;
        }
        // view page
        case "show":
        {
            if( $wikiStore->pageExists( $wikiId, $title ) )
            {
                $wikiPage->loadPage( $title );

                $content = $wikiPage->getContent();

                $title = $wikiPage->getTitle();
            }
            else
            {
                $message = "Page " . $title . " not found";
            }
            break;
        }
        // save page
        case "save":
        {
            if ( isset( $content ) )
            {
                $time = date( "Y-m-d H:i:s" );

                $contentpg = addslashes( $content );

                if ( $wikiPage->pageExists( $title ) )
                {
                    $wikiPage->loadPage( $title );
                    $wikiPage->edit( $creatorId, $contentpg, $time, true );
                }
                else
                {
                    $wikiPage->create( $creatorId, $title, $contentpg, $time, true );
                }

                if ( $wikiPage->hasError() )
                {
                    die ( "Database error : " . $wikiPage->getError() );
                }
                else
                {
                    $message = $langWikiPageSaved;
                }
            }
            
            $action = 'show';
            
            break;
        }
        // page history
        case "history":
        {
            $wikiPage->loadPage( $title );
            $title = $wikiPage->getTitle();
            $history = $wikiPage->history();
            break;
        }
        default:
        {
        }
    }
    
    // --------- End of wiki command processing -----------
    
    // change to use empty page content
    
    if ( ! isset( $content ) )
    {
        $content = '';
    }

    // FIXME get help string use an help file instead
    $help = $wikiRenderer->help();
    $help = preg_replace( '~<li>(.*?)</li>~', '<li>\1</li>' . "\n", $help );
    
    // set xtra head
    
    $jspath = document_web_path() . '/lib/javascript';

    $htmlHeadXtra[] = "<script type=\"text/javascript\">"
        . "\nvar sLangWikiShowHelp = '".addslashes($langWikiShowHelp) . "'"
        . "\nvar sLangWikiHideHelp = '".addslashes($langWikiHideHelp) . "'"
        . "\nvar sLangWikiExampleWarning = '".addslashes($langWikiExampleWarning) . "'"
        . "\nvar sLangWikiFullDemoText = '".get_demo_text() . "'"
        . "\nvar sImgPath = '".$imgRepositoryWeb . "'"
        . "\n</script>\n"
        ;
    $htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"".$jspath."/wiki_help.js\"></script>\n";
    
    // TODO : MOVE to CSS
    $htmlHeadXtra[] =
        "<style type=\"text/css\">
        .wikiTitle h1{
            color: Black;
            background: none;
            font-size: 200%;
            font-weight: bold;
            /*font-weight: normal;*/
            border-bottom: 2px solid #aaaaaa;
        }
        .wikiTitle p.wikiPreview{
            padding: 2px 2px 2px 2px;
            width: 50%;
            background-color: red;
        }
        .wikiTitle h1.wikiPreview
        {
            background-color: red;
            border: 0;
        }
        .wikiTitle p.wikiPreview{
            padding: 2px 2px 2px 2px;
            width: 50%;
            background-color: red;
        }
        .wikiTitle h1.wikiPreview
        {
            background-color: red;
            border: 0;
        }
        .wiki2xhtml h2,h3,h4{
            color: Black;
            background: none;
        }
        .wiki2xhtml h2{
            border-bottom: 1px solid #aaaaaa;
            font-size:175%;
            font-weight:bold;
        }
        .wiki2xhtml h3{
            border-bottom: 1px groove #aaaaaa;
            font-size:150%;
            font-weight:bold;
        }
        .wiki2xhtml h4{
            font-size:125%:
            font-weight:bold;
        }
        
        .wiki2xhtml a.wikiEdit{
            color: red;
        }
        </style>"
        ;
        
    // Breadcrumps
    
    $interbredcrump[]= array ("url"=>"wiki.php", 'name'=> $langWiki);
    $interbredcrump[]= array ("url"=>"wiki.php?action=show&amp;wikiId=" . $wikiId
        , 'name'=> $wiki->getTitle() );
        
    switch( $action )
    {
        case "edit":
        {
            $dispTitle = ( $title == "__MainPage__" ) ? $langWikiMainPage : $title;
            $interbredcrump[]= array ("url"=>"page.php?action=show&amp;wikiId="
                . $wikiId . "&amp;title=" . $title
                , 'name'=> $dispTitle );
            $nameTools = $langEdit;
            $noPHP_SELF = true;
            break;
        }
        default:
        {
            $nameTools = ( $title == "__MainPage__" ) ? $langWikiMainPage : $title ;
            $noPHP_SELF = true;
        }
    }
    
    // Claroline Header and Banner

    require_once "../inc/claro_init_header.inc.php";
    
    if ( !empty($message) )
    {
        echo claro_disp_message_box($message);
    }
    
    echo claro_disp_tool_title( sprintf( $langWikiTitlePattern, $wiki->getTitle() ), false );
    
    // Check javascript
    
    $javascriptEnabled = claro_is_javascript_enabled();
    
    // --------- Start of wiki display ---------------
    
    // user is not allowed to read this page
    
    if ( ! $is_allowedToRead )
    {
        echo "You are not allowed here !!!";
        
        require_once "../inc/claro_init_footer.inc.php";
        
        die ( '' );
    }
    
    // Wiki navigation bar
    
    echo '<p>';


    echo '<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=show'
        . '&amp;title=__MainPage__'
        . '">'.$langWikiMainPage.'</a>'
        ;
        
    if ( $is_allowedToEdit || $is_allowedToCreate )
    {
        echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=edit'
        . '&amp;title=' . urlencode( $title )
        . '">'
        . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />'
        . $langWikiEditPage.'</a>'
        ;
    }
    else
    {
        echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
            . $langWikiEditPage . '</span>'
            ;
    }
    
    echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
        . $langWikiPageHistory . '</span>'
        ;
        
    echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
        . $langWikiRecentChanges . '</span>'
        ;
        
    echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
        . $langWikiAllPages . '</span>'
        ;

    echo '</p>' . "\n";
    
    switch( $action )
    {
        // edit page
        case "edit":
        {
            if ( ! $wiki->pageExists( $title ) && ! $is_allowedToCreate )
            {
                echo "You are not allowed to create wiki pages";
            }
            else
            {
                $script = $_SERVER['PHP_SELF'];
                // TODO move to config
                // $showWikiEditorToolbar = true;
                // TODO move to config
                // $forcePreviewBeforeSaving = true;

                echo claro_disp_wiki_editor( $wikiId, $title, $content, $script
                    , $showWikiEditorToolbar, $forcePreviewBeforeSaving );
                    
                echo claro_disp_wiki_help( $help, $javascriptEnabled );
            }

            break;
        }
        // page preview
        case "preview":
        {
            if ( ! isset( $content ) )
            {
                $content = '';
            }

            echo claro_disp_wiki_preview( $wikiRenderer, $title, $content );
            
            echo claro_disp_wiki_preview_buttons( $wikiId, $title, $content );

            break;
        }
        // view page
        case "show":
        {
            if( $wikiPage->hasError() )
            {
                echo $wikiPage->getError();
            }
            else
            {
                // get localized value for wiki main page title
                if( $title === '__MainPage__' )
                {
                    $displaytitle = $langWikiMainPage;
                }
                else
                {
                    $displaytitle = $title;
                }
                
                echo '<div class="wikiTitle">' . "\n";
                echo '<h1>'.$displaytitle.'</h1>' . "\n";
                echo '</div>' . "\n";
                
                echo '<div class="wiki2xhtml">' . "\n";
                echo $wikiRenderer->render( $content );
                echo '</div>' . "\n";
            }

            break;
        }
        // show differences
        case "diff":
        {
            break;
        }
        // restore page
        case "restore":
        {
            break;
        }
        // page history
        case "history":
        {
            // page title
            // list{date, " by " + user} in $history + link to show using wiki.title and version.id
            break;
        }
        default:
        {
            trigger_error( "Invalid action supplied to " . $_SERVER['PHP_SELF']
                , E_USER_ERROR
                );
        }
    }
    
    // ------------ End of wiki script ---------------

    // Claroline footer
    
    require_once "../inc/claro_init_footer.inc.php";
?>