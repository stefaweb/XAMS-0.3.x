<?php

require_once 'include/config.php';
require 'include/i18n.php';
require 'include/xdb.php';

class xclass
{
    public $objname = 'xclass';

    public $db = null;
    public $i18n;
    public $VSPECfile = null;
    public $VSPEC = null;
    public $pid_field_name = null;
    public $formular_error = [];
    public $lngbase = null;
    public $objhaschanged = false;

    public $formular_errors = false;

    public $authenticated = false;
    public $auth_mode = _AUTH_NONE;

    public function xclass($init = true)
    {
        // DB connection
        $DS = &$GLOBALS['DATABASE_SETTINGS'];
        $this->db = DB::connect($DS['Host'], $DS['User'], $DS['Pwd'], $DS['DB'], $DS['SETName']);
        if (DB::isError($this->db)) {
            die($this->db->getMessage());
        }

        // i18n
        $this->i18n = &i18n::singleton();
        $this->i18n->LoadLngBase($this->lngbase);

        // VSPEC
        if (empty($this->VSPECfile)) {
            $this->VSPECfile = strtolower(get_class($this));
        }
        $filename = 'include/varspec/'.$this->VSPECfile.'.inc';
        if (file_exists($filename)) {
            include $filename;
        }
        if ($init && is_array($this->VSPEC)) {
            foreach ($this->VSPEC as $k=>$elem) {
                $this->$k = (isset($this->VSPEC[$k]['init'])) ? $this->VSPEC[$k]['init'] : null;
                if ($this->VSPEC[$k]['type'] == Tpid) {
                    $this->pid_field_name = $k;
                }
            }
        }

        // Misc
        if ($this->i18n->get('Date Format') == 'MM/DD/YYYY') {
            $this->date_format = ($DS['DBType'] == 'mysql') ? '%d/%m/%Y %r' : 'DD.Mon.YYYY HH24:MI:SS';
        } else {
            $this->date_format = ($DS['DBType'] == 'mysql') ? '%d.%m.%Y %T' : 'DD.Mon.YYYY HH24:MI:SS';
        }
    }

    public function insertId($table, &$id, $before)
    {
        if ($before) {
            return;
        }

        if (isset($this->VSPEC[$this->pid_field_name]['autoid']) && $this->VSPEC[$this->pid_field_name]['autoid'] == false) {
            $id = $this->{$this->pid_field_name};
        } elseif ($GLOBALS['DATABASE_SETTINGS']['DBType'] == 'mysql') {
            $id = mysql_insert_id();
        }
    }

    public function setAuthMode($mode = _AUTH_NONE)
    {
        $this->auth_mode = $mode;
    }

    public function getAuthMode()
    {
        return $this->auth_mode;
    }

    public function isAuthLoad()
    {
        return $this->auth_mode & _AUTH_LOAD;
    }

    public function isAuthAdd()
    {
        return $this->auth_mode & _AUTH_ADD;
    }

    public function isAuthUpdate()
    {
        return $this->auth_mode & _AUTH_UPDATE;
    }

    public function isAuthDelete()
    {
        return $this->auth_mode & _AUTH_DELETE;
    }

    // Return detailled changes on object for detailled logging
    public function Object2Log()
    {
        if (_LOG_LEVEL < 2) {
            return;
        }
        $log_entry = null;
        if (is_array($this->VSPEC)) {
            foreach ($this->VSPEC as $k=>$elem) {
                // Debug
        //echo 'K value: '. $k. '<br/>';
                if ($elem['type'] != Tpassword && $elem['type'] != Tpid && (isset($elem['atype']) && $elem['atype'] != Tdummy) && !empty($this->$k)) {
                    $log_entry .= "\n$k -> ".$this->$k;
                }
            }
        }

        return $log_entry;
    }

    // Logs XAMS actions
    public function XAMS_Log($MsgType, $Message, $MsgStatus = 'ok', $TopID = false, $UserType = false)
    {
        if (!_LOG_OPTION || (_LOG_LEVEL < 3 && $MsgType == 'Selection')) {
            return false;
        }

        $logctables = ['unknowns', 'users', 'customers', 'resellers', 'admins'];

        if ($TopID === false) {
            $TopID = (int) $_SESSION['SESSION_logged_in_user_id'];
        }

        if ($UserType) {
            $field_index = $UserType;
        } elseif (isset($_SESSION['SESSION_logged_in_user'])) {
            $field_index = $_SESSION['SESSION_logged_in_user'];
        } else {
            $field_index = 0;
        }

        $logctable = 'pm_logs_c_'.$logctables[$field_index];

        $LogID = null;
        $this->insertId('pm_log', $LogID, true);
        $result = $this->db->query('INSERT INTO pm_log (id, msgtype, msgstatus, resource) VALUES (?, ?, ?, ?)', [$LogID, $MsgType, $MsgStatus, 'XAMS']);
        $this->insertId('pm_log', $LogID, false);

        $username = (empty($_SESSION['SESSION_logged_in_user_name'])) ? $this->login : $_SESSION['SESSION_logged_in_user_name'];

        if ($field_index > 0) {
            $this->db->query('INSERT INTO ! VALUES (?, ?)', [$logctable, $LogID, $TopID]);
        } else {
            $this->db->query('INSERT INTO ! VALUES (?)', [$logctable, $LogID]);
        }

        if (!empty($Message)) {
            $Message .= $this->Object2Log();
            $this->db->query('INSERT INTO pm_log_message (logid, name, message) VALUES (?, ?, ?)', [$LogID, $username, $Message]);
        }
    }

