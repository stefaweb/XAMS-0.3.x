<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'id' => array(
        'type' => Tpid
    ),
    'name' => array(
        'type' => Talphanumeric,
        'minl' => 2,
        'maxl' => 100,
        'empty' => false
    ),
    'password' => array(
        'type' => Tpassword,
        'minl' => 4,
        'maxl' => 100,
        'empty' => false
    ),
    'locked' => array(
        'type' => Tcheckbox,
        'init' => 'false'
    ),
    'maxcustomers' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'maxsites' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'maxdomains' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'maxusers' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'maxaliases' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'maxquota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'quotaunit' => array(
        'type' => Tnumeric,
        'atype' => Tdummy,
        'init' => 1 // = MB
    ),
    'maxsitequota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'sitequotaunit' => array(
        'type' => Tnumeric,
        'atype' => Tdummy,
        'init' => 1 // = MB
    ),
    'maxuserquota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true
    ),
    'userquotaunit' => array(
        'type' => Tnumeric,
        'atype' => Tdummy,
        'init' => 1 // = MB
    ),
    'viruscheckin' => array(
        'type' => Tcheckbox,
        'init' => 'true',
    ),
    'viruscheckout' => array(
        'type' => Tcheckbox,
        'init' => 'true',
    ),
    'spamcheckin' => array(
        'type' => Tcheckbox,
        'init' => 'true',
    ),
    'spamcheckout' => array(
        'type' => Tcheckbox,
        'init' => 'true',
    ),
    'spamscore' => array(
        'type' => Tnumeric,
        'min' => 1,
        'max' => 999,
        'init' => 5,
        'empty' => false
    ),
    'highspamscore' => array(
        'type' => Tnumeric,
        'min' => 1,
        'max' => 999,
        'init' => 15,
        'empty' => false
    ),     
    'added' => array(
        'type' => Tinsertdate
    )
);

?>