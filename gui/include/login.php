<?php

include_once 'include/xclass.php';

class login extends xclass
{
    public $objname = 'login';
    public $login = null;
    public $password = null;
    public $LoggedIn = false;
    public $lngbase = 'login';

    public $notice;

    public function Login($init = true)
    {
        xclass::xclass($init);
    }

    public function doLogin($username, $password, $usertype = null)
    {
        if ($this->LoggedIn) {
            return false;
        }
        $LoginOK = false;
        $this->login = $username;
        $this->password = md5($password);

        if (_USER_LOGIN && strpos($this->login, '@') !== false) {
            $usertype = 'user';
        } elseif (_RESELLER_LOGIN && strpos($this->login, 'res_') === 0) {
            $usertype = 'reseller';
            $this->login = substr($this->login, 4, strlen($this->login) - 4);
        } elseif (_ADMIN_LOGIN && strpos($this->login, 'adm_') === 0) {
            $usertype = 'admin';
            $this->login = substr($this->login, 4, strlen($this->login) - 4);
        } elseif (_CUSTOMER_LOGIN && !$usertype) {
            $usertype = 'customer';
        }

        switch ($usertype) {
            case 'admin':
                if (_ADMIN_LOGIN) {
                    $LoginOK = $this->DoAdminLogin();
                }
                break;
            case 'reseller':
                if (_RESELLER_LOGIN) {
                    $LoginOK = $this->DoResellerLogin();
                }
                break;
            case 'customer':
                if (_CUSTOMER_LOGIN) {
                    $LoginOK = $this->DoCustomerLogin();
                }
                break;
            case 'user':
                if (_USER_LOGIN) {
                    $LoginOK = $this->DoUserLogin();
                }
                break;
        }
        $this->LoggedIn = $LoginOK;

        return $LoginOK;
    }

    public function DoUserLogin()
    {
        $LoginOK = false;
        if (strpos($this->login, '@') === false) {
            return false;
        }
        list($name, $domain) = explode('@', $this->login);
        $sql = 'SELECT      u.id, u.password, u.AddrType
                FROM        pm_users u
                INNER JOIN  pm_sites s
                ON          u.siteid = s.id
                INNER JOIN  pm_domains d
                ON          s.id = d.siteid
                WHERE       d.name = ?
                AND         u.name = ?';
        $result = $this->db->getRow($sql, [$domain, $name]);

        if ($result > 0) {
            list($this->uid, $dbpass, $addrtype) = $result;
            if ($dbpass == $this->password && ($addrtype & _XAMS)) {
                $_SESSION['SESSION_logged_in_user'] = _USER;
                $this->XAMS_Log('Login', "User $this->login logged in successfully", 'ok', $this->uid, _USER);
                $LoginOK = true;
            } else {
                $this->XAMS_Log('Login', "FAILED: User $this->login failed log in", 'failed', $this->uid, _USER);
            }
        }

        return $LoginOK;
    }

    public function DoCustomerLogin()
    {
        $LoginOK = false;
        $sql = 'SELECT id, password, locked, failures
                FROM   pm_customers
                WHERE  name = ?';
        $result = $this->db->getRow($sql, [$this->login]);

        if ($result > 0) {
            list($this->uid, $dbpass, $locked, $failures) = $result;
            if ($dbpass == $this->password && $locked != 'true') {
                $_SESSION['SESSION_logged_in_user'] = _CUSTOMER;
                $this->db->query('UPDATE pm_customers SET failures = 0 WHERE id = ?', $this->uid);
                $this->XAMS_Log('Login', "Customer $this->login logged in successfully", 'ok', $this->uid, _CUSTOMER);
                $LoginOK = true;
            } else {
                if ($failures >= 2) {
                    $this->db->query('UPDATE pm_customers SET locked = \'y\', failures = failures+1 WHERE id = ?', $this->uid);
                    $this->XAMS_Log('Login', "FAILED: Customer $this->login failed log in", 'failed', $this->uid, _CUSTOMER);
                    $this->XAMS_Log('Login', "Locked Customer $this->login cause of to many failed logins", 'ok', $this->uid, _CUSTOMER);
                } else {
                    $this->db->query('UPDATE pm_customers SET failures = failures+1 WHERE id = ?', $this->uid);
                    $this->XAMS_Log('Login', "FAILED: Customer $this->login failed log in", 'failed', $this->uid, _CUSTOMER);
                }
            }
        }

        return $LoginOK;
    }

    public function DoResellerLogin()
    {
        $LoginOK = false;
        $sql = 'SELECT id, password, locked, failures
                FROM   pm_resellers
                WHERE  name = ?';
        $result = $this->db->getRow($sql, [$this->login]);

        if ($result > 0) {
            list($this->uid, $dbpass, $locked, $failures) = $result;
            if ($dbpass == $this->password && $locked != 'true') {
                $_SESSION['SESSION_logged_in_user'] = _RESELLER;
                $this->db->query('UPDATE pm_resellers SET failures = 0 WHERE id = ?', $this->uid);
                $this->XAMS_Log('Login', "Reseller $this->login logged in successfully", 'ok', $this->uid, _RESELLER);
                $LoginOK = true;
            } else {
                if ($failures >= 2) {
                    $this->db->query('UPDATE pm_resellers SET locked = \'y\', failures = failures+1 WHERE id = ?', $this->uid);
                    $this->XAMS_Log('Login', "FAILED: Reseller $this->login failed log in", 'failed', $this->uid, _RESELLER);
                    $this->XAMS_Log('Login', "Locked Reseller $this->login cause of to many failed logins", 'ok', $this->uid, _RESELLER);
                } else {
                    $this->db->query('UPDATE pm_resellers SET failures = failures+1 WHERE id = ?', $this->uid);
                    $this->XAMS_Log('Login', "FAILED: Reseller $this->login failed log in", 'failed', $this->uid, _RESELLER);
                }
            }
        }

        return $LoginOK;
    }

    public function DoAdminLogin()
    {
        $LoginOK = false;
        $sql = 'SELECT id, password
                FROM   pm_admins
                WHERE  name = ?
                AND    locked = \'false\'';
        $result = $this->db->getRow($sql, [$this->login]);

        if ($result > 0) {
            list($this->uid, $dbpass) = $result;
            if ($dbpass == $this->password) {
                $_SESSION['SESSION_logged_in_user'] = _ADMIN;
                $this->XAMS_Log('Login', "Admin $this->login logged in successfully", 'ok', $this->uid, _ADMIN);
                $LoginOK = true;
            } else {
                $this->XAMS_Log('Login', "FAILED: Admin $this->login failed log in", 'failed', $this->uid, _ADMIN);
            }
        } else {
            $this->XAMS_Log('Login', "Failed login for $this->login - unknown to XAMS", 'failed', 0);
        }

        return $LoginOK;
    }
}
