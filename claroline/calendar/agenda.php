<?php // $Id$
/**
 * CLAROLINE 
 *
 * - For a Student -> View agenda Content
 * - For a Prof    -> - View agenda Content
 *         - Update/delete existing entries
 *         - Add entries
 *         - generate an "announce" entries about an entries
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 */

$tlabelReq = 'CLCAL___';

require '../inc/claro_init_global.inc.php';
require_once($clarolineRepositorySys . '/linker/linker.inc.php');
require_once($includePath . '/lib/agenda.lib.php');

claro_unquote_gpc();

define('CONFVAL_LOG_CALENDAR_INSERT', FALSE);
define('CONFVAL_LOG_CALENDAR_DELETE', FALSE);
define('CONFVAL_LOG_CALENDAR_UPDATE', FALSE);

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$nameTools = $langAgenda;

claro_set_display_mode_available(TRUE);

$is_allowedToEdit   = $is_courseAdmin;


if ( $is_allowedToEdit )
{
    if ( !isset($_REQUEST['cmd']) )
    {
        linker_init_session();
    }

    if( $jpspanEnabled )
    {
        linker_set_local_crl( isset ($_REQUEST['id']) );
    }

    if ( isset($_REQUEST['cmd'])
         && ( $_REQUEST['cmd'] == 'rqAdd' || $_REQUEST['cmd'] == 'rqEdit' ) 
       )
    {
        linker_html_head_xtra();
    }
}


//stats
include($includePath . '/lib/events.lib.inc.php');
event_access_tool($_tid, $_courseTool['label']);


$tbl_c_names = claro_sql_get_course_tbl();
$tbl_calendar_event = $tbl_c_names['calendar_event'];

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

$dialogBox = '';

if     ( $cmd == 'rqAdd' ) $subTitle = $langAddEvent;
elseif ( $cmd == 'rqEdit') $subTitle = $langEditEvent;
else                       $subTitle = '';


$is_allowedToEdit = claro_is_allowed_to_edit();
/**
 * COMMANDS SECTION
 */

$display_form = FALSE;
$display_command = FALSE;

