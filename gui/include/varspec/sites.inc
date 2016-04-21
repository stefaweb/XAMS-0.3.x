<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'id' => array(
        'type' => Tpid
    ),
    'resellerid' => array(
        'type' => Tid,
        'empty' => false 
    ),
    'name' => array(
        'type' => Talphanumeric,
        'minl' => 2,
        'maxl' => 100,
        'empty' => false
    ),
    'domainname' => array(
        'type' => Talphanumeric,
        'atype' => Tdummy,
        'minl' => 2,
        'empty' => true
    ),
    'quotaunit' => array(
        'type' => Tnumeric,
        'atype' => Tdummy,
        'init' => 1 // = MB
    ),
    'userquotaunit' => array(
        'type' => Tnumeric,
        'atype' => Tdummy,
        'init' => 1 // = MB
    ),
    'maxquota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'empty' => false
    ),
    'maxuserquota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'empty' => false
    ),
    'maxaddr' => array(
        'type' => Tnumeric,
        'min' => 0,
        'empty' => false
    ),
    'maxaliases' => array(
        'type' => Tnumeric,
        'min' => 0,
        'empty' => false
    ),
    'addrtype' => array(
        'type' => Tbitfield,
        'translation' => 'p,i,s,x',
        'init' => _POP | _SMTP | _XAMS | _IMAP,
        'empty' => true
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
    'sitestate' => array(
        'type' => Talphanumeric,
        'init' => 'default',
    ),
    'added' => array(
        'type' => Tinsertdate
    )
);

?>
