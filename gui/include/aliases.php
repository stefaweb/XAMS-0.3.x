<?php

include_once 'include/xclass.php';
include_once 'include/sites.php';

class Aliases extends xclass
{
    var $objname = 'aliases';
    var $notice;
    var $lngbase = 'alias';
    var $tablename = 'pm_aliases';

    function Aliases($init=true)
    {
        $this->mySite = new Sites();
        $this->myReseller =& $this->mySite->myReseller;

        xclass::xclass($init);

        // Load Site list
        if (isADMIN)
            $this->sites = $this->db->getAll('SELECT   id, name
                                              FROM     pm_sites
                                              ORDER BY name', DB_FETCHMODE_ASSOC);
        elseif (isRESELLER)
            $this->sites = $this->db->getAll('SELECT   id, name
                                              FROM     pm_sites
                                              WHERE    resellerid = ?
                                              ORDER BY name', array(USERID), DB_FETCHMODE_ASSOC);
        elseif (isCUSTOMER)
            $this->sites = $this->db->getAll('SELECT    id, name
                                              FROM      pm_sites s
                                              LEFT JOIN pm_sites_c_customers scc
                                              ON        scc.siteid = s.id
                                              WHERE     scc.customerid = ?
                                              ORDER BY  name', array(USERID), DB_FETCHMODE_ASSOC);
    }

    // Check which actions the logged in user can perform on this alias
    function Authenticate()
    {
        if ($this->authenticated) return $this->getAuthMode();
        switch (USERT)
        {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
                $mode = (USERID === $this->mySite->resellerid) ? _AUTH_ALL : _AUTH_ADD;
                $this->setAuthMode($mode);
                break;
            case _CUSTOMER:
                $this->mySite->Load($this->siteid);
                $mode = (in_array(USERID, $this->myReseller->customers)) ? _AUTH_ALL : _AUTH_ADD;
                $this->setAuthMode($mode);
                break;
            case _USER:
                // keep everything false
                break;
        }
        xclass::Authenticate(true);
        return $this->getAuthMode();
    }

    function LoadSite()
    {
        $this->mySite->Load($this->siteid);
    }

    // Quota-Check: May a new alias added to the reseller/site
    function QC_AddAlias()
    {
        if (!$this->mySite->id) return 3;
        elseif ($this->myReseller->FreeAliases() == 0) return 1;
        elseif ($this->mySite->FreeAliases() == 0) return 2;
    }

    // xclass::check_formular enhancement
    function check_formular($form_mode)
    {
        // Quota check
        if (!empty($this->siteid))
            $this->LoadSite();

        // Check if alias can be added to the reseller/site
        if ($this->mode == 'new')
        {
            $err = $this->QC_AddAlias();
            $err_arr = array(null,
                             'Unfortunately no more aliases can be added. Reseller based Alias-Quota has reached.',
                             'Unfortunately no more aliases can be added. Site based Alias-Quota has reached.',
                             'Select a Site in the drop-down menu.');
            if ($err)
                $this->error_func('siteid', $this->i18n->get($err_arr[$err]));
        }

        xclass::check_formular($form_mode);
    }

    // Get AliasCount of a specific Site
    function AliasesOfSite($id)
    {
        $Aliases = $this->db->getOne('SELECT COUNT(*) FROM pm_aliases WHERE siteid = ?', array($id));
        return $Aliases;
    }

    // Add/Update/Delete Addressbook for alias
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
            $this->db->query('DELETE FROM pm_alias_info WHERE aliasid = ? AND infofieldid IN (!)', array($this->id, $idstr));
            $sth = $this->db->prepare('INSERT INTO pm_alias_info VALUES (?, ?, ?)');
            foreach ($addressbook_ids as $id)
                if (!empty($this->{'addressbook_'. $id}))
                    $this->db->execute($sth, array($id, $this->id, $this->{'addressbook_'. $id}));
        }
    }

    function Load($id=false)
    {
        if ($id) $this->id = $id;
        if (empty($this->id)) die($this->objname. '->Load() - Have no ID to load!');

        $sql = 'SELECT siteid, leftpart, rightpart, bounceforward, blackhole,
                       DATE_FORMAT(added, ?) added,
                       DATE_FORMAT(updated, ?) updated
                FROM   pm_aliases
                WHERE  id = ?';

        $val = array($this->date_format, $this->date_format, $this->id);

        xclass::Load($sql, $val);

        $this->mySite->Load($this->siteid);

        $this->XAMS_Log('Selection', "Selected Alias $this->leftpart @ Site ". $this->mySite->name);

        $this->Authenticate();
    }

    function Add()
    {
        $this->Authenticate();
        $result = xclass::Add();

        $this->add_addressbook();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Alias '%s' was added successfully."), $this->leftpart);
            $this->XAMS_Log("Insertion", "Added Alias $this->leftpart to Site ". $this->mySite->name);
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Alias '%s' could not be added."), $this->leftpart);
            $this->XAMS_Log("Insertion", "Failed adding Alias $this->leftpart to Site ". $this->mySite->name, "failed");
        }
    }

    function Update()
    {
        $result = xclass::Update();

        $this->add_addressbook();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Alias '%s' was updated successfully."), $this->leftpart);
            $this->XAMS_Log("Update", "Updated Alias $this->leftpart in Site ". $this->mySite->name);
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Alias '%s' could not be updated."), $this->leftpart);
            $this->XAMS_Log("Update", "Failed updating Alias $this->leftpart in Site ". $this->mySite->name, "failed");
        }
    }

    function Delete()
    {
        // Delete Alias
        $result = xclass::Delete();

        // Delete Addressbook of Alias
        $this->db->query('DELETE FROM pm_alias_info WHERE aliasid = ?', $this->id);

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("Alias '%s' was deleted successfully."), $this->leftpart);
            $this->XAMS_Log("Deletion", "Deleted Alias $this->leftpart in Site ". $this->mySite->name);
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("Alias '%s' could not be deleted."), $this->leftpart);
            $this->XAMS_Log("Deletion", "Failed deleting Alias $this->leftpart in Site ". $this->mySite->name, "failed");
        }
    }
}

?>
