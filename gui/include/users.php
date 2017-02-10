<?php

include_once 'include/xclass.php';
include_once 'include/sites.php';
include_once 'include/customers.php';
include_once 'include/i18n.php';

class Users extends xclass
{

    var $objname = 'users';
    var $notice = null;
    var $Name;
    var $lngbase = 'user';
    var $sites = array();
    var $tablename = 'pm_users';

    function Users($init=true)
    {
        xclass::xclass($init);

        $this->mySite = new Sites();
        $this->myReseller =& $this->mySite->myReseller;

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

    // Check which actions the logged in user can perform on this user
    function Authenticate()
    {
        if ($this->authenticated) return $this->getAuthMode();
        switch (USERT)
        {
            case _ADMIN:
                $this->setAuthMode(_AUTH_ALL);
                break;
            case _RESELLER:
                $mode = (USERID === $this->mySite->myReseller->id) ? _AUTH_ALL : _AUTH_ADD;
                $this->setAuthMode($mode);
                break;
            case _CUSTOMER:
                $this->mySite->Load($this->siteid);
                $mode = (in_array(USERID, $this->myReseller->customers)) ? _AUTH_ALL : _AUTH_ADD;
                $this->setAuthMode($mode);
                break;
            case _USER:
                if ($this->id === USERID)
                    $this->setAuthMode(_AUTH_LOAD | _AUTH_UPDATE);
                // keep everything false
                break;
        }
        xclass::Authenticate(true);
        return $this->getAuthMode();
    }

    function CalcQuota($load)
    {
        if ($load)
        {
            if ($this->quota > 0)
                for ($this->quotaunit=0; $this->quotaunit < 3; $this->quotaunit++)
                    if ($this->quota % 1024 == 0) $this->quota /= 1024;
                    else break;
        }
        else
        {
            if ($this->quota > 0)
                for ($i=0; $i < $this->quotaunit; $i++) $this->quota *= 1024;
        }
    }

    // Add/Update/Delete Addressbook for user
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
            $this->db->query('DELETE FROM pm_user_info WHERE userid = ? AND infofieldid IN (!)', array($this->id, $idstr));
            $sth = $this->db->prepare('INSERT INTO pm_user_info VALUES (?, ?, ?)');
            foreach ($addressbook_ids as $id)
                if (!empty($this->{'addressbook_'. $id}))
                    $this->db->execute($sth, array($id, $this->id, $this->{'addressbook_'. $id}));
        }
    }

    function Load($id=false)
    {
        if ($id) $this->id = $id;
        if (empty($this->id)) die($this->objname. '->Load() - ERROR : Have no ID to load!');

        $sql = 'SELECT siteid, name, uniquename, quota, addrtype,
                       viruscheckin, viruscheckout,
                       spamcheckin, spamcheckout, spamscore, highspamscore, relayonauth,
                       autoreply, autoreplysubject, autoreplytext, relayoncheck, accountstate,
                       DATE_FORMAT(added, ?) added,
                       DATE_FORMAT(updated, ?) updated
                FROM   pm_users
                WHERE  id = ?';

        $val = array($this->date_format, $this->date_format, $this->id);

        xclass::Load($sql, $val);

        $this->mySite->Load($this->siteid);

        $this->ApplyQuota();

        $this->XAMS_Log("Selection", "Selected User $this->name");

        $this->Authenticate();
    }

    function LoadSite()
    {
        $this->mySite->Load($this->siteid);
    }

    // Quota-Check: May a new user added to the reseller/site
    function QC_AddUser()
    {
        if (!$this->mySite->id) return 4;
        elseif ($this->myReseller->FreeUsers() == 0) return 1;
        elseif ($this->mySite->FreeUsers() == 0) return 2;
        elseif ($this->mySite->FreeQuota() == 0) return 3;
        else return 0;
    }

    // Check if UniqueName already exist
    function UU_Check()
    {
        $c = (int)$this->db->getOne('SELECT COUNT(id) FROM pm_users WHERE UniqueName = ?', $this->uniquename);
        return ($c == 0);
    }

    // Check if UniqueName do not contain bad characters
    function RR_Check()
    {
        $c = !eregi("^[_a-z0-9-]+$",$this->uniquename);
        return ($c == 0);
    }

    // Check if Name do not contain bad characters 
    function R_Check()
    {
	$c = !eregi("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",$this->name);
        return ($c == 0);
    }

    // xclass::check_formular enhancement
    function check_formular($form_mode)
    {
        // Quota check
        if (!empty($this->siteid))
        {
            $this->LoadSite();
            $this->ApplyQuota();
        }

        // Check if users can be added to the reseller/site
        if ($this->mode == 'new')
        {
            $err = $this->QC_AddUser();
            $err_arr = array(null,
                             'Unfortunately no more users can be added. Reseller\'s User-Quota has reached.',
                             'Unfortunately no more users can be added. The site based User-Quota has reached.',
                             'Unfortunately no more users can be added. The site based Quota has reached.',
                             'Select a Site in the drop-down menu.');
            if ($err)
                $this->error_func('siteid', $this->i18n->get($err_arr[$err]));
        }

        if ($form_mode == 'new')
        {
            if (!$this->R_Check() && strlen($this->name) != 0 )
                $this->error_func('name', $this->i18n->get('Forbidden characters in username'));
	    $this->name = strtolower($this->name);

            if (!$this->UU_Check())
                $this->error_func('uniquename', $this->i18n->get('This unique username does already exists'));
            if (!$this->RR_Check() && strlen($this->uniquename) != 0 )
                $this->error_func('uniquename', $this->i18n->get('Forbidden characters in unique name'));
	    $this->uniquename = strtolower($this->uniquename);
        }
        else
        {
            if ($this->LoadedData['uniquename'] != $this->uniquename && !$this->UU_Check())
                $this->error_func('uniquename', $this->i18n->get('This unique username does already exists'));
        }

        // Check for AddrType
        for ($i=1; $i<=128; $i*=2)
        {
            if (($this->addrtype & $i) && !($this->mySite->addrtype & $i))
            {
                $this->error_func('addrtype', $this->i18n->get('Selected AddrType is not allowed.'));
                break;
            }
        }

        // Check for Viruscheck
        if (($this->viruscheckin == 'true') && ($this->mySite->viruscheckin != 'true'))
            $this->error_func('viruscheckin', $this->i18n->get('Site does not allow users having incoming viruscheck.'));
        if (($this->viruscheckout == 'true') && ($this->mySite->viruscheckout != 'true'))
            $this->error_func('viruscheckout', $this->i18n->get('Site does not allow users having outgoing viruscheck.'));

        // Check for Spamcheck
        if (($this->spamcheckin == 'true') && ($this->mySite->spamcheckin != 'true'))
            $this->error_func('spamcheckin', $this->i18n->get('Site does not allow users having incoming spamcheck.'));
        if (($this->spamcheckout == 'true') && ($this->mySite->spamcheckout != 'true'))
            $this->error_func('spamcheckout', $this->i18n->get('Site does not allow users having outgoing spamcheck.'));

        xclass::check_formular($form_mode);
    }

    function ApplyQuota()
    {
        // Apply the maximum amount of quota
        $free_quota = $this->mySite->FreeQuota();
        if ($free_quota < 0)
        {
            $this->VSPEC['quota']['min'] = -1;
            $this->VSPEC['quota']['max'] = null;
        }
        else
        {
            if (isset($this->mode) && $this->mode == 'update')
            {
                if ($this->quota <= $this->LoadedData['quota'])
                    $free_quota = $this->quota;
                else
                    $free_quota += $this->LoadedData['quota'];
            }
            $this->VSPEC['quota']['max'] = $free_quota;
        }
    }

    function Add()
    {
        $this->Authenticate();
	if (empty($this->id)) $this->notice = sprintf($this->i18n->get("User '%s' could not be added."), $this->id); 
        if (!empty($this->password)) $this->password = md5($this->password);
        $result = xclass::Add();

        $this->add_addressbook();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("User '%s' was added successfully."), $this->name);
            $this->XAMS_Log("Insertion", "Added User $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("User '%s' could not be added."), $this->name);
            $this->XAMS_Log("Insertion", "Failed adding User $this->name", "failed");
        }
    }

    function Update()
    {
        if (!empty($this->password)) $this->password = md5($this->password);
        $result = xclass::Update();

        $this->add_addressbook();

        if ($result)
        {
            $this->notice = sprintf($this->i18n->get("User '%s' was updated successfully."), $this->name);
            $this->XAMS_Log("Update", "Updated User $this->name");
        }
        else
        {
            $this->notice = sprintf($this->i18n->get("User '%s' could not be updated."), $this->name);
            $this->XAMS_Log("Update", "Failed updating User $this->name", "failed");
        }
    }

    function Delete()
    {
	// Delete Alias of User
	$res = $this->db->query('SELECT id, siteid, rightpart FROM pm_aliases WHERE siteid = ?', $this->siteid);
	while ($donnees = mysql_fetch_array($res->_res))
	{
	    $elements = explode(",", $donnees["rightpart"]);
	    $tmp = NULL;
	    $i = 0;
	    $flag = 0;
	    foreach ($elements as $line)
	    {
		$line = ltrim($line);
	    	if (count($elements) == 1 && strcmp($line, $this->name) == 0)
	    	$this->db->query('DELETE FROM pm_aliases WHERE id = ?', $donnees["id"]);
	    	else if (strcmp($line, $this->name) != 0)
	    	{
		    $tmp[$i++] = $line;
		    $flag = 1;
		}
	    }
	    if ($flag == 1)
	    $this->db->query('UPDATE pm_aliases SET rightpart = \'' . mysql_real_escape_string(implode(", ", $tmp)) . '\' WHERE id = ?', $donnees["id"]);
 	}

	// Delete User
	$result = xclass::Delete();

	// Delete Addressbook of User
	$this->db->query('DELETE FROM pm_user_info WHERE userid = ?', $this->id);

	if ($result)
	{
	    $this->notice = sprintf($this->i18n->get("User '%s' was deleted successfully."), $this->name);
	    $this->XAMS_Log("Deletion", "Deleted User $this->name");
    	}
	else
    	{
	    $this->notice = sprintf($this->i18n->get("User '%s' could not be updated."), $this->name);
	    $this->XAMS_Log("Deletion", "Failed deleting User $this->name", "failed");
    	}
    }
}
?>
