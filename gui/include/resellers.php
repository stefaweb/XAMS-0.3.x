<?php

include_once 'include/xclass.php';

class Resellers extends xclass
{
    var $objname = 'resellers';
    var $notice;
    var $addressbook_ids;
    var $lngbase = 'reseller';
    var $tablename = 'pm_resellers';
    var $customers = array();
    var $sites = array();

    function Resellers($init=true)
    {
        xclass::xclass($init);
    }

    // Check which actions the logged in user can perform on this reseller
    function Authenticate()
    {
        if ($this->authenticated) return ($this->getAuthMode());
        switch (USERT)
        {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
                if ($this->id == USERID)
                {
                    $this->setAuthMode(_AUTH_LOAD | _AUTH_UPDATE);
                }
                break;
            case _CUSTOMER:
            case _USER:
                break;
        }
        xclass::Authenticate(true);
        return $this->getAuthMode();
    }

    // Add/Update/Delete Addressbook for reseller
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
            $this->db->query('DELETE FROM pm_reseller_info WHERE resellerid = ? AND infofieldid IN (!)', array($this->id, $idstr));
            $sth = $this->db->prepare('INSERT INTO pm_reseller_info VALUES (?, ?, ?)');
            foreach ($addressbook_ids as $id)
                if (!empty($this->{'addressbook_'. $id}))
                    $this->db->execute($sth, array($id, $this->id, $this->{'addressbook_'. $id}));
        }
    }

    // Return the amount of free Customers (-1 if unlimited)
    function FreeCustomers()
    {
        if ($this->maxcustomers < 0) return -1;
        $sum_customers = $this->db->getOne('SELECT COUNT(*) FROM pm_customers WHERE resellerid = ?', array($this->id));
        $free_customers = $this->maxcustomers - $sum_customers;
        return ($free_customers < 0) ? 0 : $free_customers;
    }

    // Return the amount of free Sites (-1 if unlimited)
    function FreeSites()
    {
        if ($this->maxsites < 0) return -1;
        $sum_sites = (int)$this->db->getOne('SELECT COUNT(*) FROM pm_sites WHERE resellerid = ?', array($this->id));
        $free_sites = $this->maxsites - $sum_sites;
        return ($free_sites < 0) ? 0 : $free_sites;
    }

    // Return the amount of free Domains (-1 if unlimited)
    function FreeDomains()
    {
        if ($this->maxdomains < 0) return -1;
        $sum_domains = (int)$this->db->getOne('SELECT COUNT(*) FROM pm_domains d INNER JOIN pm_sites s ON d.siteid = s.id WHERE s.resellerid = ?', array($this->id));
        $free_domains = $this->maxdomains - $sum_domains;
        return ($free_domains < 0) ? 0 : $free_domains;
    }

    // Get the amount of maxusers this site can have
    function FreeUsers4Site()
    {
        if ($this->maxusers < 0) return -1;
        $sum_maxusers = (int)$this->db->getOne('SELECT SUM(maxaddr) FROM pm_sites WHERE maxaddr > 0 AND resellerid = ?', array($this->id));
        $free_users = $this->maxusers - $sum_maxusers;
        return ($free_users < 0) ? 0 : $free_users;
    }

    // Get the amount of maxaliases this site can have
    function FreeAliases4Site()
    {
        if ($this->maxaliases < 0) return -1;
        $sum_maxaliases = (int)$this->db->getOne('SELECT SUM(maxaliases) FROM pm_sites WHERE maxaliases > 0 AND resellerid = ?', array($this->id));
        $free_aliases = $this->maxaliases - $sum_maxaliases;
        return ($free_aliases < 0) ? 0 : $free_aliases;
    }

    // Return the amount of free Users (-1 if unlimited)
    function FreeUsers()
    {
        if ($this->maxusers < 0) return -1;
        $sql = 'SELECT     COUNT(*)
                FROM       pm_users u
                INNER JOIN pm_sites s
                ON         u.siteid = s.id
                INNER JOIN pm_resellers r
                ON         s.resellerid = r.id
                WHERE      r.id = ?';
        $sum_users = (int)$this->db->getOne($sql, array($this->id));
        $free_users = $this->maxusers - $sum_users;
        return ($free_users < 0) ? 0 : $free_users;
    }

    function FreeAliases()
    {
        if ($this->maxaliases < 0) return -1;
        $sql = 'SELECT      COUNT(*)
                FROM        pm_aliases a
                INNER JOIN  pm_sites s
                ON          a.siteid = s.id
                WHERE       s.resellerid = ?';
        $sum_aliases = (int)$this->db->getOne($sql, array($this->id));
        $free_aliases = $this->maxaliases - $sum_aliases;
        return ($free_aliases < 0) ? 0 : $free_aliases;
    }

    function FreeQuota($siteid)
    {
        if ($this->maxquota < 0) return -1;
        $sum_maxquota = (int)$this->db->getOne('SELECT SUM(maxquota) FROM pm_sites WHERE maxquota > 0 AND resellerid = ?', array($this->id));
        $free_quota = $this->maxquota - $sum_maxquota;
        $current_maxquota = (int)$this->db->getOne('SELECT maxquota FROM pm_sites WHERE id = ?', $siteid);
        $free_quota = $free_quota + $current_maxquota;
        if ($free_quota > $this->maxsitequota)
           $free_quota = $this->maxsitequota;
        return ($free_quota < 0) ? 0 : $free_quota;
    }

    function FreeUserQuota()
    {
        return $this->maxuserquota;
    }

    function Load($id=false, $Load4Login=false)
    {
        if ($id) $this->id = $id;
        if (empty($this->id)) die($this->objname. '->Load() - Have no ID to load!');

        $sql = 'SELECT name, locked, maxcustomers, maxsites, maxdomains,
                       maxusers, maxaliases, maxquota, maxsitequota,
                       maxuserquota, viruscheckin, viruscheckout,
                       spamcheckin, spamcheckout, spamscore, highspamscore,
                       DATE_FORMAT(added, ?) as added,
                       DATE_FORMAT(updated, ?) as updated
                FROM   pm_resellers
                WHERE  id = ?';
        $val = array($this->date_format, $this->date_format, $this->id);

        $result = xclass::Load($sql, $val);

        if ($result)
        {
            // Load Customer-Id's this Reseller is responsible for
            $this->customers = $this->db->getCol('SELECT id FROM pm_customers WHERE resellerid = ?', 0, array($this->id));

            // Load Sites this Reseller is responsible for
            $this->sites = $this->db->getCol('SELECT id FROM pm_sites WHERE resellerid = ?', 0, array($this->id));
        }

        if ($Load4Login)
        {
            $this->XAMS_Log("Selection", "Selected Reseller $this->name", "ok", $this->id, _RESELLER);
        }
        else
        {
            $this->XAMS_Log("Selection", "Selected Reseller $this->name");
        }

        $this->Authenticate();
        return $result;
    }

    // Update pm_sites when adding/updating a Reseller
    function UpdateSites()
    {
        if (count($this->sites) > 0)
        {
            $ids = addslashes(implode(', ', $this->sites));
            $this->db->query('UPDATE pm_sites SET resellerid = 0 WHERE resellerid = ? AND id NOT IN (!)', array($this->id, $ids));
            $this->db->query('UPDATE pm_sites SET resellerid = ? WHERE id IN (!)', array($this->id, $ids));

            // Delete Site-assignments of our customers
            $cids = $this->db->getCol('SELECT id FROM pm_customers WHERE resellerid = ?', 0, $this->id);
            if (count($cids))
            {
                $cids = addslashes(implode(', ', $cids));
                $this->db->query('DELETE FROM pm_sites_c_customers WHERE customerid IN (!) AND siteid NOT IN (!)', array($cids, $ids));
            }
        }
        else
        {
            $this->db->query('UPDATE pm_sites SET resellerid = NULL WHERE resellerid = ?', array($this->id));
        }
    }

    function Add()
    {
        $this->Authenticate();
        $this->password = md5($this->password);
        $result = xclass::Add();

        $this->UpdateSites();

        $this->add_addressbook();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Reseller '%s' was added successfully."), $this->name);
            $this->XAMS_Log("Insertion", "Added Reseller $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Reseller '%s' could not be added."), $this->name);
            $this->XAMS_Log("Insertion", "Failed adding Reseller $this->name", "failed");
        }
    }

    function Update()
    {
        if (!empty($this->password)) $this->password = md5($this->password);
        $result = xclass::Update();

        $this->UpdateSites();

        $this->add_addressbook();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Reseller '%s' was updated successfully."), $this->name);
            $this->XAMS_Log("Update", "Updated Reseller $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Reseller '%s' could not be updated."), $this->name);
            $this->XAMS_Log("Update", "Failed updating Reseller $this->name", "failed");
        }
    }

    function Delete()
    {
        // Delete Reseller
        //$result = $this->db->query('DELETE FROM pm_resellers WHERE id = ?', $this->id);
        $result = xclass::Delete();

        // Delete Addressbook of Reseller
        $this->db->query('DELETE FROM pm_reseller_info WHERE resellerid = ?', $this->id);

        // Delete Site-Templates of Reseller
        $this->db->query('DELETE FROM pm_site_templates WHERE resellerid = ?', $this->id);

        // Delete User-Templates of Reseller
        $this->db->query('DELETE FROM pm_user_templates WHERE resellerid = ?', $this->id);

        // Select all SiteIDs this Reseller was assigned to
        $sites_array = $this->db->getCol('SELECT id FROM pm_sites WHERE resellerid = ?', 0, array($this->id));

        // Delete all sites<->customers assignments where Site was assigned to Reseller
        if (count($sites_array) > 0)
        {
            $sites = implode(', ', addslashes($sites_array));
            $this->db->query('DELETE FROM pm_sites_c_customers WHERE siteid IN (!)', $sites);
        }

        // Give all reseller-sites free when deleting a reseller
        $this->db->query('UPDATE pm_sites SET resellerid = NULL WHERE resellerid = ?', $this->id);

        // Give all customers free when deleting a reseller
        $this->db->query('UPDATE pm_customers SET resellerid = NULL WHERE resellerid = ?', $this->id);

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Reseller '%s' was deleted successfully."), $this->name);
            $this->XAMS_Log("Deletion", "Deleted Reseller $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Reseller '%s' could not be deleted."), $this->name);
            $this->XAMS_Log("Deletion", "Failed deleting Reseller $this->name", "failed");
        }
    }
}
?>