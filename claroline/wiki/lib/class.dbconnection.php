<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * @version 1.11 $Revision$
 *
 * @copyright   (c) 2001-2012, Universite catholique de Louvain (UCL)
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
abstract class CLWIKI_Database_Connection
{

    protected $error = '';
    protected $errno = 0;
    protected $connected = false;

    protected abstract function setError($errmsg = '', $errno = 0);

    public function getError()
    {
        if ($this->error != '')
        {
            $errno = $this->errno;
            $error = $this->error;
            $this->error = '';
            $this->errno = 0;
            return $errno . ' - ' . $error;
        }
        else
        {
            return false;
        }
    }

    public function hasError()
    {
        return ( $this->error != '' );
    }

    public abstract function connect();

    public function isConnected()
    {
        return $this->connected;
    }

    public abstract function close();

    public abstract function executeQuery($sql);

    public abstract function getAllObjectsFromQuery($sql);

    public abstract function getObjectFromQuery($sql);

    public abstract function getAllRowsFromQuery($sql);

    public abstract function getRowFromQuery($sql);

    public abstract function queryReturnsResult($sql);
    
    public abstract function getLastInsertID();

}

class MyDatabaseConnection extends CLWIKI_Database_Connection
{

    private $db_link;
    private $host;
    private $username;
    private $passwd;
    private $dbname;

    public function __construct($host, $username, $passwd, $dbname)
    {
        $this->db_link = null;
        $this->host = $host;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->dbname = $dbname;
    }

    protected function setError($errmsg = '', $errno = 0)
    {
        if ($errmsg != '')
        {
            $this->error = $errmsg;
            $this->errno = $errno;
        }
        else
        {
            $this->error = ( @mysql_error() !== false ) ? @mysql_error() : 'Unknown error';
            $this->errno = ( @mysql_errno() !== false ) ? @mysql_errno() : 0;
        }

        $this->connected = false;
    }

    public function connect()
    {
        $this->db_link = @mysql_connect($this->host, $this->username, $this->passwd);

        if (!$this->db_link)
        {
            $this->setError();

            return false;
        }

        if (@mysql_select_db($this->dbname, $this->db_link))
        {
            $this->connected = true;
            return true;
        }
        else
        {
            $this->setError();

            return false;
        }
    }

    public function close()
    {
        if ($this->db_link != false)
        {
            @mysql_close($this->db_link);
        }
        else
        {
            $this->setError("No connection found");
        }
        $this->connected = false;
    }

    public function executeQuery($sql)
    {
        mysql_query($sql, $this->db_link);

        if (@mysql_errno($this->db_link) != 0)
        {
            $this->setError();

            return 0;
        }

        return @mysql_affected_rows($this->db_link);
    }

    public function getAllObjectsFromQuery($sql)
    {
        $result = mysql_query($sql, $this->db_link);

        if (@mysql_num_rows($result) > 0)
        {
            $ret = array ();

            while (( $item = @mysql_fetch_object($result) ) != false)
            {
                $ret[] = $item;
            }
        }
        else
        {
            $this->setError();

            @mysql_free_result($result);

            return null;
        }

        @mysql_free_result($result);

        return $ret;
    }

    public function getObjectFromQuery($sql)
    {
        $result = mysql_query($sql, $this->db_link);

        if (( $item = @mysql_fetch_object($result) ) != false)
        {
            @mysql_free_result($result);

            return $item;
        }
        else
        {
            $this->setError();

            @mysql_free_result($result);
            return null;
        }
    }

    public function getAllRowsFromQuery($sql)
    {
        $result = mysql_query($sql, $this->db_link);

        if (@mysql_num_rows($result) > 0)
        {
            $ret = array ();

            while (( $item = @mysql_fetch_array($result) ) != false)
            {
                $ret[] = $item;
            }
        }
        else
        {
            $this->setError();

            @mysql_free_result($result);

            return null;
        }

        @mysql_free_result($result);

        return $ret;
    }

    public function getRowFromQuery($sql)
    {
        $result = mysql_query($sql, $this->db_link);

        if (( $item = @mysql_fetch_array($result) ) != false)
        {
            @mysql_free_result($result);

            return $item;
        }
        else
        {
            $this->setError();

            @mysql_free_result($result);

            return null;
        }
    }

    public function queryReturnsResult($sql)
    {
        $result = mysql_query($sql, $this->db_link);

        if (@mysql_errno($this->db_link) == 0)
        {

            if (@mysql_num_rows($result) > 0)
            {
                @mysql_free_result($result);

                return true;
            }
            else
            {
                @mysql_free_result($result);

                return false;
            }
        }
        else
        {
            $this->setError();

            return false;
        }
    }

    public function getLastInsertID()
    {
        if ($this->hasError())
        {
            return 0;
        }
        else
        {
            return mysql_insert_id($this->db_link);
        }
    }

}
