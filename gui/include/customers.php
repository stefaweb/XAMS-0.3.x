<?php

include_once 'include/xclass.php';
include_once 'include/resellers.php';

class customers extends xclass
{
    public $objname = 'customers';
    public $notice;
    public $addressbook_ids;
    public $sites = [];
    public $name = null;
    public $lngbase = 'customer';
    public $tablename = 'pm_customers';

    public function Customers($init = true)
    {
        xclass::xclass($init);

        $this->myReseller = new Resellers();
    }

    // Check which actions the logged in user can perform on this customer
    public function Authenticate()
    {
        if ($this->authenticated) {
            return $this->getAuthMode();
        }
        switch (USERT) {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
                $mode = (USERID === $this->resellerid) ? _AUTH_ALL : _AUTH_ADD;
                $this->setAuthMode($mode);
                break;
            case _CUSTOMER:
                if (USERID === $this->id) {
                    $this->setAuthMode(_AUTH_LOAD | _AUTH_UPDATE);
                }
                break;
            case _USER:
                // keep everything false
                break;
        }
        xclass::Authenticate(true);

        return $this->getAuthMode();
    }

    // Add/Update/Delete Addressbook for customer
    public function add_addressbook()
    {
        $addressbook_ids = [];
        // Get addressbook ids which has to be updated
        foreach ($this as $k=>$elem) {
            if (preg_match('/^addressbook_\d+$/', $k)) {
                $addressbook_ids[] = substr($k, 12);
            }
        }

        // Delete Addressbook entries and add it again
        if (count($addressbook_ids) > 0) {
            $idstr = addslashes(implode(', ', $addressbook_ids));
            $this->db->query('DELETE FROM pm_customer_info WHERE customerid = ? AND infofieldid IN (!)', [$this->id, $idstr]);
            $sth = $this->db->prepare('INSERT INTO pm_customer_info VALUES (?, ?, ?)');
            foreach ($addressbook_ids as $id) {
                if (!empty($this->{'addressbook_'.$id})) {
                    $this->db->execute($sth, [$id, $this->id, $this->{'addressbook_'.$id}]);
                }
            }
        }
    }

    public function Load($id = false)
    {
        if ($id) {
            $this->id = $id;
        }
        if (empty($this->id)) {
            die($this->objname.'->Load() - Have no ID to load!');
        }

        $sql = 'SELECT name, locked, resellerid,
                       DATE_FORMAT(added, ?) as added,
                       DATE_FORMAT(updated, ?) as updated
                FROM   pm_customers
                WHERE  id = ?';

        $val = [$this->date_format, $this->date_format, $this->id];

        $result = xclass::Load($sql, $val);

        if (!empty($this->resellerid)) {
            $this->myReseller->Load($this->resellerid);
        }

        // Load Sites this Customer is responsible for
        $this->sites = $this->db->getCol('SELECT siteid FROM pm_sites_c_customers WHERE customerid = ?', 0, [$this->id]);

        $this->XAMS_Log('Selection', "Selected Customer $this->name");

        $this->Authenticate();

        return $result;
    }

    // Update pm_sites when adding/updating a Customer
    public function UpdateSites()
    {
        $values = null;
        if (count($this->sites) > 0) {
            $ids = addslashes(implode(',', $this->sites));

            foreach ($this->sites as $elem) {
                if ($values) {
                    $values .= ', ';
                }
                $values .= "($this->id, $elem)";
            }

            $this->db->query('INSERT IGNORE INTO pm_sites_c_customers VALUES !', addslashes($values));
            $this->db->query('DELETE FROM pm_sites_c_customers WHERE customerid = ? AND siteid NOT IN (!)', [$this->id, $ids]);
        } else {
            $this->db->query('DELETE FROM pm_sites_c_customers WHERE customerid = ?', [$this->id]);
        }
    }

    public function Add()
    {
        $this->Authenticate();
        $this->password = md5($this->password);
        $result = xclass::Add();

        $this->UpdateSites();

        $this->add_addressbook();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Customer '%s' was added successfully."), $this->name);
            $this->XAMS_Log('Insertion', "Added Customer $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Customer '%s' could not be added."), $this->name);
            $this->XAMS_Log('Insertion', "Failed adding Customer $this->name", 'failed');
        }
    }

    public function Update()
    {
        if (!empty($this->password)) {
            $this->password = md5($this->password);
        }
        $result = xclass::Update();

        $this->UpdateSites();

        $this->add_addressbook();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Customer '%s' was updated successfully."), $this->name);
            $this->XAMS_Log('Update', "Updated Customer $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Customer '%s' could not be updated."), $this->name);
            $this->XAMS_Log('Update', "Failed updating Customer $this->name", 'failed');
        }
    }

    public function Delete()
    {
        // Delete Customer
        $result = xclass::Delete();

        // Delete Addressbook of Customer
        $this->db->query('DELETE FROM pm_customer_info WHERE customerid = ?', $this->id);

        // Delete Sites <-> Customers Assignments
        $this->db->query('DELETE FROM pm_sites_c_customers WHERE customerid = ?', $this->id);

        // Delete Site-Templates of Customer
        $this->db->query('DELETE FROM pm_site_templates WHERE customerid = ?', $this->id);

        // Delete User-Templates of Customer
        $this->db->query('DELETE FROM pm_user_templates WHERE customerid = ?', $this->id);

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Customer '%s' was deleted successfully."), $this->name);
            $this->XAMS_Log('Deletion', "Deleted Customer $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Customer '%s' could not be deleted."), $this->name);
            $this->XAMS_Log('Deletion', "Failed deleting Customer $this->name", 'failed');
        }
    }
}
