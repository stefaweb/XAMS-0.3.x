<?php

include_once 'include/xclass.php';

class Alias_Addressbook extends xclass
{
    public $objname = 'alias_addressbook';
    public $notice;
    public $VSPECfile = 'addressbook';
    public $lngbase = 'addressbook';
    public $Fields = [];
    public $overview = [];

    public function Alias_Addressbook($init = true)
    {
        xclass::xclass($init);

        $acl_fields = [null, null, 'acl_customer', 'acl_reseller', null];
        $this->acl_field = $acl_fields[USERT];

        if ($this->acl_field) {
            $sql = 'SELECT   id, name, ! acl
                    FROM     pm_alias_info_fields aif
                    WHERE    ! = ?
                    ORDER BY ord';
            $result = $this->db->getAll($sql, [$this->acl_field, $this->acl_field, _ACL_WRITE], DB_FETCHMODE_ASSOC);
        } else {
            $sql = 'SELECT   id, name
                    FROM     pm_alias_info_fields aif
                    ORDER BY ord';
            $result = $this->db->getAll($sql, DB_FETCHMODE_ASSOC);
        }

        foreach ($result as $row) {
            $this->Fields[$row['name']]['value'] = null;
            $this->Fields[$row['name']]['id'] = $row['id'];
            $this->Fields[$row['name']]['writeable'] = (isADMIN || ($row['acl'] & _ACL_WRITE));
        }
    }

    public function &LoadOverview()
    {
        $sql = 'SELECT   id, name, ldapname, acl_reseller, acl_customer, ord
                FROM     pm_alias_info_fields
                ORDER BY ord';

        $this->overview = $this->db->getAll($sql, DB_FETCHMODE_ASSOC);

        $this->XAMS_Log('Selection', 'Selected Alias-Addressbook-Overview');

        return $this->overview;
    }

    public function Load($aliasid = false)
    {
        if ($aliasid) {
            $this->aliasid = $aliasid;
        }

        if ($this->acl_field) {
            $sql = 'SELECT    id, name, value, ! acl
                    FROM      pm_alias_info_fields aif
                    LEFT JOIN pm_alias_info ai
                    ON        ai.infofieldid = aif.id
                    AND       aliasid = ?
                    WHERE     ! > 0
                    ORDER BY  ord';
            $result = $this->db->getAll($sql, [$this->acl_field, $this->aliasid, $this->acl_field], DB_FETCHMODE_ASSOC);
        } else {
            $sql = 'SELECT    id, name, value
                    FROM      pm_alias_info_fields aif
                    LEFT JOIN pm_alias_info ai
                    ON        ai.infofieldid = aif.id
                    AND       aliasid = ?
                    ORDER BY  ord';
            $result = $this->db->getAll($sql, [$this->aliasid], DB_FETCHMODE_ASSOC);
        }

        foreach ($result as $row) {
            if (empty($field) || ($row['acl'] & _ACL_READ)) {
                $this->Fields[$row['name']]['value'] = $row['value'];
                $this->Fields[$row['name']]['id'] = $row['id'];
                $this->Fields[$row['name']]['writeable'] = (isADMIN || ($row['acl'] & _ACL_WRITE));
            }
        }
    }

    public function Add()
    {
        $max = $this->db->getOne('SELECT MAX(ord)+1 FROM pm_alias_info_fields');
        if (!$max) {
            $max = 1;
        }
        $sql = 'INSERT INTO pm_alias_info_fields (name, ldapname, acl_reseller, acl_customer, ord) VALUES (?, ?, ?, ?, ?)';
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
                    $this->db->query('UPDATE pm_alias_info_fields SET ord = ord-1 WHERE ord <= ? AND ord > ?', [$this->position[$index], $this->ord[$index]]);
                    $this->db->query('UPDATE pm_alias_info_fields SET ord = ? WHERE id = ?', [$this->position[$index], $this->id[$index]]);
                } else { // Shift field up
                    $this->db->query('UPDATE pm_alias_info_fields SET ord = ord+1 WHERE ord > ? AND ord < ?', [$this->position[$index], $this->ord[$index]]);
                    $this->db->query('UPDATE pm_alias_info_fields SET ord = ? WHERE id = ?', [$this->position[$index] + 1, $this->id[$index]]);
                }
            }
            $sql = 'UPDATE pm_alias_info_fields
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
            $this->db->query('UPDATE pm_alias_info_fields SET ord = ord-1 WHERE ord > ?', $this->ord[$index]);
            $result = $this->db->query('DELETE FROM pm_alias_info_fields WHERE id = ?', $this->id[$index]);
            $this->db->query('DELETE FROM pm_alias_info WHERE infofieldid = ?', $this->id[$index]);
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
