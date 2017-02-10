<?php

if (!extension_loaded('mysql')) {
    dl('mysql.so');
}

define('DB_FETCHMODE_ORDERED', 1);
define('DB_FETCHMODE_ASSOC', 2);

// Tiny XAMS PEAR::DB replacement
// PEAR dropped PHP < 4.2.0 support, so we need a replacement
class DB
{
    public $_connection;

    public function connect($host, $user, $pass, $db, $sn)
    {
        $ret = new self();
        if (!$ret->_connection = @mysql_connect($host, $user, $pass)) {
            $ret = new DB_Error('Couldn\'t connect to database - '.mysql_error());
        } elseif (!@mysql_select_db($db)) {
            $ret = new DB_Error('Couldn\'t select database - '.mysql_error());
        }
        $sn = 'SET NAMES '.$sn;
        if (!@mysql_query($sn)) {
            $ret = new DB_Error('Couldn\'t SET NAMES MYSQL - '.mysql_error());
        }

        return $ret;
    }

    public function isError(&$obj)
    {
        if (is_array($obj)) {
            return false;
        }

        return strtolower(get_class($obj)) == 'db_error';
    }

    public function prepare($q)
    {
        return $q;
    }

    public function execute($sth, $data)
    {
        return $this->query($sth, $data);
    }

    public function query($q, $array = null)
    {
        return DB_Query::query($this->_connection, $q, $array);
    }

    public function getRow($q, $array = null, $mode = DB_FETCHMODE_ORDERED)
    {
        $res = $this->query($q, $array);
        if (self::isError($res)) {
            return $res;
        }

        return $res->fetchRow($mode);
    }

    public function getAll($q, $array = null, $mode = DB_FETCHMODE_ORDERED)
    {
        $res = $this->query($q, $array);
        if (self::isError($res)) {
            return $res;
        }

        return $res->fetchAll($mode);
    }

    public function getOne($q, $array = null)
    {
        $res = $this->query($q, $array);
        if (self::isError($res)) {
            return $res;
        }

        return $res->fetchOne();
    }

    public function getCol($q, $col = 0, $array = null)
    {
        $res = $this->query($q, $array);
        if (self::isError($res)) {
            return $res;
        }

        return $res->fetchCol($col);
    }
}

class DB_Query
{
    public $_res;

    public function query(&$connection, $q, $array)
    {
        if (!is_null($array)) {
            $q = self::replace($q, $array);
        }
        $res = new self();
        if (!$res->_res = mysql_query($q, $connection)) {
            $res = new DB_Error('Error while querying the database - '.mysql_error());
        }

        return $res;
    }

    public function replace(&$q, &$array)
    {
        if (strlen($q) == 0) {
            return '';
        }

        if (!is_array($array)) {
            $array = [$array];
        }
        $nq = '';
        $replace = [];
        $ap = 0;
        for ($i = 0; $i < strlen($q); $i++) {
            if (is_null($q[$i])) {
                $nq .= 'NULL';
            } elseif ($q[$i] == '?') {
                $nq .= "'".mysql_escape_string($array[$ap++])."'";
            } elseif ($q[$i] == '!') {
                $nq .= $array[$ap++];
            } else {
                $nq .= $q[$i];
            }
        }

        return $nq;
    }

    public function numRows()
    {
        return mysql_num_rows($this->_res);
    }

    public function fetchRow($mode = DB_FETCHMODE_ORDERED)
    {
        if ($mode == DB_FETCHMODE_ORDERED) {
            $row = mysql_fetch_array($this->_res);
        } elseif ($mode == DB_FETCHMODE_ASSOC) {
            $row = mysql_fetch_assoc($this->_res);
        }

        return $row;
    }

    public function fetchAll($mode = DB_FETCHMODE_ORDERED)
    {
        $row = [];
        if ($mode == DB_FETCHMODE_ORDERED) {
            while ($r = mysql_fetch_array($this->_res)) {
                $row[] = $r;
            }
        } elseif ($mode == DB_FETCHMODE_ASSOC) {
            while ($r = mysql_fetch_assoc($this->_res)) {
                $row[] = $r;
            }
        } else {
            die('Undefined mode in '.__FILE__.' line '.__LINE__);
        }

        return $row;
    }

    public function fetchOne()
    {
        $row = mysql_fetch_array($this->_res);

        return $row[0];
    }

    public function fetchCol($col)
    {
        $row = [];
        while ($r = mysql_fetch_array($this->_res)) {
            $row[] = $r[$col];
        }

        return $row;
    }
}

class DB_Error
{
    public $_errormsg;

    public function DB_Error($msg)
    {
        $this->_raiseError($msg);
    }

    public function _raiseError($msg)
    {
        $this->_errormsg = $msg;
    }

    public function getMessage()
    {
        return $this->_errormsg;
    }
}
