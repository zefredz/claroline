<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    /**
     * CLAROLINE
     *
     * @version 1.8 $Revision$
     *
     * @copyright 2001-2006 Universite catholique de Louvain (UCL)
     *
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
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
    
    if ( ! $is_toolAllowed )
    {
        if ( is_null( $_cid ) )
        {
            claro_disp_auth_form( true );
        }
        else
        {
            claro_die(get_lang("Not allowed"));
        }
    }
    
    // if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
    
    // config file
    require_once $includePath . "/conf/CLWIKI.conf.php";
    
    // check and set user access level for the tool
    
    if ( ! isset( $_REQUEST['wikiId'] ) )
    {
        header( "Location: wiki.php" );
        exit();
    }
    
    // set admin mode and groupId
    
    $is_allowedToAdmin = claro_is_allowed_to_edit();
    

    if ( $_gid && $is_groupAllowed )
    {
        // group context
        $grouId = $_gid;
        
        $interbredcrump[]  = array ('url' => '../group/group.php', 'name' => get_lang("Groups"));
        $interbredcrump[]= array ('url' => '../group/group_space.php', 'name' => $_group['name']);
    }
    elseif ( $_gid && ! $is_groupAllowed )
    {
        claro_die(get_lang("Not allowed"));
    }
    elseif ( $is_courseAllowed )
    {
        // course context
        $groupId = 0;
    }
    else
    {
        claro_disp_auth_form();
    }
    
    // Wiki specific classes and libraries
    
    require_once "lib/class.clarodbconnection.php";
    require_once "lib/class.wiki2xhtmlrenderer.php";
    require_once "lib/class.wikipage.php";
    require_once "lib/class.wikistore.php";
    require_once "lib/class.wiki.php";
    require_once "lib/class.wikisearchengine.php";
    require_once "lib/lib.requestfilter.php";
    require_once "lib/lib.wikisql.php";
    require_once "lib/lib.wikidisplay.php";
    require_once "lib/lib.javascript.php";

    // security fix : disable access to other groups wiki
    if ( isset( $_REQUEST['wikiId'] ) )
    {
        $wikiId = (int) $_REQUEST['wikiId'];

        // Database nitialisation

        $tblList = claro_sql_get_course_tbl();

        $con = new ClarolineDatabaseConnection();

        $sql = "SELECT `group_id` "
            . "FROM `" . $tblList[ "wiki_properties" ] . "` "
            . "WHERE `id` = " . $wikiId
            ;

        $result = $con->getRowFromQuery( $sql );
        
        $wikiGroupId = (int) $result['group_id'];

        if ( isset( $_gid ) && $_gid != $wikiGroupId )
        {
            claro_die(get_lang("Not allowed"));
        }
        elseif( !isset( $_gid ) && $result['group_id'] != 0 )
        {
            claro_die(get_lang("Not allowed"));
        }
    }
    
    // Claroline libraries
    
    require_once $includePath . '/lib/user.lib.php';
    
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
    
    // auto create wiki in devel mode
    if ( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) )
    {
        init_wiki_tables( $con, false );
    }
    
    // Objects instantiation
    
    $wikiStore = new WikiStore( $con, $config );
    
    if ( ! $wikiStore->wikiIdExists( $wikiId ) )
    {
        die ( get_lang("Invalid Wiki Id") );
    }
    
    $wiki = $wikiStore->loadWiki( $wikiId );
    $wikiPage = new WikiPage( $con, $config, $wikiId );
    $wikiRenderer = new Wiki2xhtmlRenderer( $wiki );
    
    $accessControlList = $wiki->getACL();
    
    // --------------- Start of access rights management --------------
    
    // Wiki access levels
    
    $is_allowedToEdit   = false;
    $is_allowedToRead   = false;
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
            $is_allowedToEdit = $is_allowedToRead && ( $is_allowedToAdmin
                || ( $is_groupMember && WikiAccessControl::isAllowedToEditPage( $accessControlList, 'group' ) )
                || ( $is_courseMember && WikiAccessControl::isAllowedToEditPage( $accessControlList, 'course' ) )
                || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'other' ) );
            $is_allowedToCreate = $is_allowedToEdit && ( $is_allowedToAdmin
                || ( $is_groupMember && WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'group' ) )
                || ( $is_courseMember && WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'course' ) )
                || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'other' ) );
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
                $is_allowedToEdit = $is_allowedToRead && ( $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'course' ) );
                $is_allowedToCreate = $is_allowedToEdit && ( $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'course' ) );
            }
            // not a course member
            else
            {
                $is_allowedToRead = $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToReadPage( $accessControlList, 'other' );
                $is_allowedToEdit = $is_allowedToRead && ( $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'other' ) );
                $is_allowedToCreate = $is_allowedToEdit && ( $is_allowedToAdmin
                    || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'other' ) );
            }
        }
    }
    
    if ( ! $is_allowedToRead )
    {
        claro_die( get_lang("You are not allowed to read this page") );
    }
    
    // --------------- End of  access rights management ----------------
    
    // filter action

    if ( $is_allowedToEdit || $is_allowedToCreate )
    {
        $valid_actions = array( 'edit', 'preview', 'save'
            , 'show', 'recent', 'diff', 'all', 'history'
            , 'rqSearch', 'exSearch'
            );
    }
    else
    {
        $valid_actions = array( 'show', 'recent', 'diff', 'all'
            , 'history', 'rqSearch', 'exSearch'
            );
    }

    $_CLEAN = filter_by_key( 'action', $valid_actions, "R", false );
    
    $action = ( isset( $_CLEAN['action'] ) ) ? $_CLEAN['action'] : 'show';
    
    // get request variables
    
    $creatorId = $_uid;
    
    $versionId = ( isset( $_REQUEST['versionId'] ) ) ? $_REQUEST['versionId'] : 0;

    $title = ( isset( $_REQUEST['title'] ) ) ? strip_tags( $_REQUEST['title'] ) : '';
    
    if ( 'diff' == $action )
    {
        $old = ( isset( $_REQUEST['old'] ) ) ? (int) $_REQUEST['old'] : 0;
        $new = ( isset( $_REQUEST['new'] ) ) ? (int) $_REQUEST['new'] : 0;
    }
    
    // get content
    
    if ( 'edit' == $action )
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
        $content = ( isset( $_REQUEST['content'] ) ) ? $_REQUEST['content'] : '';
    }
    
    // use __MainPage__ if empty title

    if ( '' === $title )
    {
        // create wiki main page in a localisation compatible way
        $title = '__MainPage__';
        
        if ( $wikiStore->pageExists( $wikiId, $title ) )
        {
            // do nothing
        }
        // auto create wiki in devl mode
        elseif ( ( ! $wikiStore->pageExists( $wikiId, $title ) )
            && ( defined('DEVEL_MODE') && ( DEVEL_MODE == true ) ) )
        {
            init_wiki_main_page( $con, $wikiId, $creatorId );
        }
        else
        {
            // something weird's happened
            claro_die( get_lang( "Wrong page title" ) );
        }
    }
    
    // --------- Start of wiki command processing ----------
    
    // init message
    $message = '';
    
    switch( $action )
    {
        case 'rqSearch':
        {            
            break;
        }
        case 'exSearch':
        {
            $pattern = isset( $_REQUEST['searchPattern'] )
                ? trim($_REQUEST['searchPattern'])
                : null
                ;
                
            if ( !empty( $pattern ) )
            {
                $searchEngine = new WikiSearchEngine( $con, $config );
                $searchResult = $searchEngine->searchInWiki( $pattern, $wikiId, CLWIKI_SEARCH_ANY );
                
                if ( $searchEngine->hasError() )
                {
                    claro_die( $searchEngine->getError() );
                }
                
                if ( is_null( $searchResult ) )
                {
                    $searchResult = array();
                }
                
                $wikiList = $searchResult;
            }
            else
            {
                $message = '<p>'.get_lang("Missing search keywords").'</p>';
                $action = 'rqSearch';
            }
            break;
        }
        // show differences
        case 'diff':
        {
            require_once 'lib/lib.diff.php';
            
            if ( $wikiStore->pageExists( $wikiId, $title ) )
            {
                // older version
                $wikiPage->loadPageVersion( $old );
                $old = $wikiPage->getContent();
                $oldTime = $wikiPage->getCurrentVersionMtime();
                $oldEditor = $wikiPage->getEditorId();
                
                // newer version
                $wikiPage->loadPageVersion( $new );
                $new = $wikiPage->getContent();
                $newTime = $wikiPage->getCurrentVersionMtime();
                $newEditor = $wikiPage->getEditorId();
                
                // get differences
                $diff = '<table style="border: 0;">'.diff( $old, $new, true, 'format_table_line' ).'</table>';
            }
            
            break;
        }
        // recent changes
        case 'recent':
        {
            require_once $includePath . '/lib/user.lib.php';
            $recentChanges = $wiki->recentChanges();
            break;
        }
        // all pages
        case 'all':
        {
            $allPages = $wiki->allPages();
            break;
        }
        // edit page content
        case 'edit':
        {
            if( $wikiStore->pageExists( $wikiId, $title ) )
            {
                if ( 0 == $versionId )
                {
                    $wikiPage->loadPage( $title );
                }
                else
                {
                    $wikiPage->loadPageVersion( $versionId );
                }
                
                if ( '' == $content )
                {
                    $content = $wikiPage->getContent();
                }
                
                if  ( '__CONTENT__EMPTY__' == $content )
                {
                    $content = '';
                }

                $title = $wikiPage->getTitle();
                
                $_SESSION['wikiLastVersion'] = $wikiPage->getLastVersionId();
            }
            else
            {
                if ( $content == '' )
                {
                    $message = get_lang("This page is empty, use the editor to add content.");
                }
            }
            break;
        }
        // view page
        case 'show':
        {
            unset( $_SESSION['wikiLastVersion'] );
            
            if ( $wikiStore->pageExists( $wikiId, $title ) )
            {
                if ( $versionId == 0 )
                {
                    $wikiPage->loadPage( $title );
                }
                else
                {
                    $wikiPage->loadPageVersion( $versionId );
                }

                $content = $wikiPage->getContent();

                $title = $wikiPage->getTitle();
            }
            else
            {
                $message = get_lang( "Page" ) . " " . $title . " " . get_lang ( "not found" );
            }
            break;
        }
        // save page
        case 'save':
        {
            if ( isset( $content ) )
            {
                $time = date( "Y-m-d H:i:s" );

                if ( $wikiPage->pageExists( $title ) )
                {
                    $wikiPage->loadPage( $title );
                    
                    if ( $content == $wikiPage->getContent() )
                    {
                        unset( $_SESSION['wikiLastVersion'] );

                        $message = get_lang("Identical content<br />no modification saved");
                        
                        $action = 'show';
                    }
                    else
                    {
                        if ( isset( $_SESSION['wikiLastVersion'] )
                            && $wikiPage->getLastVersionId() != $_SESSION['wikiLastVersion'] )
                        {
                            $action = 'conflict';
                        }
                        else
                        {
                            $wikiPage->edit( $creatorId, $content, $time, true );
                        
                            unset( $_SESSION['wikiLastVersion'] );
                        
                            if ( $wikiPage->hasError() )
                            {
                                $message = get_lang( "Database error : " ) . $wikiPage->getError();
                            }
                            else
                            {
                                $message = get_lang("Page saved");
                            }
                            
                            $action = 'show';
                        }
                    }
                    
                    //notify modification of the page
                
                    $eventNotifier->notifyCourseEvent('wiki_page_modified'
                                         , $_cid
                                         , $_tid
                                         , $wikiId
                                         , $_gid
                                         , '0');
                }
                else
                {
                    $wikiPage->create( $creatorId, $title, $content, $time, true );
                    
                    if ( $wikiPage->hasError() )
                    {
                        $message = get_lang( "Database error : " ) . $wikiPage->getError();
                    }
                    else
                    {
                        $message = get_lang("Page saved");
                    }
                    
                    $action = 'show';
                    
                    //notify creation of the page
                
                    $eventNotifier->notifyCourseEvent('wiki_page_added'
                                         , $_cid
                                         , $_tid
                                         , $wikiId
                                         , $_gid
                                         , '0');
                }               
            }
            
            break;
        }
        // page history
        case 'history':
        {
            $wikiPage->loadPage( $title );
            $title = $wikiPage->getTitle();
            $history = $wikiPage->history( 0, 0, 'DESC' );
            break;
        }
    }
    
    // change to use empty page content
    
    if ( ! isset( $content ) )
    {
        $content = '';
    }
    
    // --------- End of wiki command processing -----------
    
    // --------- Start of wiki display --------------------
    
    // set xtra head
    
    $jspath = document_web_path() . '/lib/javascript';

    // set image repository
    $htmlHeadXtra[] = "<script type=\"text/javascript\">"
        . "\nvar sImgPath = '".$imgRepositoryWeb . "'"
        . "\n</script>\n"
        ;
    
    // set style
    $htmlHeadXtra[] = '<style type="text/css">
.wikiTitle h1{
    color: Black;
    background: none;
    font-size: 200%;
    font-weight: bold;
    border-bottom: 2px solid #aaaaaa;
}
.wiki2xhtml{
    margin-left: 5px;
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
.diff{
    font-family: monospace;
    padding: 5px;
    margin: 5px;
}
.diffEqual{
    background-color: white;
}
.diffMoved{
    background-color: #00CCFF;
}
.diffAdded{
    background-color: lime;
}
.diffDeleted{
    background-color: #FF00AA;
}
</style>'
        ;
        
    // Breadcrumps
    
    $interbredcrump[]= array ( 'url' => 'wiki.php', 'name' => get_lang("Wiki"));
    $interbredcrump[]= array ( 'url' => NULL
        , 'name' => $wiki->getTitle() );
        
    switch( $action )
    {
        case 'edit':
        {
            $dispTitle = ( '__MainPage__' == $title ) ? get_lang("Main page") : $title;
            $interbredcrump[]= array ( 'url' => 'page.php?action=show&amp;wikiId='
                . $wikiId . '&amp;title=' . $title
                , 'name' => $dispTitle );
            $nameTools = get_lang("Edit");
            $noPHP_SELF = true;
            break;
        }
        case 'all':
        {
            $nameTools = get_lang("All pages");
            $noPHP_SELF = true;
            break;
        }
        case 'recent':
        {
            $nameTools = get_lang("Recent changes");
            $noPHP_SELF = true;
            break;
        }
        case 'history':
        {
            $dispTitle = ( '__MainPage__' == $title ) ? get_lang("Main page") : $title;
            $interbredcrump[]= array ( 'url' => 'page.php?action=show&amp;wikiId='
                . $wikiId . '&amp;title=' . $title
                , 'name' => $dispTitle );
            $nameTools = get_lang("Page history");
            $noPHP_SELF = true;
            break;
        }
        default:
        {
            $nameTools = ( '__MainPage__' == $title ) ? get_lang("Main page") : $title ;
            $noPHP_SELF = true;
        }
    }
    
    // Claroline Header and Banner

    require_once $includePath . '/claro_init_header.inc.php';
    
    // tool title
    
    $toolTitle = array();
    $toolTitle['mainTitle'] = sprintf( get_lang("Wiki : %s"), $wiki->getTitle() );
    
    if ( $_gid )
    {
        $toolTitle['supraTitle'] = $_group['name'];
    }

    switch( $action )
    {
        case 'all':
        {
            $toolTitle['subTitle'] = get_lang("All pages");
            break;
        }
        case 'recent':
        {
            $toolTitle['subTitle'] = get_lang("Recent changes");
            break;
        }
        case 'history':
        {
            $toolTitle['subTitle'] = get_lang("Page history");
            break;
        }
        case 'rqSearch':
        case 'exSearch':
        {
            $toolTitle['subTitle'] = get_lang("Search in pages");
            break;
        }
        default:
        {
            /*$subTitle = ( '__MainPage__' == $title )
                ? get_lang("Main page")
                : $title
                ;
                
            $toolTitle['subTitle'] = $subTitle;*/
                
            break;
        }
    }
    
    echo claro_html::tool_title( $toolTitle, false );
    
    if ( !empty($message) )
    {
        echo claro_html::message_box($message) . "\n";
    }
    
    // Check javascript
    
    $javascriptEnabled = claro_is_javascript_enabled();
    
    // Wiki navigation bar
    
    echo '<p>';
    
    echo '<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=show'
        . '&amp;title=__MainPage__'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'wiki.gif" border="0" alt="edit" />&nbsp;'
        . get_lang("Main page").'</a>'
        ;
    
    echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=recent'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'history.gif" border="0" alt="recent changes" />&nbsp;'
        . get_lang("Recent changes").'</a>'
        ;

    echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=all'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'book.gif" border="0" alt="all pages" />&nbsp;'
        . get_lang("All pages").'</a>'
        ;
        
    echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . 'wiki.php'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'info.gif" border="0" alt="all pages" />&nbsp;'
        . get_lang("List of Wiki") .'</a>'
        ;
        
     echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=rqSearch'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'search.gif" border="0" alt="all pages" />&nbsp;'
        . get_lang("Search").'</a>'
        ;
    
    echo '</p>';
    
    if ( 'recent' != $action && 'all' != $action 
        && 'rqSearch' != $action && 'exSearch' != $action )
    {
    
    echo '<p>';
    
    if ( 'show' == $action || 'edit' == $action || 'history' == $action )
    {
        echo '<a class="claroCmd" href="'
            . $_SERVER['PHP_SELF']
            . '?wikiId=' . $wiki->getWikiId()
            . '&amp;action=show'
            . '&amp;title=' . rawurlencode($title)
            . '">'
            . '<img src="'.$imgRepositoryWeb.'back.gif" border="0" alt="back" />&nbsp;'
            . get_lang("Back to page").'</a>'
            ;
    }
    else
    {
        echo '<span class="claroCmdDisabled">'
            . '<img src="'.$imgRepositoryWeb.'back.gif" border="0" alt="back" />&nbsp;'
            . get_lang("Back to page").'</span>'
            ;
    }
        
    if ( $is_allowedToEdit || $is_allowedToCreate )
    {
        // Show context
        if ( 'show' == $action || 'edit' == $action || 'diff' == $action )
        {
            echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
                . $_SERVER['PHP_SELF']
                . '?wikiId=' . $wiki->getWikiId()
                . '&amp;action=edit'
                . '&amp;title=' . rawurlencode( $title )
                . '&amp;versionId=' . $versionId
                . '">'
                . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />&nbsp;'
                . get_lang("Edit this page").'</a>'
                ;
        }
        // Other contexts
        else
        {
            echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
                . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />&nbsp;'
                . get_lang("Edit this page") . '</span>'
                ;
        }
    }
    else
    {
        echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
            . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />&nbsp;'
            . get_lang("Edit this page") . '</span>'
            ;
    }
    
    if ( 'show' == $action || 'edit' == $action 
        || 'history' == $action || 'diff' == $action )
    {
        // active
        echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
                . $_SERVER['PHP_SELF']
                . '?wikiId=' . $wiki->getWikiId()
                . '&amp;action=history'
                . '&amp;title=' . rawurlencode( $title )
                . '">'
                . '<img src="'.$imgRepositoryWeb.'version.gif" border="0" alt="history" />&nbsp;'
                . get_lang("Page history").'</a>'
                ;
    }
    else
    {
        // inactive
        echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
            . '<img src="'.$imgRepositoryWeb.'version.gif" border="0" alt="history" />&nbsp;'
            . get_lang("Page history") . '</span>'
            ;
    }
        
    if ( 'edit' == $action || 'diff' == $action )
    {
        echo '&nbsp;|&nbsp;<a class="claroCmd" href="#" onClick="MyWindow=window.open(\''
            . 'help_wiki.php?help=syntax'
            . '\',\'MyWindow\',\'toolbar=no,location=no,directories=no,status=yes,menubar=no'
            . ',scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10\'); return false;">'
            . '<img src="'.$imgRepositoryWeb.'help_little.gif" border="0" alt="history" />&nbsp;'
            . get_lang("Wiki syntax") . '</a>'
            ;
    }

    echo '</p>' . "\n";
    
    }
    
    switch( $action )
    {
        case 'conflict':
        {
            if( '__MainPage__' === $title )
            {
                $displaytitle = get_lang("Main page");
            }
            else
            {
                $displaytitle = $title;
            }
            
            echo '<div class="wikiTitle">' . "\n";
            echo '<h1>'.$displaytitle
                . ' : ' . get_lang("Edit conflict")
                . '</h1>'
                . "\n"
                ;
            echo '</div>' . "\n";
            
            $message = get_block('blockWikiConflictHowTo');
                
            echo claro_html::message_box ( $message ) . '<br />' . "\n";
            
            echo '<form id="editConflict" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
            echo '<textarea name="conflictContent" id="content"'
                 . ' cols="80" rows="15" wrap="virtual">'
                 ;
            echo $content;
            echo '</textarea><br /><br />' . "\n";
            echo '<div>' . "\n";
            echo '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n";
            echo '<input type="hidden" name="title" value="'.$title.'" />' . "\n";
            echo '<input type="submit" name="action[edit]" value="'.get_lang("Edit last version").'" />' . "\n";
            $url = $_SERVER['PHP_SELF']
                . '?wikiId=' . $wikiId
                . '&amp;title=' . $title
                . '&amp;action=show'
                ;
            echo claro_html::button( $url, get_lang("Cancel") ) . "\n";
            echo '</div>' . "\n";
            echo '</form>';
            break;
        }
        case 'diff':
        {
            if( '__MainPage__' === $title )
            {
                $displaytitle = get_lang("Main page");
            }
            else
            {
                $displaytitle = $title;
            }
            
            $oldTime = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($oldTime) )
                        ;
                        
            $userInfo = user_get_data( $oldEditor );
            $oldEditorStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];

            $newTime = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($newTime) )
                        ;
                        
            $userInfo = user_get_data( $newEditor );
            $newEditorStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];

            $versionInfo = '('
                . sprintf( 
                    get_lang("differences between version of %1\$s modified by %2\$s and version of %3\$s modified by %4\$s")
                        , $oldTime, $oldEditorStr, $newTime, $newEditorStr )
                . ')'
                ;
                
            $versionInfo = '&nbsp;<span style="font-size: 40%; font-weight: normal; color: red;">'
                        . $versionInfo . '</span>'
                        ;

            echo '<div class="wikiTitle">' . "\n";
            echo '<h1>'.$displaytitle
                . $versionInfo
                . '</h1>'
                . "\n"
                ;
            echo '</div>' . "\n";
            
            echo '<strong>'.get_lang("Keys :").'</strong>';

            echo '<div class="diff">' . "\n";
            echo '= <span class="diffEqual" >'.get_lang("Unchanged line").'</span><br />';
            echo '+ <span class="diffAdded" >'.get_lang("Added line").'</span><br />';
            echo '- <span class="diffDeleted" >'.get_lang("Deleted line").'</span><br />';
            echo 'M <span class="diffMoved" >'.get_lang("Moved line").'</span><br />';
            echo '</div>' . "\n";
            
            echo '<strong>'.get_lang("Differences :").'</strong>';

            echo '<div class="diff">' . "\n";
            echo $diff;
            echo '</div>' . "\n";
            
            break;
        }
        case 'recent':
        {
            if ( is_array( $recentChanges ) )
            {
                echo '<ul>' . "\n";
                
                foreach ( $recentChanges as $recentChange )
                {
                    $pgtitle = ( '__MainPage__' == $recentChange['title'] )
                        ? get_lang("Main page")
                        : $recentChange['title']
                        ;
                        
                    $entry = '<strong><a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                        . $wikiId . '&amp;title=' . rawurlencode( $recentChange['title'] )
                        . '&amp;action=show"'
                        . '>'.$pgtitle.'</a></strong>'
                        ;
                        
                    $time = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($recentChange['last_mtime']) )
                        ;

                    $userInfo = user_get_data( $recentChange['editor_id'] );
                    
                    if ( !empty( $userInfo ) )
                    {
                        $userStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];
                    }
                    else
                    {
                        $userStr = get_lang( "Unknown" );
                    }
                    
                    if ( $is_courseMember )
                    {
                        $userUrl = '<a href="'. $clarolineRepositoryWeb
                            . 'user/userInfo.php?uInfo='
                            . $recentChange['editor_id'].'">'
                            .$userStr.'</a>'
                            ;
                    }
                    else
                    {
                        $userUrl = $userStr;
                    }
                        
                    echo '<li>'
                        . sprintf( get_lang("%1\$s modified on %2\$s by %3\$s"), $entry, $time, $userUrl )
                        . '</li>'
                        . "\n"
                        ;
                }

                echo '</ul>' . "\n";
            }
            break;
        }
        case 'all':
        {
            // handle main page
            
            echo '<ul><li><a href="'.$_SERVER['PHP_SELF']
                . '?wikiId=' . $wikiId
                . '&amp;title=' . rawurlencode("__MainPage__")
                . '&amp;action=show">'
                . get_lang("Main page")
                . '</a></li></ul>' . "\n"
                ;
            
            // other pages
            
            if ( is_array( $allPages ) )
            {
                echo '<ul>' . "\n";
                
                foreach ( $allPages as $page )
                {
                    if ( '__MainPage__' == $page['title'] )
                    {
                        // skip main page
                        continue;
                    }

                    $pgtitle = rawurlencode( $page['title'] );

                    $link = '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                        . $wikiId . '&amp;title=' . $pgtitle . '&amp;action=show"'
                        . '>' . $page['title'] . '</a>'
                        ;
                        
                    echo '<li>' . $link. '</li>' . "\n";
                }
                echo '</ul>' . "\n";
            }
            break;
        }
        // edit page
        case 'edit':
        {
            if ( ! $wiki->pageExists( $title ) && ! $is_allowedToCreate )
            {
                echo get_lang("You are not allowed to create pages");
            }
            elseif ( $wiki->pageExists( $title ) && ! $is_allowedToEdit )
            {
                echo get_lang("You are not allowed to edit this page");
            }
            else
            {
                $script = $_SERVER['PHP_SELF'];

                echo claro_disp_wiki_editor( $wikiId, $title, $versionId, $content, $script
                    , get_conf('showWikiEditorToolbar'), get_conf('forcePreviewBeforeSaving') )
                    ;
            }

            break;
        }
        // page preview
        case 'preview':
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
        case 'show':
        {
            if( $wikiPage->hasError() )
            {
                echo $wikiPage->getError();
            }
            else
            {
                // get localized value for wiki main page title
                if( '__MainPage__' === $title )
                {
                    $displaytitle = get_lang("Main page");
                }
                else
                {
                    $displaytitle = $title;
                }
                
                if ( $versionId != 0 )
                {
                    $editorInfo = user_get_data( $wikiPage->getEditorId() );

                    $editorStr = $editorInfo['firstname'] . "&nbsp;" . $editorInfo['lastname'];

                    if ( $is_courseMember )
                    {
                        $editorUrl = '&nbsp;-&nbsp;<a href="'. $clarolineRepositoryWeb
                            . 'user/userInfo.php?uInfo='
                            . $wikiPage->getEditorId() .'">'
                            . $editorStr.'</a>'
                            ;
                    }
                    else
                    {
                        $editorUrl = '&nbsp;-&nbsp;' . $editorStr;
                    }
                    
                    $mtime = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($wikiPage->getCurrentVersionMtime()) )
                        ;
                        
                    $versionInfo = sprintf( get_lang("(version of %1\$s modified by %2\$s)"), $mtime, $editorUrl );
                        
                    $versionInfo = '&nbsp;<span style="font-size: 40%; font-weight: normal; color: red;">'
                        . $versionInfo . '</span>'
                        ;
                }
                else
                {
                    $versionInfo = '';
                }
                
                echo '<div class="wikiTitle">' . "\n";
                echo '<h1>'.$displaytitle
                    . $versionInfo
                    . '</h1>'
                    . "\n"
                    ;
                echo '</div>' . "\n";
                
                echo '<div class="wiki2xhtml">' . "\n";
                echo $wikiRenderer->render( $content );
                echo '</div>' . "\n";
                
                echo '<div style="clear:both;"><!-- spacer --></div>' . "\n";
            }

            break;
        }
        case 'history':
        {
            if( '__MainPage__' === $title )
            {
                $displaytitle = get_lang("Main page");
            }
            else
            {
                $displaytitle = $title;
            }

            echo '<div class="wikiTitle">' . "\n";
            echo '<h1>'.$displaytitle.'</h1>' . "\n";
            echo '</div>' . "\n";
            
            echo '<form id="differences" method="GET" action="'
                . $_SERVER['PHP_SELF']
                . '">'
                . "\n"
                ;
                
            echo '<div>' . "\n"
                . '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n"
                . '<input type="hidden" name="title" value="'.$title.'" />' . "\n"
                . '<input type="submit" name="action[diff]" value="'
                . get_lang("Show differences")
                . '" />' . "\n"
                . '</div>' . "\n"
                ;
            
            echo '<table style="border: 0px;">' . "\n";
            
            if ( is_array( $history ) )
            {
                $firstPass = true;
                
                foreach ( $history as $version )
                {
                    echo '<tr>' . "\n";
                    
                    if ( true == $firstPass )
                    {
                        $checked = ' checked="checked"';
                        $firstPass = false;
                    }
                    else
                    {
                        $checked = '';
                    }
                    
                    echo '<td>'
                        . '<input type="radio" name="old" value="'.$version['id'].'"'.$checked.' />' . "\n"
                        . '</td>'
                        . "\n"
                        ;
                        
                    echo '<td>'
                        . '<input type="radio" name="new" value="'.$version['id'].'"'.$checked.' />' . "\n"
                        . '</td>'
                        . "\n"
                        ;

                    $userInfo = user_get_data( $version['editor_id'] );

                    if ( ! empty( $userInfo ) )
                    {
                        $userStr = $userInfo['firstname'] . " " . $userInfo['lastname'];
                    }
                    else
                    {
                        $userStr = get_lang( "Unknown" );
                    }
                    
                    if ( $is_courseMember )
                    {
                        $userUrl = '<a href="'. $clarolineRepositoryWeb
                            . 'user/userInfo.php?uInfo='
                            . $version['editor_id'].'">'
                            .$userStr.'</a>'
                            ;
                    }
                    else
                    {
                        $userUrl = $userStr;
                    }
                    
                    $versionUrl = '<a href="' . $_SERVER['PHP_SELF'] . '?wikiId='
                        . $wikiId . '&amp;title=' . rawurlencode( $title )
                        . '&amp;action=show&amp;versionId=' . $version['id']
                        . '">'
                        . claro_disp_localised_date( $dateTimeFormatLong
                            , strtotime($version['mtime']) )
                        . '</a>'
                        ;
                    
                    echo '<td>'
                        . sprintf( get_lang("%1\$s by %2\$s"), $versionUrl, $userUrl )
                        . '</td>'
                        . "\n"
                        ;
                        
                    echo '</tr>' . "\n";
                }
            }
            
            echo '</table>' . "\n";
            
            echo '</form>';
            
            break;
        }
        case 'exSearch':
        {
            echo '<h3>'.get_lang("Search result").'</h3>' . "\n";
            
            echo '<ul>' . "\n";
            
            foreach ( $searchResult as $page )
            {
                if ( '__MainPage__' == $page['title'] )
                {
                    $title = get_lang( "Main Page" );
                }
                else
                {
                    $title = $page['title'];
                }

                $urltitle = rawurlencode( $page['title'] );

                $link = '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                    . $wikiId . '&amp;title=' . $urltitle . '&amp;action=show"'
                    . '>' . $title . '</a>'
                    ;
                    
                echo '<li>' . $link. '</li>' . "\n";
            }
            echo '</ul>' . "\n";
            break;
        }
        case 'rqSearch':
        {
            $searchForm = '<form method="post" action="'
                . $_SERVER['PHP_SELF'].'?wikiId='.$wikiId.'">'."\n"
                . '<input type="hidden" name="action" value="exSearch">'."\n"
                . '<label for="searchPattern">'
                . get_lang("Search")
                . '</label><br />'."\n"
                . '<input type="text" id="searchPattern" name="searchPattern">'."\n"
                . '<input type="submit" value="'.get_lang("Ok").'">'."\n"
                . claro_html::button($_SERVER['PHP_SELF'].'?wikiId='.$wikiId, get_lang("Cancel"))
                . '</form>'."\n"
                ;
            echo claro_html::message_box($searchForm) . "\n";
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
    
    require_once $includePath . '/claro_init_footer.inc.php';
?>
