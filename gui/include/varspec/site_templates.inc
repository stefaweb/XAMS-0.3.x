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
        'null' => true,
        'empty' => true
    ),
    'maxuserquota' => array(
        'type' => Tnumeric,
        'min' => -1,
        'null' => true,
        'empty' => true
    ),
    'maxaddr' => array(
        'type' => Tnumeric,
        'null' => true,
        'empty' => true
    ),
    'maxaliases' => array(
        'type' => Tnumeric,
        'null' => true,
        'empty' => true
    ),
    'addrtype' => array(
        'type' => Tbitfield,
        'translation' => 'p,i,s,x',
        'init' => _SMTP | _POP | _IMAP | _XAMS,
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
    'leftpart1' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'rightpart1' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'bounceforward1' => array(
        'type' => Tcheckbox,
        'empty' => false
    ),
    'leftpart2' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'rightpart2' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'bounceforward2' => array(
        'type' => Tcheckbox,
        'empty' => false
    ),
    'leftpart3' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'rightpart3' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'bounceforward3' => array(
        'type' => Tcheckbox,
        'empty' => false
    ),
    'leftpart4' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'rightpart4' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'bounceforward4' => array(
        'type' => Tcheckbox,
        'empty' => false
    ),
    'leftpart5' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'rightpart5' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'bounceforward5' => array(
        'type' => Tcheckbox,
        'empty' => false
    ),
    'added' => array(
        'type' => Tinsertdate
    )
);

?>
