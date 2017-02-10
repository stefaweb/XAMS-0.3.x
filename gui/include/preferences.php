<?php

include_once 'include/xclass.php';

class preferences extends xclass
{
    public $objname = 'preferences';
    public $notice;
    public $lngbase = 'preferences';

    public function Preferences($init = true)
    {
        xclass::xclass($init);
    }

    // Check which actions the logged in user can perform on preferences
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
                break;
        }
        xclass::Authenticate(true);

        return $this->getAuthMode();
    }

    public function Load($log = true)
    {
        $this->Authenticate();
        $sql = 'SELECT loglevel, loglines, newversioncheck, lastversioncheck,
                       lastnewscheck, defaultlanguage, onlinenews, loginwelcome,
		       spamscore, highspamscore
                FROM   pm_preferences
                LIMIT  1';

        xclass::Load($sql, null);

        if ($log) {
            $this->XAMS_Log('Selection', 'Selected Preferences');
        }
    }

    public function Update()
    {
        $sql = $this->create_sql_update('pm_preferences', $vals);
        $result = $this->db->query($sql, $vals);

        if ($result) {
            $this->notice = $this->i18n->get('Preferences was updated successfully.');
            $this->XAMS_Log('Update', 'Updated Preferences');
        } else {
            $this->notice = $this->i18n->get('Preferences could not be updated.');
            $this->XAMS_Log('Update', 'Failed updating Preferences', 'failed');
        }
    }
}