if ( $is_allowedToEdit )
{
    if ( isset($_REQUEST['id']) ) $id = (int) $_REQUEST['id'];
    else                          $id = 0;

    if ( isset($_REQUEST['title']) ) $title = trim($_REQUEST['title']);
    else                             $title = '';

    if ( isset($_REQUEST['content']) ) $content = trim($_REQUEST['content']);
    else                               $content = '';

    $lasting = ( isset($_REQUEST['content']) ? trim($_REQUEST['lasting']) : '');

    $ex_rss_refresh = FALSE;
    if ( $cmd == 'exAdd' )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        $insert_id = agenda_add_item($title,$content, $date_selection, $hour, $lasting) ;
        if ( $insert_id != false )
        {
            $dialogBox .= '<p>' . $langEventAdded . '</p>';
            $dialogBox .= linker_update(); //return textual error msg

            if ( CONFVAL_LOG_CALENDAR_INSERT )
            {
                event_default('CALENDAR', array ('ADD_ENTRY' => $entryId));
            }

            // notify that a new agenda event has been posted

            $eventNotifier->notifyCourseEvent('agenda_event_added', $_cid, $_tid, $insert_id, $_gid, '0');
            $ex_rss_refresh = TRUE;

        }
        else
        {
            $dialogBox .= '<p>' . $langUnableToAdd . '</p>';
        }
    }

    /*------------------------------------------------------------------------
    EDIT EVENT COMMAND
    --------------------------------------------------------------------------*/


    if ( $cmd == 'exEdit' )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        if ( !empty($id) )
        {
            if ( agenda_update_item($id,$title,$content,$date_selection,$hour,$lasting))
{
                $dialogBox .= linker_update(); //return textual error msg
                $eventNotifier->notifyCourseEvent('agenda_event_modified', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
                $ex_rss_refresh = TRUE;
                $dialogBox .= '<p>' . $langEventUpdated . '</p>';
            }
            else
            {
                $dialogBox .= '<p>' . $langUnableToUpdate . '</p>';
            }
        }
    }

    /*------------------------------------------------------------------------
    DELETE EVENT COMMAND
    --------------------------------------------------------------------------*/

    if ( $cmd == 'exDelete' && !empty($id) )
    {

        if ( agenda_delete_item($id) )
        {
            $dialogBox .= '<p>' . $langEventDeleted . '</p>';
            
            $eventNotifier->notifyCourseEvent('agenda_event_deleted', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
            $ex_rss_refresh = TRUE;
            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                event_default('CALENDAR',array ('DELETE_ENTRY' => $id));
            }
        }
        else
        {
            $dialogBox = '<p>' . $langUnableToDelete . '</p>';
        }

    }

    /*----------------------------------------------------------------------------
    DELETE ALL EVENTS COMMAND
    ----------------------------------------------------------------------------*/

    if ( $cmd == 'exDeleteAll' )
    {
        if ( agenda_delete_all_items())
        {
            $dialogBox .= '<p>' . $langEventDeleted . '</p>';

            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                event_default('CALENDAR', array ('DELETE_ENTRY' => 'ALL') );
            }
        }
        else
        {
            $dialogBox = '<p>' . $langUnableToDelete . '</p>';
        }
    }
    /*-------------------------------------------------------------------------
    EDIT EVENT VISIBILITY
    ---------------------------------------------------------------------------*/


    if ($cmd == 'mkShow' || $cmd == 'mkHide')
    {
        if ($cmd == 'mkShow')  
        {
            $visibility = 'SHOW';
            $eventNotifier->notifyCourseEvent('agenda_event_visible', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
            $ex_rss_refresh = TRUE;
        }
        
        if ($cmd == 'mkHide')  
        {
            $visibility = 'HIDE';
            $eventNotifier->notifyCourseEvent('agenda_event_invisible', $_cid, $_tid, $id, $_gid, '0'); // notify changes to event manager
            $ex_rss_refresh = TRUE;
        }

        if ( agenda_set_item_visibility($id, $visibility)  )
        {
            $dialogBox = $langViMod;
        }
//        else
//        {
//            //error on delete
//        }
    }

    /*------------------------------------------------------------------------
    EVENT EDIT
    --------------------------------------------------------------------------*/

    if ( $cmd == 'rqEdit' || $cmd == 'rqAdd' )
    {
    	claro_set_display_mode_available(false);
        
        if ( $cmd == 'rqEdit' && !empty($id) )
        {
            $editedEvent = agenda_get_item($id) ;
            $nextCommand = 'exEdit';
        }
        else
        {
            $editedEvent['id'            ] = '';
            $editedEvent['title'         ] = '';
            $editedEvent['content'       ] = '';
            $editedEvent['dayAncient'    ] = FALSE;
            $editedEvent['hourAncient'   ] = FALSE;
            $editedEvent['lastingAncient'] = FALSE;

            $nextCommand = 'exAdd';

        }
        $display_form =TRUE; 
    } // end if cmd == 'rqEdit' && cmd == 'rqAdd'


    if ($cmd != 'rqEdit' && $cmd != 'rqAdd') // display main commands only if we're not in the event form
    {
        $display_command = TRUE;
    } // end if diplayMainCommands
    
    // rss update
    if ($ex_rss_refresh && file_exists('./agenda.rssgen.inc.php'))
    {
        include('./agenda.rssgen.inc.php');
    }

} // end id is_allowed to edit









/**
 *     DISPLAY SECTION
 *                    
 */
include($includePath . '/claro_init_header.inc.php');
echo claro_disp_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

if ( !empty($dialogBox) ) echo claro_disp_message_box($dialogBox);


