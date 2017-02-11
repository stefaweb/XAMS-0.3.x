<?php

// Superclass for exim style filters

include_once 'include/xclass.php';

class SCFilters extends xclass
{
    public $objname = 'scfilters';
    public $notice = null;
    public $lngbase = 'eximfilter';
    public $VSPECfile = 'eximfilters';
    public $tablename = 'pm_exim_filters';

    public function SCFilters($init = true)
    {
        xclass::xclass($init);
    }

    public function Load($userid = false)
    {
        if ($userid) {
            $this->userid = $userid;
        }
        $sql = 'SELECT filter,
                       active,
                       DATE_FORMAT(added, ?) added,
                       DATE_FORMAT(updated, ?) updated
                FROM   pm_exim_filters
                WHERE  userid = ?';
        $val = [$this->date_format, $this->date_format, $this->userid];

        xclass::Load($sql, $val);

        if ($this->filter) {
            $this->XAMS_Log('Selection', 'Selected Exim-Filter of User '.$this->myUser->name);

            return true;
        }

        return false;
    }

    public function remove_cr()
    {
        $this->filter = preg_replace("/\r/", null, $this->filter);
    }

    public function Add()
    {
        $this->remove_cr();
        $result = xclass::Add();
    }

    public function Update()
    {
        $this->remove_cr();
        $result = xclass::Update();
    }

    public function Delete()
    {
        $sql = 'DELETE FROM pm_exim_filters WHERE userid = ?';
        $result = $this->db->query($sql, [$this->userid]);

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Filter for User '%s' was deleted successfully."), $this->myUser->name);
            $this->XAMS_Log('Deletion', 'Deleted Filter for User '.$this->myUser->name);
        } else {
            $this->notice = sprintf($this->i18n->get("Filter for User '%s' could not be deleted."), $this->myUser->name);
            $this->XAMS_Log('Deletion', 'Failed deleting Filter for User '.$this->myUser->name, 'failed');
        }
    }
}
