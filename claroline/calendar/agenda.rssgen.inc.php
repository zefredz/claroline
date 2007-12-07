<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
/**
 * CLAROLINE
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

// just call the rss_builder for course, nothing to prepare.
include( dirname(__FILE__) . '/../inc/lib/rss/write/gencourse_rss.inc.php');
build_course_feed(true, $_cid);

?>