if ($display_form)
{
?>
<form onSubmit="linker_confirm();delay(500);return true;" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<input type="hidden" name="cmd" value="<?php echo $nextCommand       ?>"> 
<input type="hidden" name="id"  value="<?php echo $editedEvent['id'] ?>">

<table>

<tr>
<td>&nbsp;</td>
<td><label for="fday"><?php echo $langDay;    ?></label></td>
<td><label for="fmonth"><?php echo $langMonth;  ?></label></td>
<td><label for="fyear"><?php echo $langYear;   ?></label></td>
<td><label for="fhour"><?php echo $langHour;   ?></label></td>
<td><label for="fminute"><?php echo $langMinute; ?></label></td>
<td><label for="lasting"><?php echo $langLasting ?></label></td>
</tr>

<?php 

$day     = date('d');
$month   = date('m');
$year    = date('Y');
$hours   = date('H');
$minutes = date('i');

if ($editedEvent['hourAncient'])
{
    list($hours, $minutes) = split(':', $editedEvent['hourAncient']);
}

if ($editedEvent['dayAncient'])
{
    list($year, $month, $day) = split('-',  $editedEvent['dayAncient']);
}

$title   = $editedEvent['title'];
$content = $editedEvent['content'];

?>
<tr>

<td>&nbsp</td>

<td>
<select name="fday" id="fday">
<option value="<?php echo $day ?>" selected>[<?php echo $day ?>]</option>
<option value="01">1</option>
<option value="02">2</option>
<option value="03">3</option>
<option value="04">4</option>
<option value="05">5</option>
<option value="06">6</option>
<option value="07">7</option>
<option value="08">8</option>
<option value="09">9</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
<option value="24">24</option>
<option value="25">25</option>
<option value="26">26</option>
<option value="27">27</option>
<option value="28">28</option>
<option value="29">29</option>
<option value="30">30</option>
<option value="31">31</option>
</select>
</td>

<td>

<select name="fmonth" id="fmonth">
<option value="<?php echo $month ?>" selected>
[<?php echo $langMonthNames['long'][ $month - 1 ] ?>]
</option>
<option value="01"><?php echo $langMonthNames['long'][0] ?></option>
<option value="02"><?php echo $langMonthNames['long'][1] ?></option>
<option value="03"><?php echo $langMonthNames['long'][2] ?></option>
<option value="04"><?php echo $langMonthNames['long'][3] ?></option>
<option value="05"><?php echo $langMonthNames['long'][4] ?></option>
<option value="06"><?php echo $langMonthNames['long'][5] ?></option>
<option value="07"><?php echo $langMonthNames['long'][6] ?></option>
<option value="08"><?php echo $langMonthNames['long'][7] ?></option>
<option value="09"><?php echo $langMonthNames['long'][8] ?></option>
<option value="10"><?php echo $langMonthNames['long'][9] ?></option>
<option value="11"><?php echo $langMonthNames['long'][10] ?></option>
<option value="12"><?php echo $langMonthNames['long'][11] ?></option>
</select>
</td>

<td>
<select name="fyear" id="fyear">
<option value="<?php echo $year -1 ?>"><?php echo $year -1 ?></option>
<option value="<?php echo $year ?>"  selected>[<?php echo $year ?>]</option>
<option value="<?php echo $year +1 ?>"><?php echo $year +1 ?></option>
<option value="<?php echo $year +2 ?>"><?php echo $year +2 ?></option>
<option value="<?php echo $year +3 ?>"><?php echo $year +3 ?></option>
<option value="<?php echo $year +4 ?>"><?php echo $year +4 ?></option>
<option value="<?php echo $year +5 ?>"><?php echo $year +5 ?></option>
</select>
</td>

<td>

<select name="fhour" id="fhour">
<option value="<?php echo $hours ?>">
[<?php echo $hours ?>]
</option>
<option value="00">00</option>
<option value="01">01</option>
<option value="02">02</option>
<option value="03">03</option>
<option value="04">04</option>
<option value="05">05</option>
<option value="06">06</option>
<option value="07">07</option>
<option value="08">08</option>
<option value="09">09</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
</select>

</td>
<td>

<select name="fminute" id="fminute">
<option value="<?php echo $minutes ?>">[<?php echo $minutes ?>]</option>
<option value="00">00</option>
<option value="05">05</option>
<option value="10">10</option>
<option value="15">15</option>
<option value="20">20</option>
<option value="25">25</option>
<option value="30">30</option>
<option value="35">35</option>
<option value="40">40</option>
<option value="45">45</option>
<option value="50">50</option>
<option value="55">55</option>
</select>

</td>

<td>
    <input type="text" name="lasting" id="lasting" size="20" maxlength="20" value="<?php echo $editedEvent['lastingAncient']; ?>">
</td>

</tr>


<tr>
<td valign="top"><label for="title"><?php echo $langTitle ?> : </label></td>

<td colspan="6"> 
<input size="80" type="text" name="title" id="title" value="<?php  echo isset($title) ? htmlspecialchars($title) : '' ?>">   
</td>
</tr>

<tr> 

<td valign="top">
<label for="content"><?php echo $langDetail ?> : </label>
</td>

<td colspan="6"> 
<?php echo claro_disp_html_area('content', htmlspecialchars($content), 12, 67, $optAttrib = ' wrap="virtual" '); ?>
<br>

</td></tr>
<tr>
<td>&nbsp;</td>
<td colspan="6">

<?php 
//---------------------
// linker

if( $jpspanEnabled )
{
    linker_set_local_crl( isset ($_REQUEST['id']) );
    linker_set_display();
}
else // popup mode
{
    if(isset($_REQUEST['id'])) linker_set_display($_REQUEST['id']);
    else                       linker_set_display();
}

echo '</td></tr>' . "\n"
.    '<tr><td>&nbsp;</td><td colspan="6">' . "\n";

if( $jpspanEnabled )
{
    echo '<input type="submit" onClick="linker_confirm();"  class="claroButton" name="submitEvent" value="' . $langOk . '">' . "\n";
}
else // popup mode
{
    echo '<input type="submit" class="claroButton" name="submitEvent" value="' . $langOk . '">' . "\n";
}

// linker
//---------------------
echo claro_disp_button($_SERVER['PHP_SELF'], 'Cancel');

?>
</td>

</tr>

</table>

</form>
<?php
}

