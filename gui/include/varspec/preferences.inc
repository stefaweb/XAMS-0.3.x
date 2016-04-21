<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'loginwelcome' => array(
        'type' => Talphanumeric,
        'maxl' => 50,
        'empty' => true
    ),
    'loglines' => array(
        'type' => Tnumeric,
        'min' => 1,
        'max' => 999,
        'init' => 13,
        'empty' => false
    ),
    'newversioncheck' => array(
        'type' => Tcheckbox
    ),
    'onlinenews' => array(
        'type' => Tcheckbox
    ),
    'lastversioncheck' => array(
        'type' => Tdatetime
    ),
    'lastnewscheck' => array(
        'type' => Tdatetime
    ),
    'defaultlanguage' => array(
        'type' => Talphanumeric,
        'minl' => 1,
        'maxl' => 30,
        'empty' => false
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
);
        
?>
