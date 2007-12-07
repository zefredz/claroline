<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');

/*
+----------------------------------------------------------------------+
| CLAROLINE 1.7                                                        |
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
|   This program is free software; you can redistribute it and/or
|   modify it under the terms of the GNU General Public License
|   as published by the Free Software Foundation; either version 2
|   of the License, or (at your option) any later version.
+----------------------------------------------------------------------+
| Authors: Amand Tihon <amand@alrj.org>                                |
+----------------------------------------------------------------------+
*/

include('exercise.class.php');
include('question_export.php');

$tbl_cdb_names = claro_sql_get_course_tbl();
$TBL_EXERCICE_QUESTION = $tbl_cdb_names['quiz_rel_test_question'];
$TBL_EXERCICES         = $tbl_cdb_names['quiz_test'             ];
$TBL_QUESTIONS         = $tbl_cdb_names['quiz_question'         ];
$TBL_REPONSES          = $tbl_cdb_names['quiz_answer'           ];

/**
 * This class represents an entire exercise to be exported in IMS/QTI.
 * It will be represented by a single <section> containing several <item>.
 *
 * Some properties cannot be exported, as IMS does not support them :
 *   - type (one page or multiple pages)
 *   - start_date and end_date
 *   - max_attempts
 *   - show_answer
 *   - anonymous_attempts
 *
 * @author Amand Tihon <amand@alrj.org>
 */
class ImsSection
{
    var $exercise;

    /**
     * Constructor.
     * @param $exe The Exercise instance to export
     * @author Amand Tihon <amand@alrj.org>
     */
    function ImsSection($exe)
    {
        $this->exercise = $exe;
    }

    function start_section()
    {
        $out = '<section ident="EXO_' . $this->exercise->selectId() . '" title="' . $this->exercise->selectTitle() . '">' . "\n";
        return $out;
    }

    function end_section()
    {
        return "</section>\n";
    }

    function export_duration()
    {
        if ($max_time = $this->exercise->get_max_time())
        {
            // return exercise duration in ISO8601 format.
            $minutes = floor($max_time / 60);
            $seconds = $max_time % 60;
            return '<duration>PT' . $minutes . 'M' . $seconds . "S</duration>\n";
        }
        else
        {
            return '';
        }
    }

    /**
     * Export the presentation (Exercise's description)
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_presentation()
    {
        $out = "<presentation_material><flow_mat><material>\n"
             . "  <mattext><![CDATA[" . $this->exercise->selectDescription() . "]]></mattext>\n"
             . "</material></flow_mat></presentation_material>\n";
        return $out;
    }

    /**
     * Export the ordering information.
     * Either sequential, through all questions, or random, with a selected number of questions.
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_ordering()
    {
        $out = '';
        if ($n = $this->exercise->isRandom()) {
            $out.= "<selection_ordering>"
                 . "  <selection>\n"
                 . "    <selection_number>" . $n . "</selection_number>\n"
                 . "  </selection>\n"
                 . '  <order order_type="Random" />'
                 . "\n</selection_ordering>\n";
        }
        else
        {
            $out.= '<selection_ordering sequence_type="Normal">' . "\n"
                 . "  <selection />\n"
                 . "</selection_ordering>\n";
        }

        return $out;
    }

    /**
     * Export the questions, as a succession of <items>
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_questions()
    {
        $out = "";
        foreach ($this->exercise->selectQuestionList() as $q)
        {
            $out .= export_question($q, False);
        }
        return $out;
    }

    /**
     * Export the exercise in IMS/QTI.
     *
     * @param bool $standalone Wether it should include XML tag and DTD line.
     * @return a string containing the XML flow
     * @author Amand Tihon <amand@alrj.org>
     */
    function export($standalone)
    {
        global $charset;

        $head = $foot = "";
        if ($standalone) {
            $head = '<?xml version = "1.0" encoding = "' . $charset . '" standalone = "no"?>' . "\n"
                  . '<!DOCTYPE questestinterop SYSTEM "ims_qtiasiv1p2p1.dtd">' . "\n"
                  . "<questestinterop>\n";
            $foot = "</questestinterop>\n";
        }

        $out = $head
             . $this->start_section()
             . $this->export_duration()
             . $this->export_presentation()
             . $this->export_ordering()
             . $this->export_questions()
             . $this->end_section()
             . $foot;

        return $out;
    }
}

/**
 * Send a complete exercise in IMS/QTI format, from its ID
 *
 * @param int $exerciseId The exercise to exporte
 * @param boolean $standalone Wether it should include XML tag and DTD line.
 * @return The XML as a string, or an empty string if there's no exercise with given ID.
 * @author Amand Tihon <amand@alrj.org>
 */
function export_exercise($exerciseId, $standalone=True)
{
    $objExercise = new Exercise();
    if (! $objExercise->read($exerciseId))
    {
        return "";
    }
    $ims = new ImsSection($objExercise);
    $xml = $ims->export($standalone);
    return $xml;

}

?>