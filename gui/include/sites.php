<?php

include_once 'include/xclass.php';
include_once 'include/domains.php';
include_once 'include/resellers.php';
include_once 'include/customers.php';

class Sites extends xclass
{

    var $objname = 'sites';
    var $notice;
    var $resellers = array();
    var $lngbase = 'site';
    var $tablename = 'pm_sites';

    function Sites($init=true)
    {
        xclass::xclass($init);

        $this->myDomains = new Domains();
        $this->myReseller = new Resellers();

        if (isADMIN)
            $this->resellers = $this->db->getAll('SELECT id, name FROM pm_resellers ORDER BY name', DB_FETCHMODE_ASSOC);

        if (isCUSTOMER)
            $this->customers = $this->db->getCol('SELECT customerid FROM pm_sites_c_customers WHERE siteid = ?', 0, array(USERID));
    }

    // Check which actions the logged in user can perform on this site
    function Authenticate()
    {
        if ($this->authenticated) return $this->getAuthMode();
        switch (USERT)
        {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
                $mode = (USERID === $this->resellerid) ? _AUTH_ALL : _AUTH_ADD;
                $this->setAuthMode($mode);
                break;
            case _CUSTOMER:
                if (is_array($this->myReseller->customers) && in_array(USERID, $this->myReseller->customers))
                    $this->setAuthMode(_AUTH_LOAD | _AUTH_UPDATE); // Can update addressbook
                break;
            case _USER:
                // keep everything false
                break;
        }
        xclass::Authenticate(true);
        return $this->getAuthMode();
    }

    function CalcQuotaUnits($load)
    {
        if ($load)
        {
            if ($this->maxquota > 0)
            {
                for ($this->quotaunit=0; $this->quotaunit<3; $this->quotaunit++)
                    if ($this->maxquota % 1024 == 0) $this->maxquota /= 1024;
                    else break;
            }

            if ($this->maxuserquota > 0)
            {
                for ($this->userquotaunit=0; $this->userquotaunit<3; $this->userquotaunit++)
                    if ($this->maxuserquota % 1024 == 0) $this->maxuserquota /= 1024;
                    else break;
            }
        }
        else
        {
            if ($this->maxquota > 0) for ($i=0; $i<$this->quotaunit; $i++) $this->maxquota *= 1024;
            if ($this->maxuserquota > 0) for ($i=0; $i<$this->userquotaunit; $i++) $this->maxuserquota *= 1024;
        }
    }

    // Add/Update/Delete Addressbook for site
    function add_addressbook()
    {
        $addressbook_ids = array();
        // Get addressbook ids which has to be updated
        foreach ($this as $k=>$elem)
            if (preg_match('/^addressbook_\d+$/', $k))
                $addressbook_ids[] = substr($k, 12);

        // Delete Addressbook entries and add it again
        if (count($addressbook_ids) > 0)
        {
            $idstr = addslashes(implode(', ', $addressbook_ids));
            $this->db->query('DELETE FROM pm_site_info WHERE siteid = ? AND infofieldid IN (!)', array($this->id, $idstr));
            $sth = $this->db->prepare('INSERT INTO pm_site_info VALUES (?, ?, ?)');
            foreach ($addressbook_ids as $id)
                if (!empty($this->{'addressbook_'. $id}))
                    $this->db->execute($sth, array($id, $this->id, $this->{'addressbook_'. $id}));
        }
    }

    // Check if Site already exist
        function S_Check()
        {
            $c = (int)$this->db->getOne('SELECT COUNT(id) FROM pm_sites WHERE Name = ?', $this->name);
            return ($c == 0);
        }

        // Check if Site do not contain bad characters 
        function R_Check()
        {
            $c = !eregi("^[_a-z0-9-]+$",$this->name);
            return ($c == 0);
        }

