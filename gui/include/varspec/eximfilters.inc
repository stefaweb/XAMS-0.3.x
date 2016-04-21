<?php

include_once 'include/varspec/const.inc';

$this->VSPEC = array(
    'userid' => array(
        'type' => Tpid,
        'autoid' => false
    ),
    'filter' => array(
        'type' => Talphanumeric,
        'empty' => true
    ),
    'active' => array(
        'type' => Tcheckbox,
        'init' => 'true'
    ),
    'added' => array(
        'type' => Tinsertdate
    )
);

?>