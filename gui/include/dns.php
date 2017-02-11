<?php

include_once 'include/xclass.php';

class dns extends xclass
{
    public $objname = 'dns';
    public $notice;
    public $multiplier = [1, 60, 3600, 86400, 604800, 2592000];
    public $units = ['ttl', 'refresh', 'retry', 'expire', 'nttl'];
    public $records = [];
    public $lngbase = 'dns';
    public $tablename = 'pm_dns';

    public function DNS($init = true)
    {
        xclass::xclass($init);
        $this->CalcUnits();
    }

    // Check which actions the logged in user can perform on this dns
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
            case _CUSTOMER:
            case _USER:
                // keep everything false
                break;
        }
        xclass::Authenticate(true);

        return $this->getAuthMode();
    }

    public function CalcUnits()
    {
        // Calculate units (minutes,hours,days,weeks,months)
        foreach ($this->units as $elem)
            for ($i=5; $i>=0; $i--)
                do
                {
                    if ($this->$elem % $this->multiplier[$i] == 0)
                    {
                        $this->{$elem. '_unit'} = $i;
                        $this->$elem /= $this->multiplier[$i];
                        break 2;
                    }
                    else break;
                }
                while (true);
    }

    public function multiply()
    {
        global $multiplier;
        foreach ($this->units as $elem) {
            $this->dummy[$elem] = $this->$elem;
            $this->$elem *= $this->multiplier[$this->{$elem.'_unit'}];
        }
    }

    public function divide()
    {
        global $multiplier;
        foreach ($this->units as $elem) {
            $this->$elem = $this->dummy[$elem];
        }
    }

    public function CalculateSerial()
    {
        if ($this->serialautomatic[0] == 't') {
            if (strlen($this->LoadedData['serial']) < 10) {
                $this->serial = date('Ymd01');
            } else {
                $date_today = (int) date('Ymd');
                $date_stored = (int) substr($this->serial, 0, 8);
                $date_stored2 = (int) substr($this->serial, 8, 2);
                if ($date_today > $date_stored) {
                    $this->serial = date('Ymd01');
                } else {
                    $this->serial = sprintf('%d%02d', $date_stored, ++$date_stored2);
                }
            }
        }
    }

    public function Load($id = false)
    {
        if ($id) {
            $this->id = $id;
        }
        $sql = 'SELECT name, zonetype, masterdns, zoneadmin, serial,
                       serialautomatic, ttl, refresh, retry, expire,
                       nttl, changed,
                       DATE_FORMAT(added, ?) added,
                       DATE_FORMAT(updated, ?) updated
                FROM   pm_dns
                WHERE  id = ?';
        $val = [$this->date_format, $this->date_format, $this->id];

        xclass::Load($sql, $val);

        $this->records = $this->db->getAll('SELECT id, name, type, parameter1, parameter2, comment FROM pm_dns_records WHERE dnsid = ? ORDER BY type, name', [$this->id], DB_FETCHMODE_ASSOC);

        $this->CalcUnits();

        $this->XAMS_Log('Selection', "Selected Zone $this->name");

        $this->Authenticate();
    }

    public function Add()
    {
        $this->Authenticate();
        $this->multiply();
        $result = xclass::Add();

        $this->divide();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Zone '%s' was added successfully."), $this->name);
            $this->XAMS_Log('Insertion', "Added Zone $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Zone '%s' could not be added."), $this->name);
            $this->XAMS_Log('Insertion', "Failed adding Zone $this->name", 'failed');
        }
    }

    public function Update()
    {
        $this->multiply();
        if ($this->ObjectChanged()) {
            $this->CalculateSerial();
        }
        $result = xclass::Update();
        $this->divide();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Zone '%s' was updated successfully."), $this->name);
            $this->XAMS_Log('Update', "Updated Zone $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Zone '%s' could not be updated."), $this->name);
            $this->XAMS_Log('Update', "Failed updating Zone $this->name", 'failed');
        }
    }

    public function Delete()
    {
        $result = xclass::Delete();

        if ($result) {
            $this->db->query('DELETE FROM pm_dns_records WHERE dnsid = ?', $this->id);

            $this->notice = sprintf($this->i18n->get("Zone '%s' was deleted successfully."), $this->name);
            $this->XAMS_Log('Deletion', "Deleted Zone $this->name");
        } else {
            $this->notice = sprintf($this->i18n->get("Zone '%s' could not be deleted."), $this->name);
            $this->XAMS_Log('Deletion', "Failed deleting Zone $this->name", 'failed');
        }
    }
}
