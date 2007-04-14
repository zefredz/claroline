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

    $tlabelReq = 'CLWIKI';

    require_once "../inc/claro_init_global.inc.php";

    if ( ! claro_is_tool_allowed() )
    {
        if ( ! claro_is_in_a_course() )
        {
            claro_disp_auth_form( true );
        }
        else
        {
            claro_die(get_lang("Not allowed"));
        }
    }

    // if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

    event_access_tool(claro_get_current_tool_id(), claro_get_current_course_tool_data('label'));

    // display mode

    claro_set_display_mode_available(TRUE);

    // check and set user access level for the tool

    // set admin mode and groupId

    $is_allowedToAdmin = claro_is_allowed_to_edit();


    if ( claro_is_in_a_group() && claro_is_group_allowed() )
    {
        // group context
        $groupId = (int) claro_get_current_group_id();
    }
    elseif ( claro_is_in_a_group() && ! claro_is_group_allowed() )
    {
        claro_die(get_lang("Not allowed"));
    }
    elseif ( claro_is_course_allowed() )
    {
        // course context
        $groupId = 0;
    }
    else
    {
        claro_disp_auth_form();
    }

    // require wiki files

    require_once "lib/class.clarodbconnection.php";
    require_once "lib/class.wiki.php";
    require_once "lib/class.wikistore.php";
    require_once "lib/class.wikipage.php";
    require_once "lib/lib.requestfilter.php";
    require_once "lib/lib.wikisql.php";
    require_once "lib/lib.javascript.php";
    require_once "lib/lib.wikidisplay.php";

    // filter request variables

    // filter allowed actions using user status
    if ( $is_allowedToAdmin )
    {
        $valid_actions = array( 'list', 'rqEdit', 'exEdit', 'rqDelete', 'exDelete', 'rqSearch', 'exSearch', 'exExport' );
    }
    elseif ( claro_is_group_member() && $groupId )
    {
        $valid_actions = array( 'list', 'rqEdit', 'exEdit', 'rqDelete', 'exDelete', 'rqSearch', 'exSearch' );
    }
    else
    {
        $valid_actions = array( 'list', 'rqSearch', 'exSearch' );
    }

    $_CLEAN = filter_by_key( 'action', $valid_actions, 'R', false );

    $action = ( isset( $_CLEAN['action'] ) ) ? $_CLEAN['action'] : 'list';

    $wikiId = ( isset( $_REQUEST['wikiId'] ) ) ? (int) $_REQUEST['wikiId'] : 0;

    $creatorId = claro_get_current_user_id();

    // get request variable for wiki edition
    if ( $action == 'exEdit' )
    {
        $wikiTitle = ( isset( $_POST['title'] ) ) ? strip_tags( $_POST['title'] ) : '';
        $wikiDesc = ( isset( $_POST['desc'] ) ) ? strip_tags( $_POST['desc'] ) : '';

        if ( $wikiDesc == get_lang("Enter the description of your wiki here") )
        {
            $wikiDesc = '';
        }

        $acl = ( isset( $_POST['acl'] ) ) ? $_POST['acl'] : null;

        // initialise access control list

        $wikiACL = WikiAccessControl::emptyWikiACL();

        if ( is_array( $acl ) )
        {
            foreach ( $acl as $key => $value )
            {
                if ( $value == 'on' )
                {
                    $wikiACL[$key] = true;
                }
            }
        }

        // force Wiki ACL coherence

        if ( $wikiACL['course_read'] == false && $wikiACL['course_edit'] == true )
        {
            $wikiACL['course_edit'] = false;
        }
        if ( $wikiACL['group_read'] == false && $wikiACL['group_edit'] == true )
        {
            $wikiACL['group_edit'] = false;
        }
        if ( $wikiACL['other_read'] == false && $wikiACL['other_edit'] == true )
        {
            $wikiACL['other_edit'] = false;
        }

        if ( $wikiACL['course_edit'] == false  && $wikiACL['course_create'] == true )
        {
            $wikiACL['course_create'] = false;
        }
        if ( $wikiACL['group_edit'] == false  && $wikiACL['group_create'] == true )
        {
            $wikiACL['group_create'] = false;
        }
        if ( $wikiACL['other_edit'] == false  && $wikiACL['other_create'] == true )
        {
            $wikiACL['other_create'] = false;
        }
    }

    // Database nitialisation

    $tblList = claro_sql_get_course_tbl();

    $config = array();
    $config['tbl_wiki_properties'   ] = $tblList['wiki_properties'   ];
    $config['tbl_wiki_pages'        ] = $tblList['wiki_pages'        ];
    $config['tbl_wiki_pages_content'] = $tblList['wiki_pages_content'];
    $config['tbl_wiki_acls'         ] = $tblList['wiki_acls'         ];

    $con = new ClarolineDatabaseConnection();

    // DEVEL_MODE database initialisation
    if( defined( 'DEVEL_MODE' ) && ( DEVEL_MODE == true ) )
    {
        init_wiki_tables( $con, false );
    }

    // Objects instantiation

    $wikiStore = new WikiStore( $con, $config );
    $wikiList = array();

    // --------- Start of command processing ----------------

    switch ( $action )
    {
        case 'exExport':
        {
            require_once "lib/class.wiki2xhtmlexport.php";

            if ( ! $wikiStore->wikiIdExists( $wikiId ) )
            {
                // die( get_lang("Invalid Wiki Id") );
                $message = get_lang("Invalid Wiki Id");
                $action = 'error';
            }
            else
            {
                $wiki = $wikiStore->loadWiki( $wikiId );
                $wikiTitle = $wiki->getTitle();
                $renderer = new WikiToSingleHTMLExporter( $wiki );

                $contents = $renderer->export();

                if ( 0 != $groupId )
                {
                    $groupPart = '_group' . (int) $groupId;
                }
                else
                {
                    $groupPart = '';
                }

                require_once get_conf( 'includePath' ) . '/lib/fileUpload.lib.php';
                // TODO : use function wich return get_conf('coursesRepositorySys') . '/' . $_course['path']
                $exportDir = get_conf('coursesRepositorySys') . '/' . claro_get_course_path() . '/document';
                $exportFile = replace_dangerous_char( $wikiTitle, 'strict' ) . $groupPart;

                $i = 1;
                while ( file_exists($exportDir . '/' .$exportFile.'_'.$i.'.html') ) $i++;

                $wikiFileName = $exportFile . '_' . $i . '.html';
                $exportPath = $exportDir . '/' . $wikiFileName;

                file_put_contents( $exportPath, $contents );
            }

            break;
        }
        case 'exSearch':
        {
            require_once "lib/class.wikisearchengine.php";

            $pattern = isset( $_REQUEST['searchPattern'] )
                ? trim($_REQUEST['searchPattern'])
                : null
                ;

            if ( !empty( $pattern ) )
            {
                $searchEngine = new WikiSearchEngine( $con, $config );
                $searchResult = $searchEngine->searchAllWiki( $pattern, $groupId, CLWIKI_SEARCH_ANY );

                if ( $searchEngine->hasError() )
                {
                    $message = $searchEngine->getError();
                    $action = 'error';
                    break;
                }

                if ( is_null( $searchResult ) )
                {
                    $searchResult = array();
                }

                $wikiList = $searchResult;

                break;
            }
            else
            {
                $message = '<p>'.get_lang("Missing search keywords").'</p>';
            }
        }
        // search wiki
        case 'rqSearch':
        {
            if ( !isset( $message ) ) $message = '';

            $message .= '<form>'."\n"
                . '<input type="hidden" name="action" value="exSearch" />'."\n"
                . '<label for="searchPattern">'
                . get_lang("Search")
                . '</label><br />'."\n"
                . '<input type="text" id="searchPattern" name="searchPattern" />'."\n"
                . '<input type="submit" value="'.get_lang("Ok").'" />'."\n"
                . claro_html_button($_SERVER['PHP_SELF'], get_lang("Cancel"))
                . '</form>'."\n"
                ;

            $action = 'list';
            break;
        }
        // request delete
        case 'rqDelete':
        {
            if ( ! $wikiStore->wikiIdExists( $wikiId ) )
            {
                // die( get_lang("Invalid Wiki Id") );
                $message = get_lang("Invalid Wiki Id");
                $action = 'error';
            }
            else
            {
                $wiki = $wikiStore->loadWiki( $wikiId );
                $wikiTitle = $wiki->getTitle();
                $message = get_lang("WARNING : you are going to delete this wiki and all its pages. Are you sure to want to continue ?");
            }

            break;
        }
        // execute delete
        case 'exDelete':
        {
            if ( $wikiStore->wikiIdExists( $wikiId ) )
            {
                $wiki = $wikiStore->deleteWiki( $wikiId );
            }
            else
            {
                $message = get_lang("Invalid Wiki Id");
                $action = 'error';
            }

            $message = get_lang("Wiki deletion succeed");

            //notify that the wiki was deleted

            $eventNotifier->notifyCourseEvent('wiki_deleted'
                                         , claro_get_current_course_id()
                                         , claro_get_current_tool_id()
                                         , $wikiId
                                         , $groupId
                                         , '0');

            $action = 'list';

            break;
        }
        // request edit
        case 'rqEdit':
        {
            if ( $wikiId == 0 )
            {
                $wikiTitle = get_lang("New Wiki");
                $wikiDesc = '';
                $wikiACL = null;
            }
            elseif ( $wikiStore->wikiIdExists( $wikiId ) )
            {
                $wiki = $wikiStore->loadWiki( $wikiId );
                $wikiTitle = $wiki->getTitle();
                $wikiDesc = $wiki->getDescription();
                $wikiACL = $wiki->getACL();
                $groupId = $wiki->getGroupId();

            }
            else
            {
                $message = get_lang("Invalid Wiki Id");
                $action = 'error';
            }
            break;
        }
        // execute edit
        case 'exEdit':
        {
            if ( $wikiId == 0 )
            {
                $wiki = new Wiki( $con, $config );
                $wiki->setTitle( $wikiTitle );
                $wiki->setDescription( $wikiDesc );
                $wiki->setACL( $wikiACL );
                $wiki->setGroupId( $groupId );
                $wikiId = $wiki->save();

                //notify wiki modification

                $eventNotifier->notifyCourseEvent('wiki_added'
                                         , claro_get_current_course_id()
                                         , claro_get_current_tool_id()
                                         , $wikiId
                                         , claro_get_current_group_id()
                                         , '0');

                $mainPageContent = sprintf(
                    get_lang("This is the main page of the Wiki %s. Click on '''Edit''' to modify the content.")
                    , $wikiTitle )
                    ;

                $wikiPage = new WikiPage( $con, $config, $wikiId );
                $wikiPage->create( $creatorId, '__MainPage__'
                    , $mainPageContent, date("Y-m-d H:i:s"), true );

                $message = get_lang("Wiki creation succeed");
            }
            elseif ( $wikiStore->wikiIdExists( $wikiId ) )
            {
                $wiki = $wikiStore->loadWiki( $wikiId );
                $wiki->setTitle( $wikiTitle );
                $wiki->setDescription( $wikiDesc );
                $wiki->setACL( $wikiACL );
                $wiki->setGroupId( $groupId );
                $wikiId = $wiki->save();

                //notify wiki creation

                $eventNotifier->notifyCourseEvent('wiki_modified'
                                         , claro_get_current_course_id()
                                         , claro_get_current_tool_id()
                                         , $wikiId
                                         , claro_get_current_group_id()
                                         , '0');

                $message = get_lang("Wiki edition succeed");
            }
            else
            {
                $message = get_lang("Invalid Wiki Id");
                $action = 'error';
            }

            $action = 'list';

            break;
        }
    }

    // list wiki
    if ( 'list' == $action )
    {
        if ( $groupId == 0 )
        {
            $wikiList = $wikiStore->getCourseWikiList();
        }
        else
        {
            $wikiList = $wikiStore->getWikiListByGroup( $groupId );
        }
    }

    // ------------ End of command processing ---------------

    // javascript

    if ( $action == 'rqEdit' )
    {
        $jspath = document_web_path() . '/lib/javascript';
        $htmlHeadXtra[] = '<script type="text/javascript" src="'.$jspath.'/wiki_acl.js"></script>';
        $claroBodyOnload[] = 'initBoxes();';
    }

    // Breadcrumps

    switch( $action )
    {
        case 'rqEdit':
        {
            $interbredcrump[]= array ('url' => 'wiki.php', 'name' => get_lang("Wiki") );
            $interbredcrump[]= array ('url' => NULL
                , 'name' => $wikiTitle);
            $nameTools = get_lang("Properties");
            $noPHP_SELF = true;
            break;
        }
        case 'rqDelete':
        {
            $interbredcrump[]= array ('url' => 'wiki.php', 'name' => get_lang("Wiki") );
            $interbredcrump[]= array ('url' => NULL
                , 'name' => $wikiTitle);
            $nameTools = get_lang("Delete");
            $noPHP_SELF = true;
            break;
        }
        case 'list':
        default:
        {
            $noQUERY_STRING = true;
            $nameTools = 'Wiki';
        }
    }

    // Claro header and banner

    require_once get_path('incRepositorySys') . "/claro_init_header.inc.php";

    // --------- Start of display ----------------

    // toolTitle

    $toolTitle = array();

    if ( claro_is_in_a_group() )
    {
        $toolTitle['supraTitle'] = claro_get_current_group_data('name');
    }

    switch( $action )
    {
        // edit form
        case 'rqEdit':
        {
            if ( $wikiId == 0 )
            {
                $toolTitle['mainTitle'] = get_lang("Wiki : Create new Wiki");
            }
            else
            {
                $toolTitle['mainTitle'] = get_lang("Wiki : Edit properties");
                $toolTitle['subTitle'] = $wikiTitle;
            }

            break;
        }
        // delete form
        case 'rqDelete':
        {
            $toolTitle['mainTitle'] = get_lang("Delete Wiki");

            break;
        }
        // list wiki
        case 'list':
        {
            $toolTitle['mainTitle'] = sprintf( get_lang("Wiki : %s"), get_lang("List of Wiki") );

            break;
        }
    }

    echo claro_html_tool_title( $toolTitle, "../wiki/help_wiki.php?help=admin" ) . "\n";

    if ( ! empty( $message ) )
    {
        echo claro_html_message_box( $message ) . "\n";
    }

    switch( $action )
    {
        // an error occurs
        case 'error':
        {
            break;
        }
        case 'exExport':
        {
            echo '<blockquote>'
                . get_lang( "Wiki %TITLE% exported to course documents. (this file is visible)"
                    , array( '%TITLE%' => $wikiTitle ) )
                . '</blockquote>'
                . '<p>'
                . claro_html_cmd_link( get_module_url('CLDOC')
                                     . '/document.php?gidReset=1'
                                     . claro_url_relay_context('&amp;')
                                     , get_lang("Go to documents tool"))
                . '&nbsp;|&nbsp;'
                . claro_html_cmd_link( $_SERVER['PHP_SELF']
                                     . claro_url_relay_context('?')
                                     , get_lang("Go back to Wiki list"))
                . '</p>'
                . "\n"
                ;

            break;
        }
        // edit form
        case 'rqEdit':
        {
            echo claro_disp_wiki_properties_form( $wikiId, $wikiTitle
                , $wikiDesc, $groupId, $wikiACL );

            break;
        }
        // delete form
        case 'rqDelete':
        {
            echo '<form method="post" action="'
                . $_SERVER['PHP_SELF']
                . '" id="rqDelete">'
                . "\n"
                ;

            echo '<div style="padding: 5px">'
                . '<input type="hidden" name="wikiId" value="' . $wikiId . '" />' . "\n"
                . '<input type="submit" name="action[exDelete]" value="' . get_lang("Continue") . '" />' . "\n"
                . claro_html_button ($_SERVER['PHP_SELF'], get_lang("Cancel") )
                . '</div>'
                ;

            echo '</form>' . "\n";

            break;
        }
        // list wiki
        case 'exSearch':
        case 'list':
        {
            //find the wiki with recent modification from the notification system

            if (claro_is_user_authenticated())
            {
                $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
                $modified_wikis = $claro_notifier->get_notified_ressources(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id());
            }
            else
            {
                $modified_wikis = array();
            }

            // if admin, display add new wiki link
            echo '<p>';

            if ( ( $groupId && claro_is_group_member() ) || $is_allowedToAdmin )
            {
                echo claro_html_cmd_link(
                    $_SERVER['PHP_SELF'] . '?action=rqEdit'
                    . claro_url_relay_context('&amp;')
                    , '<img src="' . get_path('imgRepositoryWeb') . '/wiki.gif" '
                    . ' alt="' . get_lang("Create a new Wiki").'" />'
                    . '&nbsp;'
                    . get_lang("Create a new Wiki"))
                    . '&nbsp;|&nbsp;'
                    ;
            }

            echo claro_html_cmd_link(
                $_SERVER['PHP_SELF'] . '?action=rqSearch'
                . claro_url_relay_context('&amp;')
                , '<img src="' . get_path('imgRepositoryWeb') . '/search.gif" '
                . ' alt="' . get_lang("Search") . '" />'
                . '&nbsp;'
                . get_lang("Search"))
                . '</p>'
                . "\n"
                ;

            // display list in a table

            echo '<table class="claroTable emphaseLine" style="width: 100%">' . "\n";

            // if admin, display title, edit and delete
            if ( ( $groupId && claro_is_group_member() ) || $is_allowedToAdmin )
            {
                echo '<thead>' . "\n"
                    . '<tr class="headerX" style="text-align: center;">' . "\n"
                    . '<th>'.get_lang("Title").'</th>' . "\n"
                    . '<th>'.get_lang("Number of pages").'</th>' . "\n"
                    . '<th>'.get_lang("Recent changes").'</th>'
                    . '<th>'.get_lang("Properties").'</th>' . "\n"
                    . '<th>'.get_lang("Delete").'</th>' . "\n"
                    . ( true === $is_allowedToAdmin ? '<th>'.get_lang("Export").'</th>' . "\n" : '' )
                    . '</tr>' . "\n"
                    . '</thead>' . "\n"
                    ;
            }
            // else display title only
            else
            {
                echo '<thead>' . "\n"
                    . '<tr class="headerX" style="text-align: center;">' . "\n"
                    . '<th>'.get_lang("Title").'</th>' . "\n"
                    . '<th>'.get_lang("Number of pages").'</th>' . "\n"
                    . '<th>'.get_lang("Recent changes").'</th>'
                    . '</tr>' . "\n"
                    . '</thead>' . "\n"
                    ;
            }

            echo '<tbody>' . "\n";

            // wiki list not empty
            if ( is_array( $wikiList ) && count( $wikiList ) > 0 )
            {
                foreach ( $wikiList as $entry )
                {
                    echo '<tr>' . "\n";

                    // display title for all users

                    //modify style if the wiki is recently added or modified since last login

                    if ( (claro_is_user_authenticated() && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $entry['id'])))
                    {
                        $classItem=" hot";
                    }
                    else // otherwise just display its title normally
                    {
                        $classItem="";
                    }


                    echo '<td style="text-align: left;">';

                    // display direct link to main page

                    echo '<a class="item'.$classItem.'" href="page.php?wikiId='
                        . $entry['id'].'&amp;action=show'
                        . '">'
                        . '<img src="' . get_path('imgRepositoryWeb') . '/wiki.gif" alt="'.get_lang("Wiki").'" />&nbsp;'
                        . $entry['title'] . '</a>'
                        ;
                        ;

                    echo '</td>' . "\n";

                    echo '<td style="text-align: center;">';

                    echo '<a href="page.php?wikiId=' . $entry['id'] . '&amp;action=all">';

                    echo $wikiStore->getNumberOfPagesInWiki( $entry['id'] );

                    echo '</a>';

                    echo '</td>' . "\n";

                    echo '<td style="text-align: center;">';

                    // display direct link to main page

                    echo '<a href="page.php?wikiId='
                        . $entry['id'].'&amp;action=recent'
                        . '">'
                        . '<img src="' . get_path('imgRepositoryWeb') . '/history.gif" alt="'.get_lang("Recent changes").'" />'
                        . '</a>'
                        ;
                        ;

                    echo '</td>' . "\n";

                    // if admin, display edit and delete links

                    if ( ( $groupId && claro_is_group_member() ) || $is_allowedToAdmin )
                    {
                        // edit link

                        echo '<td style="text-align:center;">';
                        echo '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                            . $entry['id'].'&amp;action=rqEdit'
                            . '">'
                            . '<img src="' . get_path('imgRepositoryWeb') . 'settings.gif" border="0" alt="'
                            . get_lang("Edit properties").'" />'
                            . '</a>'
                            ;
                        echo '</td>' . "\n";

                        // delete link

                        echo '<td style="text-align:center;">';
                        echo '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                            . $entry['id'].'&amp;action=rqDelete'
                            . '">'
                            . '<img src="' . get_path('imgRepositoryWeb') . 'delete.gif" border="0" alt="'.get_lang("Delete").'" />'
                            . '</a>'
                            ;
                        echo '</td>' . "\n";

                        if ( true === $is_allowedToAdmin )
                        {
                            echo '<td style="text-align:center;">';
                            echo '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                                . $entry['id'].'&amp;action=exExport'
                                . '">'
                                . '<img src="' . get_path('imgRepositoryWeb') . 'export.gif" border="0" alt="'.get_lang("Export").'" />'
                                . '</a>'
                                ;
                            echo '</td>' . "\n";
                        }
                    }

                    echo '</tr>' . "\n";

                    if ( ! empty( $entry['description'] ) )
                    {
                        echo '<tr>' . "\n";

                        if ( $groupId && claro_is_group_member() )
                        {
                            $colspan = 5;
                        }
                        elseif ( $is_allowedToAdmin )
                        {
                            $colspan = 6;
                        }
                        else
                        {
                            $colspan = 3;
                        }

                        echo '<td colspan="'
                            . $colspan.'"><div class="comment">'
                            . $entry['description'].'</div></td>'
                            . "\n"
                            ;

                        echo '</tr>' . "\n";
                    }
                }
            }
            // wiki list empty
            else
            {
                if ( $groupId && claro_is_group_member() )
                {
                    $colspan = 5;
                }
                elseif ( $is_allowedToAdmin )
                {
                    $colspan = 6;
                }
                else
                {
                    $colspan = 3;
                }

                echo '<tr><td colspan="'.$colspan.'" style="text-align: center;">'
                 . get_lang("No Wiki")
                 . '</td></tr>' . "\n"
                 ;
            }

            echo '</tbody>' . "\n";
            echo '</table>' . "\n" . "\n";

            break;
        }
        default:
        {
            trigger_error( "Invalid action supplied to " . $_SERVER['PHP_SELF']
                , E_USER_ERROR
                );
        }
    }

// ------------ End of display ---------------
// Claroline footer
require_once get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>