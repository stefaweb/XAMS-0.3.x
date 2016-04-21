<?php

include_once 'include/xclass.php';

class User_Addressbook extends xclass
{

    var $objname = 'user_addressbook';
    var $notice;
    var $VSPECfile = 'addressbook';
    var $lngbase = 'addressbook';
    var $Fields = array();
    var $overview = array();

    function User_Addressbook()
    {
        xclass::xclass();

        $acl_fields = array(null, 'acl_user', 'acl_customer', 'acl_reseller', null);
        $this->acl_field = $acl_fields[USERT];

        if ($this->acl_field)
        {
            $sql = 'SELECT   id, name, ! acl
                    FROM     pm_user_info_fields uif
                    WHERE    ! = ?
                    ORDER BY ord';
            $result = $this->db->getAll($sql, array($this->acl_field, $this->acl_field, _ACL_WRITE), DB_FETCHMODE_ASSOC);
        }
        else
        {
            $sql = 'SELECT   id, name
                    FROM     pm_user_info_fields uif
                    ORDER BY ord';
            $result = $this->db->getAll($sql, DB_FETCHMODE_ASSOC);
        }

        foreach ($result as $row)
        {
            $this->Fields[$row['name']]['value'] = null;
            $this->Fields[$row['name']]['id'] = $row['id'];
            $this->Fields[$row['name']]['writeable'] = (isADMIN || ($row['acl'] & _ACL_WRITE));
        }
    }

    function &LoadOverview()
    {
        $sql = 'SELECT   id, name, ldapname, acl_reseller, acl_customer, acl_user, ord
                FROM     pm_user_info_fields
                ORDER BY ord';

        $this->overview = $this->db->getAll($sql, DB_FETCHMODE_ASSOC);

        $this->XAMS_Log('Selection', 'Selected User-Addressbook-Overview');

        return $this->overview;
    }

    function Load($userid=false)
    {
        if ($userid) $this->userid = $userid;

        if ($this->acl_field)
        {
            $sql = 'SELECT    id, name, value, ! acl
                    FROM      pm_user_info_fields uif
                    LEFT JOIN pm_user_info ui
                    ON        ui.infofieldid = uif.id
                    AND       userid = ?
                    WHERE     ! > 0
                    ORDER BY  ord';
            $result = $this->db->getAll($sql, array($this->acl_field, $this->userid, $this->acl_field), DB_FETCHMODE_ASSOC);
        }
        else
        {
            $sql = 'SELECT    id, name, value
                    FROM      pm_user_info_fields uif
                    LEFT JOIN pm_user_info ui
                    ON        ui.infofieldid = uif.id
                    AND       userid = ?
                    ORDER BY  ord';
            $result = $this->db->getAll($sql, array($this->userid), DB_FETCHMODE_ASSOC);
        }

        foreach ($result as $row)
        {
            $this->Fields[$row['name']]['value'] = $row['value'];
            $this->Fields[$row['name']]['id'] = $row['id'];
            $this->Fields[$row['name']]['writeable'] = (isADMIN || ($row['acl'] & _ACL_WRITE));
        }
    }

    function Add()
    {
        $max = $this->db->getOne('SELECT MAX(ord)+1 FROM pm_user_info_fields');
        if (!$max) $max = 1;
        $sql = 'INSERT INTO pm_user_info_fields (name, ldapname, acl_reseller, acl_customer, acl_user, ord)
                VALUES (?, ?, ?, ?, ?, ?)';
        $val = array($this->name, $this->ldapname, $this->acl_reseller, $this->acl_customer, $this->acl_user, $max);
        $result = $this->db->query($sql, $val);

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get('Addressbook Entry %s has been successfully added.'), $this->name);
            $this->XAMS_Log('Insertion', "Added Addressbook Entry $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get('Adressbook Entry %s could not be added'), $this->name);
            $this->XAMS_Log('Insertion', "Failed adding Adressbook Entry $this->name", 'failed');
        }
    }

    function Update($index = -1)
    {
        if ($index >= 0)
        {
            if (!empty($this->position[$index]))
            {
                if ($this->position[$index] > $this->ord[$index])
                { // Shift field down
                    $this->db->query('UPDATE pm_user_info_fields SET ord = ord-1 WHERE ord <= ? AND ord > ?', array($this->position[$index], $this->ord[$index]));
                    $this->db->query('UPDATE pm_user_info_fields SET ord = ? WHERE id = ?', array($this->position[$index], $this->id[$index]));
                }
                else
                { // Shift field up
                    $this->db->query('UPDATE pm_user_info_fields SET ord = ord+1 WHERE ord > ? AND ord < ?', array($this->position[$index], $this->ord[$index]));
                    $this->db->query('UPDATE pm_user_info_fields SET ord = ? WHERE id = ?', array($this->position[$index]+1, $this->id[$index]));
                }
            }
            $sql = 'UPDATE pm_user_info_fields
                    SET    name = ?, ldapname = ?, acl_reseller = ?, acl_customer = ?, acl_user = ?
                    WHERE  id = ?';
            $val = array($this->name[$index], $this->ldapname[$index], $this->acl_reseller[$index],
                         $this->acl_customer[$index], $this->acl_user[$index], $this->id[$index]);

            $result = $this->db->query($sql, $val);

            if ($result)
            {
                $this->notice = sprintf($this->i18n->get('Addressbook Entry %s has been successfully updated.'), $this->name[$index]);
                $this->XAMS_Log('Update', "Updated Addressbook Entry {$this->name[$index]}");
            }
            else
            {
                $this->notice = sprintf($this->i18n->get('Addressbook Entry %s could not be updated'), $this->name[$index]);
                $this->XAMS_Log('Update', "Failed updating Addressbook Entry {$this->name[$index]}", 'failed');
            }
        }
    }

    function Delete($index = -1)
    {
        if ($index >= 0)
        {
            $this->db->query('UPDATE pm_user_info_fields SET ord = ord-1 WHERE ord > ?', $this->ord[$index]);
            $result = $this->db->query('DELETE FROM pm_user_info_fields WHERE id = ?', $this->id[$index]);
            $this->db->query('DELETE FROM pm_user_info WHERE infofieldid = ?', $this->id[$index]);
            if ($result)
            {
                $this->notice = sprintf($this->i18n->get('Addressbook Entry %s has been successfully deleted.'), $this->name[$index]);
                $this->XAMS_Log('Deletion', "Deleted Addressbook Entry {$this->name[$index]}");
            }
            else
            {
                $this->notice = sprintf($this->i18n->get('Addressbook Entry %s could not be deleted'), $this->name[$index]);
                $this->XAMS_Log('Deletion', "Failed deleting Addressbook Entry {$this->name[$index]}", 'failed');
            }
        }
    }
}
?>