<?php

include_once 'include/eximfilters.php';
include_once 'include/users.php';

class filters extends SCFilters
{
    public function Filters($init = true)
    {
        SCFilters::SCFilters($init);

        $this->myUser = new Users();
    }

    // Check which actions the logged in user can perform on this user-filter
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
                if ($this->myUser->myReseller->id === USERID) {
                    $this->setAuthMode(_AUTH_ALL);
                }
                break;
            case _CUSTOMER:
                if (in_array(USERID, $this->myUser->myReseller->customers)) {
                    $this->setAuthMode(_AUTH_LOAD | _AUTH_UPDATE);
                }
                break;
            case _USER:
                // keep everything false
                break;
        }
        xclass::Authenticate(true);

        return $this->getAuthMode();
    }

    public function Load($id = false)
    {
        SCFilters::Load($id);
        $this->myUser->Load($id);
        if (!$this->myUser->isAuthLoad()) {
            die($this->i18n->get('Access denied.'));
        }
        $this->Authenticate();
    }

    public function Add()
    {
        $this->Authenticate();
        SCFilters::Add();
    }
}
