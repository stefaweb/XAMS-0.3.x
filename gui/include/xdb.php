<?php

if (!extension_loaded('mysql')) dl('mysql.so');

define('DB_FETCHMODE_ORDERED', 1);
define('DB_FETCHMODE_ASSOC', 2);

// Tiny XAMS PEAR::DB replacement
// PEAR dropped PHP < 4.2.0 support, so we need a replacement
class DB
{

    var $_connection;

    function connect($host, $user, $pass, $db, $sn)
    {
        $ret = new DB();
        if (!$ret->_connection = @mysql_connect($host, $user, $pass))
            $ret = new DB_Error('Couldn\'t connect to database - ' . mysql_error());
        elseif (!@mysql_select_db($db))
            $ret = new DB_Error('Couldn\'t select database - ' . mysql_error());
	$sn = "SET NAMES " . $sn;
	if (!@mysql_query($sn))
	    $ret = new DB_Error('Couldn\'t SET NAMES MYSQL - ' . mysql_error());

        return $ret;
    }

    function isError(&$obj)
    {
	if (is_array($obj))
	   return false;
        return (strtolower(get_class($obj)) == 'db_error');
    }

    function prepare($q)
    {
        return $q;
    }

    function execute($sth, $data)
    {
        return $this->query($sth, $data);
    }

    function query($q, $array = null)
    {
        return DB_Query::query($this->_connection, $q, $array);
    }

    function getRow($q, $array = null, $mode = DB_FETCHMODE_ORDERED)
    {
        $res = $this->query($q, $array);
        if (DB::isError($res)) return $res;
        return $res->fetchRow($mode);
    }

    function getAll($q, $array = null, $mode = DB_FETCHMODE_ORDERED)
    {
        $res = $this->query($q, $array);
        if (DB::isError($res)) return $res;
        return $res->fetchAll($mode);
    }

    function getOne($q, $array = null)
    {
        $res = $this->query($q, $array);
        if (DB::isError($res)) return $res;
        return $res->fetchOne();
    }

    function getCol($q, $col = 0, $array = null)
    {
        $res = $this->query($q, $array);
        if (DB::isError($res)) return $res;
        return $res->fetchCol($col);
    }

}

class DB_Query
{

    var $_res;

    function query(&$connection, $q, $array)
    {
        if (!is_null($array)) $q = DB_Query::replace($q, $array);
        $res = new DB_Query();
        if (!$res->_res = mysql_query($q, $connection))
            $res = new DB_Error('Error while querying the database - ' . mysql_error());
        return $res;
    }

    function replace(&$q, &$array)
    {
        if (strlen($q) == 0)
            return '';

        if (!is_array($array))
            $array = array($array);
        $nq = '';
        $replace = array();
        $ap = 0;
        for ($i = 0; $i < strlen($q); $i++)
        {
            if (is_null($q[$i]))
                $nq .= 'NULL';
            elseif ($q[$i] == '?')
                $nq .= "'" . mysql_escape_string($array[$ap++]) . "'";
            elseif ($q[$i] == '!')
                $nq .= $array[$ap++];
            else
                $nq .= $q[$i];
        }
        return $nq;
    }

    function numRows()
    {
        return mysql_num_rows($this->_res);
    }

    function fetchRow($mode = DB_FETCHMODE_ORDERED)
    {
        if ($mode == DB_FETCHMODE_ORDERED)
        {
            $row = mysql_fetch_array($this->_res);
        }
        elseif ($mode == DB_FETCHMODE_ASSOC)
        {
            $row = mysql_fetch_assoc($this->_res);
        }
        return $row;
    }

    function fetchAll($mode = DB_FETCHMODE_ORDERED)
    {
        $row = array();
        if ($mode == DB_FETCHMODE_ORDERED)
        {
            while ($r = mysql_fetch_array($this->_res))
                $row[] = $r;
        }
        elseif ($mode == DB_FETCHMODE_ASSOC)
        {
            while ($r = mysql_fetch_assoc($this->_res))
                $row[] = $r;
        }
        else
        {
            die('Undefined mode in ' . __FILE__ . ' line ' . __LINE__);
        }
        return $row;
    }

    function fetchOne()
    {
        $row = mysql_fetch_array($this->_res);
        return $row[0];
    }

    function fetchCol($col)
    {
        $row = array();
        while ($r = mysql_fetch_array($this->_res))
            $row[] = $r[$col];
        return $row;
    }
}

class DB_Error
{
    var $_errormsg;

    function DB_Error($msg)
    {
        $this->_raiseError($msg);
    }

    function _raiseError($msg)
    {
        $this->_errormsg = $msg;
    }

    function getMessage()
    {
        return $this->_errormsg;
    }
}

?>