if ($display_command)
{
        echo '<p>'

        /*
        * Add event button
        */

        .    '<a class="claroCmd" href="'.$_SERVER['PHP_SELF'].'?cmd=rqAdd">'
        .    '<img src="'.$imgRepositoryWeb.'agenda.gif" alt="">'
        .    $langAddEvent
        .    '</a>'
        .    ' | '

        /*
        * remove all event button
        */

        .    '<a class= "claroCmd" href="'.$_SERVER['PHP_SELF'].'?cmd=exDeleteAll" '
        .    ' onclick="if (confirm(\''.clean_str_for_javascript($langClearList).' ? \')){return true;}else{return false;}">'
        .    '<img src="'.$imgRepositoryWeb.'delete.gif" alt="">'
        .    $langClearList
        .    '</a>'
        .    '</p>'
        ;

}

echo '<table class="claroTable" width="100%">' . "\n";

if( isset($_REQUEST['order']) && $_REQUEST['order'] == 'desc' )
{
    $orderDirection = 'DESC';
}
else
{
    $orderDirection = 'ASC';
}

$eventList = agenda_get_item_list($orderDirection);

$monthBar     = '';

if ( count($eventList) < 1 )
{
    echo '<br><blockquote>' . $langNoEventInTheAgenda . '</blockquote>';
}
else
{
    if ( $orderDirection == 'DESC' )
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?order=asc" >' . $langOldToNew . '</a>' . "\n";
    }
    else
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?order=desc" >' . $langNewToOld . '</a>' . "\n";
    }
}

$nowBarAlreadyShowed = FALSE;