    // Generates bitfield from comma seperated string
    public function string2bitfield($str, $trans)
    {
        $arr = explode(',', $trans);
        $bitfield = 0;
        $i = 1;
        foreach ($arr as $elem) {
            if (preg_match("/$elem/", $str)) {
                $bitfield |= $i;
            }
            $i *= 2;
        }

        return $bitfield;
    }

    // Generates comma seperated string from bitfield
    public function bitfield2string($bitfield, $trans)
    {
        $arr = explode(',', $trans);
        $i = 1;
        $str = null;
        foreach ($arr as $elem) {
            if ($bitfield & $i) {
                $str .= $elem.',';
            }
            $i *= 2;
        }
        if (strlen($str) > 0) {
            $str = substr($str, 0, -1);
        }

        return $str;
    }

    // Test if one of the values of an array is a specific value
    public function isin($val, $vals)
    {
        foreach ($vals as $k) {
            if ($k == $val) {
                return true;
            }
        }

        return false;
    }

    // Generates SQL-Insert for this object
    public function create_sql_insert($table_name, &$val_arr)
    {
        $sql = "INSERT INTO $table_name (COLS) VALUES (VALS)";
        $vals = $cols = $val_arr = [];
        $vals2 = null;
        foreach ($this->VSPEC as $k=>$elem) {
            if (
                (isset($elem['atype']) && $elem['atype'] == Tdummy)                  // Field is a dummy
                || ($elem['type'] == Tpid) && (!(isset($elem['autoid'])) || $elem['autoid'] == true)  // Field is a primary ID
                || (isset($elem['null']) && ($elem['null'] && empty($this->$k)))     // Field is empty and can be null
               ) {
                continue;
            }

            $val = ($elem['type'] == Tinsertdate) ? 'NOW()' : $this->$k;
            $cols[] = $k;
            $val_arr[] = $val;
            $vals2 .= ($val == 'NOW()') ? '!, ' : '?, ';
        }
        $vals2 = substr($vals2, 0, -2);
        $sql = preg_replace(['/COLS/', '/VALS/'], [implode(', ', $cols), $vals2], $sql);

        return $sql;
    }

    // Generates SQL-Update for this object
    public function create_sql_update($table_name, &$val_arr)
    {
        $sql = "UPDATE $table_name SET ";
        $fields_to_update = 0;
        $val_arr = [];
        foreach ($this->VSPEC as $k=>$elem) {
            if ((isset($elem['atype']) && $elem['atype'] == Tdummy)                  // Field is a dummy
            || ($elem['type'] == Tpid)                                               // Field is a primary ID
            || ($elem['type'] == Tnumeric && strlen($this->$k) == 0 && isset($elem['null']) && $elem['null'] == true)     // Field is numeric, null and that's ok
            || ($elem['type'] == Tinsertdate)                                        // Field is insertdate
            || ($elem['type'] == Tpassword && empty($this->$k))                      // Field is password and no (new) password has been entered
            || (isset($this->LoadedData[$k]) && $this->$k == $this->LoadedData[$k])) { // Field hasn't changed since last Load()
                continue;
            }

            $dummy = $this->LoadedData[$k] = $this->$k;
            if (count($val_arr) > 0) {
                $sql .= ', ';
            }
            if (isset($elem['null']) && $elem['null'] && $dummy == '') {
                $dummy = 'NULL';
                $sql .= $k.' = !';
            } else {
                $sql .= $k.' = ?';
            }
            $val_arr[] = $dummy;
        }
        if (!empty($this->pid_field_name)) {
            $sql .= " WHERE $this->pid_field_name = ".$this->{$this->pid_field_name};
        }

        return (count($val_arr) == 0) ? null : $sql;
    }

    // Generates SQL-Delete for this object
    public function create_sql_delete($table_name, &$val_arr)
    {
        $sql = "DELETE FROM $table_name WHERE id = ?";
        $val_arr = [$this->{$this->pid_field_name}];

        return (count($val_arr) == 0) ? null : $sql;
    }

