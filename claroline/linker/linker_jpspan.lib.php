<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
    * linker_jpspan.lib
    *
    * is a lib of function for the linker Jpspan.  
    * @package CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    **/
    
    
   /**
    * load the Javascript which will be necessary 
    * to the execution of jpspan
    */    
    function linker_html_head_xtra()
    {
        global $htmlHeadXtra;
        global $claroBodyOnload;
        global $_course;
        require_once(get_path('incRepositorySys') . '/lib/JPSpan/JPSpan.php');
        require_once(get_path('incRepositorySys') . '/lib/JPSpan/JPSpan/Include.php');
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\" src=\""
            . path() ."/linker_jpspan_server.php?client\"></script>\n"
            ;
        
        //lang variable
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_add = '".addslashes(get_lang("Attach"))."';</script>\n";    
                
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_delete = '".addslashes(get_lang("Delete"))."';</script>\n";    
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_empty = '".addslashes(get_lang("Empty"))."';</script>\n";    
                
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_up = '".addslashes(get_lang("Up"))."';</script>\n";    
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_my_other_courses = '".addslashes(get_lang("My other courses"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_public_courses = '".addslashes(get_lang("Public courses"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_external_link = '".addslashes(get_lang("External link"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_attachements = '".addslashes(get_lang("Attached resources"))."';</script>\n";
                
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_already_in_attachement_list = '".addslashes(get_lang("%itemName is already attached"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_linker_add_new_attachment = '".addslashes(get_lang("Attach an existing resource"))."';</script>\n"; 
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_linker_close = '".addslashes(get_lang("Close"))."';</script>\n"; 
                
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_linker_close = '".addslashes(get_lang("Close"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_linker_prompt_for_url = '".addslashes(get_lang("Enter link url"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_linker_prompt_invalid_url = '".addslashes(get_lang("Invalid url"))."';</script>\n";
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var lang_linker_prompt_invalid_email = '".addslashes(get_lang("Invalid email address"))."';</script>\n";
        
        //javascript function 
        $htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"" 
            . path() . "/arrayutils.js\"></script>\n"
            ;
        $htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"" 
            . path() . "/prompt_utils.js\"></script>\n"
            ;    
        $htmlHeadXtra[] = "<script type=\"text/javascript\" src=\""
            . path() . "/linker_jpspan_display.js\"></script>\n"
            ;
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
            . "var linklistallreadysubmitted = false;</script>\n"
            ;    

        // config variable
        if( get_conf('otherCoursesAllowed') )
        {    
            $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var other_courses_allowed = true;</script>\n";
        }
        else
        {
            $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var other_courses_allowed = false;</script>\n";
        }
        
        if( get_conf('publicCoursesAllowed') )
        {    
            $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var public_courses_allowed = true;</script>\n";
        }
        else
        {
            $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var public_courses_allowed = false;</script>\n";    
        }    
        
        if( get_conf('externalLinkAllowed') )
        {    
            $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var external_link_allowed = true;</script>\n";
        }
        else
        {
            $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var external_link_allowed = false;</script>\n";    
        }    
        
        // other variable 
        $courseCrl = CRLTool::createCRL(get_conf('platform_id'),claro_get_current_course_id());
        $htmlHeadXtra[] = "<script type=\"text/javascript\">
                var coursecrl = '".$courseCrl."';</script>\n";    
        
        
        $htmlHeadXtra[] = "<script type=\"text/javascript\">"
                . "var img_repository_web  = '" . get_path('imgRepositoryWeb') ."';</script>\n";
                
        $claroBodyOnload[] = "clear_all();";    
        $claroBodyOnload[] = "hide_div('navbox');";    
        $claroBodyOnload[] = "init_shopping_cart();";
    }
    
   /**
    * set the id of resource in the sript 
    * what makes it possible jpspan to recover this id     
    *
    * @param $isSetResouceId integer of the resource
    * @param $tLabel tlabel of a tool
    * @global array htmlHeadXtra 
    */    
    function linker_set_local_crl( $isSetResouceId, $tLabel = NULL )
    {
        global $htmlHeadXtra;
        
        if( $isSetResouceId )
        {
            $crlSource =  getSourceCrl( $tLabel );
        
            $htmlHeadXtra[] = "<script type=\"text/javascript\">
                var localcrl = '".$crlSource."';</script>\n";
        }
        else
        {
            $htmlHeadXtra[] = "<script type=\"text/javascript\">
                var localcrl = false;</script>\n";                
        }
    }
    
    /**
    * the dislay of the linker 
    *
    * @param $extraGetVar not use in jpspan 
    *    but left in respect to the linker api
    * @param $tLabel not use in jpspan
    *    but left in respect to the linker api
    */    
    function linker_set_display( $extraGetVar = false, $tLabel = NULL )
    {   
        echo '<div id="shoppingCart" style="width:100%">' . "\n"
        .    '</div>' . "\n"
        .    '<div style="margin-top : 1em;margin-bottom : 1em;" id="openCloseAttachment">' . "\n"
        .    '<a href="#btn" name="btn" onclick="change_button(\'open\');return false;">' . get_lang("Attach an existing resource") . '</a>' . "\n"
        .    '</div>' . "\n"
        .    '<div class="claroMessageBox" style="margin-top : 1em;margin-bottom : 1em;" id="navbox">' . "\n"
        .    '<div id="toolBar">' . "\n"
        .    '</div>' . "\n"
        .    '<div id="courseBar">' . "\n"
        .    '<hr />' . "\n"
        .    '</div>' . "\n"
        .    '<div id="nav">' . "\n"
        .    '</div>' . "\n"
        .    '</div>' . "\n"
        .    '<div id="hiddenFields" style="display:none;"></div>'
        ;
    }    
    
        
   

?>