<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

// Confirm javascript code

$htmlHeadXtra[] =
          "<script type=\"text/javascript\">
           function confirm_delete(name)
           {
               if (confirm('". clean_str_for_javascript(get_lang('Are you sure to delete')) . " ' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }

           function confirm_empty(name)
           {
               if (confirm('". clean_str_for_javascript(get_lang('Delete all messages of')) . " ' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }
           </script>";

if( (bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__) ) ) die();
if( ! $is_allowedToEdit) die();

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd']; else $cmd = null;

if ( $cmd == 'exMkCat' )
{
    if ( trim($_REQUEST['catName']) != '')
    {
        if ( create_category( trim($_REQUEST['catName']) ) )
        {
           $dialogBox->success( get_lang('The new category has been created.') );
        }
        else
        {
            $dialogBox->error( get_lang('Unable to create category') );
            $cmd = 'rqMkCat';
        }
    }
    else
    {
         $dialogBox->error( get_lang('Missing field(s)') );
         $cmd = 'rqMkCat';
    }
}

if ( $cmd == 'rqMkCat' )
{
    if ( isset($_REQUEST['catName']) ) $catName = $_REQUEST['catName'];
    else                               $catName = '';

    $htmlAddCat = '<strong>'.get_lang('Add a category').'</strong>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .  '<input type="hidden" name="cmd" value="exMkCat" />'."\n"
               .  '<label for="catName">'.get_lang('Name').' : </label><br />'."\n"
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="' . $catName . '" /><br /><br />'."\n"
               .  '<input type="submit" value="'.get_lang('Ok').'" />&nbsp; '
               .  claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .  '</form>'
               .  "\n";

    $dialogBox->form($htmlAddCat);
}

if ( $cmd == 'exMkForum' )
{
    $forumPostAllowed = ( isset($_REQUEST['forumPostUnallowed']) ) ? false : true;

    if (   ( ( trim($_REQUEST['forumName']) != '') )
        && (   0 < (int) $_REQUEST['forumCatId']   )  )
    {
            if ( create_forum(trim($_REQUEST['forumName']),
                              trim($_REQUEST['forumDesc']),
                              $forumPostAllowed,
                              (int) $_REQUEST['forumCatId'] ) )
            {
               $dialogBox->success( get_lang('Forum created') );
            }
            else
            {
               $dialogBox->error( get_lang('Unable to create forum') );
               $cmd        = 'rqMkForum';
            }
    }
    else
    {
        $dialogBox->error( get_lang('Missing field(s)') );
        $cmd        = 'rqMkForum';
    }
}

if ( $cmd == 'rqMkForum' )
{
    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = get_lang('Category') . ' : <br />'."\n"
                       .'<select name="forumCatId">';

        foreach($formCategoryList as $thisFormCategory)
        {
            $catSelectBox .= '<option  value="'.$thisFormCategory['cat_id'].'">'
                          .  $thisFormCategory['cat_title']
                          .'</option>';
        }

        $catSelectBox .= '</select><br />'."\n";
    }
    else
    {
        $catSelectBox = '';
    }

    $reqForumName = isset($_REQUEST['forumName']) ? $_REQUEST['forumName'] : '';
    $reqForumDesc = isset($_REQUEST['forumDesc']) ? $_REQUEST['forumDesc'] : '';

    $reqForumPostUnallowedState = isset($_REQUEST['forumPostUnallowed']) ?
                                  ' checked ' : '';


    $htmlAddForum = '<strong>'.get_lang('Add forum').'</strong>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
               .'<input type="hidden" name="cmd" value="exMkForum" />'."\n"
               .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .'<label for="forumName">'.get_lang('Name').': </label><br />'."\n"
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.$reqForumName.'" /><br />'."\n"
               .'<label for="forumDesc">' . get_lang('Description') . ' : </label><br />'."\n"
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'."\n"
               .$reqForumDesc
               .'</textarea><br />'."\n"
               .$catSelectBox."\n"
               .'<br />'."\n"
               .'<input type="checkbox" id="forumPostUnallowed" name="forumPostUnallowed" '.$reqForumPostUnallowedState.' />'."\n"
               .'<label for="forumPostUnallowed">'.get_lang('Locked').' <small>('.get_lang('No new post allowed').')</small></label><br />'."\n"
               .'<br />'."\n"
               .'<input type="submit" value="'.get_lang('Ok').'" />&nbsp; '
               . claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .'</form>'."\n\n";

    $dialogBox->form($htmlAddForum);
}

if ( $cmd == 'exEdCat' )
{
    if ( trim($_REQUEST['catName']) != '' )
    {
        if ( update_category_title( $_REQUEST['catId'], $_REQUEST['catName'] ) )
        {
            $dialogBox->success( get_lang('Category updated') );
        }
        else
        {
            $dialogBox->error( get_lang('Unable to update category') );
        }
    }
    else
    {
        $dialogBox->error( get_lang('Missing field(s)') );
        $cmd        = 'rqEdCat';
    }
}

if ( $cmd == 'rqEdCat' )
{
    $categorySettingList = get_category_settings($_REQUEST['catId']);

    if ( $categorySettingList )
    {
        $htmlEditCat = '<strong>'.get_lang('Edit category').'</strong>'."\n"
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .  '<input type="hidden" name="catId" value="'.$categorySettingList['cat_id'].'" />'."\n"
               .  '<input type="hidden" name="cmd" value="exEdCat" />'."\n"
               .  '<label for="catName">'.get_lang('Name').' : </label><br />'."\n"
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="'.$categorySettingList['cat_title'].'" /><br /><br />'."\n"
               .  '<input type="submit" value="'.get_lang('Ok').'" />&nbsp; '
               .  claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .  '</form>'."\n"
               .  "\n";

        $dialogBox->form($htmlEditCat);
    }
}

if ( $cmd == 'exEdForum' )
{
    $forumPostAllowed = ( isset($_REQUEST['forumPostUnallowed']) ) ? false : true;

    if ( trim($_REQUEST['forumName'] != '') )
    {
        if ( update_forum_settings($_REQUEST['forumId'   ], $_REQUEST['forumName'],
                                   $_REQUEST['forumDesc' ], $forumPostAllowed,
                                   $_REQUEST['forumCatId']) )
        {
            $dialogBox->success( get_lang('Forum updated') );
        }
        else
        {
            $dialogBox->error( get_lang('Unable to update forum') );
        }
    }
    else
    {
        $dialogBox->error( get_lang('Missing field(s)') );
        $cmd        = 'rqEdForum';
    }
}

if ( $cmd == 'rqEdForum' )
{
    $forumSettingList = get_forum_settings($_REQUEST['forumId']);

    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = get_lang('Category') . ' : <br />'."\n"
                       .'<select name="forumCatId">';

        foreach( $formCategoryList as $thisFormCategory )
        {
            if ( $forumSettingList['cat_id'] == $thisFormCategory['cat_id'] )
            {
                $selectedState = ' selected="selected" ';
            }
            else
            {
                $selectedState = '';

            }
            $catSelectBox .= '<option  value="'.$thisFormCategory['cat_id'].'"'.$selectedState.'>'
                          .  htmlspecialchars($thisFormCategory['cat_title'])
                          .'</option>';
        }

        $catSelectBox .= '</select><br />'."\n";
    }
    else
    {
        $catSelectBox = '';
    }

    $formForumNameValue        = isset($_REQUEST['forumName']) ?
                                 $_REQUEST['forumName'] : $forumSettingList['forum_name'];

    $formForumDescriptionValue = isset($_REQUEST['forumDesc']) ?
                                 $_REQUEST['forumDesc'] : $forumSettingList['forum_desc'];

    $formForumPostUnallowedState = $_REQUEST['cmd'] == 'exEdForum' ?
                                    ( isset($_REQUEST['forumPostUnallowed']) ? ' checked="checked" ' : '' )
                                   :
                                    ( $forumSettingList['forum_access'] == 0 ? '  checked="checked" ' : '' );

    $htmlEditForum = '<strong>'.get_lang('Edit forum').'</strong>'."\n"
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
               .'<input type="hidden" name="cmd" value="exEdForum" />'."\n"
               .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .'<input type="hidden" name="forumId" value="'.$forumSettingList['forum_id'].'" />'."\n"
               .'<label for="forumName">'.get_lang('Name').': </label><br />'."\n"
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.htmlspecialchars($formForumNameValue).'" /><br />'."\n"
               .'<label for="forumDesc">' . get_lang('Description') . ' : </label><br />'."\n"
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'."\n"
               .htmlspecialchars($formForumDescriptionValue)
               .'</textarea><br />'."\n"
               .$catSelectBox."\n"
               .'<br />'."\n"
               .'<input type="checkbox" id="forumPostUnallowed" name="forumPostUnallowed" '.$formForumPostUnallowedState.' />'."\n"
               .'<label for="forumPostUnallowed">'.get_lang('Locked').' <small>('.get_lang('No new post allowed').')</small></label><br />'."\n"
               .'<br />'."\n"
               .'<input type="submit" value="'.get_lang('Ok').'" />&nbsp; '
               . claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .'</form>'."\n\n";

    $dialogBox->form($htmlEditForum);

}

if ( $cmd == 'exDelCat' )
{
    if ( delete_category($_REQUEST['catId']) )
    {
        $dialogBox->success( get_lang('Category deleted') );
    }
    else
    {
        $dialogBox->error( get_lang('Unable to delete category') );

        if ( claro_failure::get_last_failure() == 'GROUP_FORUMS_CATEGORY_REMOVALE_FORBIDDEN' )
        {
            $dialogBox->error( get_lang('Group forums category can\'t be deleted') );
        }
        elseif(claro_failure::get_last_failure() == 'GROUP_FORUM_REMOVALE_FORBIDDEN')
        {
            $dialogBox->error( get_lang('You can not remove a group forum. You have to remove the group first') );
        }

    }
}

if ( $cmd == 'exDelForum' )
{
    $forumSettingList = get_forum_settings($_REQUEST['forumId']);

    if ( is_null($forumSettingList['idGroup']) )
    {
        if ( delete_forum ($_REQUEST['forumId']) )
        {
            $dialogBox->success( get_lang('Forum deleted') );
        }
        else
        {
            $dialogBox->error( get_lang('Unable to delete Forum') );
        }
    }
    else
    {
            $dialogBox->error( get_lang('You can\'t remove a group forum. You have to remove the group first') );
    }
}

if ( $cmd == 'exEmptyForum' )
{
    if ( delete_all_post_in_forum($_REQUEST['forumId']) )
    {
        $dialogBox->success( get_lang('Forum emptied') );
    }
    else
    {
        $dialogBox->error( get_lang('Unable to empty forum') );
    }
}

if ( $cmd == 'exMvUpCat' )
{
    move_up_category($_REQUEST['catId']);
}

if ( $cmd == 'exMvDownCat')
{
    move_down_category($_REQUEST['catId']);
}

if ( $cmd == 'exMvUpForum' )
{
    move_up_forum($_REQUEST['forumId']);
}

if ( $cmd == 'exMvDownForum' )
{
    move_down_forum($_REQUEST['forumId']);
}

?>