foreach ( $eventList as $thisEvent )
{

    if (($thisEvent['visibility']=='HIDE' && $is_allowedToEdit) || $thisEvent['visibility']=='SHOW')
    {
        $style = $thisEvent['visibility']=='HIDE' ?'invisible' : $style='';

        // TREAT "NOW" BAR CASE
        if ( ! $nowBarAlreadyShowed )
        if (( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) > time() ) && $orderDirection == 'ASC'  )
        ||
        ( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) < time() ) && $orderDirection == 'DESC' )
        )
        {
            if ($monthBar != date('m',time()))
            {
                $monthBar = date('m',time());

                echo '<tr>'."\n"
                .    '<th class="superHeader" colspan="2" valign="top">' . "\n"
                .    ucfirst(claro_disp_localised_date('%B %Y', time()))
                .    '</th>' . "\n"
                .    '</tr>' . "\n"
                ; 
            }


            // 'NOW' Bar

            echo '<tr>' . "\n"
            .    '<td>' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'pixel.gif" width="20" alt=" ">'
            .    '<span class="highlight">'
            .    '<i>'
            .    ucfirst(claro_disp_localised_date( $dateFormatLong)) . ' '
            .    ucfirst(strftime( $timeNoSecFormat))
            .    ' -- ' . $langNow
            .    '</i>'
            .    '</span>' . "\n"
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;

            $nowBarAlreadyShowed = TRUE;
        }

        /*
        * Display the month bar when the current month
        * is different from the current month bar
        */

        if ( $monthBar != date( 'm', strtotime($thisEvent['day']) ) )
        {
            $monthBar = date('m', strtotime($thisEvent['day']));

            echo '<tr>' . "\n"
            .    '<th class="superHeader" valign="top">' . "\n"
            .    ucfirst(claro_disp_localised_date('%B %Y', strtotime( $thisEvent['day']) ))
            .    '</th>' . "\n"
            .    '</tr>' . "\n"
            ;
        }

        /*
        * Display the event date
        */

        echo '<tr class="headerX" valign="top">' . "\n"
        .    '<th>' . "\n"
        .    '<a href="#form" name="event' . $thisEvent['id'] . '"></a>' . "\n"
        .    '<img src="' . $imgRepositoryWeb . 'agenda.gif" alt=" ">'
        .    ucfirst(claro_disp_localised_date( $dateFormatLong, strtotime($thisEvent['day']))).' '
        .    ucfirst( strftime( $timeNoSecFormat, strtotime($thisEvent['hour']))).' '
        .    ( empty($thisEvent['lasting']) ? '' : $langLasting.' : '.$thisEvent['lasting'] );

        /*
        * Display the event content
        */

        echo '</th>' . "\n"
        .    '</tr>' . "\n"
        .    '<tr>' . "\n"
        .    '<td>' . "\n"
        .    '<div class="content ' . $style . '">' . "\n"
        .    ( empty($thisEvent['title']  ) ? '' : '<p><strong>' . htmlspecialchars($thisEvent['title']) . '</strong></p>' . "\n" )
        .    ( empty($thisEvent['content']) ? '' :  claro_parse_user_text($thisEvent['content']) )
        .    '</div>' . "\n"
        ;
        linker_display_resource();
    }
    if ($is_allowedToEdit)

    {
        echo '<a href="' . $_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;id=' . $thisEvent['id'] . '">'
        .    '<img src="' . $imgRepositoryWeb.'edit.gif" border="O" alt="' . $langModify . '">'
        .    '</a> '
        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisEvent['id'] . '" '
        .    'onclick="javascript:if(!confirm(\''
        .    clean_str_for_javascript($langDelete . ' ' . $thisEvent['title'].' ?')
        .    '\')) {document.location=\'' . $_SERVER['PHP_SELF'] . '\'; return false}" >'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . $langDelete . '">'
        .    '</a>'
        ;

        //  Visibility
        if ($thisEvent['visibility']=='SHOW')
        {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisEvent['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb . 'visible.gif" alt="' . $langInvisible . '">'
            .    '</a>' . "\n";
        }
        else
        {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisEvent['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb . 'invisible.gif" alt="' . $langVisible . '">'
            .    '</a>' . "\n";
        }
    }
    echo '</td>'."\n"
    . '</tr>'."\n"
    ;

}   // end while

echo '</table>';

include( $includePath . '/claro_init_footer.inc.php' );

?>