    public function Add()
    {
        if (!$this->isAuthAdd()) {
            die($this->objname.'->Add() - Permission denied.');
        }
        $this->insertId($this->tablename, $this->{$this->pid_field_name}, true);
        $sql = $this->create_sql_insert($this->tablename, $vals);
        $result = $this->db->query($sql, $vals);
        $this->insertId($this->tablename, $this->{$this->pid_field_name}, false);

        return $result;
    }

    public function Update()
    {
        if (!$this->isAuthUpdate()) {
            die($this->objname.'->Update() - Permission denied.');
        }
        $sql = $this->create_sql_update($this->tablename, $vals);
        $result = $this->db->query($sql, $vals);

        return $result;
    }

    public function Delete()
    {
        if (!$this->isAuthDelete()) {
            die($this->objname.'->Delete() - Permission denied.');
        }
        $sql = $this->create_sql_delete($this->tablename, $vals);
        $result = $this->db->query($sql, $vals);

        return $result;
    }

    // Check if object has been changed since last Load()
    public function ObjectChanged()
    {
        $changed = false;
        foreach ($this->LoadedData as $k=>$elem) {
            if ($this->$k != $elem || $this->objhaschanged) {
                $changed = true;
                break;
            }
        }
        if ($changed) {
            $this->assign('changed', 'true');
        }

        return $changed;
    }

    // Superclass Load() to write Database-Results in $this->
    public function Load($sql, $val)
    {
        $row = $this->db->getRow($sql, $val, DB_FETCHMODE_ASSOC);
        if (DB::isError($row)) {
            die($row->getMessage());
        }
        if ($row > 0) {
            foreach ($row as $k=>$elem) {
                $k = strtolower($k);
                if (isset($this->VSPEC[$k]) && $this->isin($this->VSPEC[$k]['type'], [Tnumeric, Tpid, Tid, Tbitfield])) {
                    $elem = (int) $elem;
                }
                $this->$k = $this->LoadedData[$k] = $elem;
            }
        }

        return true;
    }

    // Show status message $msg and halts
    public function status($msg)
    {
        include 'status.php';
        if (gpost('button') != $this->i18n->get('Delete')) {
            echo '<input type="button" value="Précédent" onclick="history.back();">';
        } else {
            echo '<input type="button" value="Précédent" onclick="location.assign(\'startup.php\')">';
        }
        exit();
    }

    // Assign variable to object
    public function assign($var, $value)
    {
        if (isset($this->VSPEC[$var]) && $this->VSPEC[$var]['type'] == Tnumeric) {
            $this->{$var} = (int) $value;
        } else {
            $this->{$var} = $value;
        }
    }

    public function assignVSPEC($arra, $arrb, $value)
    {
        $this->VSPEC[$arra][$arrb] = $value;
    }

    public function assignFormVar($var, $value)
    {
        $this->{$var} = $value;
        if (!is_array($this->VSPEC) || isset($this->VSPEC[$var])) {
            $this->Vars4FormChecking[] = $var;
        }
    }

    // Write array of vars into a specific object
    public function Assign2Object($data)
    {
        foreach ($data as $elem) {
            if (!in_array($elem, $_POST) && !preg_match('/_$/', $elem)) { // NULL any object-var that is not posted (needed for customer->resp.sites - they're not deactivated)
                $this->assign($elem, null);
            }
            // Deactivated checkboxes aren't transmitted
            $uncheckedvalue = (empty($this->VSPEC[$elem]['uncheckedvalue'])) ? 'false' : $this->VSPEC[$elem]['uncheckedvalue'];
            if (isset($this->VSPEC[$elem]['type']) && $this->VSPEC[$elem]['type'] == Tcheckbox) {
                $this->assign($elem, $uncheckedvalue);
            }
            foreach ($_POST as $Pk => $Pelem) {
                if (preg_match(sprintf('/^%s([0-9]+)?$/', $elem), $Pk)) {
                    if (isset($this->VSPEC[$Pk]['type']) && $this->VSPEC[$Pk]['type'] == Tbitfield) {
                        $Pelem = $this->string2bitfield($Pelem, $this->VSPEC[$Pk]['translation']);
                    }
                    $this->assign($Pk, $Pelem);
                    if (!preg_match('/_$/', $elem)) { // Don't break if var is like addressbook_ because more could come
                        break;
                    }
                }
            }
            // We only check variables at check_formular() that in VSPEC _AND_ transmitted
            // to this function. So we can create simplier menus based on a full VSPEC
            // without errors (caused of non-transmitted var's)
            if (!is_array($this->VSPEC) || isset($this->VSPEC[$elem])) {
                $this->Vars4FormChecking[] = $elem;
            }
        }
    }

    public function error_func($k, $text)
    {
        if (!isset($this->formular_error[$k]['error'])) {
            $this->formular_error[$k]['error'] = null;
        }
        $this->formular_error[$k]['status'] = $this->formular_errors = true;
        $this->formular_error[$k]['error'] .= $text;
    }

