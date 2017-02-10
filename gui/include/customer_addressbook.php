<?php

include_once 'include/xclass.php';

class Customer_Addressbook extends xclass
{
    public $objname = 'customer_addressbook';
    public $notice;
    public $VSPECfile = 'addressbook';
    public $lngbase = 'addressbook';
    public $Fields = [];
    public $overview = [];

    public function Customer_Addressbook($init = true)
    {
        xclass::xclass($init);

        if (isCUSTOMER) {
            $sql = sprintf('SELECT   id, name, acl_customer acl
                            FROM     pm_customer_info_fields
                            WHERE    acl_customer = %d
                            ORDER BY ord', _ACL_WRITE);
        } else {
            $sql = 'SELECT   id, name, acl_customer acl
                    FROM     pm_customer_info_fields
                    ORDER BY ord';
        }

        $result = $this->db->query($sql);
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this->Fields[$row['name']]['value'] = null;
            $this->Fields[$row['name']]['id'] = $row['id'];
            $this->Fields[$row['name']]['writeable'] = (isADMIN || ($row['acl'] & _ACL_WRITE));
        }
    }

    public function &LoadOverview()
    {
        $sql = 'SELECT   id, name, ldapname, acl_reseller, acl_customer, ord
                FROM     pm_customer_info_fields
                ORDER BY ord';

        $this->overview = $this->db->getAll($sql, DB_FETCHMODE_ASSOC);

        $this->XAMS_Log('Selection', 'Selected Customer-Addressbook-Overview');

        return $this->overview;
    }

    public function Load($customerid = false)
    {
        if ($customerid) {
            $this->customerid = $customerid;
        }

        if (isCUSTOMER) {
            $sql = sprintf('SELECT    id, name, value, acl_customer acl
                            FROM      pm_customer_info_fields cif
                            LEFT JOIN pm_customer_info ci
                            ON        ci.infofieldid = cif.id
                            AND       customerid = %d
                            WHERE     acl_customer > 0
                            ORDER BY  ord', $this->customerid);
        } else {
            $sql = sprintf('SELECT    id, name, value, acl_customer acl
                            FROM      pm_customer_info_fields cif
                            LEFT JOIN pm_customer_info ci
                            ON        ci.infofieldid = cif.id
                            AND       customerid = %d
                            ORDER BY  ord', $this->customerid);
        }

        $result = $this->db->query($sql);
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this->Fields[$row['name']]['value'] = $row['value'];
            $this->Fields[$row['name']]['id'] = $row['id'];
            $this->Fields[$row['name']]['writeable'] = (isADMIN || ($row['acl'] & _ACL_WRITE));
        }
    }

    public function Add()
    {
        $max = $this->db->getOne('SELECT MAX(ord)+1 FROM pm_customer_info_fields');
        if (!$max) {
            $max = 1;
        }
        $sql = 'INSERT INTO pm_customer_info_fields (name, ldapname, acl_reseller, acl_customer, ord) VALUES (?, ?, ?, ?, ?)';
        $val = [$this->name, $this->ldapname, $this->acl_reseller, $this->acl_customer, $max];
        $result = $this->db->query($sql, $val);

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Addressbook Entry '%s' has been successfully added."), $this->name);
            $this->XAMS_Log('Insertion', "Added Addressbook Entry $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Adressbook Entry '%s' could not be added."), $this->name);
            $this->XAMS_Log('Insertion', "Failed adding Adressbook Entry $this->name", 'failed');
        }
    }

    public function Update($index = -1)
    {
        if ($index >= 0) {
            if (!empty($this->position[$index])) {
                if ($this->position[$index] > $this->ord[$index]) { // Shift field down
                    $this->db->query('UPDATE pm_customer_info_fields SET ord = ord-1 WHERE ord <= ? AND ord > ?', [$this->position[$index], $this->ord[$index]]);
                    $this->db->query('UPDATE pm_customer_info_fields SET ord = ? WHERE id = ?', [$this->position[$index], $this->id[$index]]);
                } else { // Shift field up
                    $this->db->query('UPDATE pm_customer_info_fields SET ord = ord+1 WHERE ord > ? AND ord < ?', [$this->position[$index], $this->ord[$index]]);
                    $this->db->query('UPDATE pm_customer_info_fields SET ord = ? WHERE id = ?', [$this->position[$index] + 1, $this->id[$index]]);
                }
            }
            $sql = 'UPDATE pm_customer_info_fields
                    SET    name = ?, ldapname = ?, acl_reseller = ?, acl_customer = ?
                    WHERE  id = ?';
            $val = [$this->name[$index], $this->ldapname[$index], $this->acl_reseller[$index], $this->acl_customer[$index], $this->id[$index]];

            $result = $this->db->query($sql, $val);

            if ($result) {
                $this->notice = sprintf($this->i18n->get("Addressbook Entry '%s' has been successfully updated."), $this->name[$index]);
                $this->XAMS_Log('Update', "Updated Addressbook Entry {$this->name[$index]}");
            } else {
                $this->notice = sprintf($this->i18n->get("Addressbook Entry '%s' could not be updated."), $this->name[$index]);
                $this->XAMS_Log('Update', "Failed updating Addressbook Entry {$this->name[$index]}", 'failed');
            }
        }
    }

    public function Delete($index = -1)
    {
        if ($index >= 0) {
            $this->db->query('UPDATE pm_customer_info_fields SET ord = ord-1 WHERE ord > ?', $this->ord[$index]);
            $result = $this->db->query('DELETE FROM pm_customer_info_fields WHERE id = ?', $this->id[$index]);
            $this->db->query('DELETE FROM pm_customer_info WHERE infofieldid = ?', $this->id[$index]);
            if ($result) {
                $this->notice = sprintf($this->i18n->get("Addressbook Entry '%s' has been successfully deleted."), $this->name[$index]);
                $this->XAMS_Log('Deletion', "Deleted Addressbook Entry {$this->name[$index]}");
            } else {
                $this->notice = sprintf($this->i18n->get("Addressbook Entry '%s' could not be deleted."), $this->name[$index]);
                $this->XAMS_Log('Deletion', "Failed deleting Addressbook Entry {$this->name[$index]}", 'failed');
            }
        }
    }
}
