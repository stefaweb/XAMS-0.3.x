<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'siteid' => array(
        'type' => Tnumeric,
        'min' => 0
    ),
    'dnsid' => array(
        'type' => Tid
    ),
    'name' => array(
        'type' => Talphanumeric,
        'minl' => 2,
        'maxl' => 100,
        'empty' => false
    ),
    'oldname' => array(
        'type' => Talphanumeric,
        'atype' => Tdummy
    ),
    'added' => array(
        'type' => Tinsertdate
    )
);
        
?>