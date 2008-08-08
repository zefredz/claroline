<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
* CLAROLINE
*
* User desktop : notepad portlet
* FIXME : MVC + ajax handling + Move to own module !!!
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/
 
class MyNotes extends UserDesktopPortlet
{
    private $id = 0;
    private $note = '';
    private $label = 'Mynotes';

    public function __construct()
    {
        $jsloader = JavascriptLoader::getInstance();
        $jsloader->load('jquery');
            
        $htmlHeaders = "\n"   
        .   '<script type="text/javascript">' . "\n"
        .   '$(document).ready( function () {$("#note").focus(); });'
        .   '</script>' . "\n"
        ;
        
        ClaroHeader::getInstance()->addHtmlHeader($htmlHeaders);

        $tbl_lp_names = get_module_main_tbl( array('desktop_portlet_data') );
        $this->tblnote = $tbl_lp_names['desktop_portlet_data'];
    }

    protected function getId()
    {
        return (int) $this->id;
    }

    protected function setId( $id )
    {
        $this->id = (int) $id;
    }

    protected function getLabel()
    {
        return $this->label;
    }

    protected function setLabel( $value )
    {
        $this->label = trim($value);
    }

    protected function getNote()
    {
        return $this->note;
    }

    protected function setNote( $value )
    {
        $this->note = trim($value);
    }

    protected function load()
    {
        $sql = "SELECT
                    `id`,
                    `label`,
                    `idUser`,
                    `data`
                FROM `".$this->tblnote."`
                WHERE id = '". (int) $this->id . "'
                AND `idUser` = '" .(int) claro_get_current_user_id() . "'"
                ;

        $data = claro_sql_query_get_single_row($sql);

        if( !empty($data) )
        {
            // from query
            $this->id = (int) $data['id'];
            $this->label = $data['label'];
            $this->note = $data['data'];

            return true;
        }
        else
        {
            return false;
        }
    }

    protected function loadAll()
    {
        $sql = "SELECT
                    `id`,
                    `label`,
                    `idUser`,
                    `data`
                FROM `" . $this->tblnote . "`
                WHERE `idUser` = '" . claro_get_current_user_id() . "'
                ORDER BY `id`"
                ;

        if ( false === ( $data = claro_sql_query_fetch_all_rows($sql) ) )
        {
            return false;
        }
        else
        {
            return $data;
        }
    }

    protected function save()
    {
        if( ! $this->getId() )
        {
            // insert
            $sql = "INSERT INTO `" . $this->tblnote . "`
                    SET `label` = '" . claro_sql_escape( $this->getLabel() ) . "',
                        `idUser` = '" . claro_sql_escape( claro_get_current_user_id() ) . "',
                        `data` = '" . claro_sql_escape( $this->getNote() ) . "'"
                    ;

            // execute the creation query and get id of inserted assignment
            $insertedId = claro_sql_query_insert_id($sql);

            if( $insertedId )
            {
                $this->id = (int) $insertedId;

                return $this->id;
            }
            else
            {
                return false;
            }
        }
        else
        {
            // update, main query
            // do not update creation time and author id on update
            $sql = "UPDATE `" . $this->tblnote . "`
                    SET `label` = '" . claro_sql_escape( $this->getLabel() ) . "',
                        `data` = '" . claro_sql_escape( $this->getNote() ) . "'
                    WHERE `id` = '" . $this->id . "'
                    AND `idUser` = '" . claro_get_current_user_id() . "'"
                    ;

            // execute and return main query
            if( claro_sql_query($sql) )
            {
                return $this->id;
            }
            else
            {
                return false;
            }
        }
     }

    protected function delete()
    {
         if( !$this->getId() ) return true;

        $sql = "DELETE FROM `" . $this->tblnote . "`
                WHERE `id` = " . $this->getId() ."
                AND `idUser` = '" . claro_get_current_user_id() ."'"
                ;

        if( claro_sql_query($sql) == false ) return false;

        $this->setId(0);
        return true;
    }

    protected function validate()
    {
        $errorForm = null;

        $note = strip_tags( $this->getNote() );

        if( empty( $note ) )
        {
            $errorForm = true;
        }

        if( $errorForm )
        {
            return false;
        }
        else
        {
            return true;
        }
    }


    public function renderContent()
    {

// {{{ SCRIPT INITIALISATION

        $dialogBox = new DialogBox();

        $acceptedCmdList = array(
        'rqCreate',
        'exCreate',
        'rqEdit',
        'exEdit',
        'rqDelete',
        'exDelete'
        );

        ( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) ) ? $cmd = $_REQUEST['cmd'] : $cmd = null;

        ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) ? $id = (int) $_REQUEST['id'] : $id = null;

// }}}

    
    
    // $jsloader = JavascriptLoader::getInstance();
    // $jsloader->load('jquery');
    // $jsloader->load('Unobtrusivelib');
    
    // $htmlHeaders = "\n"   
    // .   '<script type="text/javascript">' . "\n"
    // .   'jQuery(function(){'
    // .       'jQuery.unobtrusivelib();'
    // .   '});'
    // .   '</script>' . "\n"
    // ;

    // $claroline->display->header->addHtmlHeader($htmlHeaders);
    
    

