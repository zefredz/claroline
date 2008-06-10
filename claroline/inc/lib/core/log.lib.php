<?php // $Id$
if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

class Logger
{
    private $tbl_log;
    
    public function __construct()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $this->tbl_log  = $tbl_mdb_names['log'];
    }
    
    public function log( $type, $data )
    {
        $cid        = claro_get_current_course_id();
        $tid        = claro_get_current_tool_id();
        $uid        = claro_get_current_user_id();
        $date       = claro_date("Y-m-d H:i:00");

        $ip         = !empty( $_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        $data = serialize( $data );

        if ( claro_debug_mode() )
        {
            Console::message( 'Data logged '
                    . $type . ' : '
                    . var_export( $data, true ) );
        }

        $sql = "INSERT INTO `" . $this->tbl_log . "`
                SET `course_code` = " . ( is_null($cid) ? "NULL" : "'" . addslashes($cid) . "'" ) . ",
                    `tool_id` = ". ( is_null($tid) ? "NULL" : "'" . addslashes($tid) . "'" ) . ",
                    `user_id` = ". ( is_null($uid) ? "NULL" : "'" . addslashes($uid) . "'" ) . ",
                    `ip` = ". ( is_null($ip) ? "NULL" : "'" . addslashes($ip) . "'" ) . ",
                    `date` = '" . $date . "',
                    `type` = '" . addslashes($type) . "',
                    `data` = '" . addslashes($data) . "'";

        return claro_sql_query($sql);
    }
}