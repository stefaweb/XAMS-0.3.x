<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'id' => array(
        'type' => Tpid
    ),
    'siteid' => array(
        'type' => Tnumeric,
        'min' => 1,
	'empty' => true
    ),
    'leftpart' => array(
        'type' => Talphanumeric,
        'minl' => 1,
        'maxl' => 255,
        'empty' => false
    ),
    'rightpart' => array(
        'type' => Talphanumeric,
        'minl' => 1,
        'maxl' => 2000,
        'empty' => false
    ),
    'bounceforward' => array(
        'type' => Tcheckbox,
        'uncheckedvalue' => 'false',
        'init' => 'false'
    ),
    'blackhole' => array(
        'type' => Tcheckbox,
        'uncheckedvalue' => 'false',
        'init' => 'false'
    ),
    'added' => array(
        'type' => Tinsertdate
    )
);

?>
