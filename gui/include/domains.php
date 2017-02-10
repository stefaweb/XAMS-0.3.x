<?php

include_once 'include/xclass.php';

class domains extends xclass
{
    public $objname = 'domains';
    public $notice;
    public $domainnames = [];
    public $domains_for_insertion = [];
    public $domains_for_deletion = [];

    public function Domains($init = true)
    {
        xclass::xclass($init);
    }

    public function Load($siteid)
    {
        if ($siteid) {
            $this->siteid = $siteid;
        }
        $this->domainsnames = [];

        $this->domainnames = $this->db->getCol('SELECT name FROM pm_domains WHERE siteid = ? ORDER BY name', 0, [$this->siteid]);
        $this->domains = count($this->domainnames);

        $this->XAMS_Log('Selection', "Selected Domains of Site $this->sitename");
    }

    // GenerateDomainList checks which domains (from the formular-entered)
    // are new (should be added to db) or which are obsolete (should be deleted)
    public function GenerateDomainList($list)
    {
        $list = explode("\n", $list);
        $this->newdomains = 0;

        // Check which domains are new
        foreach ($list as $k => $elem) {
            $list[$k] = $elem = trim($elem);
            if (!empty($elem) && !in_array($elem, $this->domainnames)) {
                $this->newdomains++;
                $this->domains_for_insertion[] = $elem;
            }
        }

        // Check which domains have been droped
        foreach ($this->domainnames as $elem) {
            $elem = trim($elem);
            if (!empty($elem) && !in_array($elem, $list)) {
                $this->newdomains--;
                $this->domains_for_deletion[] = $elem;
            }
        }

        // To have an up to date formular (reload on error):
        $this->domainnames = $list;
    }

    public function Update()
    {
        $new_domains = $del_domains = [];
        $result = $result2 = true;
        // Check for Domains that should be added to pm_domains

        $sth = $this->db->prepare('INSERT INTO pm_domains (siteid, name, added) VALUES (?, ?, NOW())');
        foreach ($this->domains_for_insertion as $elem) {
            $result = $this->db->execute($sth, [$this->siteid, $elem]);
            if (!$result) {
                break;
            }
        }

        // Check for Domains that should be removed from pm_domains
        $sth = $this->db->prepare('DELETE FROM pm_domains WHERE name = ? AND siteid = ?');
        foreach ($this->domains_for_deletion as $elem) {
            $result2 = $this->db->execute($sth, [$elem, $this->siteid]);
            if (!$result2) {
                break;
            }
        }

        // Execute INSERT and DELETE SQL
        if ($result && $result2) {
            $this->notice = "Domains of Site $this->sitename has been successfully updated.";
            $this->XAMS_Log('Update', "Updated Domains of Site $this->sitename\n
                                       New Domains: ".implode(',', $this->domains_for_insertion).'
                                       Deleted Domains: '.implode(',', $this->domains_for_deletion));
        } else {
            $this->notice = "Domains of Site $this->sitename could not be updated";
            $this->XAMS_Log('Update', "Failed updating Domains of Site $this->sitename", 'failed');
        }
    }
}