    // xclass::check_formular enhancement
    function check_formular($form_mode)
    {
        if (!empty($this->resellerid))
        {
            $this->myReseller->Load($this->resellerid);
            $this->ApplyQuota();
            
            // Check if something selected in drop-down menu
            //if ($form_mode == 'new' && empty($this->resellerid))
	    //    $this->error_func('resellerid', $this->i18n->get('Select a Reseller in the drop-down menu'));

            // Check if Site-Quota of selected Reseller has reached
            if ($form_mode == 'new' && $this->myReseller->FreeSites() == 0)
                $this->error_func('resellerid', $this->i18n->get('Unfortunately no more sites can be added. Your Site-Quota has reached.'));

            // Check for Reseller's DomainQuota
            $this->myDomains->GenerateDomainList($this->domainname);
            if ($this->myReseller->FreeDomains() != -1 && $this->myReseller->FreeDomains() - $this->myDomains->newdomains < 0)
                $this->error_func('domainname', $this->i18n->get("Unfortunately no more domains can be added. Reseller's Domain-Quota has reached."));
        }
      
	if ($this->mode == 'new')
	{
	    if ($form_mode == 'new' && empty($this->resellerid))
	        $this->error_func('resellerid', $this->i18n->get('Select a Reseller in the drop-down menu'));
            if (!$this->S_Check())
                $this->error_func('name', $this->i18n->get('This Site does already exists'));
            if (!$this->R_Check() && strlen($this->name) != 0 )
	        $this->error_func('name', $this->i18n->get('Forbidden characters in Site name'));
            $this->name = strtoupper($this->name);
	}

        xclass::check_formular($form_mode);
    }

    function Load($id=false)
    {
        if ($id) $this->id = $id;
        if (empty($this->id)) die($this->objname. '->Load() - ERROR : Have no ID to load!');

        $sql = 'SELECT    s.resellerid, r.name resellername, s.name, s.maxquota,
                          s.maxuserquota, s.maxaddr, s.maxaliases, s.addrtype,
                          s.viruscheckin, s.viruscheckout, s.sitestate,
                          s.spamcheckin, s.spamcheckout, s.spamscore, s.highspamscore,
                          DATE_FORMAT(s.added, ?) added,
                          DATE_FORMAT(s.updated, ?) updated
                FROM      pm_sites s
                LEFT JOIN pm_resellers r
                ON        r.id = s.resellerid
                WHERE     s.id = ?';
        $val = array($this->date_format, $this->date_format, $this->id);

        $result = xclass::Load($sql, $val);

        // Load assigned reseller and apply quotas
        $this->myReseller->Load($this->resellerid);
        $this->ApplyQuota();

        // Load all domains hanging under this site
        $this->myDomains->assign('sitename', $this->name);
        $this->myDomains->Load($this->id);

        $this->XAMS_Log("Selection", "Selected Site $this->name");

        $this->Authenticate();
        return $result;
    }

    function ApplyQuota()
    {
        // Apply the maximum amount of Site-Quota
        $free_sitequota = $this->myReseller->FreeQuota($this->id);
        if ($free_sitequota < 0)
        {
            $this->VSPEC['maxquota']['min'] = -1;
            $this->VSPEC['maxquota']['max'] = null;
        }
        else
        {
            $this->VSPEC['maxquota']['max'] = $free_sitequota;
        }

        // Apply the maximum amount of User-Quota
        $free_userquota = $this->myReseller->FreeUserQuota();
        if ($this->maxquota != -1 && $this->maxquota < $free_userquota)
            $free_userquota = $this->maxquota;
        if ($free_userquota < 0)
        {
            $this->VSPEC['maxuserquota']['min'] = -1;
            $this->VSPEC['maxuserquota']['max'] = null;
        }
        else
            $this->VSPEC['maxuserquota']['max'] = $free_userquota;
            
        // Apply the maximum amount of Users
        $free_users = $this->myReseller->FreeUsers4Site();
        if ($free_users < 0)
        {
            $this->VSPEC['maxaddr']['min'] = -1;
            $this->VSPEC['maxaddr']['max'] = null;
        }
        else
        {
            if (isset($this->mode) && $this->mode == 'update')
            {
                if ($this->maxaddr <= $this->LoadedData['maxaddr'])
                    $free_users = $this->maxaddr;
                else
                    $free_users += $this->LoadedData['maxaddr'];
            }
            $this->VSPEC['maxaddr']['max'] = abs($free_users);
        }

        // Apply the maximum amount of Aliases
        $free_aliases = $this->myReseller->FreeAliases4Site();
        if ($free_aliases < 0)
        {
            $this->VSPEC['maxaliases']['min'] = -1;
            $this->VSPEC['maxaliases']['max'] = null;
        }
        else
        {
            if (isset($this->mode) && $this->mode == 'update')
            {
                if ($this->maxaliases <= $this->LoadedData['maxaliases'])
                    $free_aliases = $this->maxaliases;
                else
                    $free_aliases += $this->LoadedData['maxaliases'];
            }
            $this->VSPEC['maxaliases']['max'] = abs($free_aliases);
        }
    }

