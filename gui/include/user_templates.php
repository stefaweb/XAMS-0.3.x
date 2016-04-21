<?php

include_once 'include/xclass.php';
include_once 'include/sites.php';
include_once 'include/customers.php';

class User_Templates extends xclass
{

    var $objname = 'user_templates';
    var $notice;
    var $Name;
    var $lngbase = 'user_template';
    var $tablename = 'pm_user_templates';

    function User_Templates($init=true)
    {
        xclass::xclass($init);

        $this->mySite = new Sites();
        $this->myReseller = new Resellers();
    }

    // Check which actions the logged in user can perform on this user-template
    function Authenticate()
    {
        if ($this->authenticated) return $this->getAuthMode();
        switch (USERT)
        {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
                if ($this->resellerid === USERID)
                    $this->setAuthMode(_AUTH_ALL);
                break;
            case _CUSTOMER:
                if ($this->customerid === USERID)
                    $this->setAuthMode(_AUTH_ALL);
                break;
            case _USER:
                break;
        }
        xclass::Authenticate(true);
        return $this->getAuthMode();
    }

    function Load($id=false)
    {
        if ($id) $this->id = $id;
        if (empty($this->id)) die($this->objname. '->Load() - Have no ID to load!');
        $sql = 'SELECT adminid, resellerid, customerid, templatename, name,
                       password, quota, addrtype, viruscheckin, viruscheckout,
                       spamcheckin, spamcheckout, spamscore, highspamscore, relayonauth,
                       relayonauth, relayoncheck,
                       DATE_FORMAT(added, ?) added,
                       DATE_FORMAT(updated, ?) updated
                FROM   pm_user_templates
                WHERE  id = ?';
        $val = array($this->date_format, $this->date_format, $this->id);

        xclass::Load($sql, $val);

        $this->XAMS_Log("Selection", "Selected User-Template $this->templatename");

        $this->Authenticate();
    }

    function Add()
    {
        $this->Authenticate();
        $this->password = md5($this->password);
        $sql = $this->create_sql_insert('pm_user_templates', $vals);
        $result = $this->db->query($sql, $vals);

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("User-Template '%s' was added successfully."), $this->templatename);
            $this->XAMS_Log("Insertion", "Added User-Template $this->templatename");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("User-Template '%s' could not be added."), $this->templatename);
            $this->XAMS_Log("Insertion", "Failed adding User-Template $this->templatename", "failed");
        }
    }

    function Update()
    {
        if (!empty($this->password)) $this->password = md5($this->password);
        $sql = $this->create_sql_update('pm_user_templates', $vals);

        $result = $this->db->query($sql, $vals);
        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("User-Template '%s' was updated successfully."), $this->templatename);
            $this->XAMS_Log("Update", "Updated User-Template $this->templatename");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("User-Template '%s' could not be updated."), $this->templatename);
            $this->XAMS_Log("Update", "Failed updating User-Template $this->templatename", "failed");
        }
    }

    function Delete()
    {
        $result = xclass::Delete();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("User-Template '%s' was deleted successfully."), $this->templatename);
            $this->XAMS_Log("Deletion", "Deleted User-Template $this->templatename");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("User-Template '%s' could not be deleted."), $this->templatename);
            $this->XAMS_Log("Deletion", "Failed deleting User-Template $this->templatename", "failed");
        }
    }

}
?>
