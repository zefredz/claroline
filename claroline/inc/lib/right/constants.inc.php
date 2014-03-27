<?php // $Id$

/**
 * CLAROLINE
 *
 * Constants of the right package
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.right
 * @author      Claro Team <cvs@claroline.net>
 */

DEFINE('PROFILE_TYPE_COURSE','COURSE');

/**
 * Anonymous
 */

get_lang('Anonymous');
get_lang('Course visitor (the user has no account on the platform)');

DEFINE('ANONYMOUS_PROFILE','anonymous');

/**
 * Guest
 */

get_lang('Guest');
get_lang('Course visitor (the user has an account on the platform, but is not enrolled in the course)');

DEFINE('GUEST_PROFILE','guest');

/**
 * User
 */

get_lang('User');
get_lang('Course member (the user is actually enrolled in the course)');

DEFINE('USER_PROFILE','user');

/**
 * Manager
 */

get_lang('Manager');
get_lang('Course Administrator');

DEFINE('MANAGER_PROFILE','manager');

/**
 * Prefix for custom profile
 */

DEFINE('CUSTOM_PROFILE_PREFIX','profile_');
