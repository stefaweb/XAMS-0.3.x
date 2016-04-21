<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'id' => array(
        'type' => Tpid
    ),
    'adminid' => array(
        'type' => Tnumeric,
        'empty' => true,
        'null' => true
    ),
    'resellerid' => array(
        'type' => Tnumeric,
        'empty' => true,
        'null' => true
    ),
    'customerid' => array(
        'type' => Tnumeric,
        'empty' => true,
        'null' => true
    ),
    'templatename' => array(
        'type' => Talphanumeric,
        'minl' => 2,
        'maxl' => 100,
        'empty' => false
    ),
    'name' => array(
        'type' => Talphanumeric,
        'minl' => 2,
        'maxl' => 100,
        'empty' => true
    ),
    'password' => array(
        'type' => Tpassword,
        'minl' => 5,
        'maxl' => 100,
        'empty' => true
    ),
    'quotaunit' => array(
        'type' => Tnumeric,
        'atype' => Tdummy,
        'init' => 1 // = MB
    ),
    'quota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true,
        'empty' => true
    ),
    'addrtype' => array(
        'type' => Tbitfield,
        'translation' => 'p,i,s,x',
        'init' => _POP | _SMTP | _XAMS | _IMAP,
        'empty' => true
    ),
    'viruscheckin' => array(
        'type' => Talphanumeric,
        'null' => true,
    ),
    'viruscheckout' => array(
        'type' => Talphanumeric,
        'null' => true,
    ),
    'spamcheckin' => array(
        'type' => Talphanumeric,
        'null' => true,
    ),
    'spamcheckout' => array(
        'type' => Talphanumeric,
        'null' => true,
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
    'relayonauth' => array(
        'type' => Tcheckbox,
        'init' => 'true'
    ),
    'relayoncheck' => array(
        'type' => Tcheckbox,
        'init' => 'false'
    ),
    'added' => array(
        'type' => Tinsertdate
    )
);

?>