// {{{ CONTROLLER

        if( !is_null($id) )
        {
            $this->setId( $id );

            if( !$this->load() )
            {
                $cmd = null;
                $id = null;
            }
        }

        if( $cmd == 'exDelete' )
        {

            $this->setId( $id );

            if( $this->delete() )
            {
                $dialogBox->success( get_lang('Note deleted !') );
            }
            else
            {
                $dialogBox->error( get_lang('Note not deleted !') );
            }
        }

        if( $cmd == 'rqDelete' )
        {
            $htmlConfirmDelete = get_lang('Are you sure to delete note ?')
            .     '<br /><br />'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id='.$_REQUEST['id'].'">' . get_lang('Yes') . '</a>'
            .    '&nbsp;|&nbsp;'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '">' . get_lang('No') . '</a>'
            ;

            $dialogBox->question( $htmlConfirmDelete );
        }

        if( $cmd == 'exCreate' )
        {
            $this->setNote( $_REQUEST['note'] );

            if( $this->validate() )
            {
                if( $insertedId = $this->save() )
                {
                    if( is_null($id) )
                    {
                        $dialogBox->success( get_lang('Note successfully created') );
                        $id = $insertedId;
                    }
                    else
                    {
                        $dialogBox->success( get_lang('Note successfully modified') );
                    }
                }
                else
                {
                    $cmd = 'rqEdit';
                }
            }
            else
            {
                $dialogBox->error( get_lang('The fields Notes is required !') );
                $cmd = 'rqCreate';
            }
        }

        if( $cmd == 'rqCreate' )
        {
            // show form
            $htmlEditForm = "\n\n";

            $htmlEditForm .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
            .    claro_form_relay_context()
            .    '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' . "\n"
            .    '<input type="hidden" name="cmd" value="exCreate" />' . "\n"
            ;

            if( !is_null($id) )
            {
                $htmlEditForm .= '<input type="hidden" name="id" value="' . $this->getId() . '" />' . "\n";
            }

            // note
            $htmlEditForm .= "\n"
            .    '<strong>' . get_lang('Add note') . ' : </strong><br />' . "\n"
            .    '<label for="note">' . get_lang('Notes') . '</label>&nbsp;<span class="required">*</span> : ' . "\n"
            .    '<textarea name="note" id="note" cols="50" rows="5">' . $this->getNote() . '</textarea>' . "\n"
            .    '<br /><br />' . "\n"
            // end form
            .    '<span class="required">*</span>&nbsp;'.get_lang('Denotes required fields') . '<br />' . "\n"
            .    '<input type="submit" value="' . get_lang('Ok') . '" />&nbsp;' . "\n"
            .    claro_html_button(htmlspecialchars( $_SERVER['PHP_SELF'] ), get_lang('Cancel'))
            .    '</form>' . "\n"
            ;

            $dialogBox->form($htmlEditForm);
        }
// }}}

// {{{ VIEW

        $menu[] = '<a href="' . htmlspecialchars( $_SERVER['PHP_SELF'] . '?cmd=rqCreate' ) . '" class="claroCmd">' . get_lang('Add note') . '</a>' . "\n";

        $output = claro_html_menu_horizontal( $menu );

        $output .= '<br />' . "\n";

        $output .= $dialogBox->render();

        $output .= '<br />' . "\n";

        $output .= "\n"
        .    '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
        .    '<thead>' . "\n"
        .      '<tr class="headerX" align="center" valign="top">' . "\n"
        .        '<th>' . get_lang('Notes') . '</th>' . "\n"
        .        '<th>' . get_lang('Edit') . '</th>' . "\n"
        .        '<th>' . get_lang('Delete') . '</th>' . "\n"
        .      '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        ;

        if( $allNotes = $this->loadAll() )
        {
            foreach( $allNotes as $note )
            {
                $output .= "\n"
                .      '<tr>' . "\n"
                .       '<td>' . $note['data'] . '</td>' . "\n"
                .       '<td align="center"><a href="' . htmlspecialchars( $_SERVER['PHP_SELF'] . '?cmd=rqCreate&amp;id=' . (int)$note['id'] ) . '"><img src="' . get_icon_url('Edit') . '" alt="' . get_lang('Edit') . '" /></a></td>' . "\n"
                .       '<td align="center"><a href="' . htmlspecialchars( $_SERVER['PHP_SELF'] . '?cmd=rqDelete&amp;id=' . (int)$note['id'] ) . '"><img src="' . get_icon_url('Delete') . '" alt="' . get_lang('Delete') . '" /></a></td>' . "\n"
                .      '</tr>' . "\n"
                ;
            }
        }
        else
        {
            $output .= "\n"
            .      '<tr>' . "\n"
            .       '<td align="center" colspan="3">' . get_lang('Empty') . '</td>' . "\n"
            .      '</tr>' . "\n"
            ;        
        }

        $output .= "\n"
        .    '</tbody>' . "\n"
        .    '</table>' . "\n"
        ;
// }}}
        return $output;
    }

    public function renderTitle()
    {
        return get_lang('My notes');
    }
}
?>