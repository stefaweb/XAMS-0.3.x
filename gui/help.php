<?php
    require 'gfl.php';
    require 'include/i18n.php';
    include 'include/xslclass.php';
    include_once 'include/global.php';
    header('Content-Type: text/html; charset=UTF-8');

    $help = addslashes(gget('help'));

    $tl = &i18n::singleton();
    $tl->LoadLngBase('std_menu');

    $file = 'i18n/'.$_SESSION['SESSION_LANGUAGE'].'/help/'.$help.'.xml';
    if (!file_exists($file)) {
        $file = "i18n/english/help/$help.xml";
    }

    $xml_trans = ['patterns' => ["'{{skindir}}'"], 'replacements' => [_SKIN]];

    $xsl_trans = [
        'patterns'     => ['/{{XAMS Online help}}/', '/{{Last modification}}/'],
        'replacements' => [$tl->get('XAMS Online help'), $tl->get('Last modification')],
        ];

    $myXSL = &xslclass::singleton();
    $myXSL->load_xsl(realpath(_SKIN.'/xsl/help.xsl'));
    $myXSL->load_xml(realpath($file));
    $myXSL->xml_replace($xml_trans);
    $myXSL->xsl_replace($xsl_trans);
    $myXSL->out();
