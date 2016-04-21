<?php

include_once 'include/xclass.php';

class Admins extends xclass
{
    var $objname = 'admins';
    var $lngbase = 'administrator';
    var $tablename = 'pm_admins';

    var $notice;

    function Admins($init=true)
    {
        xclass::xclass($init);
    }

    function Load($id=false, $Load4Login=false)
    {
        if ($id) $this->id = $id;

        $sql = 'SELECT name, locked,
                       DATE_FORMAT(added, ?) as added,
                       DATE_FORMAT(updated, ?) as updated
                FROM   pm_admins
                WHERE  id = ?';
        $val = array($this->date_format, $this->date_format, $this->id);

        xclass::Load($sql, $val);

        if ($Load4Login)
        {
            $this->XAMS_Log("Selection", "Selected Admin $this->name", "ok", $this->id, _ADMIN);
        }
        else
        {
            $this->XAMS_Log("Selection", "Selected Admin $this->name");
        }

        $this->Authenticate();
    }

    // Check which actions the logged in user can perform on this administrator
    function Authenticate()
    {
        if ($this->authenticated) return $this->getAuthMode();
        switch (USERT)
        {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
            case _CUSTOMER:
            case _USER:
                break;
        }
        xclass::Authenticate(true);
        return $this->getAuthMode();
    }

    function Add()
    {
        $this->Authenticate();
        $this->password = md5($this->password);

        $result = xclass::Add();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Admin '%s' was added successfully."), $this->name);
            $this->XAMS_Log("Insertion", "Added Admin $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Admin '%s' could not be added."), $this->name);
            $this->XAMS_Log("Insertion", "Failed adding Admin $this->name", "failed");
        }
    }

    function Update()
    {
        if (!empty($this->password)) $this->password = md5($this->password);

        $result = xclass::Update();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Admin '%s' was updated successfully."), $this->name);
            $this->XAMS_Log("Update", "Updated Admin $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Admin '%s' could not be updated."), $this->name);
            $this->XAMS_Log("Update", "Failed updating Admin $this->name", "failed");
        }
    }

    function Delete()
    {
        // Delete Administrator
        $result = xclass::Delete();

        // Delete Site-Templates of Administrator
        $this->db->query('DELETE FROM pm_site_templates WHERE adminid = ?', $this->id);

        // Delete User-Templates of Administrator
        $this->db->query('DELETE FROM pm_user_templates WHERE adminid = ?', $this->id);

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Admin '%s' was deleted successfully."), $this->name);
            $this->XAMS_Log("Deletion", "Deleted Admin $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Admin '%s' could not be deleted."), $this->name);
            $this->XAMS_Log("Deletion", "Failed deleting Admin $this->name", "failed");
        }
    }
}
?>