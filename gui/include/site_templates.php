<?php

include_once 'include/xclass.php';
include_once 'include/i18n.php';

class Site_Templates extends xclass
{
    public $objname = 'site_templates';
    public $notice;
    public $lngbase = 'site_template';
    public $tablename = 'pm_site_templates';

    public function Site_Templates($init = true)
    {
        xclass::xclass($init);
    }

    // Check which actions the logged in user can perform on this site-template
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
                if ($this->resellerid === USERID) {
                    $this->setAuthMode(_AUTH_ALL);
                }
                break;
            case _CUSTOMER:
            case _USER:
                // keep everything false
                break;
        }
        xclass::Authenticate(true);

        return $this->getAuthMode();
    }

    public function GetName($id = false)
    {
        if ($id) {
            $this->id = $id;
        }
        $name = $this->db->getOne('SELECT templatename FROM pm_site_templates WHERE id = ?', [$this->id]);

        return $name;
    }

    public function Load($id = false)
    {
        if ($id) {
            $this->id = $id;
        }
        if (empty($this->id)) {
            die($this->objname.'->Load() - Have no ID to load!');
        }
        $sql = 'SELECT adminid, resellerid, templatename,
                       name, maxquota, maxuserquota, maxaddr, maxaliases,
                       addrtype, viruscheckin, viruscheckout,
                       spamcheckin, spamcheckout, spamscore, highspamscore,
                       leftpart1, rightpart1, bounceforward1,
                       leftpart2, rightpart2, bounceforward2,
                       leftpart3, rightpart3, bounceforward3,
                       leftpart4, rightpart4, bounceforward4,
                       leftpart5, rightpart5, bounceforward5,
                       DATE_FORMAT(added, ?) added,
                       DATE_FORMAT(updated, ?) updated
                FROM   pm_site_templates
                WHERE  id = ?';

        $val = [$this->date_format, $this->date_format, $this->id];

        xclass::Load($sql, $val);

        $this->XAMS_Log('Selection', "Selected Site-Template $this->templatename [$this->id]");

        $this->Authenticate();
    }

    public function Add()
    {
        $this->Authenticate();
        $result = xclass::Add();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Site-Template '%s' was added successfully."), $this->templatename);
            $this->XAMS_Log('Insertion', "Added Site-Template $this->templatename");
        } else {
            $this->notice = sprintf($this->i18n->get("Site-Template '%s' could not be added."), $this->templatename);
            $this->XAMS_Log('Insertion', "Failed adding Site-Template $this->templatename", 'failed');
        }
    }

    public function Update()
    {
        $result = xclass::Update();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Site-Template '%s' was updated successfully."), $this->templatename);
            $this->XAMS_Log('Update', "Updated Site-Template $this->templatename");
        } else {
            $this->notice = sprintf($this->i18n->get("Site-Template '%s' could not be updated."), $this->templatename);
            $this->XAMS_Log('Update', "Failed updating Site-Template $this->templatename", 'failed');
        }
    }

    public function Delete()
    {
        $result = xclass::Delete();

        if ($result) {
            $this->notice = sprintf($this->i18n->get("Site-Template '%s' was deleted successfully."), $this->templatename);
            $this->XAMS_Log('Deletion', "Deleted Site-Template $this->templatename");
        } else {
            $this->notice = sprintf($this->i18n->get("Site-Template '%s' could not be deleted."), $this->templatename);
            $this->XAMS_Log('Deletion', "Failed updating Site-Template $this->templatename", 'failed');
        }
    }
}