    function FreeQuota()
    {
        if ($this->maxquota < 0) return -1;
        $sum_maxquota = (int)$this->db->getOne('SELECT SUM(quota) FROM pm_users WHERE quota > 0 AND siteid = ?', array($this->id));
        $free_quota = $this->maxquota - $sum_maxquota;
        return ($free_quota < 0) ? 0 : $free_quota;
    }

    function FreeUsers()
    {
        if ($this->maxaddr < 0) return -1;
        $users = (int)$this->db->getOne('SELECT COUNT(*) FROM pm_users WHERE siteid = ?', array($this->id));
        $users = $this->maxaddr - $users;
        return ($users < 0) ? 0 : $users;
    }

    function FreeAliases()
    {
        if ($this->maxaliases < 0) return -1;
        $aliases = (int)$this->db->getOne('SELECT COUNT(*) FROM pm_aliases WHERE siteid = ?', array($this->id));
        $aliases = $this->maxaliases - $aliases;
        return ($aliases < 0) ? 0 : $aliases;
    }

    function Add()
    {
        $this->Authenticate();
        $result = xclass::Add();

        $this->add_addressbook();

        $this->assign('resellername', $this->myReseller->name);
        $this->myDomains->assign('siteid', $this->id);
        $this->myDomains->assign('sitename', $this->name);
        $this->myDomains->GenerateDomainList($this->domainname);
        $this->myDomains->Update();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Site '%s' was added successfully."), $this->name);
            $this->XAMS_Log("Insertion", "Added Site $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Site '%s' could not be added."), $this->name);
            $this->XAMS_Log("Insertion", "Failed adding Site $this->name", "failed");
        }
    }

    function Update()
    {
        $result = xclass::Update();

        $this->add_addressbook();

        $this->myDomains->GenerateDomainList($this->domainname);
        $this->myDomains->Update();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Site '%s' was updated successfully."), $this->name);
            $this->XAMS_Log("Update", "Updated Site $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Site '%s' could not be updated."), $this->name);
            $this->XAMS_Log("Update", "Failed updating Site $this->name", "failed");
        }
    }

    function Delete()
    {
        $result = xclass::Delete();

        // Delete Site
        //$result = $this->db->query('DELETE FROM pm_sites WHERE id = ?', $this->id);

        // Delete Addressbook of Site
        $this->db->query('DELETE FROM pm_site_info WHERE siteid = ?', $this->id);

        // Delete Domains of Site
        $this->db->query('DELETE FROM pm_domains WHERE siteid = ?', $this->id);

        // Delete User-Addressboks of Users of Site
        $ids = array();
        $ids = $this->db->getCol('SELECT id FROM pm_users WHERE siteid = ?', 0, array($this->id));
        if ($ids) $this->db->query('DELETE FROM pm_user_info WHERE userid IN (!)', $ids);

        // Delete Users of Site
        $this->db->query('DELETE FROM pm_users WHERE siteid = ?', $this->id);

        // Delete Alias-Addressboks of Aliases of Site
        $ids = array();
        $ids = $this->db->getCol('SELECT id FROM pm_aliases WHERE siteid = ?', 0, array($this->id));
        if ($ids) $this->db->query('DELETE FROM pm_alias_info WHERE aliasid IN (!)', $ids);

        // Delete Aliases of Site
        $this->db->query('DELETE FROM pm_aliases WHERE siteid = ?', $this->id);

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Site '%s' was deleted successfully."), $this->name);
            $this->XAMS_Log("Deletion", "Deleted Site $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Site '%s' could not be deleted."), $this->name);
            $this->XAMS_Log("Deletion", "Failed deleting Site $this->name", "failed");
        }
    }

}

?>