    public function show_field_property($name)
    {
        if (isset($this->formular_error[$name])
            && isset($this->formular_error[$name]['status'])
            && $this->formular_error[$name]['status'] == true) {
            return '<a href="#" class="tooltip"><img src="'._SKIN.'/img/error.png" /><span>'.$this->formular_error[$name]['error'].'</span></a>';
        } else {
            return '&nbsp;';
        }
    }

    // Check formular
    public function check_formular($form_mode)
    {
        if ($form_mode == 'update' && isset($this->VSPEC['password'])) {
            $this->assignVSPEC('password', 'empty', true);
        }

        foreach ($this->Vars4FormChecking as $k) {
            $elem = &$this->VSPEC[$k];
            $http = trim($this->$k);

            $empty_ok = (empty($http)) ? ((!(isset($elem['empty']) && $elem['empty'])) || (isset($elem['null']) && $elem['null'])) : true;

            if ($empty_ok) {
                switch ($elem['type']) {
                    case Tid:
                        if (!preg_match('/^[1-9]\d*$/', $http) && $http != 0) {
                            $this->error_func($k, $this->i18n->get('Invalid ID'));
                        }
                        break;
                    case Tnumeric:
                        if (strlen($http) == 0 && isset($elem['null']) && $elem['null']) {
                            $http = null;
                            break;
                        }

                        // Kick leading 0's -> otherwise octal Value
                        if (strlen($http) > 1) {
                            $http = preg_replace('/^0*/', null, $http);
                        }

                        // Field is empty?
                        if (strlen($http) == 0 && (!isset($elem['empty']) || !$elem['empty'])) {
                            $this->error_func($k, $this->i18n->get('Field has to be filled out'));
                        } else {
                            // Value ok?
                            if ((isset($elem['max'])) && ((int) $http > $elem['max'])) {
                                $this->error_func($k, sprintf($this->i18n->get("Value is too big (max='%s')"), $elem['max']));
                            }
                            if (in_array('quotabooltrue', $_POST)) {
                                if ((isset($elem['min'])) && ((int) $http < 1)) {
                                    $this->error_func($k, sprintf($this->i18n->get("Value is too low (min='%s')"), 1));
                                } elseif ((isset($elem['min'])) && ((int) $http < $elem['min'])) {
                                    $this->error_func($k, sprintf($this->i18n->get("Value is too low (min='%s')"), $elem['min']));
                                }
                            }

                            // Length ok?
                            if ((isset($elem['maxl'])) && (strlen($http) > $elem['maxl'])) {
                                $this->error_func($k, sprintf($this->i18n->get("Too long (max='%s')"), $elem[maxl]));
                            }
                            if ((isset($elem['minl'])) && (strlen($http) < $elem['minl'])) {
                                $this->error_func($k, sprintf($this->i18n->get("Too short (min='%s')"), $elem['minl']));
                            }

                            // String contains only 0-9?
                            if (!preg_match('/-?\d/', $http)) {
                                $this->error_func($k, $this->i18n->get('Contain non-numeric Characters'));
                            }
                        }
                        break;
                    case Tpassword:
                    case Talphanumeric:
                        if (strlen($http) == 0 && isset($elem['null']) && $elem['null']) {
                            $http = null;
                            break;
                        }
                        // Field is empty?
                        if (strlen($http) == 0 && (isset($elem['empty']) && $elem['empty'])) {
                            break;
                        } else {
                            if ((isset($elem['maxl'])) && (strlen($http) > $elem['maxl'])) {
                                $this->error_func($k, sprintf($this->i18n->get("Too long (max='%s')"), $elem['maxl']));
                            }
                            if ((isset($elem['minl'])) && (strlen($http) < $elem['minl']) && ($k != 'rightpart') && ($k != 'leftpart')) {
                                $this->error_func($k, sprintf($this->i18n->get("Too short (min='%s')"), $elem['minl']));
                            }
                            if (($k == 'rightpart')) {
                                if (!preg_match("#^([a-z0-9._-]+(@[a-z0-9._-]{2,}\.[a-z]{2,4})?, ?)*[a-z0-9._-]+(@[a-z0-9._-]{2,}\.[a-z]{2,4})?$#", $http)) {
                                    $this->error_func($k, sprintf($this->i18n->get('Email address invalid')));
                                }
                            }
                            if (($k == 'leftpart')) {
                                if (!preg_match('#^[a-z0-9._-]+$#', $http)) {
                                    $this->error_func($k, sprintf($this->i18n->get('Incoming address invalid')));
                                }
                            }
                        }
                        break;
                    case Tcheckbox:
                        break;
                    default:
                        break;
                }
                if (strlen($http) > 0) {
                    $this->{$k} = $http;
                }
            }
        }
    }
}
