<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Lock
 *
 * @version     1.0 $Revision$
 * @copyright   2001-2010 Universite catholique de Louvain (UCL)
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     icterms
 */

class Claro_KernelHook_Lock
{
    const CLARO_KERNEL_HOOK_LOCK = 'claroKernelHookLock';
    
    public static function getLock()
    {
        $moduleLabel = get_current_module_label();
        
        if (claro_debug_mode() )
        {
            pushClaroMessage(var_export(@$moduleLabel, true));
            pushClaroMessage(var_export(@$_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ], true));
        }
        
        if ( empty($moduleLabel) )
        {
            return false;
        }
        
        if( self::hasLock( $moduleLabel ) )
        {
            return true;
        }
        elseif( self::lockAvailable() )
        {
            $_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ] = $moduleLabel;
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public static function hasLock()
    {
        $moduleLabel = get_current_module_label();
        
        if (claro_debug_mode() )
        {
            pushClaroMessage(var_export(@$moduleLabel, true));
            pushClaroMessage(var_export(@$_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ], true));
        }
        
        return isset( $_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ] )
            && $_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ] == $moduleLabel;
    }
    
    public static function lockAvailable()
    {
        return !isset( $_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ] )
            || empty( $_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ] );
    }
    
    public static function releaseLock()
    {
        $moduleLabel = get_current_module_label();
        
        if (claro_debug_mode() )
        {
            pushClaroMessage(var_export(@$moduleLabel, true));
            pushClaroMessage(var_export(@$_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ], true));
        }
        
        if( self::hasLock( $moduleLabel ) )
        {
            unset( $_SESSION[ self::CLARO_KERNEL_HOOK_LOCK ] );
            return true;
        }
        else
        {
            return false;
        }
    }
}
